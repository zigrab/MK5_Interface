<?php

namespace pineapple;

class PineAP
{

    private function communicate($command, $return_bytes = 0)
    {
        $socket = @fsockopen("unix:///var/run/pinejector.sock");
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
        return $this->communicate("beacon_interval:" . $interval);
    }

    public function setResponseInterval($interval)
    {
        return $this->communicate("response_interval:" . $interval);
    }

    public function setSource($mac)
    {
        return $this->communicate("source:" . $mac);
    }

    public function setTarget($mac)
    {
        return $this->communicate("target:" . $mac);
    }

    public function addSSID($ssid)
    {
        return $this->communicate("add_ssid:" . $ssid);
    }

    public function delSSID($ssid)
    {
        if ($this->communicate("del_ssid:" . $ssid)) {
            exec("sed -r '/^({$ssid})$/d' -i /etc/pineapple/ssid_file");
            return true;
        }
        return false;
    }
}
