
-- PASADO UN PARAMETRO DEVUELVE SI ES NUMERICO
DROP function if exists isnumeric;
create function isnumeric(val varchar(1024)) 
returns tinyint(1) deterministic 
return val regexp '^(-|\\+)?([0-9]+\\.[0-9]*|[0-9]*\\.[0-9]+|[0-9]+)$'; 


-- ------------------------------------------------------- --
-- BORRAR ULTIMA INSERCION
DROP PROCEDURE IF EXISts delete_last_insert;
DELIMITER $$

CREATE PROCEDURE delete_last_insert() 

BEGIN

SET SQL_SAFE_UPDATES = 0;
SET @max = (select max(data_date) from scrapes);
delete from scrapes where data_date = @max;
SET SQL_SAFE_UPDATES = 1;

END $$

DELIMITER ;


-- --------------------------------------------------- --
-- TEMPORAL CLEAN AND INSERT FROM CSV

DROP PROCEDURE IF EXISTS temporal_clean_and_insert;

DELIMITER $$



CREATE PROCEDURE temporal_clean_and_insert() 

BEGIN


DECLARE categoria int;
-- 
-- --  --borro tabla temporal si existe
--   -- --  borro tabla temporal si existe
--   DROP TABLE IF EXISTS temporal;
--   
-- 
-- 
-- -- web-scraper-order	web-scraper-start-url	categroias	categroias-href	paginacion	paginacion-href	titulo	product_id	url	precio	vendidos
-- 
--   CREATE TABLE temporal (
--   					             worder varchar(30),
--                         start_url varchar(200),
--                         subcategoria varchar(200),
--                         categorias_href varchar(200),
--                         pagination varchar(200),
--                         paginationhref varchar(200),
--                         titulo varchar(200),
--                         product_id varchar(50),
--                         url varchar(400),
--                         precio varchar (10),
--                         vendidos varchar(200)
--   					  );
--               
-- 
-- 
--   -- --  importo datos del csv crudo del scrapper
--   LOAD DATA INFILE '/opt/lampp/htdocs/scraper-objetos/imports/temp-import.csv'
--   INTO TABLE temporal
--   CHARACTER SET UTF8
--   FIELDS TERMINATED BY ','
--   ENCLOSED BY '\"'
--   IGNORE 1 LINES;

-- ELIMINO LAS FILAS DE PRODUCTOS USADOS
  DELETE FROM temporal WHERE vendidos LIKE '%usado%';

--   --  borro registros duplicados
  ALTER IGNORE TABLE temporal add unique index (product_id);


  --  --  borro las columnas que no me sirven
  ALTER TABLE temporal DROP COLUMN worder;
  ALTER TABLE temporal DROP COLUMN pagination;
  ALTER TABLE temporal DROP COLUMN paginationhref;
  ALTER TABLE temporal DROP COLUMN categorias_href;



--   --  Desactivo temporalmente la seguridad para hacer cambios masivos en la tabla
  SET SQL_SAFE_UPDATES = 0;
  

--   --  quito las comillas en todos los campos
--   --  en el campo vendidos quito las palabras "vendidos" y "vendido"
  UPDATE  temporal SET
  product_id = replace(product_id,'\"',''),
  precio = replace(precio,'\"',''),
  titulo = replace(titulo,'\"',''),
  vendidos = replace(vendidos,'\"',''),
  url = replace(url,'\"',''),
  subcategoria = replace(subcategoria,'\"',''),
  vendidos = replace(vendidos,'vendidos',''),
  vendidos = replace(vendidos,'vendido','');


  -- reemplazo el start_url por el numero de categorias
  SELECT categorias.id FROM categorias INNER JOIN temporal on trim(temporal.start_url) like concat(trim(categorias.start_url),'%') limit 1 into categoria;
  
  -- inserto en la tabla insertions
  INSERT INTO insertions (categoria_id) VALUES (categoria);


  --  --  creo la columna localidad para separar el campo "vendidos"
 ALTER TABLE temporal ADD COLUMN localidad VARCHAR(200);
  


--   --  tomo la localidad del campo vendidos y la pongo en el campo localidad
--   --  saco la localidad y el guion del campo vendidos
  UPDATE temporal SET localidad = SUBSTRING_INDEX(SUBSTRING_INDEX(vendidos, '-', 2), '-', -1),
  					vendidos = SUBSTRING_INDEX(SUBSTRING_INDEX(vendidos, '-', 1), '-', -1);
  

