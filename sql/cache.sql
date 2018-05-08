-- create table criterios_ordenamiento(
-- 	id int unsigned not null auto_increment primary key,
-- 	name varchar(50)
-- 
-- );
use scraper;
create table insertions (
	id int unsigned not null auto_increment primary key,
	categoria_id int unsigned not null,	
    insertion_date timestamp default now(),
    foreign key (categoria_id) references categorias(id)
);


drop table if exists cache_bestSellers;
create table cache_bestSellers
(
		id int unsigned not null auto_increment primary key, 
        cache_date timestamp default now(),
        criterio varchar(100),
        categoria_id int unsigned not null,
		product_id varchar(100) not null, 
        url varchar(300),
		localidad varchar(100),
		titulo varchar(200) not null,
		precio int unsigned not null,
		inicio_periodo varchar(10) not null,
		fin_periodo varchar(10) not null,
		dias_periodo varchar(20) not null,
		ventas_periodo int(10) unsigned not null,
		dinero_movido int(10) unsigned not null,
        favorito tinyint(1)
);


-- CACHE CATEGORIAS
drop table if exists cache_categorias;
create table cache_categorias
(
	id int unsigned not null auto_increment primary key, 
    cache_date timestamp default now(),
	categoria_id int unsigned,
    nombre varchar(100) not null,
    productos int unsigned,
    start_url varchar(300),
    last_insert timestamp
);

INSERT INTO cache_categorias (categoria_id,nombre,productos,start_url,last_insert)
SELECT c.id,c.name,count(distinct s.product_id) as registros, c.start_url, max(i.insertion_date) as last_insert
FROM categorias as c
INNER JOIN scrapes as s on s.categoria_id = c.id
left join insertions as i on i.categoria_id = c.id 
group by c.id;

-- cache totales
drop table if exists cache_totales;
create table cache_totales
(
 id int unsigned not null auto_increment primary key, 
 cache_date timestamp default now(),
 total_productos int unsigned,
 total_scrapes int unsigned,
 total_categorias int unsigned
);



SELECT
		 "dinero_movido" as criterio,
          2 as categoria_id,
         product_id,
         title AS titulo,
    	   url,
         price AS precio,
         location AS localidad,
         DATE_FORMAT(MIN(data_date), '%d/%m/%Y') as inicio_periodo,
         DATE_FORMAT(MAX(data_date), '%d/%m/%Y') as fin_periodo,
         CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date)),' dias')  AS dias_periodo,
         MAX(sells) - MIN(sells) AS ventas_periodo,
         (MAX(sells) - MIN(sells)) * price AS dinero_movido
        FROM scrapes AS s 
        WHERE (2 = 0) OR (2 > 0  AND s.categoria_id = 2)
  	   GROUP BY product_id HAVING COUNT(product_id) > 1 AND dias_periodo > 0 
		ORDER BY dinero_movido desc, ventas_periodo desc
		LIMIT 30;
-- 
-- -- restore from cache
-- select product_id, localidad, titulo, precio, inicio_periodo, fin_periodo, vendidos_periodo, dinero_movido
-- from cache_bestSellers
-- where criterio = "vendidos"
-- and categoria_id = 1;
-- 
-- 
-- -- cargar cache de datos
-- INSERT INTO cache_bestSellers (criterio,categoria_id,product_id, titulo, url, precio, localidad, inicio_periodo, fin_periodo, dias_periodo,ventas_periodo,dinero_movido)
-- SELECT
-- 	 "dinero_movido",
--      0,
--      product_id,
--      title AS titulo,
-- 	 url,
--      price AS precio,
--      location AS localidad,
--      DATE_FORMAT(MIN(data_date), '%d/%m/%Y') as inicio_periodo,
--      DATE_FORMAT(MAX(data_date), '%d/%m/%Y') as fin_periodo,
--      CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date)),' dias')  AS dias_periodo,
--      MAX(sells) - MIN(sells) AS ventas_periodo,
--      (MAX(sells) - MIN(sells)) * price AS dinero
--      FROM scrapes 
--   --   WHERE categoria_id = 1
-- 	 GROUP BY product_id HAVING COUNT(product_id) > 1 
-- 	 ORDER BY  dinero desc, ventas_periodo desc
--      LIMIT 30;
-- 



