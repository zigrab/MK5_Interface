<?php
namespace pineapple;

$pineapple = new Pineapple(__FILE__);
require_once($pineapple->directory . "/" . "overlay.class.php");


namespace overlay;

$overlay = new Overlay('wlan0');

if (isset($_GET['ap_scan'])) {
    echo $overlay->getAPs();
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

<center>
    <fieldset>
        <legend>Settings</legend>
        Scan Type:
        <input type='radio' name='scan_type' value='ap' checked>AP Only
        <input type='radio' name='scan_type' value='ap_client'>AP & Client
        &nbsp;&nbsp;&nbsp;
        Scan Duration:
        <select name='scan_duration'>
            <option value='15'>15 Seconds</option>
            <option value='30'>30 Seconds</option>
            <option value='45'>45 Seconds</option>
            <option value='60'>1 Minute</option>
            <option value='120'>2 Minutes</option>
            <option value='300'>5 Minutes</option>
            <option value='600'>10 Minutes</option>
        </select>
        &nbsp;&nbsp;&nbsp;
        Continuous Scan: <input type='checkbox' name='auto_scan'>
        &nbsp;&nbsp;&nbsp;
        <a href='#' onclick="overlay_start_scan(); return false;">START</a> | <a href='#' onclick="overlay_stop_scan(); return false;">STOP</a>
    </fieldset>
    <br />
    <div class='overlay_message'></div>
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