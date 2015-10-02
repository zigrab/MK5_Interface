<?php

if (exec("uci get wireless.@wifi-iface[1].disabled") == "1") {
    $disabled = "checked";
} else {
    $disabled = "";
}

if (exec("uci get wireless.@wifi-iface[0].hidden") == "1") {
    $hidden = "checked";
} else {
    $hidden = "";
}


?>

<fieldset>
  <legend>Open Access Point<help id='network:open_ap'></help></legend>

  <form id="ap_config" method="POST" action="/components/system/network/functions.php?open_ap_config" onSubmit="$(this).AJAXifyForm(popup); return false;">
    <table>
      <tr><td>SSID:</td><td><input type="text" name="ssid" value="<?=str_replace('"', '&quot;', trim(exec("uci get wireless.@wifi-iface[0].ssid")))?>"></td></tr>
      <tr><td>Channel:</td><td><select name="channel" ><? for($i=1; $i<= 14; $i++){echo "<option value='$i'>$i</option>";} ?></select></td></tr>
      <tr><td>Hidden:</td><td><input type="checkbox" name="hidden" <?=$hidden?>></td></tr>
  </table>
  <input type="submit" value="Save">
</form>

</fieldset>

<br /><br />

<fieldset>
  <legend>Secure Management Access Point<help id='network:management_ap'></help></legend>

  <form id="ap_config" method="POST" action="/components/system/network/functions.php?management_ap_config" onSubmit="$(this).AJAXifyForm(popup); return false;">
    <table>
      <tr><td>SSID:</td><td><input type="text" name="ssid" value="<?=str_replace('"', '&quot;', trim(exec("uci get wireless.@wifi-iface[1].ssid")))?>"></td></tr>
      <tr><td>WPA2 Password:</td><td><input type="password" name="password"></td></tr>
      <tr><td>Disabled:</td><td><input type="checkbox" name="disabled" <?=$disabled?>></td></tr>
  </table>
  <p><small>Note: The channel of the secure management access point will be the same as the one of the open access point.</small></p>
  <input type="submit" value="Save">
</form>

</fieldset>

<br /><br />

<script type="text/javascript">
    <?php 
    $encryption = trim(exec("uci get wireless.@wifi-iface[0].encryption"));
    if ($encryption == "psk2+ccmp") {
        echo "var encryption='WPA2';";
    } elseif ($encryption == "psk+ccmp") {
        echo "var encryption='WPA';";
    } else {
        echo "var encryption='None';";
    }
    echo "var channel=".trim(exec("uci get wireless.radio0.channel")).";";
    ?>

    $('option[value="'+encryption+'"]').prop('selected', 'true');
    $('option[value="'+channel+'"]').prop('selected', 'true');
</script>