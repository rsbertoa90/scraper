<?php
require_once("clases/categoria.php");

$errores=[];
$categorias = Categorias::getAll();

$mensajes="";

if ($_POST){
  $newCat = new Categoria();

  $newCat->name = trim($_POST["categoria"]);
  $newCat->start_url = trim($_POST["start_url"]);


  if(!trim($newCat->name) || !trim($newCat->start_url)){
    $mensajes="Error - Los campos no pueden estar vacios";
  }else{
    $mensajes=Categorias::guardar($newCat);
    if(!$mensajes){
      header("location: categorias.php?m=1");
      exit;
    }
  }

}

if(isset($_GET["action"])){
  if($_GET["action"]=="borrar"){

    $categoria = new Categoria($_GET["id"]);
    $mensajes = Categorias::borrar($categoria);
    if (!$mensajes){
      header("location: categorias.php?b=1");
    }
  }
}

if(isset($_GET["m"])){
  $mensajes="Nueva categoria insertada con exito!";
} elseif(isset($_GET["b"])){
  $mensajes="borrado exitoso";
}



 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php") ?>
  <body>
    <?php require_once("./partials/header.php") ?>
    <?php require_once("./partials/menu.php") ?>

    <main>
      <h3>  <span class="error-message"><?=$mensajes?></span> </h3>
      <div class="tabla">
        <table>
          <tr>
            <th>NOMBRE</th>
            <th>START_URL</th>
            <th>CANTIDAD DE PRODUCTOS SEGUIDOS</th>
            <th>ULTIMA INSERCION</th>
            <th> - </th>
          </tr>

          <?php foreach ($categorias as $cat): ?>
            <tr>
              <td>
                <?=$cat->name?>
              </td>
              <td>
                <a target="_blank" href="<?=$cat->start_url?>"> <?=$cat->start_url?></a>
              </td>
              <td>
                <?=$cat->cantidad_registros?>
              </td>
              <td>
                <?=$cat->last_insert?>
              </td>
              <td>
                <a href="categorias.php?action=borrar&id=<?=$cat->id?>" >[borrar]</a> </li>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
      <ul>
      </ul>
      <div class="">
        <h3>AGREGAR NUEVA CATEGORIA</h3>
        <form class="" action="categorias.php" method="post">
          <div class="">
            <input type="text" name="categoria" placeholder="NOMBRE" >
          </div>
          <div class="">
            <input type="text" name="start_url" placeholder="START_URL">
          </div>
          <button type="submit">ACEPTAR</button>
        </form>
      </div>

    </main>
  </body>
</html>
