<?php

namespace overlay;

class Overlay
{

    /**
     * Constructor for the Overlay class. Simply sets the scanning interface.
     * @param string $scanning_iface The interface to do the initial scan on.
     */
    public function __construct($scanning_iface)
    {
        $this->scanning_iface = $scanning_iface;
    }


    /**
     * [generateAPs description]
     * @return [type] [description]
     */
    public function generateAPs()
    {
        set_time_limit(300);

        unlink("/tmp/recon.ap");
        unlink("/tmp/recon.ap.done");

        exec("ifconfig {$this->scanning_iface} up");
        exec("echo \"bash -c 'for i in {1..5}; do iw {$this->scanning_iface} scan >> /tmp/recon.ap; sleep 1; done; touch /tmp/recon.ap.done'\" | at now", $scan);
        return true;
    }

    public function retreiveAPs()
    {
        if (file_exists("/tmp/recon.ap.done")) {
            return json_encode($this->parseScan());
        }
        
        return 0;
    }

    public function generateStations($duration)
    {
        if (file_exists("/tmp/running.overlay")) {
            break;
        }

        touch("/tmp/running.overlay");

        if (trim(exec("ifconfig -a | grep $(echo $(ifconfig wlan1 | grep HWaddr | awk '{print $5}' | sed 's/:/-/g')) | head -n1 | awk '{print $1}'")) == "") {
            exec("airmon-ng start wlan1");
        }
        $mon_interface = exec("ifconfig -a | grep $(echo $(ifconfig wlan1 | grep HWaddr | awk '{print $5}' | sed 's/:/-/g')) | head -n1 | awk '{print $1}'");

        exec("ifconfig wlan1 down");
        exec("echo 'pinesniffer ". $mon_interface . " ". $duration ."' | at now");

        unlink("/tmp/running.overlay");
    }

    public function retreiveStations()
    {
        while (true) {
            if (file_exists("/tmp/recon.stations")) {
                break;
            }
            usleep(500000);
        }

        $wlan0_mac = trim(exec("ifconfig wlan0 | grep HWaddr | awk '{print $5}'"));
        $stations = array();
        $stations_csv_array = explode("\n", trim(file_get_contents("/tmp/recon.stations")));
        unlink("/tmp/recon.stations");

        foreach ($stations_csv_array as $station_csv) {
            $station = array();
            $array = explode(",", $station_csv);

            if ($array[0] != "") {
                if (trim($array[0]) == $wlan0_mac) {
                    continue;
                }
                $station['sta'] = strtolower(trim($array[0]));

                if (trim($array[1]) != "FF:FF:FF:FF:FF:FF") {
                    $station['bssid'] = strtolower(trim($array[1]));
                } else {
                    $station['bssid'] = "";
                }
                array_push($stations, $station);
            }
            
            

            if ($array[0] != "") {
                $station['sta'] = strtolower($array[0]);
                if (trim($array[5]) == "(not associated)") {
                    $station['bssid'] = "";
                } else {
                    $station['bssid'] = strtolower(trim($array[5]));
                }
                
            }
        }
        echo json_encode($stations);
    }


    private function parseScan()
    {
        set_time_limit(300);
        $ap_list = array();

        $scan = preg_split("/^BSS /m", file_get_contents("/tmp/recon.ap"));
        unlink("/tmp/recon.ap");
        unlink("/tmp/recon.ap.done");
        unset($scan[0]);

        foreach ($scan as $ap) {
            $ap = explode("\n", $ap);
            $address = substr($ap[0], 0, 17);
            $ap_list[$address] = array();
            foreach ($ap as $line) {
                $line = trim($line);
                if (strpos($line, "SSID:") >= -1) {
                    $ap_list[$address]['ESSID'] = htmlspecialchars(substr($line, 6));
                    if (preg_match("/^(\\\\x00)*$/", $ap_list[$address]['ESSID'])) {
                        $ap_list[$address]['ESSID'] = '';
                    }
                } elseif (strpos($line, "DS Parameter set:") >= -1) {
                    $ap_list[$address]['channel'] = substr($line, 26);
                } elseif (strpos($line, "signal:") >= -1) {
                    $ap_list[$address]['signal'] = substr($line, 8);
                    $quality = 2*(substr($line, 8, -4)+100);
                    if ($quality >= 100) {
                        $quality = 100;
                    }
                    if ($quality <= 0) {
                        $quality = 0;
                    }
                    $ap_list[$address]['quality'] = $quality."%";
                } elseif (strpos($line, "Privacy") >= -1) {
                    if (!is_array($ap_list[$address]['security'])) {
                        $ap_list[$address]['security'] = array();
                    }
                    $ap_list[$address]['security']['WEP'] = true;
                } elseif (strpos($line, "RSN:") >= -1) {
                    unset($ap_list[$address]['security']['WEP']);
                    $security = "WPA2";
                    $ap_list[$address]['security'][$security] = array();
                } elseif (strpos($line, "WPA:") >= -1) {
                    unset($ap_list[$address]['security']['WEP']);
                    $security = "WPA";
                    $ap_list[$address]['security'][$security] = array();
                } elseif (strpos($line, "Pairwise ciphers") >= -1) {
                    if (strpos($line, "CCMP") !== false) {
                        $ap_list[$address]['security'][$security]["ccmp"] = true;
                    }
                    if (strpos($line, "TKIP") !== false) {
                        $ap_list[$address]['security'][$security]["tkip"] = true;
                    }
                }
            }
        }
        return $ap_list;
    }
}
