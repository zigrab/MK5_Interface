<?php
namespace pineapple;

require_once("/pineapple/includes/welcome/welcome.inc.php");

if (exec("echo 15 > /sys/class/gpio/export && cat /sys/class/gpio/gpio15/value && echo 15 > /sys/class/gpio/unexport") == "1") {
    
}

if (session_status() == PHP_SESSION_NONE) {
        session_start();
}

switch ($_GET['action']) {
    case 'secure_setup':
        $content = secureSetup();
        break;
    case 'reset_dips':
        $content = resetDips();
        break;
    case 'set_password':
        if (isset($_POST['set_password'])) {
            $content = handlePassword($_POST);
        } else {
            $content = passwordForm();
        }
        break;
    case 'set_ssid':
        if (isset($_POST['set_ssid'])) {
            $content = handleSSID($_POST);
        }
        break;
    case 'finish':
        completeSetup();
        break;
    default:
        $content = firstMessage();
}


?>

<html>
  <head>
    <title>Setup</title>
    <script src="/includes/js/jquery.min.js"></script>
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <?=isset($_GET['set_password']) ? '<meta name="viewport" content="width=device-width, initial-scale=1.0">' : '' ?>
  </head>

  <body bgcolor="black" text="white" link="lime" alink="lime" vlink="lime" style="text-align:center; font-family: monospace;">
    <center>
      <br /><br /><br /><img src="/includes/img/mk5_logo.gif"><br /><br />
    <?=$content?>
    </center>


</html>