<?php
namespace pineapple;


include_once('/pineapple/includes/api/tile_functions.php');
echo "MK5 Karma<help id='pineap:karma'></help> ";
if (get_karma_status()) {
    echo "<font color=\"lime\">Enabled</font>.&nbsp; | <a href='#sys/pineap/action/stop_karma/pineap_reload_tile'>Stop</a><br />";
} else {
    echo "<font color=\"red\">Disabled</font>. | <a href='#sys/pineap/action/start_karma/pineap_reload_tile'>Start</a><br />"; 
}

echo "Autostart ";
if (get_autostart_status()) {
    echo "<font color=\"lime\">Enabled</font>.&nbsp; | <a href='#sys/pineap/action/stop_autostart/pineap_reload_tile'>Disable</a><br />";
} else {
    echo "<font color=\"red\">Disabled</font>. | <a href='#sys/pineap/action/start_autostart/pineap_reload_tile'>Enable</a><br />"; 
}

echo "<br /><br />";


echo "PineAP<help id='pineap:pineap'></help> ";
if (get_pineap_status()) {
    echo "<span class='success'>Enabled</span>. | <a href='#sys/pineap/action/stop_pineap/pineap_reload_tile'>Disable</a><br />";
} else {
    echo "<span id='pineap_status'><span class='error'>Disabled</span>. | <a href='#' onclick='start_pineap()'>Enable</a></span><br />";
}

echo "Dogma<help id='pineap:dogma'></help> ";
if (get_beaconer_status()) {
    echo "<span class='success'>Enabled</span>. | <a href='#sys/pineap/action/stop_beaconer/pineap_reload_tile'>Disable</a><br />";
} else {
    echo "<span class='error'>Disabled</span>. &nbsp;| <a href='#sys/pineap/action/start_beaconer/pineap_reload_tile'>Enable</a><br />";
}

echo "Beacon Response<help id='pineap:beacon_response'></help> ";
if (get_responder_status()) {
    echo "<span class='success'>Enabled</span>. | <a href='#sys/pineap/action/stop_responder/pineap_reload_tile'>Disable</a><br />";
} else {
    echo "<span class='error'>Disabled</span>. | <a href='#sys/pineap/action/start_responder/pineap_reload_tile'>Enable</a><br />";
}

echo "Auto Harvester<help id='pineap:harvester'></help> ";
if (get_harvester_status()) {
    echo "<span class='success'>Enabled</span>. | <a href='#sys/pineap/action/stop_harvester/pineap_reload_tile'>Disable</a><br />";
} else {
    echo "<span class='error'>Disabled</span>. &nbsp;| <a href='#sys/pineap/action/start_harvester/pineap_reload_tile'>Enable</a><br />";
}


function get_beaconer_status()
{
    $pineAP = new PineAP();
    if ($pineAP->isBeaconerRunning()) {
        return true;
    }
    return false;
}

function get_harvester_status()
{
    $pineAP = new PineAP();
    if ($pineAP->isHarvesterRunning()) {
        return true;
    }
    return false;
}

function get_responder_status()
{
    $pineAP = new PineAP();
    if ($pineAP->isResponderRunning()) {
        return true;
    }
    return false;
}

function get_pineap_status()
{
    exec("pgrep pinejector", $pids);
    if (empty($pids)) {
        return false;
    } else {
        return true;
    }
}

function get_karma_status()
{
    if (exec("hostapd_cli -p /var/run/hostapd-phy0 karma_get_state | tail -1") == "ENABLED") {
        return true;
    }
    return false;
}

function get_autostart_status()
{
    if (exec('ls /etc/rc.d/ | grep karma') == '') {
        return false;
    } else {
        return true;
    }
}


?>

<script type="text/javascript">

    var karma_log_refresh = 0;

    function pineap_reload_tile(){
        refresh_small('pineap', 'sys');
    }

    function start_pineap() {
        $("#pineap_status").html('Starting <img style="height: 1em; width: 1em;" src="/includes/img/throbber.gif">')
        $.get("/components/system/pineap/functions.php?action=start_pineap", function(){
            refresh_small('pineap', 'sys');
        });
    }

</script>
