<?php
// conecta a la base de datos
function connect(){
  $dsn='mysql:host=localhost;dbname=scraper;charset=utf8;port:3306';
  $dbuser='root';
  $dbpass='rodrigo';
  $opt = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
  try {
      $db = new PDO($dsn,$dbuser,$dbpass, $opt);
    //$mysqli = new mysqli("localhost","root","rodrigo","scraper");
  } catch (PDOException $e) {
      echo $e->getMessage();exit;
  }return $db;
}







// recibe como parametro la base de datos y una categoria.
// devuelve una categoria si existe, un objeto vacio si no.
function existeCategoria($categoria){
  $DB = connect();
  $categoria = trim($categoria);
  $queryText = 'SELECT id FROM categorias WHERE TRIM(name) like :categoria ';
  try {
    $query = $DB->prepare($queryText);
    $query->bindValue(":categoria",$categoria,PDO::PARAM_STR);
    $query->execute();
    $categorias = $query->fetchColumn(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    echo $e->getMessage();
    exit;
  }
  return ($categorias);
}

// hace un insert de nueva categoria en la tabla categorias
function insertCategoria($categoria){
  $DB = connect();
  $queryText='INSERT INTO categorias(name) VALUES (:categoria)';
  try {
    $DB->beginTransaction();

    $query = $DB->prepare($queryText);
    $query->bindValue(':categoria',$categoria,PDO::PARAM_STR);
    $query->execute();

    $DB->commit();
  } catch (PDOException $e) {
    return $e->getMessage();
    $DB->rollback();
  }return true;
}

// verifica si existe la categoria. Si no existe, la inserta en la tabla.
function nuevaCategoria($categoria){
  $DB = connect();
  if( ! existeCategoria($categoria) ){
    insertCategoria($categoria);
    return "";
  } else {
      return "Ya existe una categoria con ese nombre";
  }
}

//verifica si hay registros asociados a una categoria.
function tieneHijos($id){
  $DB = connect();
  $queryText = 'SELECT * FROM scrapes WHERE categoria_id = :id';
  try {
    $query = $DB->prepare($queryText);
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    echo $e->getMessage();
    exit;
  }
  return ($result);
}

//elimina una categoria de la tabla
function deleteCategoria($id){
  $DB = connect();
  $queryText='DELETE FROM categorias WHERE id = :id';
  try {
    $DB->beginTransaction();

    $query = $DB->prepare($queryText);
    $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();

    $DB->commit();
  } catch (PDOException $e) {
    $DB->rollBack();
    return $e->getMessage();
  }return true;
}


// verifica si la categoria tiene registros asociados. si no los tiene,, la elimina de la tabla.
function borrarCategoria($id){
  if (tieneHijos($id) ){
    return "No puedo eliminar una categoria que tiene registros asociados. primero elmine dichos registros o reasignelos en otra categoria";
  }else {
    deleteCategoria($id);
    return "";
  }
}

// valida que no haya errores con el archivo
function validarImport($data){
  if ($data["categoria"]=="NULL"){
    return "por favor elige una categoria";
  }elseif (! isset($_FILES["archivo"]) || !trim($_FILES["archivo"]["name"])){
    return "no se selecciono ningun archivo";
  }else{
    $file = $_FILES["archivo"];
    if ($file["error"] != UPLOAD_ERR_OK){
      return "error al subir el archivo ->".$file['error'];
    }else{
    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
      if($ext != "csv"){
        return "El archivo debe tener extension csv";
      }
    }
  }return "";
}


// importa un CSV a la base de datos de scraps.
function scrapImports($categoria){
  $DB = connect();

  require_once("importQuery.php");
  $queryes = importQuery();

  try {
    $DB->beginTransaction();

    foreach($queryes as $queryText) {
      $query= $DB->prepare($queryText);
      if (strpos($queryText,':cat_param')){
        $query->bindValue(':cat_param',$categoria,PDO::PARAM_INT);
      }
      $query->execute();
    }
    $DB->commit();
  } catch (PDOException $e) {
    $DB->rollBack();
    return "ERROR EN MULTIQUERY: --".$e->getMessage();
  }
  return '';
}

//recibe un id de categoria, devuelve el nombre.
function nombreCategoria($id){
  global $categorias;
  foreach ($categorias as $categoria) {
    if($categoria["id"]==$id){
      return $categoria["name"];
    }
  }
}
// devuelve todas las categorias en un array, y la cantidad de productos en cada una.
//recibe como parametro la conecccion a la BD

function categorias(){
  $DB = connect();
  $queryText='SELECT c.id as id, c.name AS name, COUNT(c.id) AS cantidad
            FROM categorias as c
            LEFT JOIN scrapes AS s ON s.categoria_id = c.id
            GROUP BY c.name;'
            ;

  try {
    $query = $DB->prepare($queryText);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    echo $e->getMessage();
    exit;
  }return $result;
}

function totalScrapes($cats){
  $result=0;
  foreach ($cats as $c ) {
    $result+=$c["cantidad"];
  }
  return $result;
}


//devuelve una lista de lo que mas vendio en el periodo mas amplio de tiempo que se pueda registrar
//
function bestSellers($cantidad , $criterio, $categoria_id = 0){
  $DB=connect();

$queryText = 'SELECT
  product_id,
	c.name AS categoria,
	title AS titulo,
  url,
    price AS precio,
    location AS localidad,
    DATE_FORMAT(MIN(data_date), "%d/%m/%Y") as inicio_periodo,
    DATE_FORMAT(MAX(data_date), "%d/%m/%Y") as fin_periodo,
       MAX(sells) - MIN(sells) AS ventas_en_periodo,
      CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date))," dias")  AS periodo_en_dias,
    (MAX(sells) - MIN(sells)) * price AS dinero_movido
