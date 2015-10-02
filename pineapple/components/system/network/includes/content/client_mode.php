<center><div id="network_message"></div></center>

<fieldset>
  <legend>Join Network - <a href="JAVASCRIPT:load_ssid()">Refresh SSID List</a></legend>
  <p>
    <div id="ssid_form">Loading SSIDs..</div>
  </p><div id="ap_info"></div><br />
</fieldset>

<br /><br />

<fieldset>
  <legend>Connection Information - <a href="JAVASCRIPT:disconnect()">Disconnect</a></legend>
  <p>
    <div id="connection_information">Checking Connection..</div>
  </p>
</fieldset>


<script type="text/javascript">
  var networks;

  setTimeout(load_ssid, 1000);
  setTimeout(check_connection, 1000);

  if(check_connection_interval != undefined){
    clearInterval(check_connection_interval);
  }
  var check_connection_interval = setInterval(check_connection, 10*1000);

  function load_ssid(){
    $("#ssid_form").html('Loading SSIDs..<br />');
    $.get("/components/system/network/functions.php?scan", function(data){
      networks = jQuery.parseJSON(data);
      if(networks == ""){
        $("#ssid_form").html("No networks found.");
        $("#ap_info").html("");
      }else{
        $("#ssid_form").html("<select id='ssid_form_select' onChange='get_ap_info()'></select>");
        $.each(networks, function(key, value) {
          $("#ssid_form_select").append($("<option></option>").attr("value", key).text(networks[key]['ESSID']));
        });
        get_ap_info();
      }

    });
  }

  function get_ap_info(){
    var bssid = $('#ssid_form_select').find(":selected").val()
    var ap = networks[bssid];
    if(ap["security"] != undefined){
      var security;

      if(ap["security"]["WEP"] != undefined){
        security = "WEP";
      }else if(ap["security"]["WPA2"] != undefined){
        security = "WPA2";
      }else if(ap["security"]["WPA"] != undefined){
        security = "WPA";
      }

    }else{
      var security = "Open";
    }

    var key = "";
    if(security != "Open"){
      var key = "<tr><td>Key:</td><td><input type='password' id='psk'/></td><tr>";
    }

    var info = "<table><tr><td>BSSID:</td><td>"+bssid+"</td></tr><tr><td>SSID:</td><td>"+ap['ESSID']+"</td></tr><tr><td>Channel:</td><td>"+ap['channel']+"</td></tr><tr><td>Signal Strength:</td><td>"+ap['signal']+"</td></tr><tr><td>Quality:</td><td>"+ap['quality']+"</td></tr><tr><td>Security:</td><td>"+security+"</td></tr>"+key+"</table>";
    $("#ap_info").html(info+"<a href='JAVASCRIPT: connect()'>Connect to this network</a>");
  }

  function connect(){
    var bssid = $('#ssid_form_select').find(":selected").val()
    var ap = networks[bssid];
    ap["key"] = encodeURIComponent(btoa($("#psk").val()));
    $("#network_message").html("<img src='/includes/img/throbber.gif'><br /><font color='lime'>Connecting, please wait.</font><br /><br />")

    $.get('/components/system/network/functions.php?connect='+JSON.stringify(ap), function(data){
      if(data == "done"){
        $("#network_message").html("<font color='lime'>Connection initiated. See below for connection details.</font><br /><br />")
      }
    });
  }

  function disconnect(){
    $("#network_message").html("<img src='/includes/img/throbber.gif'><br /><font color='lime'>Disconnecting, please wait.</font><br /><br />")

    $.get('/components/system/network/functions.php?disconnect', function(data){
      $("#network_message").html("<font color='lime'>Disconnected.</font><br /><br />")
    });    
  }

  function check_connection(){
    $.get('/components/system/network/functions.php?get_connection', function(data){
      if(data == "not_associated"){
        $("#connection_information").html("Not connected.. Refreshing in 10s.");
      }else{
        $("#connection_information").html("<b>Connected.</b><br /><br />"+data);
      }
    });
  }
</script>