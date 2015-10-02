<?php

namespace pineapple;

$pineapple = new Pineapple(__FILE__);

include_once('/pineapple/includes/api/tile_functions.php');


$pineapple->drawTabs(
    [
    'installed.php' => 'Pineapple Bar: Installed',
    'available.php' => 'Pineapple Bar: Available',
    'create.php' => 'Bartender: Create New Infusions',
    'manage.php' => 'Bartender: Manage Your Infusions',
    ]
);

?>

<script type='text/javascript' src='<?=$rel_dir?>includes/helpers.js'></script>