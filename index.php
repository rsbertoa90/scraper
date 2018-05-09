<?php


require_once("clases/producto.php");
require_once("clases/categoria.php");


  if(!isset($categorias))
  {
    $categorias = Categorias::getAll();
  }

  $totales = Productos::totales();
  $totalProductos = $totales["total_productos"];
  $totalScrapes = $totales["total_scrapes"];



  if(isset($_GET["ventas_periodo"])){Productos::setCriterio("ventas_periodo");}
  else{Productos::setCriterio("dinero_movido");}



  $productos = Productos::bestSellers(0);


  //para la tabla de mejores vendidos
  $headers=["localidad","titulo","precio","dias periodo","vendidos periodo","dinero movido"];
  $indexes=["localidad","titulo","precio","dias_periodo","ventas_periodo","dinero_movido"];





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
            <th>Cantidad de productos seguidos</th>
            <th>Ultima insercion</th>
          </tr>
          <?php foreach ($categorias as $cat): ?>
            <tr class="">
              <td class="celda ">
                <a class="text-blue" href="top10cat.php?id=<?=$cat->id?>"><?=$cat->name?></a>
              </td>
              <td class="celda text-red">
                <?=$cat->cantidad_registros?>
              </td>
              <td class="celda">
                <?=$cat->last_insert?>
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

        <?php require_once("partials/tablaBestSellers.php") ?>
      </div>




    </main>
    <?php require_once("partials/js-import.php") ?>
  </body>
</html>
