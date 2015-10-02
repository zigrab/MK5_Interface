<fieldset>
    <legend>Reporting Log</legend>
    <?php
    $log_file = fopen("/tmp/reporting.log", "r");
    if ($log_file) {
        while (($line = fgets($log_file)) !== false) {
            echo "{$line}<br>";
        }
    } else {
        echo "No log entry found";
    }
    fclose($log_file);
    ?>
</fieldset>