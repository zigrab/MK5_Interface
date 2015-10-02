<?php

namespace pineapple;

$pineapple = new Pineapple(__FILE__);

include_once('/pineapple/includes/api/tile_functions.php');


$pineapple->drawTabs(
    [
    'setup.php' => 'Setup',
    'hosts.php' => 'Known Hosts',
    'keys.php' => 'Authorized Keys',
    'copy_key.php' => 'Transfer Public Key',
    ]
);

?>

<script type='text/javascript' src='<?=$rel_dir?>includes/helpers.js'></script>