<?php

include('/pineapple/includes/api/tile_functions.php');

if(isset($_GET['mobile_config'])){

  foreach($_POST as $key => $value){
    if(trim($_POST[$key]) == ''){
      $_POST[$key] = " ";
    }
  }

  echo updateMobile($_POST['ifname'], 
    $_POST['proto'], 
    $_POST['service'], 
    $_POST['device'], 
    $_POST['apn'], 
    $_POST['username'], 
    $_POST['password'], 
    $_POST['defaultroute'], 
    $_POST['ppp_redial'], 
    $_POST['peerdns'], 
    $_POST['dns'], 
    $_POST['keepalive'], 
    $_POST['pppd_options']);
}

if(isset($_GET['internet_ip'])){
  if(online()){
    echo  file_get_contents("http://wifipineapple.com/ip.php");
  }else{
    echo '<font color="red">Error Connecting</font>';
  }
}

if(isset($_GET['enable'])){
  $interface = $_GET['enable'];
  if($interface == "wlan0"){
    exec('wifi');
  }else{
    exec('ifconfig wlan1 up');
  }
}

if(isset($_GET['disable'])){
  $interface = $_GET['disable'];
  if($interface == "wlan0"){
    exec('killall hostapd && ifconfig wlan0 down');
  }else{
    exec('ifconfig wlan1 down');
  }
}

if(isset($_GET['update_route'])){

  $route = $_POST['route'];
  $iface = $_POST['iface'];
  exec("route del default");
  exec("route add default gw ".$route." ".$iface);
  exec("/etc/init.d/firewall restart");
  echo "<font color='lime'>Route changed successfully.</font>";
}

function updateMobile($ifname, 
  $proto, 
  $service, 
  $device, 
  $apn, 
  $username, 
  $password, 
  $defaultroute, 
  $ppp_redial, 
  $peerdns, 
  $dns, 
  $keepalive, 
  $pppd_options){
  exec("uci delete network.wan2");
  exec("uci set network.wan2=interface");
  exec("uci set network.wan2.ifname=\"$ifname\"");
  exec("uci set network.wan2.proto=\"$proto\"");
  exec("uci set network.wan2.service=\"$service\"");
  exec("uci set network.wan2.device=\"$device\"");
  exec("uci set network.wan2.apn=\"$apn\"");
  exec("uci set network.wan2.username=\"$username\"");
  exec("uci set network.wan2.password=\"$password\"");
  exec("uci set network.wan2.defaultroute=\"$defaultroute\"");
  exec("uci set network.wan2.ppp_redial=\"$ppp_redial\"");
  exec("uci set network.wan2.peerdns=\"$peerdns\"");
  exec("uci set network.wan2.dns=\"$dns\"");
  exec("uci set network.wan2.keepalive=\"$keepalive\"");
  exec("uci set network.wan2.pppd_options=\"$pppd_options\"");
  exec("uci commit network");
  return '<font color="lime" Success!</font> Updated Mobile WAN Configuration.';
  echo "Updated Mobile WAN Configuration.";
}

if(isset($_GET['mobile_redial'])){
  echo mobileRedial();
}

function mobileRedial(){
  exec("echo 1 > /tmp/mobileRedial");
  return '<font color="lime">Redialing.</font>';
}

if(isset($_GET['restart_dns'])){
  exec("/etc/init.d/dnsmasq restart");
  echo "<font color='lime'>DNS Restarted</font>";
}

if(isset($_GET['scan'])){

  $station_list = array();
  $scan = explode("\n", shell_exec("ifconfig wlan1 up && iwlist wlan1 scan"));

  foreach($scan as $line){
    $line = trim($line);
    if(substr($line, 0, 4) == "Cell"){
      $address = substr($line, strpos($line, ":")+2);
      $station_list[$address] = array();
    }else if(strpos($line, "ESSID:") !== false){
      $ESSID = substr(substr($line, 7), 0, -1);
      $station_list[$address]["ESSID"] = $ESSID;
    }else if(strpos($line, "Encryption key:") !== false){
      if(strpos($line, ":on") !== false){
        $station_list[$address]['security'] = array();
        $station_list[$address]['security']['WEP'] = true;
      }
    }else if(strpos($line, "802.11i/WPA2")!== false){ // check for WPA2
      unset($station_list[$address]['security']['WEP']);
      $security = "WPA2";
      $station_list[$address]['security'][$security] = array();
    }else if(strpos($line, "IE: WPA") !== false){ // check for WPA
      unset($station_list[$address]['security']['WEP']);
      $security = "WPA";
      $station_list[$address]['security'][$security] = array();
    }else if(strpos($line, "Pairwise Ciphers") !== false){ //CHECK FOR ENCRYPTION TYPES
      if(strpos($line, "CCMP") !== false){
        $station_list[$address]['security'][$security]["ccmp"] = true;
      }
      if(strpos($line, "TKIP") !== false){
        $station_list[$address]['security'][$security]["tkip"] = true;
      }
    }else if(strpos(substr($line, 0, 8), "Channel") !== false){
      $station_list[$address]['channel'] = substr($line, 8);
    }else if(strpos($line, "Quality") !== false){
      $station_list[$address]['quality'] = str_replace("/", " of ", substr($line, 8, 5));
      $station_list[$address]['signal'] = substr($line, 28);
    }
  }

  echo json_encode($station_list);
}

