<?php

  require_once("sql/sql-functions.php");


  if(!isset($categorias)){$categorias=categorias();}
  $totalScrapes= totalScrapes($categorias);
  $mensajes="";

  if(isset($_GET["vendidos"])){$criterio="vendidos";}
  else{$criterio="dinero_movido";}

  //para la tabla de mejores vendidos
  $headers=["localidad","titulo","precio","dias periodo","vendidos periodo","dinero movido"];
  $indexes=["localidad","titulo","precio","periodo_en_dias","ventas_en_periodo","dinero_movido"];

  $top10 = bestSellers(20,$criterio);




 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php") ?>
  <body>
    <?php require_once("partials/header.php") ?>
    <?php require_once("partials/menu.php") ?>

    <main>
      <div class="tabla">
        <h3>CANTIDAD DE ELEMENTOS CARGADOS POR CATEGORIA</h3>
        <table>
          <tr>
            <th>Categoria</th>
            <th>Cantidad</th>
          </tr>
          <?php foreach ($categorias as $cat): ?>
            <tr class="">
              <td class="celda ">
                <a class="text-blue" href="top10cat.php?id=<?=$cat["id"]?>"><?=$cat["name"]?></a>

              </td>
              <td class="celda text-red">
                <?=$cat["cantidad"]?>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr class="">
            <td class="celda text-blue">
              TOTAL
            </td>
            <td class="celda text-red">
              <?=$totalScrapes?>
            </td>
          </tr>
        </table>
      </div>

      <div class="tabla">
        <h3>Mejor vendidos</h3>

              <!-- botones para ordernar por vendidos o por monto en dinero -->
              <form class="" action="index.php" method="GET">
                <?php if ($criterio == "vendidos"): ?>
                  <button type="submit" class="enabled" >Ordernar por dinero movido</button>
                  <button class= "disabled" type="submit" name="vendidos" value="1" disabled>Ordenar por cantidad de vendidos</button>
                <?php else: ?>
                  <button type="submit" class ="disabled" disabled>Ordernar por dinero movido</button>
                  <button type="submit" name="vendidos" class="enabled" value="1" >Ordenar por cantidad de vendidos</button>
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
            <?php if(trim($item["url"])){$item["titulo"]="<a target='_blank' href='".$item["url"]."'>".$item["titulo"]."</a>";} ?>
            <tr>
              <?php foreach ($indexes as $index): ?>
                <td><?=$item[$index]?></td>
              <?php endforeach; ?>
              <td> <a href="historico.php?product_id=<?=$item['product_id']?>">[ver historico]</a></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>




    </main>

  </body>
</html>
