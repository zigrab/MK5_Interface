<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<div style='text-align: right'><a href='#' class="refresh" onclick='refresh_small("resources", "sys")'> </a></div>
<pre>
<?php
$cmd = "free -h";
exec ($cmd, $output);
foreach($output as $outputline) {
  echo ("$outputline\n");
}
$output = "";
?>
</pre>