
  --  borro tabla temporal si existe
  DROP TABLE IF EXISTS temporal;


  --  creo tabla temporal
  CREATE TABLE temporal (
  					  worder varchar(30),
                        start_url varchar(200),
                        product_id varchar(50),
                        precio varchar (10),
                        titulo varchar(200),
                        vendidos varchar(200),
                        pagination varchar(200),
                        paginationhref varchar(200),
                        subcategoria varchar(200),
                        categorias_href varchar(200),
                        url varchar(400)
  					  );
  

  --  importo datos del csv crudo del scrapper
  LOAD DATA INFILE '/opt/lampp/htdocs/scraper/imports/temp-import.csv'
  INTO TABLE temporal
  CHARACTER SET UTF8
  FIELDS TERMINATED BY ','
  ENCLOSED BY '\"'
  IGNORE 1 LINES;
  

  --  borro registros duplicados
  ALTER IGNORE TABLE temporal add unique index (product_id);
  

  --  borro las columnas que no me sirven
  ALTER TABLE temporal DROP COLUMN worder;
  ALTER TABLE temporal DROP COLUMN start_url;
  ALTER TABLE temporal DROP COLUMN pagination;
  ALTER TABLE temporal DROP COLUMN paginationhref;
  ALTER TABLE temporal DROP COLUMN categorias_href;



  --  Desactivo temporalmente la seguridad para hacer cambios masivos en la tabla
  SET SQL_SAFE_UPDATES = 0;
  ;

  --  quito las comillas en todos los campos
  --  en el campo vendidos quito las palabras "vendidos" y "vendido"
  UPDATE  temporal SET
  product_id = replace(product_id,'\"',''),
  precio = replace(precio,'\"',''),
  titulo = replace(titulo,'\"',''),
  vendidos = replace(vendidos,'\"',''),
  url = replace(url,'\"',''),
  subcategoria = replace(subcategoria,'\"',''),
  vendidos = replace(vendidos,'vendidos',''),
  vendidos = replace(vendidos,'vendido','');
  
  

  --  creo la columna localidad para separar el campo "vendidos"
  ALTER TABLE temporal ADD COLUMN localidad VARCHAR(200);
  ;


  --  tomo la localidad del campo vendidos y la pongo en el campo localidad
  --  saco la localidad y el guion del campo vendidos
  UPDATE temporal SET localidad = SUBSTRING_INDEX(SUBSTRING_INDEX(vendidos, '-', 2), '-', -1),
  					vendidos = SUBSTRING_INDEX(SUBSTRING_INDEX(vendidos, '-', 1), '-', -1);
  ;

--  hago trim en todas las columnas
--  quito puntos de la columna precio
  UPDATE temporal SET
	  product_id = TRIM(product_id),
    precio = TRIM(precio),
    titulo = TRIM(titulo),
    vendidos = TRIM(vendidos),
    localidad = TRIM(localidad),
    subcategoria = TRIM(subcategoria),
    url = TRIM(url),
    precio = REPLACE(precio,'.','');
  ;


-- quito los numeros en el campo subcategoria
  UPDATE temporal SET
    subcategoria = REPLACE (subcategoria,SUBSTRING(subcategoria,LOCATE('(',subcategoria)),'' );
  ;
 

  --   agrego las categorias que no existen
INSERT IGNORE INTO subcategorias (name, categoria_id)
select distinct trim(subcategoria) ,6 from temporal 
WHERE trim(subcategoria) not like '';


-- alter table subcategorias drop column name;
-- alter table  subcategorias add column name varchar(100) not null unique;

--   cambio el valor de subcategoria por el id que corresponde
UPDATE temporal
      SET subcategoria = (
            SELECT subcategorias.id
            FROM subcategorias
            WHERE subcategorias.name = temporal.subcategoria);




--  quito valores no numericos de vendidos
  UPDATE temporal SET vendidos = 0 WHERE !isnumeric( TRIM(vendidos) );
  ;


--   cambio los tipos de las columnas precio y vendidos por tipos numericos
   ALTER TABLE temporal MODIFY precio decimal(9,2),
					MODIFY vendidos INTEGER UNSIGNED;
  ;


  -- inserto el resultado en la tabla scrapes
  INSERT INTO scrapes (categoria_id, product_id, title, price, sells, location,subcategoria_id, url)
  SELECT DISTINCT 6 AS categoria_id, product_id, titulo AS title, precio AS price, vendidos AS sells, localidad AS location,subcategoria, url
  FROM temporal;
  ;

  --  --  Seteo que los productos cargados con url revisen si hay registros con el mismo product_id
  --  --  y si lo hay, que les seteen la misma url.
  --  UPDATE scrapes
  --  LEFT JOIN temporal ON scrapes.product_id = temporal.product_id
  --  SET scrapes.url = temporal.url
  --  WHERE scrapes.product_id = temporal.product_id;
  --  ;

  -- RESTAURO LA SEGURIDAD
  SET SQL_SAFE_UPDATES = 1;                     
						