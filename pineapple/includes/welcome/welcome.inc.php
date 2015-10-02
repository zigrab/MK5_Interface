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
        $text .= '<br /><br /><a href="?action=secure_setup"><h2>Continue</h2></a>';
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
    if (!isset($_SESSION['secure'])) {
        return;
    }
    $pineapple = new Pineapple(__FILE__, true);
    $password = trim($post['password']);
    $password2 = $post['password2'];
    if (!empty($password) && $password == $password2 && ($post['eula'] && $post['sw_license'])) {
        file_put_contents('/pineapple/includes/welcome/license_accepted', '');
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

function secureSetup()
{
    if (file_exists("/sd/skip_dip_setup")) {
        $_SESSION['secure'] = true;
    }
    if ($_SESSION['secure']) {
        header("Location: /?action=reset_dips");
    }
    $dip_status = checkDIPStatus();
    if ($dip_status == "wired") {
        exec("killall hostapd");
        exec("ifconfig wlan0 down");
        exec("ifconfig wlan0-1 down");
        $_SESSION['secure'] = true;
        header("Location: /?action=reset_dips");
    } elseif ($dip_status == "wireless") {
        $_SESSION['secure'] = true;
        header("Location: /?action=reset_dips");
    } else {
        $text = "<h1>Security Notice</h1>";
        $text .= "<p>For security purposes, we recommend that the initial setup is performed with the WiFi radios turned off.</p>";
        $text .= "<p>To continue, you <b>must</b> choose between two DIP switch configurations:</p>";
        $text .= "<img src='/includes/welcome/img/dips.gif'>";
        $text .= "<table>";
        $text .= "<tr><td><img src='/includes/welcome/img/10001.gif' style='margin-right: 15px;'></td><td> Disable the radios and continue with the initial setup over ethernet. (secure)</td></tr>";
        $text .= "<tr><td><img src='/includes/welcome/img/11011.gif' style='margin-right: 15px;'> </td><td> Keep the radios enabled and proceed with setup. (insecure)</td></tr>";
        $text .= "</table>";
        $text .= "<h2><a href='/?action=secure_setup'>Continue</a></h2>";
    }

    return $text;
}

function resetDips()
{
    if (checkDIPStatus() == "reset") {
        header("Location: /?action=set_password");
    }
    $text = "<h1>Reset DIP switches</h1>";
    $text .= "<p>To continue, please reset your DIP switches.</p>";
    $text .= "<img src='/includes/welcome/img/11111.gif'>";
    $text .= "<h2><a href='/?action=reset_dips'>Continue</a></h2>";

    return $text;
}

function checkDIPStatus()
{
    exportDIPSwitches();
    
    $dip2 = file_get_contents("/sys/class/gpio/gpio13/value");
    $dip3 = file_get_contents("/sys/class/gpio/gpio15/value");
    $dip4 = file_get_contents("/sys/class/gpio/gpio16/value") * (-1) + 1;

    if ($dip2 == 0 && $dip3 == 0 && $dip4 == 0) {
        return "wired";
    } elseif ($dip2 == 1 && $dip3 == 0 && $dip4 == 1) {
        return "wireless";
    } elseif ($dip2 == 1 && $dip3 == 1 && $dip4 == 1) {
        return "reset";
    } else {
        return "";
    }

    unexportDIPSwitches();

    return "wireless";
}

function exportDIPSwitches()
{
    file_put_contents("/sys/class/gpio/export", "13");
    file_put_contents("/sys/class/gpio/export", "15");
    file_put_contents("/sys/class/gpio/export", "16");
}

function unexportDIPSwitches()
{
    file_put_contents("/sys/class/gpio/unexport", "13");
    file_put_contents("/sys/class/gpio/unexport", "15");
    file_put_contents("/sys/class/gpio/unexport", "16");
}
