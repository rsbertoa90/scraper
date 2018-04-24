use scraper;

update scrapes as s
left join (select product_id,url from scrapes where url is not null ) as a on s.product_id=a.product_id
set s.url = a.url, s.subcategoria = a.subcategoria
where s.product_id = a.product_id;





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