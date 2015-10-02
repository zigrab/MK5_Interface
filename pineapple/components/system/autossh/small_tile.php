<?php
include_once('/pineapple/includes/api/tile_functions.php');

function autoSSH_connected()
{
    exec('pgrep autossh', $pids);
    if (!empty($pids)) {
        return 'checked';
    }
}

function autoSSH_autostart()
{
    if (file_exists('/etc/rc.d/S80autossh')) {
        return 'checked';
    }
}

?>

<toggle infusion='autossh' action='toggle_autossh' system <?=autoSSH_connected()?>></toggle> AutoSSH<help id="autossh:small_autossh"></help><br>
<toggle infusion='autossh' action='toggle_autossh_autostart' system <?=autoSSH_autostart()?>></toggle> Autostart<br>

<script type="text/javascript">

    function refresh_autossh(){
        refresh_small('autossh', 'sys');
    }

</script>
