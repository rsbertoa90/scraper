<?php
require_once("clases/categoria.php");

if(!isset($categorias)){$categorias=Categorias::getAll();}
 ?>
<menu>
    <ul>
      <li class="menu-item"> <a href="categorias.php">ADMINISTRAR CATEGORIAS</a> </li>
      <li class="menu-item"> <a href="import.php">IMPORTAR CSV</a> </li>
      <li class="menu-item"> <a href="favoritos.php">FAVORITOS</a> </li>
      <li class="menu-item"> <h3> MEJORES VENDIDOS </h3>
        <ul>
          <?php foreach ($categorias as $cat): ?>
            <li>
              <a href="top10cat.php?id=<?=$cat->id?>">-> <?=$cat->name?></a>
            </li>
          <?php endforeach; ?>
        </ul>
      </li>
    </ul>
</menu>
