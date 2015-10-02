<h2>Client Tracking</h2>
<div id='tracking_message'></div>
<fieldset>
    <legend>Client Management<help id="pineap:tracking_clients"></help></legend>
    <textarea rows='10' style='min-width:100%;' readonly><?php echo file_get_contents("/etc/pineapple/tracking_list")?></textarea><br><br>
    <form method='POST' action='/components/system/pineap/functions.php?tracking' onSubmit="$(this).AJAXifyForm(tracking_callback); return false;">
        <input type="text" placeholder="MAC" name='tracking_mac'> <input type="submit" value="Add MAC" name='tracking_add_mac'>
    </form>
    <form method='POST' action='/components/system/pineap/functions.php?tracking' onSubmit="$(this).AJAXifyForm(tracking_callback); return false;">
        <input type="text" placeholder="MAC" name='tracking_mac'> <input type="submit" value="Remove MAC" name='tracking_del_mac'>
    </form>
</fieldset>

<br><br>

<fieldset>
    <legend>Tracking Script<help id="pineap:tracking_script"></help></legend>
    <form method='POST' action='/components/system/pineap/functions.php?tracking' onSubmit='$(this).AJAXifyForm(tracking_callback); return false;'>
        <textarea name='tracking_script' rows='20' style='min-width:100%;'><?php echo file_get_contents("/etc/pineapple/tracking_script_user") ?></textarea>
        <br><br>
        <input type='submit' name='save_tracking_script' value='Save Script'>
    </form>
</fieldset>