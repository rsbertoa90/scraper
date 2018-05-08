<?php
abstract class DataBase
{
public static  function conect() {

      $dsn='mysql:host=localhost;dbname=scraper;charset=utf8;port:3306';
      $dbuser='root';
      $dbpass='';
      $opt = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
      try {
          return new PDO($dsn,$dbuser,$dbpass, $opt);
        //$mysqli = new mysqli("localhost","root","rodrigo","scraper");
      } catch (PDOException $e) {
          echo $e->getMessage();exit;
      }
  }



}



 ?>
