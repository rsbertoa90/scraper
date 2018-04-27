<?php
$urls=[
  "ropa de cama"=>"https://hogar.mercadolibre.com.ar/dormitorio/ropa-cama/_ItemTypeID_N",
  "cortinas"=>"https://hogar.mercadolibre.com.ar/decoracion/cortinas/_ItemTypeID_N",
  "Baterias de cocina"=>"https://hogar.mercadolibre.com.ar/cocina/bazar/baterias-de-cocina/_ItemTypeID_N",
  "Almohadas"=>"https://hogar.mercadolibre.com.ar/dormitorio/almohadas/_ItemTypeID_N",
  "Cubiertos"=>"https://hogar.mercadolibre.com.ar/cocina/bazar/cubiertos/_ItemTypeID_N",
  "Mates"=>"https://listado.mercadolibre.com.ar/arte-artesanias/artesanias/mates/_ItemTypeID_N",
  "reposteria"=>"https://listado.mercadolibre.com.ar/reposteria",
  "Lapiceras"=>"https://listado.mercadolibre.com.ar/lapiceras",
  "muebles"=>"",

];
 ?>
<!DOCTYPE html>
<html>
  <?php  require_once("partials/head.php") ?>
  <body>
    <?php  require_once("partials/header.php") ?>
    <?php  require_once("partials/menu.php") ?>
    <main>
      <ul>
        <?php foreach ($urls as $key => $value): ?>
          <li>
            <a class="url" href="<?=$value?>"><?=$key?></a>
          </li>
        <?php endforeach; ?>
      </ul>
    </main>
  </body>
</html>
