<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<div style='text-align: right'><a href='#' class="refresh" onclick='refresh_network()'> </a></div> 

<toggle infusion='network' action='toggle_wlan0' system <?=wlan0_status()?>></toggle> Wlan0<help id='network:small_wlan0'></help><br>
<toggle infusion='network' action='toggle_wlan1' system <?=wlan1_status()?>></toggle> Wlan1<help id='network:small_wlan1'></help><br>

<?php

if (exec("ifconfig -a | grep wlan2")) {
    echo "<toggle infusion='network' action='toggle_wlan2' system " . wlan2_status(). "></toggle> Wlan2<help id='network:small_wlan2'></help><br>";
}

?>
<br><br>
<div id='internet_ip'>Internet IP: <a href='JAVASCRIPT: display_internet()'>Show</a></div><br />

LAN: <?=get_lan()?><br />
Wlan1: <?=get_wlan1()?><br />

<?php

if (exec("ifconfig -a | grep wlan2")) {
    echo "Wlan2: " . get_wlan2() . '<br />';
}

?>

Mobile: <?=get_mobile()?><br />


<script type="text/javascript">

    function refresh_network(){
        refresh_small('network', 'sys');
    }

    function display_internet(){
        $('#internet_ip').html('Internet IP: Loading..');
        $.get('/components/system/network/functions.php?internet_ip', function(data){
            $('#internet_ip').html("Internet IP: "+data);
        });
    }

</script>


<?php

function wlan0_status(){
    $state = exec("ifconfig wlan0 | grep UP | awk '{print $1}'");
    if($state == "UP"){
        return 'checked';
    }
    return false;
}

function wlan1_status(){
    $state = exec("ifconfig wlan1 | grep UP | awk '{print $1}'");
    if($state == "UP"){
        return 'checked';
    }
    return false;
}

function wlan2_status(){
    $state = exec("ifconfig wlan2 | grep UP | awk '{print $1}'");
    if($state == "UP"){
        return 'checked';
    }
    return false;
}

function get_lan(){
    $ip = trim(exec("ifconfig br-lan | grep 'inet' | cut -d: -f2 | awk '{ print $1 }'"));
    if(empty($ip)){
        return 'N/A';
    }else{
        return $ip;
    } 
}

function get_wlan1(){
    $ip = trim(exec("ifconfig wlan1 | grep 'inet' | cut -d: -f2 | awk '{ print $1 }'"));
    if(empty($ip)){
        return 'N/A';
    }else{
        return $ip;
    } 
}

function get_wlan2(){
    $ip = trim(exec("ifconfig wlan2 | grep 'inet' | cut -d: -f2 | awk '{ print $1 }'"));
    if(empty($ip)){
        return 'N/A';
    }else{
        return $ip;
    } 
}

function get_mobile(){
    $ip = trim(exec("ifconfig 3g-wan2 | grep 'inet' | cut -d: -f2 | awk '{ print $1 }'"));
    if(empty($ip)){
        return 'N/A';
    }else{
        return $ip;
    } 
}
?>