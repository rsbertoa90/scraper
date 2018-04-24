<?php
if(!isset($_GET["search"])){
  header("location: index.php");
}

require_once("sql/sql-functions.php");


if (isset($_POST["vendidos"])){
  $criterio="vendidos";
}else{$criterio="dinero_movido";}

if (isset($_GET["min"]) && trim($_GET["min"])){
  $min = $_GET["min"];
}else{$min = 0;}

if (isset($_GET["max"]) && trim($_GET["max"])){
  $max = $_GET["max"];
}else{$max = 999999;}


$results = search($_GET["search"],$min,$max,$criterio);

$headers=["localidad","titulo","precio","inicio periodo","fin periodo","dias periodo","vendidos periodo","dinero movido"];
$indexes=["localidad","titulo","precio","inicio_periodo","fin_periodo","periodo_en_dias","ventas_en_periodo","dinero_movido"];


 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php"); ?>
  <body>
    <?php require_once("partials/header.php"); ?>
    <?php require_once("partials/menu.php"); ?>

    <main>

      <!-- botones para ordernar por vendidos o por monto en dinero -->
      <form class="" action="" method="POST">
        <?php if ($criterio == "vendidos"): ?>
          <button type="submit" class= "enabled" >Ordernar por dinero movido</button>
          <button class= "disabled" type="submit" name="vendidos" value="1" disabled>Ordenar por cantidad de vendidos</button>
        <?php else: ?>
          <button type="submit" class ="disabled" disabled>Ordernar por dinero movido</button>
          <button type="submit" class= "enabled" name="vendidos" value="1" >Ordenar por cantidad de vendidos</button>
      <?php endif ?>
      </form>

      <table>
        <tr>
          <?php foreach ($headers as $header): ?>
            <th>
              <?=$header?>
            </th>
          <?php endforeach; ?>
        </tr>
        <?php foreach ($results as $item): ?>
          <?php if(trim($item["url"])){$item["titulo"]="<a target='_blank' href='".$item["url"]."'>".$item["titulo"]."</a>";} ?>
          <tr>
            <?php foreach ($indexes as $index): ?>
              <td><?=$item[$index]?></td>
            <?php endforeach; ?>
            <td> <a href="historico.php?product_id=<?=$item['product_id']?>">[ver historico]</a></td>
          </tr>
        <?php endforeach; ?>
      </table>

    </main>
  </body>
</html>
