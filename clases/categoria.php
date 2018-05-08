<?php
require_once("DataBase.php");

//clase plural. el construtor trae la lista completa.
abstract class Categorias
{




  //dando un id trae el objeto categoria
  public static function getById($id)
  {
      $DB = DataBase::conect();
      try
      {
        $qt = "SELECT id,name from categorias WHERE id = :id";
        $query = $DB->prepare($qt);
        $query->bindValue(":id",$id,PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
      }
      catch (PDOException $e)
      {
        return $e->getMessage();
      }
      $categoria = new Categoria();
      $categoria->id=$result["id"];
      $categoria->name=$result["name"];
      return $categoria;
  }



  // Devuelve un array con todas las categorias, todas con la cantidad de registros en scrap que tiene cada una.
  static public function getAll()
  {
    $DB = DataBase::conect();
    try
    {

      $qt='SELECT c.categoria_id as id, c.start_url, c.nombre AS name,c.productos AS cantidad,
      DATE_FORMAT(c.last_insert,"%d/%m/%Y" ) as last_insert
                FROM cache_categorias as c
                ORDER BY c.last_insert desc,cantidad desc'
                ;
      $query = $DB->prepare($qt);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);

    }
    catch (PDOException $e)
    {
      return $e->getMessage();
    }
    $categorias =[];
    foreach ($result as $row) {

      $categoria = new Categoria();

      $categoria->id=$row["id"];
      $categoria->name=$row["name"];
      $categoria->start_url=$row["start_url"];
      $categoria->cantidad_registros=$row["cantidad"];
      $categoria->last_insert=$row["last_insert"];


      $categorias[]=$categoria;
    }
    return $categorias;
  }

//refresca el cache de todas las categorias
  static public function heatCache($id)
  {
    $DB = DataBase::conect();
    $queryText = "CALL heat_categoria_cache(:id)";
    try
    {
      $DB->beginTransaction();
      $query = $DB->prepare($queryText);
      $query->bindValue(':id',$id,PDO::PARAM_INT);
      $query->execute();
      $DB->commit();
    } catch (PDOException $e) {
      $DB->rollBack();
      return $e->getMessage();
    }

  }

  //verifica si hay registros asociados a una categoria.
  //lo usa solo la funcion borrar
 static private function tieneHijos($categoria)
  {
    $DB = DataBase::conect();
    $queryText = 'SELECT * FROM scrapes WHERE categoria_id = :id';
    try {
      $query = $DB->prepare($queryText);
      $query->bindValue(':id',$categoria->id,PDO::PARAM_INT);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
      exit;
    }
    return ($result);
  }


  // verifica si la categoria tiene registros asociados. si no los tiene,, la elimina de la tabla.
  static public function borrar($categoria)
  {
    if (self::tieneHijos($categoria) ){
      return "No puedo eliminar una categoria que tiene registros asociados. primero elmine dichos registros o reasignelos en otra categoria";
    }else {
      self::deleteCategoria($categoria);
      self::heatCache($categoria->id);
      return "";
    }
  }

  //elimina una categoria de la tabla
  //lo usa solo la funcion  borrar
  private static function deleteCategoria($categoria)
  {
    $DB = DataBase::conect();
    $queryText=' CALL delete_categoria(:id) ';
    try {
      $DB->beginTransaction();

      $query = $DB->prepare($queryText);
      $query->bindValue(':id',$categoria->id,PDO::PARAM_INT);
      $query->execute();

      $DB->commit();
    } catch (PDOException $e) {
      $DB->rollBack();
      return $e->getMessage();
    }
  }


  // recibe como parametro la base de datos y una categoria.
  // devuelve una categoria si existe, un objeto vacio si no.
  private static function existeCategoria($categoria)
  {
    $DB = DataBase::conect();
    $queryText = 'SELECT id FROM categorias WHERE TRIM(name) like :categoria ';
    try {
      $query = $DB->prepare($queryText);
      $query->bindValue(":categoria",trim($categoria->name),PDO::PARAM_STR);
      $query->execute();
      $categoria = $query->fetchColumn(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      echo $e->getMessage();
      exit;
    }
    return ($categoria["id"]);
  }

  // hace un insert de nueva categoria en la tabla categorias
  private static function insertCategoria($categoria)
  {
    $DB = DataBase::conect();
    $queryText='INSERT INTO categorias(name,start_url) VALUES (:categoria,:url)';
    try {
      $DB->beginTransaction();

      $query = $DB->prepare($queryText);
      $query->bindValue(':categoria',$categoria->name,PDO::PARAM_STR);
      $query->bindValue(':url',$categoria->start_url,PDO::PARAM_STR);
      $query->execute();
      $DB->commit();
    } catch (PDOException $e) {
      return $e->getMessage();
      $DB->rollback();
    }
    self::heatCache($DB->lastInsertId());
  }

  // verifica si existe la categoria. Si no existe, la inserta en la tabla.
  public static function guardar($categoria)
  {
    if( ! self::existeCategoria($categoria) ){
      self::insertCategoria($categoria);
      return "";
    } else {
        return "Ya existe una categoria con ese nombre";
    }
  }

}



// clase individual
class Categoria
{

  public $id;
  public $name;
  public $start_url;
  public $cantidad_registros;
  public $last_insert;







    //connstructor
      public function __construct($id=0)
      {
        if($id)
        {
          $this->id=$id;
        }else
        {
          $this->id = $id;
        }
          $this->name="";

      }

    //actualizar
    private function update()
    {
      $DB = new conection();
      try {
        $DB->beginTransaction();

        $qt="UPDATE categorias SET name = :name WHERE id=:id";
        $query = $DB->prepare($qt);
        $query->bindValue(":name",$this->name,PDO::PARAM_STR);
        $query->bindValue(":id",$this->id,PDO::PARAM_INT);
        $query->execute();
        $DB->commit();
      }
      catch (PDOException $e)
      {
        $DB->rollback();
        return $e->getMessage();
      }
    }





}




 ?>
