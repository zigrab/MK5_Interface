<?php 
namespace pineapple;

$pineapple = new Pineapple(__FILE__);

include_once('/pineapple/includes/api/tile_functions.php'); 


$pineapple->drawTabs(
    [
    'report.php'=>'Intelligence Report',
    'pineap.php'=>'PineAP',
    'config.php'=>'Karma',
    'log.php'=>'Karma Log'
    ]
);

?>
<script type='text/javascript' src='<?=$rel_dir?>includes/helpers.js'></script>