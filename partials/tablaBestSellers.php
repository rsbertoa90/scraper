<!-- botones para ordernar por vendidos o por monto en dinero -->
<form class="" action="#?id=<?=$_GET['id']?>" method="post">
  <?php if (Productos::$criterio_ordenamiento == "ventas_periodo"): ?>
      <button type="submit" class="enabled" >Ordernar por dinero movido</button>
      <button class= "disabled" type="submit" name="ventas_periodo" value="1" disabled>Ordenar por cantidad de vendidos</button>
  <?php else: ?>
      <button type="submit" class ="disabled" disabled>Ordernar por dinero movido</button>
      <button type="submit" name="ventas_periodo" class="enabled" value="1" >Ordenar por cantidad de vendidos</button>
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
  <?php foreach ($productos as $item): ?>

    <tr>
      <?php foreach ($indexes as $index): ?>
        <?php if(trim($item->url)){$item->titulo="<a target='_blank' href='{$item->url}'>{$item->titulo}</a>";} ?>
        <td><?=$item->$index?></td>
      <?php endforeach; ?>
      <td> <a href="historico.php?product_id=<?=$item->product_id?>">[ver historico]</a></td>
      <?php if ($item->EsFavorito): ?>
        <td> <button  class="heart"type="button" onclick="agregarFav('<?=$item->product_id?>')"> <span id="<?=$item->product_id?>" class="heart ion-android-favorite fav"></span> </button> </td>
      <?php else:?>
        <td>  <button class="heart"  type="button" onclick="agregarFav('<?=$item->product_id?>')"> <span  id="<?=$item->product_id?>" class="heart ion-android-favorite"></span> </button> </td>
      <?php endif;?>
    </tr>

  <?php endforeach; ?>
</table>
