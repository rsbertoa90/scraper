<?php

  if(!isset($_GET["id"])){
    header('location: index.php');
    exit;
  }

require_once("clases/producto.php");
require_once("clases/categoria.php");

$categoria = new Categoria();
$categoria = Categorias::getById($_GET["id"]);


  $headers=["localidad","titulo","precio","inicio periodo","fin periodo","dias periodo","vendidos periodo","dinero movido"];
  $indexes=["localidad","titulo","precio","inicio_periodo","fin_periodo","dias_periodo","ventas_en_periodo","dinero_movido"];


  if (isset($_POST["ventas_periodo"])){Productos::setCriterio("ventas_periodo");}
  else{Productos::setCriterio("dinero_movido");}

  if ($categoria)
  $top10 = Productos::bestSellers($_GET["id"]);

 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php") ?>
  <body>
    <?php require_once("partials/header.php") ?>
    <?php require_once("partials/menu.php") ?>
    <main>
      <h2>Mejor vendidos: <?=$categoria->name?></h2>

      <?php require_once("partials/tablaBestSellers.php") ?>
    </main>

  <?php require_once("partials/js-import.php") ?>


  </body>
</html>
