<?php

namespace pineapple;

$pineapple = new Pineapple(__FILE__);

include_once('/pineapple/includes/api/tile_functions.php');


$pineapple->drawTabs(
    [
    'main.php' => 'Main Resources',
    'usb.php' => 'USB Info',
    'taskmgr.php' => 'Task Manager',
    ]
);
?>

<script type='text/javascript' src='<?=$rel_dir?>includes/helpers.js'></script>