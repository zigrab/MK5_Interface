<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<h2>Karma Configuration<help id='pineap:karma_config'></help></h2>
<center><div id='karma_message'/></center>
<fieldset>
  <legend>Client Filtering - <a href="#sys/pineap/action/get_macs/popup">View List</a></legend>
  Currently in <?=(exec('hostapd_cli -p /var/run/hostapd-phy0 karma_get_mac_black_white') == "BLACK") ? "DENY" : "ALLOW"?> mode. <a href='#sys/pineap/action/change_mac_mode/karma_reload_config'>Switch</a>
  <form id="karma_client_bw_form" method="post" action="/components/system/pineap/functions.php?client_list" onSubmit='$(this).AJAXifyForm(karma_handle_form); return false;'>
    <table>
      <tr><td>MAC to add to list:</td><td><input type='text' name='mac' /></td></tr>
      <tr><td><input type='submit' name='submit' value='Add' onClick='$("#remove_client").val("false")'><input type='submit' name='submit' value='Remove' onClick='$("#remove_client").val("true")'></td><td></td></tr>
      <input name='remove_client' id='remove_client' type='hidden' />
    </table>
  </form>
</fieldset>

<br /><br />

<fieldset>
  <legend>SSID Filtering - <a href="#sys/pineap/action/get_ssids/popup">View List</a></legend>
  Currently in <?=(exec('hostapd_cli -p /var/run/hostapd-phy0 karma_get_black_white') == "BLACK") ? "DENY" : "ALLOW"?> mode. <a href='#sys/pineap/action/change_ssid_mode/karma_reload_config'>Switch</a>
  <form id="karma_ssid_bw_form" method="post" action="/components/system/pineap/functions.php?ssid_list" onSubmit='$(this).AJAXifyForm(karma_handle_form); return false;'>
    <table>
      <tr><td>SSID to add to list:</td><td><input type='text' name='ssid' /></td></tr>
      <tr><td><input type='submit' value='Add' onClick='$("#remove_ssid").val("false")'><input type='submit' value='Remove' onClick='$("#remove_ssid").val("true")'></td><td></td></tr>
      <input name='remove_ssid' id='remove_ssid' type='hidden' />
    </table>
  </form>
</fieldset>

<br /><br />

<fieldset>
  <legend>Karma Log Location</legend>
  <form id="karma_log_form" method="post" action="/components/system/pineap/functions.php?karma_log" onSubmit='$(this).AJAXifyForm(karma_change_log_location); return false;'>
    Log Location: <input type="text" value="<?=file_get_contents("/etc/pineapple/karma_log_location")?>" name="karma_log_location"> <input type="submit" value="Change Location">
    <br /><br /><small>Please give the absolute path to the <b>folder</b> you wish to store the karma log in.</small>
    <br /><small>Example: /tmp/ or /sd/</small>
    <br /><br /><small>The specified directory <b>must</b> exist.</small>
  </form>
</fieldset>