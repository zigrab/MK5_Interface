<?php
//Check if logged in
include_once('/pineapple/includes/api/auth.php');
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
  $connection = @file_get_contents("http://cloud.wifipineapple.com/ip.php");
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

?>
