<?php
if (isset($_POST['save_email_config'])) {
    exec("uci set reporting.ssmtp.from=" . escapeshellarg($_POST['from']));
    exec("uci set reporting.ssmtp.to=" . escapeshellarg($_POST['to']));
    exec("uci set reporting.ssmtp.server=" . escapeshellarg($_POST['server']));
    exec("uci set reporting.ssmtp.port=" . escapeshellarg($_POST['port']));
    exec("uci set reporting.ssmtp.domain=" . escapeshellarg($_POST['domain']));
    exec("uci set reporting.ssmtp.username=" . escapeshellarg($_POST['email_username']));
    exec("uci set reporting.ssmtp.password=" . escapeshellarg($_POST['email_password']));

    if (isset($_POST['tls'])) {
        exec("uci set reporting.ssmtp.tls='checked'");
    } else {
        exec("uci set reporting.ssmtp.tls=''");
    }

    if (isset($_POST['starttls'])) {
        exec("uci set reporting.ssmtp.starttls='checked'");
    } else {
        exec("uci set reporting.ssmtp.starttls=''");
    }

    exec("uci commit reporting");

    writeSSMTPFile();
}


if (isset($_POST['save_report_config'])) {
    if (isset($_POST['interval'])) {
        $interval = intval($_POST['interval']);
        if (24 >= $interval && $interval >= 1) {
            exec("uci set reporting.settings.interval={$interval}");
        } else {
            exec("uci set reporting.settings.interval=1");
        }
    } else {
        exec("uci set reporting.settings.interval=1");
    }
    if (isset($_POST['send_email'])) {
        exec("uci set reporting.settings.send_email=1");
    } else {
        exec("uci set reporting.settings.send_email=0");
    }
    if (isset($_POST['save_report'])) {
        exec("uci set reporting.settings.save_report=1");
    } else {
        exec("uci set reporting.settings.save_report=0");
    }
    if (isset($_POST['enable'])) {
        $hours_minus_one = intval(exec("uci get reporting.settings.interval"))-1;
        $hour_string = ($hours_minus_one == 0) ? "*" : "0/{$hours_minus_one}";
        exec("sed -i '/DO NOT TOUCH/d /\\/pineapple\\/components\\/system\\/reporting\\/files\\/reporting/d' /etc/crontabs/root");
        exec("echo -e '#DO NOT TOUCH BELOW\\n0 {$hour_string} * * * /pineapple/components/system/reporting/files/reporting\\n#DO NOT TOUCH ABOVE' >> /etc/crontabs/root");
    } else {
        exec("sed -i '/DO NOT TOUCH/d /\\/pineapple\\/components\\/system\\/reporting\\/files\\/reporting/d' /etc/crontabs/root");
    }
    exec("/etc/init.d/cron stop");
    exec("/etc/init.d/cron start");
    exec("uci commit reporting");
}


if (isset($_POST['save_report_contents'])) {
    if (isset($_POST['log'])) {
        exec("uci set reporting.settings.log=1");
    } else {
        exec("uci set reporting.settings.log=0");
    }
    if (isset($_POST['client'])) {
        exec("uci set reporting.settings.client=1");
    } else {
        exec("uci set reporting.settings.client=0");
    }
    if (isset($_POST['survey'])) {
        exec("uci set reporting.settings.survey=1");
    } else {
        exec("uci set reporting.settings.survey=0");
    }
    if (isset($_POST['tracking'])) {
        exec("uci set reporting.settings.tracking=1");
    } else {
        exec("uci set reporting.settings.tracking=0");
    }
    if (isset($_POST['clear_log'])) {
        exec("uci set reporting.settings.clear_log=1");
    } else {
        exec("uci set reporting.settings.clear_log=0");
    }
    if (isset($_POST['duration'])) {
        $duration = intval($_POST['duration']);
        if (300 >= $duration && $duration >= 15) {
            exec("uci set reporting.settings.duration={$duration}");
        } else {
            exec("uci set reporting.settings.duration=15");
        }
    } else {
        exec("uci set reporting.settings.duration=15");
    }


    exec("uci commit reporting");
}

function writeSSMTPFile()
{
    $SSMTP_CONFIG = "/etc/ssmtp/ssmtp.conf";
    file_put_contents($SSMTP_CONFIG, "root=". exec("uci get reporting.ssmtp.from") . "\n");
    file_put_contents($SSMTP_CONFIG, "mailhub=". exec("uci get reporting.ssmtp.server") . ":" . exec("uci get reporting.ssmtp.port") . "\n", FILE_APPEND);
    file_put_contents($SSMTP_CONFIG, "rewriteDomain=". exec("uci get reporting.ssmtp.domain") . "\n", FILE_APPEND);
    file_put_contents($SSMTP_CONFIG, "hostname=". exec("uci get reporting.ssmtp.from") . ":" . exec("uci get reporting.ssmtp.port") . "\n", FILE_APPEND);
    file_put_contents($SSMTP_CONFIG, "AuthUser=". exec("uci get reporting.ssmtp.username") . "\n", FILE_APPEND);
    file_put_contents($SSMTP_CONFIG, "AuthPass=". exec("uci get reporting.ssmtp.password") . "\n", FILE_APPEND);
    file_put_contents($SSMTP_CONFIG, "FromLineOverride=YES\n", FILE_APPEND);

    if (exec("uci get reporting.ssmtp.tls") == "checked") {
        file_put_contents($SSMTP_CONFIG, "UseTLS=YES\n", FILE_APPEND);
    } else {
        file_put_contents($SSMTP_CONFIG, "UseTLS=NO\n", FILE_APPEND);
    }

    if (exec("uci get reporting.ssmtp.starttls") == "checked") {
        file_put_contents($SSMTP_CONFIG, "USESTARTTLS=YES\n", FILE_APPEND);
    } else {
        file_put_contents($SSMTP_CONFIG, "USESTARTTLS=NO\n", FILE_APPEND);
    }

}

$directory = realpath(dirname(__FILE__)).'/';
$rel_dir = str_replace('/pineapple', '', $directory);

if (isset($_GET['update_log']) && isset($_POST['log'])) {
    $handle = fopen($directory.'custom', 'w');
    fwrite($handle, $_POST['log']);
    fclose($handle);
    echo "Log set successfully";
}

if (isset($_POST['load_log'])) {
    $infusion = $_POST['infusion'];
    if (file_exists("/sd/infusion_logs/{$infusion}")) {
        $log = file_get_contents("/sd/infusion_logs/{$infusion}");
        echo "<fieldset><legend>SD log</legend><pre>" . htmlspecialchars($log) . "</pre></fieldset>";
    }
    if (file_exists("/tmp/infusion_logs/{$infusion}")) {
        $log = file_get_contents("/tmp/infusion_logs/{$infusion}");
        echo "<br /><br /><fieldset><legend>Temp log</legend><pre>" . htmlspecialchars($log) . "</pre></fieldset>";
    }
}
