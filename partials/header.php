<?php
  $min="";
  $max="";
  $search="";
  if (isset($_GET["min"])){$min=$_GET["min"];}
  if (isset($_GET["max"])){$max=$_GET["max"];}
  if (isset($_GET["search"])){$search=$_GET["search"];}


 ?>

<header>
  <div class="navbar">
    <div class="brand">
      <h2> <a href="index.php">SCRAPER BETA TEST OBJETOS</a></h2>
    </div>
    <div class="search">
      <form class="form" id="searchform"  action="search_results.php" method="GET">
         <div class="form-block">
           <label for="">Buscar por nombre</label>
           <input type="text" name="search" placeholder="Buscar productos" value="<?=$search?>">
         </div>
         <div class="form-block">
           <label for="">Precio mayor que </label>
           <input class="numberInput"type="number" name="min" value=<?=$min?>>
         </div>
         <div class="form-block">
          <label for="">Precio menor que</label>
           <input class="numberInput" type="number" name="max" value=<?=$max?>>
         </div>
         <button type="submit">Buscar</button>
     </form>

    </div>
    <div class="links">
      <a href="index.php">HOME</a>
    </div>
  </div>
</header>
