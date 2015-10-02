<?php

namespace pineapple;

$pineapple = new Pineapple(__FILE__);

include_once('/pineapple/includes/api/tile_functions.php');


$pineapple->drawTabs(
    [
    'main.php' => 'Main',
    'dip.php' => 'Boot Modes',
    'cron.php' => 'Scheduled Tasks',
    'dnsspoof.php' => 'DNS Spoof',
    'css.php' => 'CSS Editor',
    'advanced.php' => 'Advanced',
    ]
);
?>

<script type='text/javascript' src='<?=$rel_dir?>includes/helpers.js'></script>