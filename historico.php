<?php
if(!isset($_GET["product_id"])){
  header("location: index.php");
}
require_once("sql/sql-functions.php");

$historicos = historico($_GET["product_id"]);
$totales = totales($_GET["product_id"]);

$headers=["fecha de insercion","categoria","titulo","precio","vendidos","localidad"];
$indexes=["fecha_insert","categoria","titulo","precio","ventas","localidad"];


 ?>


<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php") ?>
  <body>
    <?php require_once("partials/header.php") ?>
    <?php require_once("partials/menu.php") ?>

    <main>
      <table class="tabla">
        <tr>
          <?php foreach ($headers as $header): ?>
            <th class="text-white"><?=$header?></th>
          <?php endforeach; ?>
        </tr>
        <?php foreach ($historicos as $h): ?>
          <?php if(trim($h["url"])){$h["titulo"]="<a target='_blank' href='".$h["url"]."'>".$h["titulo"]."</a>";} ?>
          <tr>
            <?php foreach ($indexes as $i): ?>
              <td class="celda"><?=$h[$i]?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </table>

      <table class="tabla">
        <tr>
          <th>Total dias de seguimiento</th>
          <th>Total de ventas en periodo</th>
          <th>Total dinero movido en ventas</th>
        </tr>
        <tr>
          <td> <?=$totales["dias"]  ?> </td>
          <td><?=$totales["ventas"]?></td>
          <td><?=$totales["dinero_movido"]?></td>
        </tr>
      </table>
    </main>
  </body>
</html>
