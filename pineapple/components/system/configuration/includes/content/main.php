<?php include('/pineapple/includes/api/tile_functions.php'); ?>

<center><div id='config_message'></div></center>
<fieldset>
  <legend>Webserver Configuration</legend>
  <form id='config_change_port' method='POST' action='/components/system/configuration/functions.php?change_port' onSubmit='$(this).AJAXifyForm(update_message); return false;'>
    Port Number: <input type='text' name='port' placeholder='<?=explode_n(":", exec("cat /etc/config/uhttpd | grep -i listen_http | grep -v listen_https | tail -n 1"), 1)?>'/><br /> 
    <input type='submit' name='change_port' value='Change Port'>
  </form>
</fieldset>

<br /><br />

<fieldset>
  <legend>Change Root Password</legend>
  <form id='config_change_password' method='POST' action='/components/system/configuration/functions.php?change_password' onSubmit='$(this).AJAXifyForm(update_message); return false;'>
    Password: <input type='password' name='password' placeholder='********'/><br /> 
    Repeat:   <input type='password' name='repeat' placeholder='********'/><br /> 
    <input type='submit' name='change_password' value='Change Password'>
  </form>
</fieldset>