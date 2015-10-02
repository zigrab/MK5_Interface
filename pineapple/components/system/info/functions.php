<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<?php

if(isset($_GET['kill']) && is_numeric($_GET['kill'])){
  exec("kill ".$_GET['kill']);
}