<?php
  require_once("categoria.php");
  require_once("subcategoria.php");
  require_once("sql/conection.php");


  class productos

  {

    private $criterio_ordenamiento = "dinero_movido";
    private $precio_minimo = 0;
    private $precio_maximo = 999999;



    public function getCriterio(){return $this->criterio_ordenamiento;}
    public function getPrecioMaximo(){return $this->precio_maximo;}
    public function getPrecioMinimo(){return $this->precio_minimo;}

    public function setPrecioMAximo($precio){$this->precio_maximo = $precio;}
    public function setPrecioMinimo($precio){$this->precio_minimo = $precio;}

    public function setCriterio($criterio)
    {
      if ( in_array($criterio,["dinero_movido","vendidos"]) )
      {
        $this->criterio_ordenamiento=$criterio;
      }
    }


    // busco los resultados de todos los productos que coincidan en titulo con lo buscado
    // ordenado por dinero movido, y con limite de 30;

    public function search($search)
    {

      $searchArray = preg_split('/\s+/', $search);

      $DB=conect();


      $queryText="SELECT
          product_id,
          url,
        	c.name AS categoria,
        	title AS titulo,
            price AS precio,
            location AS localidad,
            DATE_FORMAT(MIN(data_date), '%d/%m/%Y') as inicio_periodo,
            DATE_FORMAT(MAX(data_date), '%d/%m/%Y') as fin_periodo,
              CAST( (MAX(sells) - MIN(sells)) AS UNSIGNED) AS ventas_en_periodo,
              CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date)),' dias')  AS periodo_en_dias,
            CAST( ( (MAX(sells) - MIN(sells)) * price) AS UNSIGNED) AS dinero_movido
        FROM scrapes AS s
        INNER JOIN categorias AS c ON s.categoria_id = c.id

        WHERE price BETWEEN :min AND :max ";

      foreach ($searchArray as $key => $value) {$queryText.=" AND title like concat('%',:{$key},'%') "; }

      $queryText.=" GROUP BY product_id HAVING COUNT(product_id) > 1 AND periodo_en_dias > 0 ";

      if($this->criterio_ordenamiento=="vendidos"){ $queryText.="  ORDER BY ventas_en_periodo desc, dinero_movido desc"; }
      else{ $queryText.="  ORDER BY dinero_movido desc, ventas_en_periodo desc"; }

      $queryText.="  limit 30;"  ;

      // var_dump($queryText);exit;

      try
      {
          $query= $DB->prepare($queryText);

          foreach ($searchArray as $key => $value) {
            $query->bindValue(":{$key}",$value,PDO::PARAM_STR);
          }

          $query->bindValue(':min',$this->precio_minimo,PDO::PARAM_INT);
          $query->bindValue(':max',$this->precio_maximo,PDO::PARAM_INT);

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

          $producto->product_id = $row["product_id"];
          $producto->url = $row["url"];
          $producto->categoria = new Categoria($row["categoria"]);
        	$producto->titulo = $row["titulo"];
          $producto->precio = $row["precio"];
          $producto->localidad  = $row["localidad"];
          $producto->inicio_periodo = $row["inicio_periodo"];
          $producto->fin_periodo = $row["fin_periodo"];
          $producto->ventas_en_periodo= $row["ventas_en_periodo"];
          $producto->dias_periodo = $row["periodo_en_dias"];
          $producto->dinero_movido = $row["dinero_movido"];

          $searchResults[]=$producto;
      }
      return $searchResults;
    }

    // devuelve un array con querys sql para importar el temporal.csv
    private function importQuery(){

      $importQuery=[];
      // --borro tabla temporal si existe
      //--  borro tabla temporal si existe
      $importQuery[] ="DROP TABLE IF EXISTS temporal;"
      ;


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
      $importQuery[] ="LOAD DATA INFILE '/opt/lampp/htdocs/scraper/imports/temp-import.csv'
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
    // devuelve un string vacio si sale todo bien, o un mensaje de error si pasa algo maaaalo.
    public function importar($archivo)
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

      $DB = conect();


      $queryes = $this->importQuery();

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
          return "ERROR EN MULTIQUERY: --".$e->getMessage();
      }
      unlink("imports/temp-import.csv");
      return '';

    }


    // Toma los registros en scrapes que no tienen url, se fija si hay registros con el mismo product_id que si tengan y los que tienen les comparten a los que no.
    public function share()
    {
      $DB = conect();
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





    // devuelve el total de registros de la tabla scrapes
    public function totalScrapes()
    {
      $DB=conect();

      $queryText="SELECT count(*)as cantidad FROM scrapes";

      try {
        $query = $DB->prepare($queryText);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result["cantidad"];
      } catch (PDOException $e) {
        return $e->getMessage();
      }

    }

    //devuelve el total de productos (PRODUCT_ID distintos) de la tabla scrapes
    public function totalProductos()
    {
      $DB=conect();

      $queryText="SELECT count(distinct product_id) as cantidad from scrapes;";

      try
      {
        $query = $DB->prepare($queryText);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result["cantidad"];
      }
      catch (PDOException $e)
      {
        return $e->getMessage();
      }


    }


    // Mejores vendidos por categoia. Si categoria no se pasa como parametro trae mejores vendidos en general.
    // devuelve un Array de objetos de clase producto
    public function bestSellers($cantidad , $categoria_id = 0)
    {
      $DB=conect();

      $queryText = "SELECT
        product_id,
      	categoria_id,
      	title AS titulo,
        url,
          price AS precio,
          location AS localidad,
          DATE_FORMAT(MIN(data_date), '%d/%m/%Y') as inicio_periodo,
          DATE_FORMAT(MAX(data_date), '%d/%m/%Y') as fin_periodo,
             MAX(sells) - MIN(sells) AS ventas_en_periodo,
            CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date)),' dias')  AS periodo_en_dias,
          (MAX(sells) - MIN(sells)) * price AS dinero_movido
        FROM scrapes AS s "
         ;

        if ($categoria_id){
        $queryText .= "WHERE categoria_id = :c_id ";
      }

      $queryText.="GROUP BY product_id HAVING COUNT(product_id) > 1 AND periodo_en_dias > 0 "
      ;

      if (trim($this->criterio_ordenamiento)=="vendidos"){
        $queryText.=' ORDER BY ventas_en_periodo desc, dinero_movido desc ';
      }else{
        $queryText.=' ORDER BY dinero_movido desc, ventas_en_periodo desc ';
      }

      $queryText.= ' LIMIT :cantidad; ';



        try
        {
          $query= $DB->prepare($queryText);
          $query->bindValue(':cantidad',$cantidad,PDO::PARAM_INT);
          if($categoria_id)
          {
            $query->bindValue(':c_id',$categoria_id,PDO::PARAM_INT);
          }
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
          $producto = new producto();
          $producto->product_id = $row["product_id"];
          $producto->categoria =new Categoria($row["categoria_id"]);
        	$producto->titulo =$row["titulo"];
          $producto->url =$row["url"];
          $producto->precio =$row["precio"];
          $producto->localidad =$row["localidad"];
          $producto->inicio_periodo =$row["inicio_periodo"];
          $producto->fin_periodo =  $row["fin_periodo"];
          $producto->ventas_en_periodo =$row["ventas_en_periodo"];
          $producto->dias_periodo =$row["periodo_en_dias"];
          $producto->dinero_movido =$row["dinero_movido"];
          $bestSellers[]=$producto;
        }
        return $bestSellers;
    }

    //borra el ultimo insert

    public function deleteLastInsert()
    {
      $DB->conect();
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
    public $ventas_en_periodo;



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
        $this->ventas_en_periodo="";

      }

      public function totales()
      {
        $DB=conect();

        $queryText = 'SELECT
            product_id,
            MAX(sells) - MIN(sells) AS ventas_total,
            CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date))," dias")  AS dias_periodo,
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
        $DB = conect();
        $queryText='SELECT DATE_FORMAT(s.data_date,"%d/%m/%Y" ) AS fecha_insert,
        categoria_id ,
        s.url ,
        s.title AS titulo,
        s.price AS precio,
        s.sells AS ventas,
        s.location AS localidad
        FROM scrapes s
        WHERE trim(product_id) LIKE trim(:product_id_param)
        ORDER BY data_date';

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
          $producto->categoria = new Categoria();
          $producto->categoria->getById($row["categoria_id"]);
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
