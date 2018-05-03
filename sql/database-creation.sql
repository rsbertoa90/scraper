-- DROP DATABASE IF EXISTS SCRAPER;

CREATE DATABASE scraper
  DEFAULT CHARACTER SET utf8
  DEFAULT COLLATE utf8_general_ci;
  
use scraper;



CREATE TABLE categorias (
  id int(6) unsigned NOT NULL AUTO_INCREMENT,
  name varchar(50) NOT NULL UNIQUE,
  start_url VARCHAR (200),
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=12 ;


CREATE TABLE subcategorias (
  id int(6) unsigned NOT NULL AUTO_INCREMENT,
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  categoria_id int(6) unsigned DEFAULT NULL,
  name varchar(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY name_categoria (name,categoria_id),
  KEY categoria_id (categoria_id),
  CONSTRAINT subcategorias_ibfk_1 FOREIGN KEY (categoria_id) REFERENCES categorias (id)
) ENGINE=InnoDB AUTO_INCREMENT=118 ;

CREATE TABLE scrapes (
  id int(15) unsigned NOT NULL AUTO_INCREMENT,
  data_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  categoria_id int(15) unsigned DEFAULT NULL,
  product_id varchar(100) NOT NULL,
  title varchar(150) NOT NULL,
  price decimal(9,2) NOT NULL,
  sells int(7) unsigned DEFAULT NULL,
  location varchar(100) DEFAULT NULL,
  url varchar(400) DEFAULT NULL,
  subcategoria_id int(6) unsigned DEFAULT NULL,
  PRIMARY KEY (id),
  KEY categoria_id (categoria_id),
  KEY subcategoria_id (subcategoria_id),
  CONSTRAINT scrapes_ibfk_1 FOREIGN KEY (categoria_id) REFERENCES categorias (id),
  CONSTRAINT scrapes_ibfk_2 FOREIGN KEY (subcategoria_id) REFERENCES subcategorias (id)
) ENGINE=InnoDB AUTO_INCREMENT=351720;







