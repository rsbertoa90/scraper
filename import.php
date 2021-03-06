<?php
require_once("clases/categoria.php");
require_once("clases/producto.php");


if(!isset($categorias)){$categorias= Categorias::getAll();}


$mensajes="";




if (isset($_GET["m"])){$mensajes="IMPORTACION EXITOSA!";}



if (isset($_GET["share"])){ $mensajes="SHARED!"; }

if (isset($_FILES["archivo"]))
{

    $mensajes = Productos::importar($_FILES["archivo"]);
    if (!$mensajes)
    {
      header("location: import.php?m=1");
      exit;
    }
}

if(isset($_POST["share"]))
{
    $mensajes = Productos::share();
    if(!$mensajes)
    {
      header("location: import.php?share=true");
      exit;
    }

}

?>

<!DOCTYPE html>

<html>
  <?php require_once("partials/head.php") ?>
  <body>
  <?php require_once("partials/header.php") ?>
  <?php require_once("partials/menu.php") ?>
  <main id = "upload">

    <div>
      <h1>IMPORTAR ARCHIVO DE DATOS DE SCRAP</h1>
      <h2>  <span class="error-message"><?=$mensajes?></span> </h2>
    </div>

    <form class="" action="import.php" method="post" enctype="multipart/form-data" id="uploadForm">
      <!-- <div class="form-block">
        <select name="categoria">
          <option value="NULL"> Elige una categoria </option>
          <?php foreach ($categorias as $categoria): ?>
            <option value="<?=$categoria->id?>"> <?=$categoria->name?> </option>
          <?php endforeach; ?>
        </select>
      </div> -->

      <div class="form-block">
        <span>SUBIR ARCHIVO</span>
        <input type="file" name="archivo">

      </div>
      <button type="submit">ENVIAR</button>
    </form>

    <form action="import.php" method="post">
      <button type="SUBSTRING" name="share" value="1">SHARE!</button>
    </form>
    <button type="button" name="button"></button>

  </main>
  <?php require_once("partials/js-import.php") ?>

</body>
</html>
