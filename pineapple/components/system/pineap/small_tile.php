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

function karmaProbesStatus()
{
    if (exec("hostapd_cli -p /var/run/hostapd-phy0 karma_log_probes_state | tail -1") == "ENABLED") {
        return 'checked';
    }
    return false;
}

function karmaAssociationsStatus()
{
    if (exec("hostapd_cli -p /var/run/hostapd-phy0 karma_log_associations_state | tail -1") == "ENABLED") {
        return 'checked';
    }
    return false;
}

function karmaAutostartStatus()
{
    if (exec('ls /etc/rc.d/ | grep pineap\$') == '') {
        return false;
    } else {
        return 'checked';
    }
}

?>


<toggle infusion='pineap' action='toggle_karma' callback='pineap_reload_tile' system <?=karmaStatus()?>></toggle> MK5 Karma<help id='pineap:karma'></help>
<br>
Log: &nbsp; <toggle infusion='pineap' action='toggle_probes' callback='pineap_reload_tile' system <?=karmaProbesStatus()?>></toggle> Probes &nbsp;&nbsp;<toggle infusion='pineap' action='toggle_associations' callback='pineap_reload_tile' system <?=karmaAssociationsStatus()?>></toggle> Associations
<br>
<br>
<toggle infusion='pineap' action='toggle_pineap' callback='pineap_reload_tile' system <?=pineapStatus()?>></toggle> PineAP Daemon<help id='pineap:pineap'></help><br>
&nbsp;&nbsp;&nbsp;&nbsp;<toggle infusion='pineap' action='toggle_beacon' callback='pineap_reload_tile' system <?=beaconStatus()?>></toggle> Send Beacon Responses<br>
&nbsp;&nbsp;&nbsp;&nbsp;<toggle infusion='pineap' action='toggle_harvester' callback='pineap_reload_tile' system <?=harvesterStatus()?>></toggle> Harvest SSIDs<br>
&nbsp;&nbsp;&nbsp;&nbsp;<toggle infusion='pineap' action='toggle_dogma' callback='pineap_reload_tile' system <?=dogmaStatus()?>></toggle> Dogma
<br>
<br>
<toggle infusion='pineap' action='toggle_karma_autostart'system <?=karmaAutostartStatus()?>></toggle> Autostart [<a href='#' onclick="configure_autostart()">config</a>]<br><br>


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

    function configure_autostart() {
        $.get('/components/system/pineap/functions.php?action=get_autostart_config', function(data){
            popup(data);
        });
    }

</script>
