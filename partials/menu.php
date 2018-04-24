<?php
require_once("sql/sql-functions.php");
if(!isset($categorias)){$categorias=categorias();}
 ?>
<menu>
    <ul>
      <li> <a href="categorias.php">ADMINISTRAR CATEGORIAS</a> </li>
      <li> <a href="import.php">IMPORTAR CSV</a> </li>
      <?php foreach ($categorias as $cat): ?>
        <li> <a href="top10cat.php?id=<?=$cat['id']?>">Mejores vendidos: <?=$cat["name"]?></a> </li>
      <?php endforeach; ?>
    </ul>
</menu>
