<?php

namespace pineapple;

$pineapple = new Pineapple(__FILE__);

include_once('/pineapple/includes/api/tile_functions.php');


$pineapple->drawTabs(
    [
    'info.php' => 'About',
    'wired.php' => 'Wired',
    'ap_config.php' => 'Access Point',
    'client_mode.php' => 'Client Mode',
    'mobile.php' => 'Mobile Broadband',
    'advanced.php' => 'Advanced',
    ]
);
?>

<script type='text/javascript' src='<?=$rel_dir?>includes/helpers.js'></script>