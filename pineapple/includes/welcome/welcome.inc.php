<?php
namespace pineapple;



function completeSetup()
{
    if (file_exists('/pineapple/includes/welcome/ssid_set')) {
        exec('rm -rf /www/pineapple/');
        exec('mkdir -p /pineapple/components/infusions');

        exec('/etc/init.d/blink disable && /etc/init.d/blink stop');
        exec('/etc/init.d/sshd enable && /etc/init.d/sshd start');
        exec('/etc/init.d/dip_handler enable');
        exec('/etc/init.d/pineapple enable && /etc/init.d/pineapple start');
        exec('/etc/init.d/sysntpd enable && /etc/init.d/sysntpd start');

        exec('uci add system led');
        exec('uci set system.@led[-1].name="ethernet"');
        exec('uci set system.@led[-1].sysfs="mk5:amber:lan"');
        exec('uci set system.@led[-1].trigger="netdev"');
        exec('uci set system.@led[-1].dev="eth0"');
        exec('uci set system.@led[-1].mode="link tx rx"');
        exec('uci commit system');

        exec('uci add system led');
        exec('uci set system.@led[-1].name="wlan0"');
        exec('uci set system.@led[-1].sysfs="mk5:blue:wlan0"');
        exec('uci set system.@led[-1].trigger="netdev"');
        exec('uci set system.@led[-1].dev="wlan0"');
        exec('uci set system.@led[-1].mode="link tx rx"');
        exec('uci commit system');

        exec('uci add system led');
        exec('uci set system.@led[-1].name="wlan1"');
        exec('uci set system.@led[-1].sysfs="mk5:red:wlan1"');
        exec('uci set system.@led[-1].trigger="netdev"');
        exec('uci set system.@led[-1].dev="wlan1"');
        exec('uci set system.@led[-1].mode="link tx rx"');
        exec('uci commit system');

        exec('wifi');
        exec('pineapple led reset');
        exec('rm -rf /pineapple/includes/welcome');
    }
}

function firstMessage()
{
    $text = 'Welcome to your WiFi Pineapple. Find support, infusions, news, and forums at 
    <a href="http://www.wifipineapple.com" target="_blank">WiFiPineapple.com</a>.';
    if (file_exists('etc/pineapple/init')) {
        $text .= '<br /><br />Please refresh this page once you see the blinking LED pattern on your pineapple.';
    } else {
        $text .= '<br /><br /><a href="?action=verify_pineapple"><h2>Continue</h2></a>';
    }
    return $text;
}

function passwordForm()
{
    $text = "First, let's change your password.<br /><br />";
    $text .= "
    <table>
      <form action='?action=set_password' method='POST'>
        <tr><td>New Password </td><td><input name='password' type='password' tabindex='1'></td></tr>
        <tr><td>Retype Password </td><td><input name='password2' type='password' tabindex='2'></td></tr>
        <tr><td><input name='eula' type='checkbox' tabindex='3'></td><td>I accept the <a href='/components/system/info/includes/content/eula.txt' target='_blank'>EULA</a></td></tr>
        <tr><td><input name='sw_license' type='checkbox' tabindex='4'></td><td>I accept the <a href='/components/system/info/includes/content/software_license.txt' target='_blank'>Software License</a></td></tr>
        <tr><td></td><td><input name='set_password' type='submit' value='Set Password' tabindex='5'></td></tr>
      </form>
    <table>
    ";
    return $text;
}

function ssidForm()
{
    $text = "Let's now set up the WPA2 management network. You can disable this at a later point.<br /><br />";
    $text .= "
    <table>
      <form action='?action=set_ssid' method='POST'>
        <tr><td>SSID </td><td><input name='ssid' type='text' tabindex='1'></td></tr>
        <tr><td>Password </td><td><input name='password' type='password' tabindex='2'></td></tr>
        <tr><td>Retype Password </td><td><input name='password2' type='password' tabindex='3'></td></tr>
        <tr><td></td><td><input name='set_ssid' type='submit' value='Finish Setup' tabindex='4'></td></tr>
      </form>
    <table>
    ";
    return $text;
}

function handlePassword($post)
{
    if (!isset($_SESSION['verified'])) {
        return;
    }
    $pineapple = new Pineapple(__FILE__, true);
    $password = trim($post['password']);
    $password2 = $post['password2'];
    if (!empty($password) && $password == $password2 && ($post['eula'] && $post['sw_license'])) {
        file_put_contents('/pineapple/includes/welcome/license_accepted', '');
        //exec('date -s "2014-01-01 00:00:00"');
        $pineapple->changePassword($password, $password);
        $text = 'Password set successfully.';
        $text .= '<br /><br />';
        $text .= ssidForm();
    } else {
        $text = "The passwords did not match or you didn't accept the licenses. Please <a href='/'>try again</a>";
    }
    return $text;
}

