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

-- drop table if exists cache_bestSellers;
create table cache_bestSellers(
		id int unsigned not null auto_increment primary key,
        cache_date timestamp default now(),
        criterio varchar(100),
        categoria_id int unsigned not null,
		product_id int unsigned not null, 
        url varchar(300),
		localidad varchar(100),
		titulo varchar(200) not null,
		precio int unsigned not null,
		inicio_periodo varchar(10) not null,
		fin_periodo varchar(10) not null,
		dias_periodo varchar(20) not null,
		vendidos_periodo int(10) unsigned not null,
		dinero_movido int(10) unsigned not null,
	
	foreign key (categoria_id) references categorias(id)
	
);


-- shouldIrenewCache?
select c.cache_date < i.insertion_date as shouldI 
from cache_bestSellers as c, insertions as i
where c.categoria_id = 1
and i.categoria_id = 1
and c.criterio = "dinero_movido"
limit 1;

-- restore from cache
select product_id, localidad, titulo, precio, inicio_periodo, fin_periodo, vendidos_periodo, dinero_movido
from cache_bestSellers
where criterio = "vendidos"
and categoria_id = 1;

-- delete from cache on new insertion
delete from cache where categoria_id = 1 and criterio = "dinero_movido";

-- cargar cache de datos
INSERT INTO cache_bestSellers (criterio,categoria_id,product_id, titulo, url, precio, localidad, inicio_periodo, fin_periodo, dias_periodo,dinero_movido)
SELECT
	 "dinero_movido",
     categoria_id,
     product_id,
     title AS titulo,
	 url,
     price AS precio,
     location AS localidad,
     DATE_FORMAT(MIN(data_date), '%d/%m/%Y') as inicio_periodo,
     DATE_FORMAT(MAX(data_date), '%d/%m/%Y') as fin_periodo,
     MAX(sells) - MIN(sells) AS ventas_en_periodo,
     CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date)),' dias')  AS dias_periodo,
     (MAX(sells) - MIN(sells)) * price AS dinero_movido
     FROM scrapes AS s 
     WHERE categoria_id = 1
	 GROUP BY product_id HAVING COUNT(product_id) > 1 AND periodo_en_dias > 0 
	 ORDER BY  dinero_movido desc, ventas_en_periodo desc
     LIMIT 30;
     


