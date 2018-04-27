<?php

//clase plural. el construtor trae la lista completa.
class subsubcategorias{
  private $lista;

  public function getLista(){return $this->lista;}

  private function getAll(){
    $DB = new connection();
    try {
      $qt = "SELECT id,name from subsubcategorias" ;
      $query = $DB->prepare($qt);
      $query->execute();
      $result = $query->fetchAll(PDO::FETCH_CLASS, 'subcategoria');
      return $result;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }

  public __construt(){
    $this->lista = $this->getAll();
  }


}

// clase individual
class subcategoria
{
  private $id;
  private $categoria_id
  public $name;

//dando un id trae el objeto categoria
private function getById($id){
    $DB = new connection();
    try {
      $qt = "SELECT id,name from subsubcategorias WHERE id = :id";
      $query = $DB->prepare($qt);
      $query->bindValue(":id",$id,PDO::PARAM_INT);
      $query->execute();
      $result = $query->fetch(PDO::FETCH_CLASS,"subcategoria");
      return $result;
    } catch (PDOException $e) {
      return $e->getMessage();
    }
  }


  public function __construct($id=0)
  {
    if($id){
      $this = $this->getById($id);
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

//insertar nueva categoria
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
