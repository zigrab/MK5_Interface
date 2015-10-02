<?php

namespace pineapple;

/**
 * Pineapple API Class
 */
class Pineapple
{
    public $directory;
    public $rel_dir;
    private $system;

    public function __construct($current_file, $system = false)
    {
        $this->system = $system;
        $this->directory = $this->getDirectory($current_file);
        $this->rel_dir = $this->getRelDir();
    }


    /**
     * Private function to return the directory
     * where this class was instanciated.
     * @param  string $current_file
     * @return string               Current directory
     */
    private function getDirectory($current_file)
    {
        return (dirname($current_file));
    }


    /**
     * Private function to return the directory
     * relative to the WiFi Pineapple webinterface.
     * @return string Relative directory
     */
    private function getRelDir()
    {
        if (substr($this->directory, 0, 3) == "/sd") {
            return str_replace('/sd', '/components', $this->directory);
        }
        return str_replace('/pineapple', '', $this->directory);
    }

 
    /**
     * Function to check if the WiFi Pineapple
     * is currently online
     * @return boolean Online status
     */
    public function online()
    {
        $connection = @fsockopen("wifipineapple.com", 80);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }

 
    /**
     * Function to check if USB storage
     * is available.
     * @return boolean USB availability
     */
    public function usbAvailable()
    {
        return (exec("mount | grep \"on /usb\" -c") >= 1) ? true : false;
    }

    
    /**
     * Function to check if SD storage
     * is available.
     * @return boolean SD availability
     */
    public function sdAvailable()
    {
        return (exec("mount | grep \"on /sd\" -c") >= 1)?true:false;
    }


    /**
     * Function to get the WiFi Pineapple
     * firmware version.
     * @return string Version in the form X.X.X
     */
    public function getVersion()
    {
        return trim(file_get_contents("/etc/pineapple/pineapple_version"));
    }


    /**
     * Function to check if a fimrware version
     * higher or equal to the one required is
     * installed.
     * @param  string $version Firmware version required (X.X.X)
     * @return boolean          Required firmware present
     */
    public function requireVersion($version)
    {
        return version_compare($this->getVersion(), $version, ">=");
    }


    /**
     * Function to execute a command in a
     * non-blocking manner.
     * @param  string $command The command to execute        
     */
    public function execute($command)
    {
        $command = str_replace("'", '\'"\'"\'', $command);
        exec("echo '" . $command ."' | at now");
    }


    /**
     * Returns the wireless UCI interface ID of a given wireless interface.
     * @param  string $interface The entire interface (eg. wlan0) or the interface number (eg. 0)
     * @return int            UCI ID of interface
     */
    public function getWifiIfaceUCIid($interface)
    {
        if (is_numeric($interface) || preg_match("/\d+[\-]\d+/", $interface)) {
            $interface = 'wlan' . $interface;
        }
        if (preg_match("/^wlan\d+$/", $interface) || preg_match("/^wlan\d+-\d+$/", $interface)) {
            exec("ifconfig -a 2&>1 | grep wlan | awk '{print $1}' | sed 's/wlan//g' | sort -n | awk '{print \"wlan\"$1}'", $list_of_interfaces);
            exec("uci show wireless | grep -o '\[[0-9]*\].device=' | awk -F '' '{print $2}'", $list_of_uci_ids);

            foreach ($list_of_uci_ids as $key => $uci_id) {
                if (exec("uci get wireless.@wifi-iface[{$uci_id}].disabled") == "1") {
                    unset($list_of_uci_ids[$key]);
                }
            }
            $list_of_uci_ids = array_values($list_of_uci_ids);

            $uci_key_loc = array_search($interface, $list_of_interfaces);
            if ($uci_key_loc !== false) {
                if (array_key_exists($uci_key_loc, $list_of_uci_ids)) {
                    return $list_of_uci_ids[$uci_key_loc];
                }
            }
        }
        return false;
    }