-- --  hago trim en todas las columnas
-- --  quito puntos de la columna precio
  UPDATE temporal SET
		start_url = categoria,
	  product_id = TRIM(product_id),
    precio = TRIM(precio),
    titulo = TRIM(titulo),
    vendidos = TRIM(vendidos),
    localidad = TRIM(localidad),
    subcategoria = TRIM(subcategoria),
    url = TRIM(url),
    precio = REPLACE(precio,'.','');
  


-- -- quito los numeros en el campo subcategoria
  UPDATE temporal SET
    subcategoria = REPLACE (subcategoria,SUBSTRING(subcategoria,LOCATE('(',subcategoria)),'' );
  


  --  --   agrego las subcategorias que no existen
INSERT IGNORE INTO subcategorias (name, categoria_id)
select distinct trim(subcategoria) ,start_url from temporal
WHERE trim(subcategoria) not like '';


--  -- alter table subcategorias drop column name;
--  -- alter table  subcategorias add column name varchar(100) not null unique;

--  --   cambio el valor de subcategoria por el id que corresponde
UPDATE temporal
      SET subcategoria = (
            SELECT subcategorias.id
            FROM subcategorias
            WHERE subcategorias.name = temporal.subcategoria
          AND subcategorias.categoria_id = temporal.start_url );




--  --  quito valores no numericos de vendidos
  UPDATE temporal SET vendidos = 0 WHERE !isnumeric( TRIM(vendidos) );


--  --   cambio los tipos de las columnas precio y vendidos por tipos numericos
   ALTER TABLE temporal MODIFY precio decimal(9,2),
					MODIFY vendidos INTEGER UNSIGNED;
  


  --  -- inserto el resultado en la tabla scrapes
  INSERT INTO scrapes (categoria_id, product_id, title, price, sells, location,subcategoria_id, url)
  SELECT DISTINCT start_url AS categoria_id, product_id, titulo AS title, precio AS price, vendidos AS sells, localidad AS location,subcategoria, url
  FROM temporal ORDER BY vendidos DESC LIMIT 999999;
  
  -- caliento cache de general y de la categoria
  CALL heat_cache_mejoresv(0);
  CALL heat_cache_mejoresv(categoria);
  CALL heat_categoria_cache(categoria);
  CALL heat_totales_cache();

  -- -- RESTAURO LA SEGURIDAD
  SET SQL_SAFE_UPDATES = 1;
  
END $$

DELIMITER ;

-- -------------------------------- --

-- CALENTAR CACHE - MEJORES VENDIDOS POR CATEGORIA- CATEGORIA 0 ES GENERAL
DROP PROCEDURE IF EXISts heat_cache_mejoresv;
DELIMITER $$


CREATE PROCEDURE heat_cache_mejoresv(in cat_param int unsigned) 

BEGIN
SET SQL_SAFE_UPDATES = 0;

DELETE FROM cache_bestSellers WHERE categoria_id = cat_param;

INSERT INTO cache_bestSellers (criterio,categoria_id,product_id,
          titulo, url, precio, localidad, inicio_periodo, fin_periodo, dias_periodo,ventas_periodo,dinero_movido,favorito)
        SELECT
		 "dinero_movido" as criterio,
          cat_param as categoria_id,
         s.product_id,
         s.title AS titulo,
    	   s.url,
         s.price AS precio,
         s.location AS localidad,
         DATE_FORMAT(MIN(s.data_date), '%d/%m/%Y') as inicio_periodo,
         DATE_FORMAT(MAX(s.data_date), '%d/%m/%Y') as fin_periodo,
         CONCAT(TIMESTAMPDIFF(DAY, MIN(s.data_date), MAX(s.data_date)),' dias')  AS dias_periodo,
         MAX(s.sells) - MIN(s.sells) AS ventas_periodo,
         (MAX(s.sells) - MIN(s.sells)) * price AS dinero_movido,
         (if (f.id is not null, 1 , 0 ) ) as favorito
        FROM scrapes AS s 
        left join favoritos as f on f.product_id = s.product_id
        WHERE (cat_param = 0) OR (cat_param > 0  AND s.categoria_id = cat_param)
  	    GROUP BY product_id HAVING COUNT(s.product_id) > 1 AND dias_periodo > 0 
		ORDER BY dinero_movido desc, ventas_periodo desc
		LIMIT 100;
        
