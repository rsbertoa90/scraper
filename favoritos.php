<?php
require_once("clases/producto.php");
require_once("clases/categoria.php");


  $headers=["localidad","titulo","precio","inicio periodo","fin periodo","dias periodo","vendidos periodo","dinero movido"];
  $indexes=["localidad","titulo","precio","inicio_periodo","fin_periodo","dias_periodo","ventas_periodo","dinero_movido"];


  if (isset($_POST["ventas_periodo"])){Productos::setCriterio("ventas_periodo");}
  else{Productos::setCriterio("dinero_movido");}


  $productos = Productos::favoritos();


 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php") ?>
  <body>
    <?php require_once("partials/header.php") ?>
    <?php require_once("partials/menu.php") ?>
    <main>
      <h2>FAVORITOS</h2>

      <?php require_once("partials/tablaBestSellers.php") ?>
    </main>

  <?php require_once("partials/js-import.php") ?>
  </body>
</html>
