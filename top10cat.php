<?php

  if(!isset($_GET["id"])){
    header('location: index.php');
    exit;
  }

require_once("clases/producto.php");
require_once("clases/categoria.php");

$categoria = new Categoria();
$categoria->getById($_GET["id"]);


  $headers=["localidad","titulo","precio","inicio periodo","fin periodo","dias periodo","vendidos periodo","dinero movido"];
  $indexes=["localidad","titulo","precio","inicio_periodo","fin_periodo","dias_periodo","ventas_en_periodo","dinero_movido"];


  if (isset($_POST["vendidos"])){Productos::setCriterio("vendidos");}
  else{Productos::setCriterio("dinero_movido");}
  $top10 = Productos::bestSellers(30,$_GET["id"]);

 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php") ?>
  <body>
    <?php require_once("partials/header.php") ?>
    <?php require_once("partials/menu.php") ?>
    <main>
      <h2>Mejor vendidos: <?=$categoria->name?></h2>

      <!-- botones para ordernar por vendidos o por monto en dinero -->
      <form class="" action="top10cat.php?id=<?=$_GET['id']?>" method="post">
        <?php if (Productos::$criterio_ordenamiento == "vendidos"): ?>
          <button type="submit"class= "enabled" >Ordernar por dinero movido</button>
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
        <?php foreach ($top10 as $item): ?>
          <tr>
            <?php foreach ($indexes as $index): ?>
              <?php if(trim($item->url)){$item->titulo="<a target='_blank' href='{$item->url}'>{$item->titulo}</a>";} ?>
              <td><?=$item->$index?></td>
            <?php endforeach; ?>
            <td> <a href="historico.php?product_id=<?=$item->product_id?>">[ver historico]</a></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </main>

  </body>
</html>
