<?php
namespace pineapple;

include('/pineapple/includes/api/tile_functions.php');

$pineapple = new Pineapple(__FILE__);
$pineapple->magicToggleFunctions(true);


if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'start_pineap':
            toggle_pineap(true);
            break;
        case 'stop_pineap':
            toggle_pineap(false);
            break;
        case 'clear_ssids':
            $pineAP = new PineAP();
            $pineAP->clearSSIDs();
            break;
        case 'get_log':
            echo get_log();
            break;
        case 'clear_log':
            exec("echo '' > $(cat /etc/pineapple/karma_log_location)pineap.log");
            break;
        case 'get_client_report':
            echo get_client_report();
            break;
        case 'change_ssid_mode':
            change_ssid_mode();
            break;
        case 'change_mac_mode':
            change_mac_mode();
            break;
        case 'get_macs':
            echo get_macs();
            break;
        case 'get_ssids':
            echo get_ssids();
            break;
        case 'get_autostart_config':
            echo get_autostart_config();
            break;
        case 'get_client_ssids':
            echo get_client_ssids($_POST['mac_array']);
            break;
    }
}


function get_client_ssids($mac_array)
{
    $ssid_array = array();
    $file = fopen(trim(file_get_contents("/etc/pineapple/karma_log_location")) . "pineap.log", "r");
    while (($line = fgets($file)) !== false) {
        if (strpos($line, "associate")) {
            $mac = substr($line, 17, 17);
            if (in_array($mac, $mac_array)) {
                $ssid_array[$mac] = htmlspecialchars(substr($line, 61, -2), ENT_QUOTES);
            }
        }
    }

    return json_encode($ssid_array);
}


function toggle_karma($enable)
{
    if ($enable) {
        exec("pineapple karma start");
    } else {
        exec("pineapple karma stop");
    }
    return true;
}

function toggle_probes($enable)
{
    if ($enable) {
        exec("hostapd_cli -p /var/run/hostapd-phy0 karma_log_probes_enable");
    } else {
        exec("hostapd_cli -p /var/run/hostapd-phy0 karma_log_probes_disable");
    }
    return true;
}

function toggle_associations($enable)
{
    if ($enable) {
        exec("hostapd_cli -p /var/run/hostapd-phy0 karma_log_associations_enable");
    } else {
        exec("hostapd_cli -p /var/run/hostapd-phy0 karma_log_associations_disable");
    }
    return true;
}

function toggle_karma_autostart($enable)
{
    if ($enable) {
        autostart('enable');
    } else {
        autostart('disable');
    }
    return true;
}

function toggle_pineap($enable)
{
    if ($enable) {
        $mac = exec("ifconfig wlan0 | grep HWaddr | awk '{print $5}'");
        $chan = exec("iw dev wlan0 info | grep channel | awk '{print $2}'");
        
        $iface = exec("ifconfig -a | grep wlan1mon | head -n1 | awk '{print $1}'");
        if (trim($iface) == "") {
            exec("airmon-ng start wlan1");
            $iface = "wlan1mon";
        }

        exec("echo 'pineap {$chan} {$mac}' | at now");
        exec("echo 'pinejector {$iface}' | at now");
    } else {
        exec("killall pineap");
        exec("killall pinejector");
    }
    return true;
}

function toggle_dogma($enable)
{
    $pineAP = new PineAP();
    if ($enable) {
        return $pineAP->enableBeaconer();
    } else {
        return $pineAP->disableBeaconer();
    }
}

function toggle_beacon($enable)
{
    $pineAP = new PineAP();
    if ($enable) {
        return $pineAP->enableResponder();
    } else {
        return $pineAP->disableResponder();
    }
}

function toggle_harvester($enable)
{
    $pineAP = new PineAP();
    if ($enable) {
        return $pineAP->enableHarvester();
    } else {
        return $pineAP->disableHarvester();
    }
}

function get_autostart_config()
{
    $karma_status = (exec('uci get pineap.autostart.karma') == '1') ? "checked" : "";
    $probes_status = (exec('uci get pineap.autostart.log_probes') == '1') ? "checked" : "";
    $associations_status = (exec('uci get pineap.autostart.log_associations') == '1') ? "checked" : "";

    $pineap_status = (exec('uci get pineap.autostart.pineap') == '1') ? "checked" : "";
    $harvester_status = (exec('uci get pineap.autostart.harvester') == '1') ? "checked" : "";
    $beacon_response_status = (exec('uci get pineap.autostart.beacon_responses') == '1') ? "checked" : "";
    $dogma_status = (exec('uci get pineap.autostart.dogma') == '1') ? "checked" : "";

    echo "<center><h3>Configure Autostart</h3></center>";
    echo "<form method='POST' action='/components/system/pineap/functions.php?save_autostart' onsubmit='$(this).AJAXifyForm(close_popup); return false;'>";
    echo "<input type='checkbox' name='karma' $karma_status> MK5 Karma<br>";
    echo "&nbsp;Log: <input type='checkbox' name='probes' $probes_status> Probes &nbsp;&nbsp;<input type='checkbox' name='associations' $associations_status> Associations";
    echo "<br><br>";
    echo "<input type='checkbox' name='pineap' $pineap_status> PineAP Daemon<br>";
    echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='beacon_responses' $beacon_response_status> Send Beacon Responses<br>";
    echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='harvester' $harvester_status> Harvest SSIDs<br>";
    echo "&nbsp;&nbsp;&nbsp;<input type='checkbox' name='dogma' $dogma_status> Dogma<br>";
    echo "<br>";
    echo "<center><input type='submit' value='Save Settings'>";
    echo "</form>";
}