FROM scrapes AS s
INNER JOIN categorias AS c ON s.categoria_id = c.id'
;
if ($categoria_id){
  $queryText .= ' WHERE c.id = :c_id ';
}

$queryText.='
GROUP BY product_id HAVING COUNT(product_id) > 1 AND periodo_en_dias > 0'
;

if (trim($criterio)=="vendidos"){
  $queryText.=' ORDER BY ventas_en_periodo desc, dinero_movido desc ';
}else{
  $queryText.=' ORDER BY dinero_movido desc, ventas_en_periodo desc ';
}

$queryText.= ' LIMIT :cantidad; ';



  try {
    $query= $DB->prepare($queryText);
    $query->bindValue(':cantidad',$cantidad,PDO::PARAM_INT);
    if($categoria_id){
      $query->bindValue(':c_id',$categoria_id,PDO::PARAM_INT);
    }
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    return $result;
  } catch (PDOException $e) {
    return "ERROR --> ".$e->getMessage();
  }
}

//Traigo el historico de un product_id
function historico($product_id){
  $DB = connect();
  $queryText='SELECT DATE_FORMAT(s.data_date,"%d/%m/%Y" ) AS fecha_insert,
  c.name AS categoria,
  s.url ,
  s.title AS titulo,
  s.price AS precio,
  s.sells AS ventas,
  s.location AS localidad
  FROM scrapes s
  INNER JOIN categorias AS c ON c.id=s.categoria_id
  WHERE trim(product_id) LIKE trim(:product_id_param)
  ORDER BY data_date';

  try {
      $query = $DB->prepare($queryText);
      $query->bindValue(':product_id_param',$product_id,PDO::PARAM_STR);
      $query->execute();
      $result= $query->fetchAll(PDO::FETCH_ASSOC);
      return $result;
  } catch (PDOException $e) {
    return $e->getMessage();
  }

}


// Traigo los totales de vendidos y dinero movido de un product_id
function totales($product_id){
  $DB=connect();

  $queryText = 'SELECT
      product_id,
      MAX(sells) - MIN(sells) AS ventas,
      CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date))," dias")  AS dias,
      (MAX(sells) - MIN(sells)) * price AS dinero_movido
  FROM scrapes AS s
  where s.product_id = :product_id_param
  GROUP BY product_id HAVING COUNT(product_id) > 1;';

  try {
    $query= $DB->prepare($queryText);
    $query->bindValue(':product_id_param',$product_id,PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result;
  } catch (PDOException $e) {
    return "ERROR --> ".$e->getMessage();
  }
}




// busco los resultados de todos los productos que coincidan en titulo con lo buscado
// ordenado por dinero movido, y con limite de 30;

function search($search,$min,$max,$criterio){

$searchArray = preg_split('/\s+/', $search);

  $DB=connect();


  $queryText="SELECT
  product_id,
  url,
	c.name AS categoria,
	title AS titulo,
    price AS precio,
    location AS localidad,
    DATE_FORMAT(MIN(data_date), '%d/%m/%Y') as inicio_periodo,
    DATE_FORMAT(MAX(data_date), '%d/%m/%Y') as fin_periodo,
       MAX(sells) - MIN(sells) AS ventas_en_periodo,
      CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date)),' dias')  AS periodo_en_dias,
    (MAX(sells) - MIN(sells)) * price AS dinero_movido
FROM scrapes AS s
INNER JOIN categorias AS c ON s.categoria_id = c.id

WHERE price BETWEEN :min AND :max ";

foreach ($searchArray as $key => $value) {
  $queryText.=" AND title like concat('%',:$key,'%') ";
}

$queryText.=" GROUP BY product_id HAVING COUNT(product_id) > 1 AND periodo_en_dias > 0 ";

if($criterio="vendidos"){
$queryText.="  ORDER BY ventas_en_periodo desc, dinero_movido desc";
}else{
  $queryText.="  ORDER BY dinero_movido desc, ventas_en_periodo desc";
}
  $queryText.="  limit 30;"
;



try {
    $query= $DB->prepare($queryText);

    foreach ($searchArray as $key => $value) {
      $query->bindValue(":$key",$value,PDO::PARAM_STR);
    }

    $query->bindValue(':min',$min,PDO::PARAM_INT);
    $query->bindValue(':max',$max,PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    return $result;
    } catch (PDOException $e) {
    return "ERROR --> ".$e->getMessage();
    }

}

function share(){
  $DB = connect();
  $queryText[] = "SET SQL_SAFE_UPDATES = 0;";
  $queryText[]=  "UPDATE scrapes AS s
    LEFT JOIN (SELECT DISTINCT product_id,url,subcategoria_id FROM scrapes WHERE url IS NOT NULL ) AS a
    ON s.product_id = a.product_id
    SET s.url = a.url, s.subcategoria_id = a.subcategoria_id
    WHERE s.product_id = a.product_id;";
  $queryText[]="SET SQL_SAFE_UPDATES = 1;";
  try {
    $DB->beginTransaction();
    foreach ($queryText as $qt) {
      $query = $DB->prepare($qt);
      $query->execute();
    }
    $DB->commit();
    return "";
  } catch (PDOException $e) {
    $DB->rollBack();
    return $e->getMessage();
  }
}
 ?>
