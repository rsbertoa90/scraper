<?php
require_once("sql/conection.php");

//clase plural. el construtor trae la lista completa.
class Categorias
{
  // Devuelve un array con todas las categorias, todas con la cantidad de registros en scrap que tiene cada una.
  static public function getAll()
  {
    $DB = conect();
    try {
      $qt='SELECT c.id as id, c.start_url, c.name AS name, COUNT(c.id) AS cantidad
                FROM categorias as c
                LEFT JOIN scrapes AS s ON s.categoria_id = c.id
                GROUP BY c.name;'
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


      $categorias[]=$categoria;
    }
    return $categorias;
  }

}



// clase individual
class Categoria
{

  public $id;
  public $name;
  public $start_url;
  public $cantidad_registros;




//dando un id trae el objeto categoria
public function getById($id)
{
    $DB = conect();
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
    $this->id=$result["id"];
    $this->name=$result["name"];
    }

    //verifica si hay registros asociados a una categoria.
    //lo usa solo la funcion borrar
    private function tieneHijos()
    {
      $DB = conect();
      $queryText = 'SELECT * FROM scrapes WHERE categoria_id = :id';
      try {
        $query = $DB->prepare($queryText);
        $query->bindValue(':id',$this->id,PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
      }
      return ($result);
    }

    //elimina una categoria de la tabla
    //lo usa solo la funcion  borrar
    private function deleteCategoria()
    {
      $DB = conect();
      $queryText='DELETE FROM categorias WHERE id = :id';
      try {
        $DB->beginTransaction();

        $query = $DB->prepare($queryText);
        $query->bindValue(':id',$this->id,PDO::PARAM_INT);
        $query->execute();

        $DB->commit();
      } catch (PDOException $e) {
        $DB->rollBack();
        return $e->getMessage();
      }return true;
    }


    // verifica si la categoria tiene registros asociados. si no los tiene,, la elimina de la tabla.
    public function borrar()
    {
      if ($this->tieneHijos() ){
        return "No puedo eliminar una categoria que tiene registros asociados. primero elmine dichos registros o reasignelos en otra categoria";
      }else {
        $this->deleteCategoria();
        return "";
      }
    }



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


    // recibe como parametro la base de datos y una categoria.
    // devuelve una categoria si existe, un objeto vacio si no.
    private function existeCategoria()
    {
      $DB = conect();
      $queryText = 'SELECT id FROM categorias WHERE TRIM(name) like :categoria ';
      try {
        $query = $DB->prepare($queryText);
        $query->bindValue(":categoria",trim($this->name),PDO::PARAM_STR);
        $query->execute();
        $categoria = $query->fetchColumn(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
        echo $e->getMessage();
        exit;
      }
      return ($categoria["id"]);
    }

    // hace un insert de nueva categoria en la tabla categorias
    private function insertCategoria()
    {
      $DB = conect();
      $queryText='INSERT INTO categorias(name,start_url) VALUES (:categoria,:url)';
      try {
        $DB->beginTransaction();

        $query = $DB->prepare($queryText);
        $query->bindValue(':categoria',$this->name,PDO::PARAM_STR);
        $query->bindValue(':url',$this->start_url,PDO::PARAM_STR);
        $query->execute();
        $DB->commit();
      } catch (PDOException $e) {
        return $e->getMessage();
        $DB->rollback();
      }return true;
    }

    // verifica si existe la categoria. Si no existe, la inserta en la tabla.
    public function guardar()
    {
      if( ! $this->existeCategoria() ){
        $this->insertCategoria();
        return "";
      } else {
          return "Ya existe una categoria con ese nombre";
      }
    }



}




 ?>
