<?php

include('/pineapple/includes/json.php');
$json = new Services_JSON();
/*
*Todo:
*Create textbox that posts to specific file
*Create textfield that posts to specific file
*Wrap all this in a form. Tada..
*
*/

function online(){
  $connection = @file_get_contents("http://wifipineapple.com/ip.php");
  if(trim($connection) != ""){
    return true;
  }
  return false;
}

function usb_available(){
  return (exec("mount | grep \"on /usb\" -c") >= 1)?true:false;
}

function sd_available(){
  return (exec("mount | grep \"on /sd\" -c") >= 1)?true:false;
}

function json_encode($string){
  global $json;
  return $json->encode($string);
}

function json_decode($string){
  global $json;
  return $json->decode($string);
}

function explode_n($delim, $string, $index){
  $array = explode($delim, $string);
  return $array[$index];
}

function change_password($current_pass, $new_pass){
  $shadow_file = file_get_contents('/etc/shadow');
  $root_array = explode(":", explode("\n", $shadow_file)[0]);
  $salt = '$1$'.explode('$', $root_array[1])[2].'$';
  $current_shadow_pass = $salt.explode('$', $root_array[1])[3];
  $current_pass = crypt($current_pass, $salt);
  $new_pass = crypt($new_pass, $salt);

  if($current_shadow_pass == $current_pass){
    $find = implode(":", $root_array);
    $root_array[1] = $new_pass;
    $replace = implode(":", $root_array);

    $shadow_file = str_replace($find, $replace, $shadow_file);
    file_put_contents("/etc/shadow", $shadow_file);

    return true;
  }else{
    return false;
  }
}

?>