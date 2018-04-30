<?php

//clase plural. el construtor trae la lista completa.
class subcategorias{

  public function getAll(){
    $DB = new connection();
    try {
      $qt = "SELECT id,categoria_id,name from subcategorias" ;
      $query = $DB->prepare($qt);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_ASSOC);
      $return = [];
      foreach ($result as $subcategoria) {
        $return[] = new subcategoria($result["id"]);
      }
      return $result;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

}


// clase individual
class subcategoria
{
  private $id;
  public $name;
  public $categoria;



//dando un id trae el objeto subcategoria
private function getById($id){
    $DB = new connection();
    try {
      $qt = "SELECT id,categoria_id,name from subcategorias WHERE id = :id";
      $query = $DB->prepare($qt);
      $query->bindValue(":id",$id,PDO::PARAM_INT);
      $query->execute();
      $result = $query->fetch(PDO::FETCH_ASSOC);

      $this->id=$result["id"];
      $this->name=$result["name"];
      $this->categoria = new categoria($result["categoria_id"]);

    } catch (PDOException $e) {
      echo $e->getMessage(); exit;
    }
  }


  public function __construct($id=0)
  {
    if($id){
      getById($id);
    }else{
      $this->id = $id;
      $this->name="";
    }
  }

//actualizar
private function update(){
  $DB = new conection();
  try {
    $DB->beginTransaction();

    $qt="UPDATE subcategorias SET name = :name WHERE id=:id";
    $query = $DB->prepare($qt);
    $query->bindValue(":name",$this->name,PDO::PARAM_STR);
    $query->bindValue(":id",$this->id,PDO::PARAM_INT);
    $query->execute();
    $DB->commit();
  } catch (PDOException $e) {
    $DB->rollback();
    return $e->getMessage();
  }
}

//insertar nueva subcategoria
private function insert(){
  $DB = new conection();
  try {
    $DB->beginTransaction();

    $qt="INSERT INTO subcategorias(name) VALUES(:name)";
    $query = $DB->prepare($qt);
    $query->bindValue(":name",$this->name,PDO::PARAM_STR);
    $query->execute();
    $DB->commit();
  } catch (PDOException $e) {
    $DB->rollback();
    return $e->getMessage();
  }
}

// si tiene id definido hace un update, sino, hace un insert.
  public function guardar(){
    if($this->id){
      $this->update();
    }else{
      $this->insert();
    }
  }

}




 ?>
