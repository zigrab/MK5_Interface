<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<?php
$ifname = exec("uci show network.wan2.ifname | sed 's/^.*=//'");
$proto = exec("uci show network.wan2.proto | sed 's/^.*=//'");
$service = exec("uci show network.wan2.service | sed 's/^.*=//'");
$device = exec("uci show network.wan2.device | sed 's/^.*=//'");
$apn = exec("uci show network.wan2.apn | sed 's/^.*=//'");
$username = exec("uci show network.wan2.username | sed 's/^.*=//'");
$password = exec("uci show network.wan2.password | sed 's/^.*=//'");
$defaultroute = exec("uci show network.wan2.defaultroute | sed 's/^.*=//'");
$ppp_redial = exec("uci show network.wan2.ppp_redial | sed 's/^.*=//'");
$peerdns = exec("uci show network.wan2.peerdns | sed 's/^.*=//'");
$dns = exec("uci show network.wan2.dns | sed 's/^.*=//'");
$keepalive = exec("uci show network.wan2.keepalive | sed 's/^.*=//'");
$pppd_options = exec("uci show network.wan2.pppd_options | sed 's/^.*=//'");
?>

<h2>Mobile Configuration</h2>
<center><div id='network_message'></div></center>
<fieldset>
  <legend>Mobile Broadband Configuration - <a href='#sys/network/mobile_redial/redial/update_message'>Redial</a></legend>
  <form id='mobile_config' method='POST' action='/components/system/network/functions.php?mobile_config' onSubmit='$(this).AJAXifyForm(update_message); return false;'>
    <table>
      <tr><td>Interface Name:</td><td><input type="text" name="ifname" value="<?=$ifname?>"/></td></tr>
      <tr><td>Protocol:</td><td><input type="text" name="proto" value="<?=$proto?>"/></td></tr>
      <tr><td>Service:</td><td><input type="text" name="service" value="<?=$service?>"/></td></tr>
      <tr><td>Device:</td><td><input type="text" name="device" value="<?=$device?>"/></td></tr>
      <tr><td>APN:</td><td><input type="text" name="apn" value="<?=$apn?>"/></td></tr> 
      <tr><td>Username:</td><td><input type="text" name="username" value="<?=$username?>"/></td></tr> 
      <tr><td>Password:</td><td><input type="text" name="password" value="<?=$password?>"/></td></tr> 
      <tr><td>Default Route:</td><td><input type="text" name="defaultroute" value="<?=$defaultroute?>"/></td></tr> 
      <tr><td>ppp redial:</td><td><input type="text" name="ppp_redial" value="<?=$ppp_redial?>"/></td></tr>
      <tr><td>Peer DNS:</td><td><input type="text" name="peerdns" value="<?=$peerdns?>"/></td></tr>
      <tr><td>DNS:</td><td><input type="text" name="dns" value="<?=$dns?>"/></td></tr>
      <tr><td>Keepalive:</td><td><input type="text" name="keepalive" value="<?=$keepalive?>"/></td></tr>
      <tr><td>pppd options:</td><td><input type="text" name="pppd_options" value="<?=$pppd_options?>"/></td></tr>
      <tr><td></td><td><input type="submit"/></td></tr>
    </table>
  </form>
</fieldset>
<br /><br />
<fieldset>
  <legend>Help</legend>
  For known configuration visit <a href="http://wifipineapple.com/modems">http://wifipineapple.com/modems</a>
</fieldset>
