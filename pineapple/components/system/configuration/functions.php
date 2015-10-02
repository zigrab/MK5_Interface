<?php

include('/pineapple/includes/api/tile_functions.php');

if(isset($_GET['change_port'])){
  echo change_port($_POST['port']);
}

if(isset($_GET['change_password'])){
  echo change_password($_POST['password'], $_POST['repeat']);
}

if(isset($_GET['update_button'])){
  echo update_button($_POST['wps_script']);
}

if(isset($_GET['update_cron'])){
  echo update_cron($_POST['cron']);
}

if(isset($_GET['update_css'])){
  echo update_css($_POST['css']);
}

if(isset($_GET['update_spoofhost'])){
  echo update_spoofhost($_POST['spoofhost']);
}

if(isset($_GET['update_index'])){
  echo update_index($_POST['page']);
}

if(isset($_GET['execute'])){
  echo execute($_POST['commands']);
}

if(isset($_GET['dnsspoof'])){
  if($_GET['dnsspoof'] == 'start'){
    exec('echo "dnsspoof -i br-lan -f /etc/pineapple/spoofhost > /dev/null 2>/tmp/dnsspoof.log" | at now');
  }else{
    exec('killall dnsspoof');
  }
}

if(isset($_GET['change_tz'])){
  echo change_tz($_POST['time']);
}


if(isset($_POST['dip'])){

  $db = new SQLite3('/etc/pineapple/mk5.db');


  foreach($_POST as $dips => $command){

    if($dips != "dip"){
      $dips = explode('-', $dips);
      $command = $db->escapeString($command);
      $query = "UPDATE dips SET command='".$command."' WHERE dip1=".$dips[0]." AND dip2=".$dips[1]." AND dip3=".$dips[2].";";
      $db->query($query);
    }

  }

  echo "DIPs have been updated successfully!";

}


if(isset($_GET['cron'])){
  if($_GET['cron'] == 'start'){
    exec('/etc/init.d/cron enable');
    exec('/etc/init.d/cron start');
  }else{
    exec('/etc/init.d/cron disable');
    exec('/etc/init.d/cron stop');
  }
}

if(isset($_GET['reset'])){
  exec('echo "mtd -r erase rootfs_data" | at now');
  echo 'Your Pineapple has been reset and is now rebooting. Please give it some time!';
}

if(isset($_GET['reboot'])){
  exec('echo reboot | at now');
  echo 'Your Pineapple is rebooting. Please give it some time!';
}


function change_port($port){
  if(!is_numeric($port) || trim($port) == ''){
    $port = '1471';
  }
  exec("sed -i 's/".explode_n(':', exec("cat /etc/config/uhttpd | grep -i listen_http | grep -v listen_https | tail -n 1"), 1)."/".$port."/g' /etc/config/uhttpd");
  return '<font color="lime">Port changed to '.$port.'.</font>';
}

function change_password($password, $repeat){
  if($password != $repeat || trim($password) == ''){
    return "<font color='red'>There was an error. Please try again</font>";
  }else{
    exec("/pineapple/components/system/configuration/files/change_password \"".addslashes($password)."\"");
    return "<font color='lime'>The password has been changed.</font>";
  }
}

function update_cron($crontab){
  file_put_contents('/etc/crontabs/root', str_replace("\r", "", $crontab));
  exec('/etc/init.d/cron stop');
  exec('/etc/init.d/cron start');

  return '<font color="lime">Cron updated.</font>';
}

function update_css($css){
  file_put_contents('/pineapple/includes/css/styles_main.css', str_replace("\r", "", $css));
  return '<font color="lime">CSS updated.</font>';
}

function update_spoofhost($spoofhost){
  file_put_contents('/etc/pineapple/spoofhost', str_replace("\r", "", $spoofhost));
  return '<font color="lime">Spoofhost file updated.</font>';
}

function update_index($page){
  file_put_contents('/www/index.php', str_replace("\r", "", $page));
  return '<font color="lime">index.php updated.</font>';
}

function execute($commands){

  $command_array = explode("\n", $commands);
  $html = '';

  foreach($command_array as $command){
    if(trim($command) != ''){
      $output = '';
      $html .= "<b>Executing '".trim($command)."</b>':\n";
      exec(trim($command), $output);
      foreach($output as $line){
        $html .= htmlspecialchars($line)."\n";
      }
      $html .= "\n\n";
    }
  }
  return $html;
}

function change_tz($time){
  if (in_array($time, range(-12, 12))) {
    exec("echo GMT".$time." > /etc/TZ");
    exec("uci set system.@system[0].timezone='GMT".$time."'");
    echo "GMT".$time;
  }else{
    exec("echo UTC > /etc/TZ");
    exec("uci set system.@system[0].timezone='UTC'");

    echo "UTC";
  }
  exec("uci commit system");
}

?>
