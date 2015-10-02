<?php

namespace pineapple;

$pineapple = new Pineapple(__FILE__);

include_once('/pineapple/includes/api/tile_functions.php');


$pineapple->drawTabs(
    [
    'about.php' => 'About',
    'upgrade.php' => 'Firmware Upgrade',
    'license.php' => 'License',
    ]
);
?>

<script type='text/javascript' src='<?=$rel_dir?>includes/helpers.js'></script>