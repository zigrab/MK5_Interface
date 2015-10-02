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
        if ($this->system) {
            $shadow_file = file_get_contents('/etc/shadow');
            $root_array = explode(":", explode("\n", $shadow_file)[0]);
            $salt = '$1$'.explode('$', $root_array[1])[2].'$';
            $current_shadow_pass = $salt.explode('$', $root_array[1])[3];
            $current = crypt($current, $salt);
            $new = crypt($new, $salt);
            if ($current_shadow_pass == $current) {
                $find = implode(":", $root_array);
                $root_array[1] = $new;
                $replace = implode(":", $root_array);

                $shadow_file = str_replace($find, $replace, $shadow_file);
                file_put_contents("/etc/shadow", $shadow_file);

                return true;
            }
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
        echo '    $("#tabs li a:not(:first)").addClass("inactive"); get_tab($("#tabs li a:first").attr("id"));';
        echo '</script>';
        echo '<ul id="tabs">';
        @mkdir($this->directory . '/tabs/');
        foreach ($tabs as $file => $tab_name) {
            if (is_int($file)) {
                $file = strtolower(str_replace(" ", "", $tab_name)) . ".php";
            }

            if (!file_exists($this->directory . '/tabs/' . $file)) {
                touch($this->directory . '/tabs/' . $file);
                print "Not exists..";
            }
            echo "<li><a id='{$this->rel_dir}/tabs/{$file}' onclick='select_tab_content($(this))'>{$tab_name}</a></li>";
        }
        echo '</ul>';
        echo "<div class='tabContainer'></div>";
    }
}
