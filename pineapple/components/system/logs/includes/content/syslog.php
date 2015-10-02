<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<h2>Syslog Output:</h2>
<?php
exec("logread | sort -nr | cut -c 8-", $log);
foreach ($log as $line) {
  echo $line."<br />";
}
?>