<?php

namespace pineapple;

$pineapple = new Pineapple(__FILE__);
$log_array = array();


if ($tmp_folder = @opendir("/tmp/infusion_logs")) {
    while (($log = readdir($tmp_folder)) !== false) {
        if ($log != "." && $log != "..") {
            array_push($log_array, $log);
        }
    }
}

if ($tmp_folder = @opendir("/sd/infusion_logs")) {
    while (($log = readdir($tmp_folder)) !== false) {
        if ($log != "." && $log != ".." && !in_array($log, $log_array)) {
            array_push($log_array, $log);
        }
    }
}

echo "<center>";
if (!empty($log_array)) {
    echo "<form method='POST' action='" . $pineapple->infusionRelRoot('logs') . "functions.php' onSubmit='$(this).AJAXifyForm(load_infusion_log); return false;'>";
    echo "<select name='infusion'>";
    foreach ($log_array as $log) {
        echo "<option id='{$log}'>{$log}</option>";
    }
    echo "</select> ";
    echo "<input type='submit' value='Load Log' name='load_log'>";
    echo "</form>";
} else {
    echo "No infusion logs present.";
}
echo "</center>";

?>

<div id='infusion_log'></div>