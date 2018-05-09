<?php
  require_once("categoria.php");
  require_once("subcategoria.php");
  require_once("DataBase.php");


  abstract class productos

  {

    static public $criterio_ordenamiento = "dinero_movido";
    static public $precio_minimo = 0;
    static public $precio_maximo = 999999;



    static public function setPrecioMaximo($precio){
      self::$precio_maximo=$precio;
    }

    static public function setPrecioMinimo($precio){
      self::$precio_minimo=$precio;
    }

    //setter criterio
    static public function setCriterio($criterio)
    {
      if ( in_array($criterio,["dinero_movido","ventas_periodo"]) )
      {
        self::$criterio_ordenamiento = $criterio;
      }
    }


    static public function favoritos()
    {
      $DB=DataBase::conect();


      $queryText="SELECT
          s.product_id,
          url,
          s.categoria_id AS categoria,
          s.title AS titulo,
            s.price AS precio,
            s.location AS localidad,
            DATE_FORMAT(MIN(s.data_date), '%d/%m/%Y') as inicio_periodo,
            DATE_FORMAT(MAX(s.data_date), '%d/%m/%Y') as fin_periodo,
              CAST( (MAX(s.sells) - MIN(s.sells)) AS UNSIGNED) AS ventas_periodo,
              CONCAT(TIMESTAMPDIFF(DAY, MIN(s.data_date), MAX(s.data_date)),' dias')  AS dias_periodo,
            CAST( ( (MAX(sells) - MIN(sells)) * price) AS UNSIGNED) AS dinero_movido,
            1 as favorito
        FROM scrapes AS s
        INNER JOIN favoritos as f ON f.product_id = s.product_id
        GROUP BY s.product_id HAVING COUNT(s.product_id) > 1 AND dias_periodo > 0 ";
      if(self::$criterio_ordenamiento=="ventas_periodo"){ $queryText.="  ORDER BY ventas_periodo desc, dinero_movido desc"; }
      else{ $queryText.="  ORDER BY dinero_movido desc, ventas_periodo desc"; }

      try
      {
          $query= $DB->prepare($queryText);
          $query->execute();
          $result = $query->fetchAll(PDO::FETCH_ASSOC);
      }
      catch (PDOException $e) {return "ERROR --> ".$e->getMessage();}

      $favoritos = [];
      foreach ($result as $row)
      {
          $producto = new Producto();
          $producto->loadFromArray($row);
          $favoritos[]=$producto;
      }
      return $favoritos;
    }

    // Tomo mejores vendidos por categoria de la cache.
    static public function bestSellers(int $categoria_id)
    {
      $DB=DataBase::conect();

      $queryText = "SELECT product_id,categoria_id as categoria,url, localidad, titulo, precio, inicio_periodo, fin_periodo,dias_periodo, ventas_periodo, dinero_movido,favorito
      FROM cache_bestSellers
      WHERE criterio = :criterio
      AND categoria_id = :categoria
      "
      ;

      if (trim(self::$criterio_ordenamiento)=="ventas_periodo"){
        $queryText.=' ORDER BY ventas_periodo desc, dinero_movido desc ';
      }else{
        $queryText.=' ORDER BY dinero_movido desc, ventas_periodo desc ';
      }

        try
        {
          $query= $DB->prepare($queryText);
          $query->bindValue(':criterio',self::$criterio_ordenamiento,PDO::PARAM_STR);
          $query->bindValue(':categoria',$categoria_id,PDO::PARAM_INT);

          $query->execute();
          $result = $query->fetchAll(PDO::FETCH_ASSOC);

        }
        catch (PDOException $e)
        {
          return "ERROR --> ".$e->getMessage();
        }
        $bestSellers=[];
        foreach ($result as $row)
        {
          $producto = new Producto();
          $producto->loadFromArray($row);
          $bestSellers[]=$producto;
        }
        return $bestSellers;
    }


    // busco los resultados de todos los productos que coincidan en titulo con lo buscado
    // ordenado por dinero movido, y con limite de 30;
    static public function search($search)
    {

      $searchArray = preg_split('/\s+/', $search);

      $DB=DataBase::conect();


      $queryText="SELECT
          s.product_id,
          url,
        	s.categoria_id AS categoria,
        	title AS titulo,
            price AS precio,
            location AS localidad,
            DATE_FORMAT(MIN(s.data_date), '%d/%m/%Y') as inicio_periodo,
            DATE_FORMAT(MAX(s.data_date), '%d/%m/%Y') as fin_periodo,
              CAST( (MAX(sells) - MIN(sells)) AS UNSIGNED) AS ventas_periodo,
              CONCAT(TIMESTAMPDIFF(DAY, MIN(s.data_date), MAX(s.data_date)),' dias')  AS dias_periodo,
            CAST( ( (MAX(sells) - MIN(sells)) * price) AS UNSIGNED) AS dinero_movido,
            (if (f.id is not null, 1 , 0 ) ) as favorito
        FROM scrapes AS s
        LEFT JOIN favoritos as f ON f.product_id = s.product_id
        WHERE price BETWEEN :min AND :max ";

      foreach ($searchArray as $key => $value) {$queryText.=" AND title like concat('%',:{$key},'%') "; }

      $queryText.=" GROUP BY s.product_id HAVING COUNT(s.product_id) > 1 AND dias_periodo > 0 ";

      if(self::$criterio_ordenamiento=="ventas_periodo"){ $queryText.="  ORDER BY ventas_periodo desc, dinero_movido desc"; }
      else{ $queryText.="  ORDER BY dinero_movido desc, ventas_periodo desc"; }

      $queryText.="  limit 30;"  ;

      try
      {
          $query= $DB->prepare($queryText);

          foreach ($searchArray as $key => $value) {
            $query->bindValue(":{$key}",$value,PDO::PARAM_STR);
          }

          $query->bindValue(':min',self::$precio_minimo,PDO::PARAM_INT);
          $query->bindValue(':max',self::$precio_maximo,PDO::PARAM_INT);

          $query->execute();
          $result = $query->fetchAll(PDO::FETCH_ASSOC);
          echo "<pre>";
          // var_dump($result);exit;
          echo "</pre>";
      }
      catch (PDOException $e) {return "ERROR --> ".$e->getMessage();}

      $searchResults = [];
      foreach ($result as $row)
      {
        $producto = new Producto();
        $producto->loadFromArray($row);
        $searchResults[]=$producto;
      }
      return $searchResults;
    }



    // devuelve un array con querys sql para importar el temporal.csv
    private function importQuery(){

      $importQuery=[];
      // --borro tabla temporal si existe
      //--  borro tabla temporal si existeproducto
      $importQuery[] ="DROP TABLE IF EXISTS temporal;";



    //web-scraper-order	web-scraper-start-url	categroias	categroias-href	paginacion	paginacion-href	titulo	product_id	url	precio	vendidos

      $importQuery[] ="CREATE TABLE temporal (
      					             worder varchar(30),
                            start_url varchar(200),
                            subcategoria varchar(200),
                            categorias_href varchar(200),
                            pagination varchar(200),
                            paginationhref varchar(200),
                            titulo varchar(200),
                            product_id varchar(50),
                            url varchar(400),
                            precio varchar (10),
                            vendidos varchar(200)
      					  );"
                  ;


      //--  importo datos del csv crudo del scrapper
      $importQuery[] ="LOAD DATA INFILE '/opt/lampp/htdocs/scraper-test/imports/temp-import.csv'
      INTO TABLE temporal
      CHARACTER SET UTF8
      FIELDS TERMINATED BY ','
      ENCLOSED BY '\"'
      IGNORE 1 LINES;";

     // llamo procedimiento alojado en la base de datos. Limpia los datos en temporal e inserta en tabla scrapes.
      $importQuery[]="CALL temporal_clean_and_insert();";

      // var_dump($importQuery);exit;
     return $importQuery;
      }
    // valida que no haya errores con el archivo. Si no los hay, importa el csv a la base de datos.
    // devuelve un string vacio si sale todo bien,cat o un mensaje de error si pasa algo maaaalo.
    static public function importar($archivo)
    {
      if (! isset($archivo) || !trim($archivo["name"]))
      {
        return "no se selecciono ningun archivo";
      }
      else
      {
        if ($archivo["error"] != UPLOAD_ERR_OK)
        {
          return "error al subir el archivo ->".$archivo['error'];
        }else
        {
          $ext = pathinfo($archivo["name"], PATHINFO_EXTENSION);
          if($ext != "csv"){ return "El archivo debe tener extension csv";  }
        }
      }
      $path = "imports/temp-import.csv";

      move_uploaded_file($archivo["tmp_name"], $path);

      $DB = DataBase::conect();


      $queryes = self::importQuery();

      try
      {
        $DB->beginTransaction();
        foreach($queryes as $queryText)
        {
            $query= $DB->prepare($queryText);
            $query->execute();
        }
        $DB->commit();
      }
      catch (PDOException $e)
      {
          $DB->rollBack();
          return "--- ERROR EN MULTIQUERY: --".$e->getMessage();
      }
      unlink("impo
  function getFrom($get,$from,$id)
  {

  }rts/temp-import.csv");
      return '';

    }


    // Toma los registros en scrapes que no tienen url, se fija si hay registros con el mismo product_id que si tengan y los que tienen les comparten a los que no.
    static public function share()
    {
      $DB = DataBase::conect();
      $queryText[] = "SET SQL_SAFE_UPDATES = 0;";
      $queryText[]=  "UPDATE scrapes AS s
        LEFT JOIN (SELECT DISTINCT product_id,url,subcategoria_id FROM scrapes WHERE url IS NOT NULL ) AS a
        ON s.product_id = a.product_id
        SET s.url = a.url, s.subcategoria_id = a.subcategoria_id
        WHERE s.product_id = a.product_id;";
      $queryText[]="SET SQL_SAFE_UPDATES = 1;";
      try
      {
        $DB->beginTransaction();
        foreach ($queryText as $qt)
        {
          $query = $DB->prepare($qt);
          $query->execute();
        }
        $DB->commit();
        return "";
      }
      catch (PDOException $e)
      {
      $DB->rollBack();
      return $e->getMessage();
      }
    }
  function getFrom($get,$from,$id)
  {

  }





    // devuelve el total de registros de la tabla scrapes
    static public function totales()
    {
      $DB=DataBase::conect();

      $queryText="SELECT total_scrapes,total_productos,total_categorias from cache_totales";

      try {
        $query = $DB->prepare($queryText);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;
      } catch (PDOException $e) {
        return $e->getMessage();
      }

    }






    //borra el ultimo insert

    public function deleteLastInsert()
    {
      $DB->DataBase::conect();
      $queryText="CALL delete_last_insert()";
      try
      {
        $DB->beginTransaction();
        $query=$DB->prepare($queryText);
        $query->execute();
        $DB->commit();
      }
      catch (PDOException $e)
      {
        $DB->rollBack();
        return $e->getMessage();
      }

    }



  }



  class producto
  {
    public $id;
    public $product_id;
    public $data_date;
    public $categoria;
    public $titulo;
    public $precio;
    public $ventas;
    public $localidad;
    public $subcategoria;
    public $url;
    public $fecha_insert;
    public $inicio_periodo;
    public $fin_periodo;
    public $dias_periodo;
    public $dinero_movido;
    public $ventas_total;
    public $ventas_periodo;
    public $EsFavorito=0;


    // doy un array con campos de producto y lo meto a un objeto productos.
    public function loadFromArray($array)
    {

      $this->product_id = $array["product_id"];
      $this->url = $array["url"];
      $this->categoria = new Categoria($array["categoria"]);
      $this->titulo = $array["titulo"];
      $this->precio = $array["precio"];
      $this->localidad  = $array["localidad"];
      $this->inicio_periodo = $array["inicio_periodo"];
      $this->fin_periodo = $array["fin_periodo"];
      $this->ventas_periodo= $array["ventas_periodo"];
      $this->dias_periodo = $array["dias_periodo"];
      $this->dinero_movido = $array["dinero_movido"];
      $this->EsFavorito = $array["favorito"];
    }

    public function __construct($product_id=0)
    {
      if($product_id){
          $this->product_id=$product_id;
      }else{
        $this->product_id=0;
      }
        $this->id="";
        $this->data_date="";
        $this->categoria="";
        $this->titulo="";
        $this->precio="";
        $this->ventas="";
        $this->localidad="";
        $this->subcategoria="";
        $this->url="";
        $this->fecha_insert="";
        $this->inicio_periodo="";
        $this->fin_periodo="";
        $this->dias_periodo="";
        $this->dinero_movido="";
        $this->ventas_total="";
        $this->ventas_periodo="";


      }
  // agregar producto a favoritos, o sacarlo si ya es.
      public function favorito()
      {
        $DB = DataBase::conect();
        try {
          $query = $DB->prepare("SELECT id FROM favoritos WHERE product_id = :id");
          $query->bindValue(":id",$this->product_id,PDO::PARAM_STR);
          $query->execute();
          $result=$query->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) { return $e->getMessage(); }
        if ($result["id"])
        {
          $this->quitarFavorito();
        }
        else
        {
          $this->agregarFavorito();
        }
    }

    private function agregarFavorito()
      {
        $DB=DataBase::conect();
        $this->EsFavorito = 1;
        try
        {
          $DB->beginTransaction();
          $query = $DB->prepare("INSERT INTO favoritos(product_id) VALUES (:id)");
          $query->bindValue(":id",$this->product_id,PDO::PARAM_STR);
          $query->execute();
          $DB->commit();
        }
        catch (PDOException $e) { $DB->rollBack(); return $e->getMessage(); }
        try
        {
          $DB->beginTransaction();
          $query = $DB->prepare("UPDATE cache_bestSellers SET favorito = 1 WHERE product_id = :id");
          $query->bindValue(":id",$this->product_id,PDO::PARAM_STR);
          $query->execute();
          $DB->commit();
        }
        catch (PDOException $e) { $DB->rollBack(); return $e->getMessage(); }

      }

      private function quitarFavorito()
      {
        $DB=DataBase::conect();
        $this->EsFavorito=0;
        try
        {
          $DB->beginTransaction();
          $query = $DB->prepare("DELETE FROM favoritos WHERE product_id=:id");
          $query->bindValue(":id",$this->product_id,PDO::PARAM_STR);
          $query->execute();
          $DB->commit();
        }
        catch (PDOException $e) { $DB->rollBack(); return $e->getMessage(); }
        try
        {
          $DB->beginTransaction();
          $query = $DB->prepare("UPDATE cache_bestSellers SET favorito=0 WHERE product_id = :id");
          $query->bindValue(":id",$this->product_id,PDO::PARAM_STR);
          $query->execute();
          $DB->commit();
        }
        catch (PDOException $e) { $DB->rollBack(); return $e->getMessage(); }


      }



      public function totales()
      {
        $DB=DataBase::conect();

        $queryText = 'SELECT
            product_id,
            MAX(sells) - MIN(sells) AS ventas_total,
            CONCAT(TIMESTAMPDIFF(DAY, MIN(s.data_date), MAX(s.data_date))," dias")  AS dias_periodo,
            (MAX(sells) - MIN(sells)) * price AS dinero_movido
        FROM scrapes AS s
        where s.product_id = :product_id_param
        GROUP BY product_id HAVING COUNT(product_id) > 1;';

        try {
          $query= $DB->prepare($queryText);
          $query->bindValue(':product_id_param',$this->product_id,PDO::PARAM_STR);
          $query->execute();
          $result = $query->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
          return "ERROR --> ".$e->getMessage();
        }
        $totales = new Producto();

        $totales->product_id = $result["product_id"];
        $totales->ventas_total = $result["ventas_total"];
        $totales->dias_periodo = $result["dias_periodo"];
        $totales->dinero_movido = $result["dinero_movido"];
        return $totales;
      }

      public function historicos()
      {
        $DB = DataBase::conect();
        $queryText='SELECT DATE_FORMAT(s.data_date,"%d/%m/%Y" ) AS fecha_insert,
        categoria_id ,
        s.url ,
        s.title AS titulo,
        s.price AS precio,
        s.sells AS ventas,
        s.location AS localidad
        FROM scrapes s
        WHERE trim(product_id) LIKE trim(:product_id_param)
        ORDER BY s.data_date';

        try {
            $query = $DB->prepare($queryText);
            $query->bindValue(':product_id_param',$this->product_id,PDO::PARAM_STR);
            $query->execute();
            $result= $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
          return $e->getMessage();
        }
        $historicos = [];
        foreach ($result as $row)
        {
          $producto = new Producto();

          $producto->fecha_insert = $row["fecha_insert"];
          $producto->categoria = Categorias::getById($row["categoria_id"]);
          $producto->titulo = $row["titulo"];
          $producto->precio = $row["precio"];
          $producto->ventas = $row["ventas"];
          $producto->localidad = $row["localidad"];
          $producto->url = $row["url"];
          $historicos[]=$producto;
        }
        return $historicos;

      }


  }
 ?>
