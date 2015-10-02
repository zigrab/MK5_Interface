<?php
    namespace pineapple;

    $pineapple = new Pineapple(__FILE__);


?>
<br />

<fieldset>
    <legend>Wired Internet Settings<help id='network:wired_internet_settings'></help></legend>
    <form method='POST' action='<?=$pineapple->infusionRelRoot()?>functions.php?wired_settings' onsubmit='$(this).AJAXifyForm(save_internet_wired_settings); return false;'>
        <table>
            <tr>
                <td>Classic Mode: </td>
                <td><input type="radio" name="wired_type" value="classic" onclick="wired_type_select()"></td>
                <td>Client Mode: </td>
                <td><input type="radio" name="wired_type" value="client" onclick="wired_type_select()"></td>
            </tr>
        </table>
        <table id='wired_client_select'>
            
        </table>
        <table id='wired_settings_table'>
            
        </table>
        <table>
            <tr>
                <td><input type='submit' value='Save'></td>
            </tr>
        </table>
    </form>
</fieldset>


<br /><br />


<fieldset>
    <legend>Local network settings<help id='network:local_network_settings'></help></legend>
    <form method='POST' action='<?=$pineapple->infusionRelRoot()?>functions.php?local_settings' onsubmit='$(this).AJAXifyForm(save_local_wired_settings); return false;'>
        <table>
            <tr>
                <td>IP:</td>
                <td><input type="text" name="ip" value="<?=exec("uci get network.lan.ipaddr")?>"></td>
            </tr>
            <tr>
                <td>Netmask:</td>
                <td><input type="text" name="netmask" value="<?=exec('uci get network.lan.netmask')?>"></td>
            </tr>
            <tr>
                <td>Gateway:</td>
                <td><input type="text" name="gateway" value="<?=exec('uci get network.lan.gateway')?>"></td>
            </tr>
            <tr>
                <td>DNS:</td>
                <td><input type="text" name="dns" value="<?=exec('uci get network.lan.dns')?>"></td>
            </tr>
            <tr><td><input type="submit" value='Save'></td></tr>
        </table>
    </form>
</fieldset>


<script type="text/javascript">

    var select = "<?=exec('uci get network.lan.ifname')?>";
    if (select) {
        $("input[name=wired_type][value=classic]").prop("checked", true);
    } else {
        $("input[name=wired_type][value=client]").prop("checked", true);
    }
    wired_type_select();


    var proto = "<?=exec('uci get network.wiredwan.proto')?>";
    if (proto == "dhcp") {
        $("select[name=wired_mode]").val("dhcp");
    } else {
        $("select[name=wired_mode]").val("static");
    }
    wired_mode_select();

    function wired_mode_select() {
        var value = $("select[name=wired_mode]").val();

        $("#wired_settings_table").empty();

        if (value == "static") {
            $("#wired_settings_table").append("<tr><td>IP: </td><td><input type='text' name='ip' value='<?=exec('uci get network.wiredwan.ipaddr')?>'></td></tr>");
            $("#wired_settings_table").append("<tr><td>Netmask: </td><td><input type='text' name='netmask' value='<?=exec('uci get network.wiredwan.netmask')?>'></td></tr>");
            $("#wired_settings_table").append("<tr><td>Gateway: </td><td><input type='text' name='gateway' value='<?=exec('uci get network.wiredwan.gateway')?>'></td></tr>");
            $("#wired_settings_table").append("<tr><td>DNS: </td><td><input type='text' name='dns' value='<?=exec('uci get network.wiredwan.dns')?>'></td></tr>");
        }
    }
</script>