    /**
     * Returns the wireless UCI device ID of a given wireless interface.
     * @param  string $interface The entire interface (eg. wlan0) or the interface number (eg. 0)
     * @return int            UCI ID of interface
     */
    public function getWifiDevUCIid($interface)
    {
        if (is_numeric($interface) || preg_match("/\d+[\-]\d+/", $interface)) {
            $interface = 'wlan' . $interface;
        }

        if (preg_match("/^wlan\d+-\d+$/", $interface)) {
            $interface = preg_replace("/-\d+/", "", $interface);
        }

        if (preg_match("/^wlan\d+$/", $interface)) {
            exec("iwconfig 2&>1 | grep -o '^wlan[0-9]*' | uniq | sort", $list_of_interfaces);
            exec("uci show wireless | grep -o '[0-9]*=wifi-device' | awk -F '=' '{print $1}'", $list_of_uci_ids);

            $uci_key_loc = array_search($interface, $list_of_interfaces);
            if ($uci_key_loc !== false) {
                if (array_key_exists($uci_key_loc, $list_of_uci_ids)) {
                    return $list_of_uci_ids[$uci_key_loc];
                }
            }
        }
        return false;
    }


    /**
     * Function to install a package or
     * a list of packages.
     * @param  string/array $pkg_or_array Package name or array of package names
     * @param  string $destination  optional: 'internal' or 'sd'
     * @return boolean               Successful initiation of package install
     */
    public function installPackage($pkg_or_array, $destination = "internal")
    {
        if ($this->online()) {
            $install_command = 'opkg update && opkg install ';
            if (is_array($pkg_or_array)) {
                $pkg_or_array = implode(' ', $pkg_or_array);
            }
            $install_command .= $pkg_or_array;
            if ($dest == 'sd') {
                if ($this->sdAvailable()) {
                    $install_command .= ' --dest sd';
                } else {
                    return false;
                }
            }
            $this->execute($install_command);
            return true;
        }
        return false;
    }


    /**
     * Function to check if a package or an
     * array of packages is present on the
     * WiFi Pineapple system.
     * @param  string/array $pkg_or_array Package name or array of pacakge names
     * @return boolean               
     */
    public function checkPackage($pkg_or_array)
    {
        $installed_packages = explode("\n", trim(shell_exec("opkg list-installed | awk '{print $1}'")));
        if (!is_array($pkg_or_array)) {
            $pkg_or_array = array($pkg_or_array);
        }
        foreach ($pkg_or_array as $package) {
            if (!in_array($package, $installed_packages)) {
                return false;
            }
        }
        return true;
    }

    /** TODO
     * [requireInfusion description]
     * @return [type] [description]
     */
    public function requireInfusion($infusion_name)
    {
        if (file_exists("/pineapple/components/infusions/{$infusion_name}/")) {
            return true;
        }
        return false;
    }

    /** TODO
     * [getInfusionVersion description]
     * @param  [type] $infusion_name [description]
     * @return [type]                [description]
     */
    public function getInfusionVersion($infusion_name)
    {
        $path = "/pineapple/components/infusions/{$infusion_name}/";
        if (file_exists($path)) {
            if (file_exists($path . 'handler.php')) {
                eval(explode("\n", file_get_contents($path . 'handler.php'))[7]);
                return $version;
            } elseif (file_exists($path . 'cli_handler.php')) {
                eval(explode("\n", file_get_contents($path . 'cli_handler.php'))[7]);
                return $version;
            }
        }
        return false;
    }

    /** TODO
     * [checkProcess description]
     * @param  [type] $proc_name [description]
     * @return [type]            [description]
     */
    public function checkProcess($proc_name)
    {
        exec("pgrep {$proc_name}", $pids);
        return !empty($pids);
    }

    /** TODO
     * [infusionRelRoot description]
     * @param  [type] $infusion_name [description]
     * @return [type]                [description]
     */
    public function infusionRelRoot($infusion_name = "")
    {
        $infusion_root = $this->infusionRoot($infusion_name);
        return str_replace('/pineapple', '', $infusion_root);
    }

    /** TODO
     * [infusionRoot description]
     * @param  [type] $infusion_name [description]
     * @return [type]                [description]
     */
    public function infusionRoot($infusion_name = "")
    {
        if (empty($infusion_name)) {
            $path = explode('/', $this->directory);
            if ($path[1] == 'sd' && $path[2] == 'infusions') {
                $infusion_name = $path[3];
            } elseif ($path[1] == 'pineapple' && $path[2] == 'components') {
                $infusion_name = $path[4];
            } else {
                return false;
            }
        }
        $inf_path = "/pineapple/components/infusions/{$infusion_name}";
        $sys_path = "/pineapple/components/system/{$infusion_name}";
        if (file_exists($inf_path)) {
            return $inf_path . '/';
        } elseif (file_exists($sys_path)) {
            return $sys_path . '/';
        }
        return false;
    }