if(isset($_GET['connect'])){
  set_time_limit(60*10);

  $ap = json_decode($_GET['connect']);

  $ap->ESSID = str_replace("\\", "\\\\", $ap->ESSID);
  $ap->ESSID = str_replace("'", "'\"'\"'", $ap->ESSID);
  $ssid = $ap->ESSID;

  $channel = $ap->channel;

  if($ap->key != null){
    $ap->key = base64_decode(rawurldecode($ap->key));
    $ap->key = str_replace("\\", "\\\\", $ap->key);
    $ap->key = str_replace("'", "'\"'\"'", $ap->key);
  }

  exec("ifconfig wlan1 down");
  exec("uci set wireless.@wifi-iface[1].mode=sta");
  exec("uci set wireless.@wifi-iface[1].network=wan");
  exec("uci set wireless.@wifi-iface[1].ssid='".$ssid."'");
  exec("uci set wireless.@wifi-device[1].channel=\"".$channel."\"");

  if($ap->security == null){
    exec("uci delete wireless.@wifi-iface[1].key");
    exec("uci delete wireless.@wifi-iface[1].encryption");

  }elseif($ap->security->WPA != null && $ap->security->WPA2 != null){
    $mode = "mixed-psk";
    $cipher = "";
    if($ap->security->WPA2->ccmp != null){
      $cipher .= "+ccmp";
    }
    if($ap->security->WPA2->tkip != null){
      $cipher .= "+tkip";
    }
    exec("uci set wireless.@wifi-iface[1].key='".$ap->key."'");
    exec("uci set wireless.@wifi-iface[1].encryption=".$mode.$cipher);
  }elseif($ap->security->WPA2 != null){
    $mode = "psk2";
    $cipher = "";
    if($ap->security->WPA2->ccmp != null){
      $cipher .= "+ccmp";
    }
    if($ap->security->WPA2->tkip != null){
      $cipher .= "+tkip";
    }
    exec("uci set wireless.@wifi-iface[1].key='".$ap->key."'");
    exec("uci set wireless.@wifi-iface[1].encryption=".$mode.$cipher);
  }elseif($ap->security->WPA != null) {
    $mode = "psk";
    $cipher = "";
    if($ap->security->WPA->ccmp != null){
      $cipher .= "+ccmp";
    }
    if($ap->security->WPA->tkip != null){
      $cipher .= "+tkip";
    }
    exec("uci set wireless.@wifi-iface[1].key='".$ap->key."'");
    exec("uci set wireless.@wifi-iface[1].encryption=".$mode.$cipher);
  }elseif($ap->security->WEP){
   exec("uci set wireless.@wifi-iface[1].key='".$ap->key."'");
   exec("uci set wireless.@wifi-iface[1].encryption=wep");
 }
 exec("uci commit wireless");
 exec("wifi");
 echo "done";
}



if(isset($_GET['get_connection'])){

  if(exec("iwconfig wlan1 | grep -ic 'Not-Associated'") == 0){
    exec("ifconfig wlan1 && iwconfig wlan1", $info);
    echo "<pre>";
    foreach($info as $line){
      echo $line."\n";
    }
    echo "<pre>";
  }else{
    echo "not_associated";
  }
}

if(isset($_GET['disconnect'])){
  exec("uci delete wireless.@wifi-iface[1].key");
  exec("uci delete wireless.@wifi-iface[1].encryption");
  exec("uci set wireless.@wifi-iface[1].mode=ap");   
  exec("uci commit wireless");
  exec("wifi");
  exec("ifconfig wlan1 down");
}
