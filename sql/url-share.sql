use scraper;

SET SQL_SAFE_UPDATES = 0;
UPDATE scrapes AS s
LEFT JOIN (SELECT DISTINCT product_id,url,subcategoria_id FROM scrapes WHERE url IS NOT NULL ) AS a 
ON s.product_id = a.product_id
SET s.url = a.url, s.subcategoria_id = a.subcategoria_id
WHERE s.product_id = a.product_id;
SET SQL_SAFE_UPDATES = 1;


select * from scrapes where url is null
AND categoria_id = 2;


use scraper;
select * from categorias;
delete from scrapes where categoria_id = 6;
SET SQL_SAFE_UPDATES = 0;
delete from subcategorias where name is null;
SET SQL_SAFE_UPDATES = 1;


delete from scrapes where data_date > date();

SET SQL_SAFE_UPDATES = 0;
delete from subcategorias where trim(name) like "";
SET SQL_SAFE_UPDATES = 1;

select * from subcategorias;