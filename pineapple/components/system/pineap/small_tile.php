<?php
namespace pineapple;

function dogmaStatus()
{
    $pineAP = new PineAP();
    if ($pineAP->isBeaconerRunning()) {
        return 'checked';
    }
    return false;
}

function harvesterStatus()
{
    $pineAP = new PineAP();
    if ($pineAP->isHarvesterRunning()) {
        return 'checked';
    }
    return false;
}

function beaconStatus()
{
    $pineAP = new PineAP();
    if ($pineAP->isResponderRunning()) {
        return 'checked';
    }
    return false;
}

function pineapStatus()
{
    exec("pgrep pineap", $pids);
    if (empty($pids)) {
        return false;
    } else {
        return 'checked';
    }
}

function karmaStatus()
{
    if (exec("hostapd_cli -p /var/run/hostapd-phy0 karma_get_state | tail -1") == "ENABLED") {
        return 'checked';
    }
    return false;
}

function karmaAutostartStatus()
{
    if (exec('ls /etc/rc.d/ | grep karma') == '') {
        return false;
    } else {
        return 'checked';
    }
}

?>


<toggle infusion='pineap' action='toggle_karma' callback='pineap_reload_tile' system <?=karmaStatus()?>></toggle> MK5 Karma<help id='pineap:karma'></help> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
<toggle infusion='pineap' action='toggle_karma_autostart'system <?=karmaAutostartStatus()?>></toggle> Autostart<br><br>

<toggle infusion='pineap' action='toggle_pineap' callback='pineap_reload_tile' system <?=pineapStatus()?>></toggle> PineAP<help id='pineap:pineap'></help><br><br>
<toggle infusion='pineap' action='toggle_dogma' callback='pineap_reload_tile' system <?=dogmaStatus()?>></toggle> Dogma<help id='pineap:dogma'></help><br><br>
<toggle infusion='pineap' action='toggle_beacon' callback='pineap_reload_tile' system <?=beaconStatus()?>></toggle> Beacon Response<help id='pineap:beacon_response'></help><br><br>
<toggle infusion='pineap' action='toggle_harvester' callback='pineap_reload_tile' system <?=harvesterStatus()?>></toggle> Harvester<help id='pineap:harvester'></help>


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
