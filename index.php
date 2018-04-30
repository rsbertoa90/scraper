<?php


require_once("clases/producto.php");
require_once("clases/categoria.php");


  if(!isset($categorias))
  {
    $categorias = new categorias();
  }
  $productos = new Productos();
  $totalScrapes= $productos->totalScrapes();
  $totalProductos = $productos->totalProductos();

  if(isset($_GET["vendidos"])){$productos->setCriterio("vendidos");}
  else{$productos->setCriterio("dinero_movido");}

  $top10 = $productos->bestSellers(20);
  $mensajes="";


  //para la tabla de mejores vendidos
  $headers=["localidad","titulo","precio","dias periodo","vendidos periodo","dinero movido"];
  $indexes=["localidad","titulo","precio","dias_periodo","ventas_en_periodo","dinero_movido"];





 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php") ?>
  <body>
    <?php require_once("partials/header.php") ?>
    <?php require_once("partials/menu.php") ?>

    <main>
      <h1>BETA TEST REFACTORIZACION A OBJETOS</h1>
      <div class="tabla">
        <h3>CANTIDAD DE ELEMENTOS CARGADOS POR CATEGORIA</h3>
        <table>
          <tr>
            <th>Categoria</th>
            <th>Cantidad de registros</th>
          </tr>
          <?php foreach ($categorias->getAll() as $cat): ?>
            <tr class="">
              <td class="celda ">
                <a class="text-blue" href="top10cat.php?id=<?=$cat->id?>"><?=$cat->name?></a>

              </td>
              <td class="celda text-red">
                <?=$cat->cantidad_registros?>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr class="">
            <td class="celda text-blue">
              TOTAL REGISTROS
            </td>
            <td class="celda text-red">
              <?=$totalScrapes?>
            </td>
          </tr>
          <tr class="">
            <td class="celda text-blue">
              TOTAL PRODUCTOS DISTINTOS
            </td>
            <td class="celda text-red">
              <?=$totalProductos?>
            </td>
          </tr>
        </table>
      </div>

      <div class="tabla">
        <h3>Mejor vendidos</h3>

              <!-- botones para ordernar por vendidos o por monto en dinero -->
              <form class="" action="index.php" method="GET">
                <?php if ($productos->getCriterio() == "vendidos"): ?>
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
            <?php if(trim($item->url)){$item->titulo="<a target='_blank' href='{$item->url}'> {$item->titulo} </a>";} ?>
            <tr>
              <?php foreach ($indexes as $index): ?>
                <td><?=$item->$index?></td>
              <?php endforeach; ?>
              <td> <a href="historico.php?product_id=<?=$item->product_id?>">[ver historico]</a></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>




    </main>

  </body>
</html>
