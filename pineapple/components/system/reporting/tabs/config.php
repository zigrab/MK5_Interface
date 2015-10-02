<?php
$config_enable = (exec("grep files/reporting /etc/crontabs/root") == "") ? "" : "checked";
$config_interval = exec("uci get reporting.settings.interval");
$config_save = (exec("uci get reporting.settings.save_report") == "1") ? "checked" : "";
$config_send = (exec("uci get reporting.settings.send_email") == "1") ? "checked" : "";

$content_log = (exec("uci get reporting.settings.log") == "1") ? "checked" : "";
$content_tracking = (exec("uci get reporting.settings.tracking") == "1") ? "checked" : "";
$content_client = (exec("uci get reporting.settings.client") == "1") ? "checked" : "";
$content_survey = (exec("uci get reporting.settings.survey") == "1") ? "checked" : "";
$content_clear_log = (exec("uci get reporting.settings.clear_log") == "1") ? "checked" : "";
$content_duration = exec("uci get reporting.settings.duration");


$ssmtp_from = exec("uci get reporting.ssmtp.from");
$ssmtp_to = exec("uci get reporting.ssmtp.to");
$ssmtp_server = exec("uci get reporting.ssmtp.server");
$ssmtp_port = exec("uci get reporting.ssmtp.port");
$ssmtp_domain = exec("uci get reporting.ssmtp.domain");
$ssmtp_username = exec("uci get reporting.ssmtp.username");
$ssmtp_password = exec("uci get reporting.ssmtp.password");
$ssmtp_tls = exec("uci get reporting.ssmtp.tls");
$ssmtp_starttls = exec("uci get reporting.ssmtp.starttls");
?>

<div id="reporting_message" style="text-align: center"></div>
<fieldset>
    <legend>Report Configuration</legend>
    <form method="POST" action="/components/system/reporting/functions.php" onsubmit="$(this).AJAXifyForm(save_reporting_config); return false;">
        <input type="checkbox" name="enable" <?=$config_enable?>> Generate Report<br>
         &nbsp;&nbsp;&nbsp;&nbsp;every <select name="interval">
            <?php
            for ($i=1; $i <= 24; $i++) {
                if ($i == $config_interval) {
                    $selected = "selected";
                } else {
                    $selected = "";
                }
                echo "<option value='{$i}' {$selected}>{$i} hours</option>";
            }
            ?>
        </select><br>
        <input type="checkbox" name="save_report" <?=$config_save?>> Store report on SD card<br>
        <input type="checkbox" name="send_email" <?=$config_send?>> Send report via email<br><br>
        <input type="submit" value="Save" name="save_report_config">
    </form>
</fieldset>

<br><br>

<fieldset>
    <legend>Report Contents</legend>
    <form method="POST" action="/components/system/reporting/functions.php" onsubmit="$(this).AJAXifyForm(save_reporting_config); return false;">
        <input type="checkbox" name="log" <?=$content_log?>> PineAP Log<br>
        &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="clear_log" <?=$content_clear_log?>> Clear when done<br>
        <input type="checkbox" name="survey" <?=$content_survey?>> PineAP Site Survey<br>
        &nbsp;&nbsp;&nbsp;&nbsp;Duration of AP & Client scan
            <select name="duration">
                <option value="15">15 seconds</option>
                <option value="30">30 seconds</option>
                <option value="60">1 minutes</option>
                <option value="120">2 minutes</option>
                <option value="300">5 minutes</option>
            </select><br>
        <input type="checkbox" name="client" <?=$content_client?>> PineAP Probing Clients Report<br>
        <input type="checkbox" name="tracking" <?=$content_tracking?>> PineAP Tracked Clients Report<br><br>
        <input type="submit" value="Save" name="save_report_contents">
    </form>
</fieldset>
<?="<script>$('select[name=duration]').val('{$content_duration}')</script>"?>
<br><br>

<fieldset>
    <legend>Email Configuration<help id='reporting:email_config'></help></legend>
    <form method="POST" action="/components/system/reporting/functions.php" onsubmit="$(this).AJAXifyForm(save_reporting_config); return false;">
        <table>
            <tr><td>From </td><td><input name="from" type="text" value="<?=$ssmtp_from?>"></td></tr>
            <tr><td>To </td><td><input name="to" type="text"value="<?=$ssmtp_to?>" ></td></tr>
            <tr><td>SMTP Server </td><td><input name="server" type="text" value="<?=$ssmtp_server?>"></td></tr>
            <tr><td>SMTP Port </td><td><input name="port" type="text" value="<?=$ssmtp_port?>"></td></tr>
            <tr><td>Domain </td><td><input name="domain" type="text" value="<?=$ssmtp_domain?>"></td></tr>
            <tr><td>Username </td><td><input name="email_username" type="text" value="<?=$ssmtp_username?>"></td></tr>
            <tr><td>Password </td><td><input name="email_password" type="password" value="<?=$ssmtp_password?>"></td></tr>
            <tr><td>Use TLS </td><td><input name="tls" type="checkbox" <?=$ssmtp_tls?>></td></tr>
            <tr><td>Use STARTTLS </td><td><input name="starttls" type="checkbox" <?=$ssmtp_starttls?>></td></tr>
            <tr><td><input name="save_email_config" type="submit" value="Save"></td><td></td><td></td></tr>
        </table>
    </form>
</fieldset> 
