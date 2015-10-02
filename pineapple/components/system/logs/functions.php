<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<?php
$directory = realpath(dirname(__FILE__)).'/';
$rel_dir = str_replace('/pineapple', '', $directory);


if (isset($_GET['kill']) && is_numeric($_GET['kill'])) {
    exec("kill ".$_GET['kill']);
}

if (isset($_GET['update_log']) && isset($_POST['log'])) {
    $handle = fopen($directory.'custom', 'w');
    fwrite($handle, $_POST['log']);
    fclose($handle);
    echo "Log set successfully";
}

if (isset($_POST['load_log'])) {
    $infusion = $_POST['infusion'];
    if (file_exists("/sd/infusion_logs/{$infusion}")) {
        $log = file_get_contents("/sd/infusion_logs/{$infusion}");
        echo "<fieldset><legend>SD log</legend><pre>" . htmlspecialchars($log) . "</pre></fieldset>";
    }
    if (file_exists("/tmp/infusion_logs/{$infusion}")) {
        $log = file_get_contents("/tmp/infusion_logs/{$infusion}");
        echo "<br /><br /><fieldset><legend>Temp log</legend><pre>" . htmlspecialchars($log) . "</pre></fieldset>";
    }
}
