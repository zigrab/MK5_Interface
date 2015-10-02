<?php
namespace pineapple;

$pineapple = new Pineapple(__FILE__);

$pineapple->drawTabs(
    [
    'config.php' => 'Report Configuration',
    'log.php' => 'Report Log',
    'syslog.php' => 'System Log',
    'dmesg.php' => 'Dmesg',
    'infusions.php' => 'Infusion Log',
    'custom.php' => 'Custom Log',
    ]
);
?>

<script type='text/javascript' src='<?=$pineapple->rel_dir?>/includes/helpers.js'></script>
