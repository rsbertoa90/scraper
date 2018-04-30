<?php
if(!isset($_GET["search"])){
  header("location: index.php");
}

require_once("clases/producto.php");

$productos = new Productos();


if (isset($_GET["min"]) && trim($_GET["min"]))
{
  $productos->setPrecioMinimo($_GET["min"]);
}
if (isset($_GET["max"]) && trim($_GET["max"]))
{
  $productos->setPrecioMaximo($_GET["max"]);
}

if( isset($_POST["vendidos"] ) ) {$productos->setCriterio("vendidos");}
else{ $productos->setCriterio("dinero_movido"); }

$results = $productos->search($_GET["search"]);

$headers=["localidad","titulo","precio","inicio periodo","fin periodo","dias periodo","vendidos periodo","dinero movido"];
$indexes=["localidad","titulo","precio","inicio_periodo","fin_periodo","dias_periodo","ventas_en_periodo","dinero_movido"];


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
        <?php if ($productos->getCriterio() == "vendidos"): ?>
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
          <?php if(trim($item->url)){$item->titulo="<a target='_blank' href='".$item->url."'>".$item->titulo."</a>";} ?>
          <tr>
            <?php foreach ($indexes as $index): ?>
              <td><?=$item->$index?></td>
            <?php endforeach; ?>
            <td> <a href="historico.php?product_id=<?=$item->product_id?>">[ver historico]</a></td>
          </tr>
        <?php endforeach; ?>
      </table>

    </main>
  </body>
</html>