INSERT INTO cache_bestSellers (criterio,categoria_id,product_id,
          titulo, url, precio, localidad, inicio_periodo, fin_periodo, dias_periodo,ventas_periodo,dinero_movido,favorito)
        SELECT
		 "ventas_periodo" as criterio,
          cat_param as categoria_id,
         s.product_id,
         s.title AS titulo,
    	   s.url,
         s.price AS precio,
         s.location AS localidad,
         DATE_FORMAT(MIN(s.data_date), '%d/%m/%Y') as inicio_periodo,
         DATE_FORMAT(MAX(s.data_date), '%d/%m/%Y') as fin_periodo,
         CONCAT(TIMESTAMPDIFF(DAY, MIN(s.data_date), MAX(s.data_date)),' dias')  AS dias_periodo,
         MAX(s.sells) - MIN(s.sells) AS ventas_periodo,
         (MAX(sells) - MIN(sells)) * price AS dinero_movido,
         (if (f.id is not null, 1 , 0 ) ) as favorito
        FROM scrapes AS s 
        left join favoritos as f on f.product_id = s.product_id
        WHERE (cat_param = 0) OR (cat_param > 0  AND s.categoria_id = cat_param)
  	   GROUP BY product_id HAVING COUNT(s.product_id) > 1 AND dias_periodo > 0 
		ORDER BY ventas_periodo desc, dinero_movido desc
		LIMIT 100;        

SET SQL_SAFE_UPDATES = 1;


END $$

DELIMITER ;
-- ----------------- --
-- fuerzzo el cache de todo
-- -------------------------------- --
-- CALENTAR CACHE DE TODAS LAS CATEGORIAS
DROP PROCEDURE IF EXISts heat_all_bestsellers_cache;
DELIMITER $$

CREATE PROCEDURE heat_all_bestsellers_cache() 

BEGIN

DECLARE n INT DEFAULT 0;
DECLARE i INT DEFAULT 0;
declare cat int default 0;
SELECT COUNT(*) FROM categorias INTO n;
SET i=0;
call heat_cache_mejoresv(0);
WHILE i<n DO 
  SELECT categorias.id from categorias order by 1 limit i,1 into cat;
  call heat_cache_mejoresv(cat);
  SET i = i + 1;
END WHILE;

END $$

DELIMITER ;
-- ----------------- --
-- CALENTAR CACHE DE CATEGORIA
DROP PROCEDURE IF EXISts heat_categoria_cache;
DELIMITER $$

CREATE PROCEDURE heat_categoria_cache(in categoria int unsigned) 


BEGIN
SET SQL_SAFE_UPDATES = 0;
DELETE FROM cache_categorias WHERE (categoria = 0) OR (categoria>0 AND cache_categorias.categoria_id = categoria);

INSERT INTO cache_categorias (categoria_id,nombre,productos,start_url,last_insert)
SELECT c.id,c.name,count(distinct s.product_id) as registros, c.start_url, max(i.insertion_date) as last_insert
FROM categorias as c
LEFT JOIN scrapes as s on s.categoria_id = c.id
LEFT join insertions as i on i.categoria_id = c.id 
WHERE (categoria = 0) OR (categoria>0 AND c.id = categoria)
group by c.id;
SET SQL_SAFE_UPDATES = 1;
END $$

DELIMITER ;


-- ------------------------------- --
-- calentar cache totales
DROP PROCEDURE IF EXISts heat_totales_cache;
DELIMITER $$

CREATE PROCEDURE heat_totales_cache() 

BEGIN
SET SQL_SAFE_UPDATES = 0;
DELETE FROM cache_totales;

INSERT INTO cache_totales (total_categorias,total_productos,total_scrapes)
select count(distinct c.id) as total_categorias,
	   count(distinct s.product_id) as total_productos,
		count(distinct s.id) as total_scrapes
FROM scrapes as s
LEFT JOIN categorias as c on c.id = s.categoria_id;
SET SQL_SAFE_UPDATES = 1;
END $$

DELIMITER ;


-- -------------------------------- --
-- BORRAR CATEGORIA EN CASCADA
-- SETEA SUBCATEGORIA EN NULO
DROP PROCEDURE IF EXISts delete_categoria;
DELIMITER $$

CREATE PROCEDURE delete_categoria(in categoria int unsigned) 


BEGIN
SET SQL_SAFE_UPDATES = 0;

DELETE FROM cache_categorias WHERE cache_categorias.categoria_id = categoria;

update scrapes 
inner join subcategorias on subcategorias.id = scrapes.subcategoria_id
set subcategoria_id = null where subcategorias.categoria_id = categoria;
 
delete from insertions where categoria_id = categoria;
delete from subcategorias where categoria_id = categoria;
delete from categorias where id = categoria;

SET SQL_SAFE_UPDATES = 1;
END $$

DELIMITER ;
