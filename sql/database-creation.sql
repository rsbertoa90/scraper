-- CREATE DATABASE scraper;
use scraper;
truncate scrapes;

drop table scrapes;
drop table categorias;


CREATE TABLE categorias (
id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
name VARCHAR (50) UNIQUE,
created_at TIMESTAMP DEFAULT NOW()
);



INSERT INTO categorias (name) VALUES ('Ropa de cama');
INSERT INTO categorias (name) VALUES ('Cortinas');
INSERT INTO categorias (name) VALUES ('Mates');
INSERT INTO categorias (name) VALUES ('Baterias de cocina');
-- select * from categorias;

CREATE TABLE scrapes(
id INT(15) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
data_date TIMESTAMP DEFAULT NOW(),
categoria_id  INT(15) UNSIGNED,
subcategoria varchar(100) ,
product_id varchar(100) NOT NULL, 
title varchar(150) NOT NULL,
price DECIMAL(9,2) NOT NULL,
sells INT(7) UNSIGNED,
location varchar(100),

FOREIGN KEY(categoria_id) REFERENCES categorias(id)

);