if (isset($_GET['save_autostart'])) {
    if (isset($_POST['karma'])) {
        exec("uci set pineap.autostart.karma=1");
    } else {
        exec("uci set pineap.autostart.karma=0");
    }
    if (isset($_POST['probes'])) {
        exec("uci set pineap.autostart.log_probes=1");
    } else {
        exec("uci set pineap.autostart.log_probes=0");
    }
    if (isset($_POST['associations'])) {
        exec("uci set pineap.autostart.log_associations=1");
    } else {
        exec("uci set pineap.autostart.log_associations=0");
    }
    if (isset($_POST['pineap'])) {
        exec("uci set pineap.autostart.pineap=1");
    } else {
        exec("uci set pineap.autostart.pineap=0");
    }
    if (isset($_POST['harvester'])) {
        exec("uci set pineap.autostart.harvester=1");
    } else {
        exec("uci set pineap.autostart.harvester=0");
    }
    if (isset($_POST['beacon_responses'])) {
        exec("uci set pineap.autostart.beacon_responses=1");
    } else {
        exec("uci set pineap.autostart.beacon_responses=0");
    }
    if (isset($_POST['dogma'])) {
        exec("uci set pineap.autostart.dogma=1");
    } else {
        exec("uci set pineap.autostart.dogma=0");
    }
    exec("uci commit pineap");
}

if (isset($_GET['tracking'])) {
    if (isset($_POST['tracking_script'])) {
        file_put_contents("/etc/pineapple/tracking_script_user", str_replace("\r", "", $_POST['tracking_script']));
        echo "<span class='success'>Script Saved.</span>";
    } else {
        $mac = $_POST['tracking_mac'];
        $add = isset($_POST['tracking_add_mac']) ? true : false;
        if (strlen($mac) == 17) {
            if ($add) {
                file_put_contents("/etc/pineapple/tracking_list", "{$mac}\n", FILE_APPEND);
                exec("/usr/bin/pineapple/uds_send /var/run/log_daemon.sock 'track:$mac'");
                echo "<span class='success'>MAC Added sucessfully.</span>";
            } else {
                exec("sed -r '/^({$mac})$/d' -i /etc/pineapple/tracking_list");
                exec("/usr/bin/pineapple/uds_send /var/run/log_daemon.sock 'untrack:$mac'");
                echo "<span class='success'>MAC Removed sucessfully.</span>";
            }
        } else {
            echo "<span class='error'>Please specify a valid MAC</span>";
        }
    }
}

if (isset($_POST['karma_log_location'])) {
    file_put_contents("/etc/pineapple/karma_log_location", $_POST['karma_log_location']);
    echo "<font color='lime'>Log Location changed successfully to '".$_POST['karma_log_location']."'. Changes will take effect after a reboot.</font>";
}

if (isset($_GET['change_ssid'])) {
    $_POST['ssid'] = str_replace("'", '\'"\'"\'', $_POST['ssid']);
    echo change_ssid($_POST['ssid'], $_POST['persistent']);
}

if (isset($_GET['client_list'])) {
    if ($_POST['remove_client'] == 'true') {
        echo del_mac($_POST['mac']);
    } else {
        echo add_mac($_POST['mac']);
    }
}

if (isset($_GET['ssid_list'])) {
    $_POST['ssid'] = str_replace("'", '\'"\'"\'', $_POST['ssid']);
    if ($_POST['remove_ssid'] == 'true') {
        echo del_ssid($_POST['ssid']);
    } else {
        echo add_ssid($_POST['ssid']);
    }
}


if (isset($_GET['pineap_add_ssid'])) {
    $pineAP = new PineAP();
    if ($pineAP->addSSID(rawurldecode($_GET['pineap_add_ssid']))) {
        echo htmlspecialchars_decode(rawurldecode($_GET['pineap_add_ssid']), ENT_QUOTES);
    }
}

if (isset($_GET['karma_ssidFilter_del'])) {
    echo del_ssid(htmlspecialchars_decode(rawurldecode($_GET['karma_ssidFilter_del'])));
}

if (isset($_GET['karma_ssidFilter_add'])) {
    echo add_ssid(htmlspecialchars_decode(rawurldecode($_GET['karma_ssidFilter_add'])));
}

