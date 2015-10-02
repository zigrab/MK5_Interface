<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<h2>Network Info</h2>

<fieldset>
<legend>Route</legend>
<?php
exec('route', $route);
echo '<pre>';
foreach($route as $line){
  echo $line."\n";
}
echo '</pre>';
?>
</fieldset>

<br /><br />

<fieldset>
<legend>Ifconfig</legend>
<?php
exec('ifconfig -a', $ifconfig);
echo '<pre>';
foreach($ifconfig as $line){
  echo $line."\n";
}
echo '</pre>';
?>
</fieldset>