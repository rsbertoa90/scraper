<?php
require_once("sql/sql-functions.php");

$errores=[];
$categorias = categorias();
$totalScrapes = totalScrapes($categorias);

$mensajes["categoria"]="";

if ($_POST){
  $newCat["name"] = trim($_POST["categoria"]);
  $newCat["start_url"] = trim($_POST["start_url"]);
  if(!trim($newCat["name"]) || !trim($newCat["start_url"])){
    $mensajes["categoria"]="Error - Los campos no pueden estar vacios";
  }else{
    $mensajes["categroia"]=nuevaCategoria($newCat);
    if(!$mensajes["categoria"]){
      header("location: categorias.php?m=1");
      exit;
    }
  }

}

if(isset($_GET["action"])){
  if($_GET["action"]=="borrar"){
    $mensajes["categoria"] = borrarCategoria($_GET["id"]);
    if (!$mensajes["categoria"]){
      header("location: categorias.php?b=1");
    }
  }
}

if(isset($_GET["m"])){
  $mensajes["categoria"]="Nueva categoria insertada con exito!";
} elseif(isset($_GET["b"])){
  $mensajes["categoria"]="borrado exitoso";
}



 ?>

<!DOCTYPE html>
<html>
  <?php require_once("partials/head.php") ?>
  <body>
    <?php require_once("./partials/header.php") ?>
    <?php require_once("./partials/menu.php") ?>

    <main>
      <h3>  <span class="error-message"><?=$mensajes['categoria']?></span> </h3>
      <div class="tabla">
        <table>
          <tr>
            <th>NOMBRE</th>
            <th>START_URL</th>
            <th> - </th>
          </tr>
          <?php foreach ($categorias as $categoria): ?>
            <tr>
              <td>
                <?=$categoria["name"]?>
              </td>
              <td>
                <a href="<?=$categoria["start_url"]?>"> <?=$categoria["start_url"]?></a>
              </td>
              <td>
                <a href="categorias.php?action=borrar&id=<?=$categoria['id']?>" >[borrar]</a> </li>
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
