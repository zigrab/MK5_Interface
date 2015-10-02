<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<center><div id='config_message'></div></center>
<fieldset>
  <legend>Factory Reset and Reboot<help id='configuration:reset_reboot'></help></legend>
  <center><a href='#sys/configuration/reset/true/popup' onclick="return confirm('Are you sure you want to factory reset your WiFi Pineapple MKV?\nYou will loose all data not stored on the SD card.')">Factory Reset Pineapple</a>  |  <a href='#sys/configuration/reboot/true/popup' onclick="return confirm('Are you sure you want to reboot?')">Reboot Pineapple</a></center>
</fieldset>

<br /><br />

<fieldset>
  <legend>Execute Commands<help id='configuration:execute_commands'></help></legend>
  <form method='POST' action='/components/system/configuration/functions.php?execute' onSubmit='$(this).AJAXifyForm(update_execute); return false;'>
    <textarea name='commands' style='width: 100%; height: 20em'></textarea>
    <br />
    <center><input type='submit' value='Execute'/></center>
  </form>
  <pre><div id='config_execute'></div></pre>
</fieldset>