    /** TODO
     * [infusionWriteLog description]
     * @param  [type]  $infusion_name [description]
     * @param  [type]  $log_text      [description]
     * @param  boolean $tmp           [description]
     * @return [type]                 [description]
     */
    public function infusionWriteLog($infusion_name, $log_text, $tmp = false)
    {
        if ($this->sdAvailable() || $tmp) {
            $path = $tmp ? '/tmp/infusion_logs/' : '/sd/infusion_logs/';
            @mkdir($path, 0777, true);

            $log = fopen($path . $infusion_name, 'a');
            fwrite($log, $log_text);
            fclose($log);

            return true;
        }
        return false;
    }

    /** TODO
     * [infusionReadLog description]
     * @param  [type]  $infusion_name [description]
     * @param  boolean $tmp           [description]
     * @return [type]                 [description]
     */
    public function infusionReadLog($infusion_name, $tmp = false)
    {
        $path = $tmp ? '/tmp/infusion_logs/' : '/sd/infusion_logs/';
        if (file_exists($path . $infusion_name)) {
            return file_get_contents($path . $infusion_name);
        }
        return '';
    }

    /**
     * Function to change the WiFi Pineapple's
     * password. Can only be used if the Pineapple
     * class is instanciated with the system flag.
     * @param  string $current Current password
     * @param  string $new     New Password
     * @return boolean          Success
     */
    public function changePassword($current, $new)
    {
        $shadow_file = file_get_contents('/etc/shadow');
        $root_array = explode(":", explode("\n", $shadow_file)[0]);
        $salt = '$1$'.explode('$', $root_array[1])[2].'$';
        $current_shadow_pass = $salt.explode('$', $root_array[1])[3];
        $current = crypt($current, $salt);
        $new = crypt($new, $salt);
        if ($current_shadow_pass == $current || $this->system) {
            $find = implode(":", $root_array);
            $root_array[1] = $new;
            $replace = implode(":", $root_array);

            $shadow_file = str_replace($find, $replace, $shadow_file);
            file_put_contents("/etc/shadow", $shadow_file);

            return true;
        }
        return false;
    }


    /**
     * Function to verify that a given password
     * is the correct password for the WiFi Pineapple.
     * @param  string $password Password to verify
     * @return boolean           correct
     */
    public function verifyPassword($password)
    {
        $shadow_file = file_get_contents('/etc/shadow');
        $root_array = explode(":", explode("\n", $shadow_file)[0]);
        $salt = '$1$'.explode('$', $root_array[1])[2].'$';
        $current_shadow_pass = $salt.explode('$', $root_array[1])[3];
        $current = crypt($password, $salt);
        if ($current_shadow_pass == $current) {
            return true;
        }
        return false;
    }


    /**
     * Function to send a notification
     * @param  string $notification The notification to be sent
     * @return null               
     */
    public function sendNotification($notification)
    {
        $notification = str_replace("'", '\'"\'"\'', $notification);
        exec("pineapple notify '{$notification}'");
    }


    /**
     * Function to draw tabs on the large tile.
     * This function will automatically create 
     * the file and directory structure required.
     * @param  array  $tabs An assoc array of tabs to draw
     */
    public function drawTabs(array $tabs)
    {
        echo '<script type="text/javascript">';
        echo '    var old_width = $("#tabs").width(); var new_width = 30; $("#tabs li").each(function(index){ new_width += ($(this).width())+3}); if(new_width > old_width) {$("#tabs").width(new_width); $("#tabs_wrapper").css({"overflow-x": "scroll" });}';
        echo '    $("#tabs li a:not(:first)").addClass("inactive"); get_tab($("#tabs li a:first").attr("id"));';
        echo '</script>';
        echo '<div id="tabs_wrapper">';
        echo '<ul id="tabs">';
        @mkdir($this->directory . '/tabs/');
        foreach ($tabs as $file => $tab_name) {
            if (is_int($file)) {
                $file = strtolower(str_replace(" ", "", $tab_name)) . ".php";
            }

            if (!file_exists($this->directory . '/tabs/' . $file)) {
                touch($this->directory . '/tabs/' . $file);
            }
            echo "<li><a id='" . base64_encode($this->rel_dir . "/tabs/" . $file) . "' onclick='select_tab_content($(this))' class>{$tab_name}</a></li>";
        }
        echo '</ul>';
        echo '</div>';
        echo "<div class='tabContainer'></div>";
    }
}
