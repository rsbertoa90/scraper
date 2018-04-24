<header>
  <div class="navbar">
    <div class="brand">
      <h2> <a href="index.php">SCRAPER</a></h2>
    </div>
    <div class="search">
      <form class="form" id="searchform"  action="search_results.php" method="GET">
         <div class="form-block">
           <label for="">Buscar por nombre</label>
           <input type="text" name="search" placeholder="Buscar productos">
         </div>
         <div class="form-block">
           <label for="">Precio mayor que </label>
           <input class="numberInput"type="number" name="min">
         </div>
         <div class="form-block">
          <label for="">Precio menor que</label>
           <input class="numberInput" type="number" name="max">
         </div>
         <button type="submit">Buscar</button>
     </form>

    </div>
    <div class="links">
      <a href="index.php">HOME</a>
    </div>
  </div>
</header>
