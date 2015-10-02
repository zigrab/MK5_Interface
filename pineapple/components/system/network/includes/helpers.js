function update_message(data) {
  $('#network_message').html(data);
}

function wired_type_select() {
    var value = $("input[name=wired_type]:checked").val();

    $("#wired_client_select").empty();
    $("#wired_settings_table").empty();

    if (value == "client") {
        $("#wired_client_select").append("<tr><td><select onchange='wired_mode_select()' name='wired_mode'><option value='dhcp'>DHCP</option><option value='static'>Static</option></select></td></tr>");
    }
}

function save_internet_wired_settings() {
    popup("<center class='success'>Wired Internet Settings Saved.</center><br />\
        For these changes to take effect, you will need to restart the WiFi Pineapple's Networking.\
        This will all clients connected to the wired and wireless interfaces for a few seconds.<br />\
        <br />\
        <center><a href='#sys/network/restart_network/true/popup'>Restart Networking</a></center>");
}

function save_local_wired_settings() {
    popup("<center class='success'>Local Network Settings Saved.</center><br />\
        For these changes to take effect, you will need to restart the WiFi Pineapple's Networking.\
        This will all clients connected to the wired and wireless interfaces for a few seconds.<br />\
        <br />\
        <center><a href='#sys/network/restart_network/true/popup'>Restart Networking</a></center>");
}