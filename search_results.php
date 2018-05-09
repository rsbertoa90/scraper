<?php
if(!isset($_GET["search"])){
  header("location: index.php");
}

require_once("clases/producto.php");


if (isset($_GET["min"]) && trim($_GET["min"]))
{
  Productos::setPrecioMinimo($_GET["min"]);
}
if (isset($_GET["max"]) && trim($_GET["max"]))
{
  Productos::setPrecioMaximo($_GET["max"]);
}

if( isset($_POST["ventas_periodo"] ) ) {Productos::setCriterio("ventas_periodo");}
else{ Productos::setCriterio("dinero_movido"); }


$headers=["localidad","titulo","precio","inicio periodo","fin periodo","dias periodo","vendidos periodo","dinero movido"];
$indexes=["localidad","titulo","precio","inicio_periodo","fin_periodo","dias_periodo","ventas_periodo","dinero_movido"];

$productos = Productos::search($_GET["search"]);


 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php"); ?>
  <body>
    <?php require_once("partials/header.php"); ?>
    <?php require_once("partials/menu.php"); ?>

    <main>

      <?php require_once("partials/tablaBestSellers.php") ?>
    </main>

    <?php require_once("partials/js-import.php") ?>
  </body>
</html>