if (isset($_GET['deauth'])) {
    $target = $_POST['target'];
    $source = $_POST['source'];
    $channel = substr($_POST['channel'], 9, 2);
    $multiplier = $_POST['multiplier'];

    $pineAP = new PineAP();

    if (!is_array($target)) {
        if ($pineAP->deauth($target, $source, $channel, $multiplier)) {
            echo "success";
        }
    } else {
        $success = 0;
        foreach ($target as $client) {
            if ($pineAP->deauth($client, $source, $channel, $multiplier)) {
                $success = 1;
            } else {
                $success = 0;
            }
        }
        echo ($success == 1) ? "success" : "";
    }



}

if (isset($_GET['pineAP'])) {
    $target = $_POST['target'];
    $source = $_POST['source'];
    $b_interval = $_POST['b_interval'];
    $r_interval = $_POST['r_interval'];

    $pineAP = new PineAP();
    $pineAP->setTarget($target);
    $pineAP->setSource($source);
    $pineAP->setBeaconInterval($b_interval);
    $pineAP->setResponseInterval($r_interval);

}

if (isset($_GET['pineAP_SSID'])) {
    $ssid = $_POST['ssid'];

    $pineAP = new PineAP();

    if (isset($_POST['add_ssid'])) {
        $pineAP->addSSID($ssid);
    } else {
        $pineAP->delSSID($ssid);
    }

}



function get_log()
{
    $leases = file_get_contents("/tmp/dhcp.leases");
    $arp = file_get_contents("/proc/net/arp");

    $file = fopen(trim(file_get_contents("/etc/pineapple/karma_log_location")) . "pineap.log", "r");

    while (($line = fgets($file)) !== false) {
        echo htmlspecialchars($line, ENT_QUOTES);
        ob_flush();
        flush();
    }
    fclose($file);
}

function get_client_report()
{
    $client_report = array();
    $client_report['stations'] = array();
    $client_report['dhcp'] = array();
    exec("cat /var/dhcp.leases", $client_report['dhcp']);
    exec("cat /proc/net/arp", $client_report['arp']);
    exec("iw dev wlan0 station dump | grep -A 1 'Station'", $station_dump);

    $count = 0;
    $station = "";
    foreach ($station_dump as $line) {
        if ($count == 0) {
            $station = substr($line, 8, 17);
        } elseif ($count == 1) {
            $station .= " " . substr($line, 16, 17);
            array_push($client_report['stations'], $station);
        }
        $count += 1;
        if ($count > 2) {
            $count = 0;
        }
    }

    return json_encode($client_report);
}

function autostart($mode)
{
    if ($mode == "enable") {
        exec("/etc/init.d/pineap enable");
    } else {
        exec("/etc/init.d/pineap disable");
    }
}

function change_ssid_mode()
{
    if (exec('hostapd_cli -p /var/run/hostapd-phy0 karma_get_black_white') == 'BLACK') {
        exec('hostapd_cli -p /var/run/hostapd-phy0 karma_white');
    } else {
        exec('hostapd_cli -p /var/run/hostapd-phy0 karma_black');
    }
}

function change_mac_mode()
{
    if (exec('hostapd_cli -p /var/run/hostapd-phy0 karma_get_mac_black_white') == 'BLACK') {
        exec('hostapd_cli -p /var/run/hostapd-phy0 karma_mac_white');
    } else {
        exec('hostapd_cli -p /var/run/hostapd-phy0 karma_mac_black');
    }
}

function change_ssid($ssid, $persistence = false)
{
    exec("hostapd_cli -p /var/run/hostapd-phy0 karma_change_ssid '".$ssid."'");
    if ($persistence) {
        exec("uci set wireless.@wifi-iface[0].ssid='".$ssid."'");
        exec("uci commit wireless");
    }
    return "<font color='lime'>SSID changed to '$ssid'.</font>";
}

function add_ssid($ssid)
{
    $ssid = escapeshellarg($ssid);
    exec("pineapple karma add_ssid {$ssid}");
    return "<font color='lime'>SSID added to list.</font>";
}

function del_ssid($ssid)
{
    $ssid = escapeshellarg($ssid);
    exec("pineapple karma del_ssid {$ssid}");
    return "<font color='lime'>SSID removed from list.</font>";
}

function add_mac($mac)
{
    exec('pineapple karma add_mac "'.$mac.'"');
    return "<font color='lime'>MAC added to list.</font>";
}

function del_mac($mac)
{
    exec('pineapple karma del_mac "'.$mac.'"');
    return "<font color='lime'>MAC removed from list.</font>";
}

function get_ssids()
{
    exec("pineapple karma list_ssids", $ssid_list);
    echo "<b>List of SSIDs:</b><br />";
    foreach ($ssid_list as $ssid) {
        echo $ssid."<br />";
    }
}

function get_macs()
{
    exec("pineapple karma list_macs", $mac_list);
    echo "<b>List of MAC addresses:</b><br />";
    foreach ($mac_list as $mac) {
        echo $mac."<br />";
    }
}
