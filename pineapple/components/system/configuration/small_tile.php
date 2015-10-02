<?php 
include_once('/pineapple/includes/api/tile_functions.php');

function dnsspoof_status()
{
    if (exec("ps -all | grep [d]nsspoof")) {
        echo 'checked';
    }
}

function cron_status()
{
    if (exec("ps -all | grep [c]ron")) {
        echo 'checked';
    }
}

?>



<div style="text-align:right">
  <a href="#" class="refresh" onclick="refresh_small('configuration', 'sys')"> </a>
</div>


<toggle infusion='configuration' action='toggle_dnsspoof' system <?=dnsspoof_status()?>></toggle> DNSSpoof<help id='configuration:small_dnsspoof'></help><br>
<toggle infusion='configuration' action='toggle_cron' system <?=cron_status()?>></toggle> Cron<help id='configuration:small_cron'></help><br>