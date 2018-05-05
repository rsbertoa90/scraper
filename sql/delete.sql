

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