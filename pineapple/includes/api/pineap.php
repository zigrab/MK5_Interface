<?php

namespace pineapple;

class PineAP
{

    private function communicate($command, $return_bytes = 0)
    {
        $socket = @fsockopen("unix:///var/run/pineap.sock");
        if ($socket) {
            fwrite($socket, $command);

            if ($return_bytes) {
                $output = fgets($socket, $return_bytes);
                return $output;
            }
            return true;
        }
        return false;
    }

    public function enableBeaconer()
    {
        return $this->communicate("beaconer:on");
    }

    public function disableBeaconer()
    {
        return $this->communicate("beaconer:off");
    }

    public function enableResponder()
    {
        return $this->communicate("responder:on");
    }

    public function disableResponder()
    {
        return $this->communicate("responder:off");
    }

    public function enableHarvester()
    {
        return $this->communicate("harvest:on");
    }

    public function disableHarvester()
    {
        return $this->communicate("harvest:off");
    }

    public function getTarget()
    {
        return $this->communicate("get_target", 1024);
    }

    public function getSource()
    {
        return $this->communicate("get_source", 1024);
    }

    public function isBeaconerRunning()
    {
        if ($this->communicate("beaconer_status", 1024) == "0") {
            return false;
        }
        return true;
    }

    public function isResponderRunning()
    {
        if ($this->communicate("responder_status", 1024) == "0") {
            return false;
        }
        return true;
    }

    public function isHarvesterRunning()
    {
        if ($this->communicate("get_harvest", 1024) == "0") {
            return false;
        }
        return true;
    }

    public function getBeaconInterval()
    {
        return $this->communicate("get_beacon_interval", 1024);
    }

    public function getResponseInterval()
    {
        return $this->communicate("get_response_interval", 1024);
    }

    public function setBeaconInterval($interval)
    {
        return $this->communicate("beacon_interval:{$interval}");
    }

    public function setResponseInterval($interval)
    {
        return $this->communicate("response_interval:{$interval}");
    }

    public function setSource($mac)
    {
        return $this->communicate("source:" . $mac);
    }

    public function setTarget($mac)
    {
        return $this->communicate("target:" . $mac);
    }

    public function deauth($target, $source, $channel, $multiplier = 1)
    {
        $channel = str_pad($channel, 2, "0", STR_PAD_LEFT);
        return $this->communicate("deauth:{$target}{$source}{$channel}{$multiplier}");
    }

    public function addSSID($ssid)
    {
        if (!$this->communicate("add_ssid:{$ssid}")) {
            if (trim(exec("grep " . escapeshellarg($ssid) . " /etc/pineapple/ssid_file")) == "") {
                file_put_contents("/etc/pineapple/ssid_file", "{$ssid}\n", FILE_APPEND);
            } else {
                return false;
            }
        }
        return true;
    }

    public function delSSID($ssid)
    {
        $this->communicate("del_ssid:{$ssid}");
        exec("sed -r '/^({$ssid})$/d' -i /etc/pineapple/ssid_file");

        return true;
    }

    public function clearSSIDs()
    {
        if (!$this->communicate("clear_ssids")) {
            file_put_contents("/etc/pineapple/ssid_file", "");
        }
        return false;
    }
}
