<?php
namespace pineapple;

$pineAP = new PineAP();

function get_pineap_status()
{
    exec("pgrep pinejector", $pids);
    if (empty($pids)) {
        return false;
    } else {
        return true;
    }
}

touch("/etc/pineapple/ssid_file");
?>

<h2>PineAP Configuration<help id='pineap:pineap_config'></help></h2>
<?php if(!get_pineap_status()) echo "<center><span class='error'>PineAP is not running. Cannot load settings. <br /><a href='#sys/pineap/action/start_pineap/save_pineap_settings_wait'>Start Now</a></span></center>"; ?>

<span id='pineap_message'></span>

<fieldset>
    <legend>General</legend>
    <form method="POST" action="/components/system/pineap/functions.php?pineAP" onsubmit="$(this).AJAXifyForm(save_pineap_settings); return false;">
        Source: <input type='text' name='source' value='<?=$pineAP->getSource()?>'><br />
        Target: <input type='text' name='target' value='<?=$pineAP->getTarget()?>'><br />
        <br /><br />
        <table>
            <tr>
                <td>Beacon Interval:</td><td><select name='b_interval'><option value='agressive'>Agressive</option><option value='normal' selected>Normal</option><option value='low'>Low</option></select><td>(Currently <?=$pineAP->getBeaconInterval()?>)</td></td>
            </tr>
            <tr>
                <td>Response Interval:</td><td><select name='r_interval'><option value='agressive'>Agressive</option><option value='normal' selected>Normal</option><option value='low'>Low</option></select><td>(Currently <?=$pineAP->getResponseInterval()?>)</td></td>
            </tr>
        </table>
        <br /><br />
        <input type='submit' value='Save Settings'>
    </form>
</fieldset>

<br /><br />

<fieldset>
    <legend>SSID Management</legend>
    <textarea rows='20' style='min-width:100%;' readonly><?=file_get_contents("/etc/pineapple/ssid_file")?></textarea>
    <br /><br />
    <form method="POST" action="/components/system/pineap/functions.php?pineAP_SSID" onsubmit="$(this).AJAXifyForm(save_pineap_settings); return false;">
        <input type='text' name='ssid' placeholder='SSID'> <input type='submit' name='add_ssid' value='Add SSID'>
    </form>
    <form method="POST" action="/components/system/pineap/functions.php?pineAP_SSID" onsubmit="$(this).AJAXifyForm(save_pineap_settings); return false;">
        <input type='text' name='ssid' placeholder='SSID'> <input type='submit' name='del_ssid' value='Remove SSID'>
    </form>
</fieldset>