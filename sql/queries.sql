-- Selecciona el dinero movido en el periodo mas amplio de tiempo

-- todo de categorias
select * from categorias;

select * from scrapes where categoria_id = 4;
-- todo lo insertado en scrapes
select c.name as categoria, s.* 
from scrapes as s
inner join categorias as c on c.id = s.categoria_id
ORDER BY sells desc;

-- contar por categoria cantidad de registros
select c.name as categoria, count(s.id) as cantidad
from categorias as c
left join scrapes as s on s.categoria_id = c.id
group by c.id ;

describe scrapes;

-- seleccionar de todos los productos cargados en mas de una fecha, las
-- diferencias en ventas y el monto de dinero de esas ventas del periodo
-- tomando como periodo el maximo y el minimo de las fechas insertadas en ese product_id
Select 
	-- c.name as categoria,
	title as titulo,
    price as precio,
   sells as ventas_total,
    location as localidad,
   MIN(data_date) as inicio_periodo,
   MAX(data_date) as fin_periodo,
       MAX(sells) - MIN(sells) as ventas_en_periodo,
      concat(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date))," dias")  as periodo_en_dias,
    (MAX(sells) - MIN(sells)) * price as dinero_movido
FROM scrapes as s 
-- inner join categorias as c on s.categoria_id=c.id
group by product_id having count(product_id) > 1
order by  dinero_movido desc
limit 30;

-- mejores vendidos filtrado por categoria

Select 
	-- title as titulo,
    url as LINK,
    -- price as precio,
   -- sells as ventas_total,
    -- location as localidad,
   -- MAX(data_date) as inicio_periodo,ount
   -- MIN(data_date) as fin_periodo,
      --  MAX(sells) - MIN(sells) as ventas_en_periodo,
      -- concat(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date))," dias")  as periodo_en_dias,
    (MAX(sells) - MIN(sells)) * price as dinero_movido
FROM scrapes as s 
inner join categorias as c on s.categoria_id=c.id
where c.id = 12
group by product_id having count(product_id) > 1
order by  dinero_movido desc
limit 20;


-- consulto todos los registros en scrapes
-- si el id de producto esta en la lista de lo mas vendido

Select s.product_id, s.title, s.price, s.sells, s.data_date from
scrapes s
inner join
(
select 
	product_id,
	(MAX(sells) - MIN(sells)) * price as dinero_movido
FROM scrapes 
group by product_id having count(*) > 1
order by 2 desc
limit 20) as top on top.product_id = s.product_id  
order by 1,5,2,3,4;


-- top10

SELECT
	c.name AS categoria,
	title AS titulo,
    price AS precio,
    location AS localidad,
    DATE_FORMAT(MIN(data_date), "%d / %m / %Y") as inicio_periodo,
    DATE_FORMAT(MAX(data_date), "%d / %m / %Y") as fin_periodo,
       MAX(sells) - MIN(sells) AS ventas_en_periodo,
      CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date))," dias")  AS periodo_en_dias,
    (MAX(sells) - MIN(sells)) * price AS dinero_movido
FROM scrapes AS s
INNER JOIN categorias AS c ON s.categoria_id = c.id
 WHERE c.id = 2
GROUP BY product_id HAVING COUNT(product_id) > 1
ORDER BY  dinero_movido desc
limit 10;

-- historia

SELECT DATE_FORMAT(s.data_date,"%d / %m / %Y" ) as fecha_insert,
c.name as categoria,
s.title as titulo,
s.price as precio,
s.sells as ventas,
s.location as localidad
FROM scrapes s
inner join categorias as c on c.id=s.categoria_id
WHERE product_id LIKE "MLA715578461"
ORDER BY data_date


-- Busqueda por titulo
-- top10

SELECT
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
 WHERE title like "%cortina%"
GROUP BY product_id HAVING COUNT(product_id) > 1
ORDER BY  dinero_movido desc
limit 30;

select * from categorias;
select * from scrapes order by data_date desc;

update  scrapesa
set categoria_id = 1
where categoria_id = 7;

-- 
--

 
 SELECT
  product_id,
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
WHERE title like concat('%','sabana','%')
AND price BETWEEN 1 AND 99999
GROUP BY product_id HAVING COUNT(product_id) > 1
ORDER BY  dinero_movido desc
limit 30;

use scraper;
-- asdasdasd
SELECT product_id, c.name AS categoria, title AS titulo, price AS precio, location AS localidad, DATE_FORMAT(MIN(data_date), "%d / %m / %Y") as inicio_periodo, DATE_FORMAT(MAX(data_date), "%d / %m / %Y") as fin_periodo, MAX(sells) - MIN(sells) AS ventas_en_periodo, CONCAT(TIMESTAMPDIFF(DAY, MIN(data_date), MAX(data_date))," dias") AS periodo_en_dias, (MAX(sells) - MIN(sells)) * price AS dinero_movido FROM scrapes AS s INNER JOIN categorias AS c ON s.categoria_id = c.id GROUP BY product_id HAVING COUNT(product_id) > 1 ORDER BY dinero_movido desc limit 10;

