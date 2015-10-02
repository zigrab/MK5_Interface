<?php 
namespace pineapple;
global $directory, $rel_dir;

$pineapple = new Pineapple(__FILE__);

include_once('/pineapple/includes/api/tile_functions.php'); 


$pineapple->drawTabs(
    [
    'syslog.php'=>'Syslog',
    'dmesg.php'=>'Dmesg',
    'custom.php'=>'Custom',
    ]
);

?>



<script type='text/javascript' src='<?=$rel_dir?>includes/helpers.js'></script>