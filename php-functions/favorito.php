<?php
require_once("../clases/producto.php");
$p = new Producto($_POST["product_id"]);
$p->favorito();
?>