function handleSSID($post)
{
    if (!file_exists('/pineapple/includes/welcome/license_accepted')) {
        return;
    }
    $ssid = $post['ssid'];
    $password = trim($post['password']);
    $password2 = $post['password2'];
    if (!empty($ssid) && !empty($password) && strlen($password) >= 8 && $password == $password2) {

        file_put_contents('/pineapple/includes/welcome/ssid_set', '');

        $ssid = str_replace("'", "'\"'\"'", $ssid);
        $password = str_replace("'", "'\"'\"'", $password);

        exec('wifi detect > /etc/config/wireless');
        exec("uci set wireless.@wifi-iface[1].ssid='".$ssid."'");
        exec("uci set wireless.@wifi-iface[1].key='".$password."'");
        exec("uci set wireless.@wifi-iface[1].disabled='0'");
        exec("uci set wireless.@wifi-iface[0].hidden='1'");
        exec("uci commit wireless");

        $text = "<p>The system is now completing the setup, please wait.</p>";
        $text .= "If you are connected over WiFi, please make sure to reconnect as you will be disconnected.<br />
                  Be sure to connect to the network you just set up.</p>";
        $text .= "<div id='finish'></div>";

        if (trim(exec("mount | grep /sd | awk {'print $5'}")) != "vfat") {
            $text .= "<br /><br />For <font color='red'>best performance</font> you are advised to <b>format the Micro SD card ext4</b>. 
                      To do so click <b>Resouces</b> then <b>USB Info</b> then <b><u>Format SD Card</u/></b>.";
        }

        $text .= "
            <script type='text/javascript'>
            $.get('/?action=finish');
            setTimeout(function() {
                var interval = setInterval(function() {
                    $.get('/', function(data) {
                        if(data != ''){
                            clearInterval(interval);
                            $('#finish').html('<h2><a href=\'/\'>Continue</a></h2>');
                        }
                    });
                }, 1500);
            }, 5000);
            </script>";
    } else {
        $text =  "Please make sure that you have filled in all the fields correctly.<br />
                  The password needs to be 8 or more characters long. Please <a href='/'>try again</a>";
    }
    return $text;
}

function verifyForm($failed = false)
{
    $text = "";
    if ($failed) {
        $text .= "<font color='red'>Wrong pattern entered. Please try again.</font><br />";
    }
    $text .= "First, let's make sure that you own this pineapple.<br /><br />";
    $text .= "
    <form action='?action=verify_pineapple' method='POST'>
      <table>
        <tr><th></th><th>On</th><th>Off</th><th>Blink</th></tr>
        <tr><td><img src='/includes/welcome/img/green.png' title='green'></td><td><input name='green' type='radio' tabindex='1' value='on'></td><td><input name='green' type='radio' tabindex='1' value='off'></td><td><input name='green' type='radio' tabindex='1' value='blink'></td></tr>
        <tr><td><img src='/includes/welcome/img/amber.png' title='amber'></td><td><input name='amber' type='radio' tabindex='1' value='on'></td><td><input name='amber' type='radio' tabindex='1' value='off'></td><td><input name='amber' type='radio' tabindex='1' value='blink'></td></tr>
        <tr><td><img src='/includes/welcome/img/blue.png' title='blue'></td><td><input name='blue' type='radio' tabindex='1' value='on'></td><td><input name='blue' type='radio' tabindex='1' value='off'></td><td><input name='blue' type='radio' tabindex='1' value='blink'></td></tr>
        <tr><td><img src='/includes/welcome/img/red.png' title='red'></td><td><input name='red' type='radio' tabindex='1' value='on'></td><td><input name='red' type='radio' tabindex='1' value='off'></td><td><input name='red' type='radio' tabindex='1' value='blink'></td></tr>
        <tr><td></td><td colspan='3'><input name='verify_pineapple' type='submit' value='Continue' tabindex='4'></td></tr>
      <table>
      
    </form>
    ";
    return $text;
}

function verifyPineapple($post)
{
    $action_array = array('off', 'on', 'blink');
    if (isset($_SESSION['verify_pattern'])
        && isset($post['amber'])
        && isset($post['blue'])
        && isset($post['red'])
    ) {
        $current_state = str_split($_SESSION['verify_pattern']);
        if (array_search($post['amber'], $action_array) == $current_state[0]
            && array_search($post['blue'], $action_array) == $current_state[1]
            && array_search($post['red'], $action_array) == $current_state[2]
        ) {
            $_SESSION['verified'] = true;
            return passwordForm();
        }
    }
    generateLEDpattern();
    return verifyForm(true);
}

function generateLEDpattern()
{
    exec("kill -9 $(ps aux | grep '[b]link' | awk '{print $1}')");

    $color_array = array('amber', 'blue', 'red');
    $action_array = array('off', 'on', 'blink');

    $_SESSION['verify_pattern'] = '';

    for ($i=0; $i<3; $i++) {
        switch ($action_array[mt_rand(0, 2)]) {
            case 'blink':
                $_SESSION['verify_pattern'] .= '2';
                exec("ash -c 'blink;while true; do pineapple led {$color_array[$i]} on; sleep 1; pineapple led {$color_array[$i]} off; sleep 1; done' &>/dev/null &");
                break;
            case 'on':
                $_SESSION['verify_pattern'] .= '1';
                exec("pineapple led {$color_array[$i]} off");
                exec("pineapple led {$color_array[$i]} on");
                break;
            default:
                $_SESSION['verify_pattern'] .= '0';
                exec("pineapple led {$color_array[$i]} off");
        }
    }
}
