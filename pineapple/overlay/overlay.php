<?php
namespace pineapple;

$pineapple = new Pineapple(__FILE__);
require_once($pineapple->directory . "/" . "overlay.class.php");


namespace overlay;

$overlay = new Overlay('wlan0');

if (isset($_GET['ap_scan'])) {
    echo $overlay->generateAPs();
    exit();
}

if (isset($_GET['retreive_aps'])) {
    echo $overlay->retreiveAPs();
    exit();
}

if (isset($_GET['generate_stations'])) {
    if (is_numeric($_GET['generate_stations'])) {
        $duration = $_GET['generate_stations'];
    } else {
        $duration = 30;
    }
    $overlay->generateStations($duration);
    exit();
}

if (isset($_GET['retreive_stations'])) {
    echo $overlay->retreiveStations();
    exit();
}

?>

<fieldset style='width: 250px'>
    <legend>Scan Settings</legend>
    Type:
    <input type='radio' name='scan_type' value='ap' checked>AP Only
    <input type='radio' name='scan_type' value='ap_client'>AP & Client
    <br /><br />
    Duration:
    <select name='scan_duration'>
        <option value='15'>15 Seconds</option>
        <option value='30'>30 Seconds</option>
        <option value='45'>45 Seconds</option>
        <option value='60'>1 Minute</option>
        <option value='120'>2 Minutes</option>
        <option value='300'>5 Minutes</option>
        <option value='600'>10 Minutes</option>
    </select>
    <br />
    Continuous: <input type='checkbox' name='auto_scan'>
    <br /><br />
    <a id='overlay_start_stop' href='#' onclick="overlay_toggle_scan(); return false;">START SCAN</a> <span class='overlay_message'></span>
    
</fieldset>
<center>
    <br />
    <div class='overlay_loading'>
        <img width="100px" src="/includes/img/throbber.gif"><br />
        <h3 style="background-color:black;">Running Initial Scan</h3>
    </div>
    <span class='overlay_col' id='1'></span>
    <span class='overlay_col' id='2'></span>
    <span class='overlay_col' id='3'></span>
    <span class='overlay_col' id='4'></span>
</center>

<script type="text/javascript" src="/overlay/overlay.js"></script>

<script type='text/javascript'>
    initial_scan()
</script>