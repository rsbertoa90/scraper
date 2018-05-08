<script src="js/jquery-3.3.1.min.js"></script>


<!-- Gestion de favoritos -->
  <script type="text/javascript">

        function agregarFav(fav)
            {

               if($('#'+fav).hasClass('fav'))
               {
                    $('#'+fav).removeClass('fav');
               } else
               {
                 $('#'+fav).addClass('fav');
               }

                datos={product_id: fav};
                console.log(datos);

                /*comentado temporalmente*/
                var request = $.ajax({
                    url: "php-functions/favorito.php", //Archivo de servidor que inserta en la BD
                    method: "POST",
                    data: datos
                });

                request.done(function( data ) {
                    console.log("Se agrego a favs: "+data);
                });

                request.fail(function( jqXHR, textStatus ) {
                    alert( "Error petición Ajax: " + textStatus );
                });

          }

    </script>
