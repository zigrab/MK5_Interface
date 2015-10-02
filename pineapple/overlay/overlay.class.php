<?php

namespace overlay;

class Overlay
{

    public function __construct($scanning_iface)
    {
        $this->scanning_iface = $scanning_iface;
    }

    public function getAPs()
    {
        $ap_list = $this->scan();

        return json_encode($ap_list);
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
        exec("echo '/pineapple/overlay/station_builder ". $mon_interface . " ". $duration ."' | at now");

        unlink("/tmp/running.overlay");
    }

    public function retreiveStations()
    {
        while (true) {
            if (file_exists("/tmp/stations.overlay")) {
                break;
            }
            sleep(1.5);
        }

        $stations = array();
        $stations_csv_array = explode("\n", trim(file_get_contents("/tmp/stations.overlay")));
        unlink("/tmp/stations.overlay");

        foreach ($stations_csv_array as $station_csv) {
            $station = array();
            $array = explode(",", $station_csv);
            if ($array[0] != "") {
                $station['sta'] = strtolower($array[0]);
                if (trim($array[5]) == "(not associated)") {
                    $station['bssid'] = "";
                } else {
                    $station['bssid'] = strtolower(trim($array[5]));
                }
                array_push($stations, $station);
            }
        }
        echo json_encode($stations);
    }

    private function scan()
    {
        set_time_limit(300);
        $iface = $this->scanning_iface;
        $ap_list = array();
        exec("ifconfig {$iface} up");
        exec("bash -c 'for i in {1..5}; do iw {$iface} scan; sleep 1; done;'", $scan);
        $scan = preg_split("/^BSS /m", implode("\n", $scan));
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
