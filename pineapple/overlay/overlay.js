window.overlay_data = {}
window.overlay_data['client_running'] = false;
window.overlay_data['auto_scan'] = false;
window.overlay_data['stop'] = false;


function ap_scan()
{
    $.get('/overlay/overlay.php?ap_scan', function(data){
        if(data.length){
            if(window.overlay_data['running']) {
                window.overlay_data['ap_data'] = JSON.parse(data);
            }
        }
    });
}


function client_scan(duration)
{
    var duration = parseInt(duration);

    $.get('/overlay/overlay.php', {generate_stations: duration});
    
    setTimeout(function(){
    
        $.get('/overlay/overlay.php', {retreive_stations: ''}, function(data){
            if (data.length) {
                if(window.overlay_data['running']) {
                    window.overlay_data['client_data'] = JSON.parse(data);
                }
            }
            window.overlay_data['client_running'] = false;
        });

    }, (duration+5)*1000);
}


function draw_data(advanced_scan)
{

    if(window.overlay_data['stop']) {
        window.overlay_data['stop'] = false;
        window.overlay_data['running'] = false;
        window.overlay_data['ap_data'] = "";
        window.overlay_data['client_data'] = "";
        return;
    }


    var ap_data = window.overlay_data['ap_data'];
    var client_data = window.overlay_data['client_data'];


    if (advanced_scan) {
        if(ap_data && client_data) {
            draw_ap_data(ap_data);
            draw_client_data(client_data);
            $(".overlay_message").html("");
            window.overlay_data['running'] = false;
        } else {
            setTimeout(function(){
                draw_data(true);
            }, 1000);
        }
    } else {
        if (ap_data) {
            draw_ap_data(ap_data);
            $(".overlay_message").html("");
            window.overlay_data['running'] = false;
        } else {
            setTimeout(function(){
                draw_data(false);
            }, 1000);
        }
    }
}

function draw_client_data(station_list)
{
    $(".overlay_clients").remove();
    for (var i=0; i < station_list.length; i++) {
        var station = station_list[i]['sta'];
        var bssid = station_list[i]['bssid'];
        if ($('#' + bssid.replace(/:/g,'')).length) {
            if (!$('#' + bssid.replace(/:/g,'')).find(".overlay_clients").length) {
                $('#' + bssid.replace(/:/g,'')).append("<div class='overlay_clients'>Clients:<br /></div>");
            }
            $('#'+bssid.replace(/:/g,'')).find(".overlay_clients").append(station + "<br />");
        } else {
            if (bssid.length) {
                if (!$("#out_of_range").length) {
                    $("#"+get_least_full_col()+".overlay_col").append("<div class='overlay_ap'><fieldset id='out_of_range'><legend><span class='error'>AP out of range</span></legend>Clients: <br /></fieldset>")
                }
                $("#out_of_range").append(station + " -> " + bssid + "<br />");
            } else {
                if (!$("#not_associated").length){
                    $("#"+get_least_full_col()+".overlay_col").append("<div class='overlay_ap'><fieldset id='not_associated'><legend><span class='error'>Not Associated</span></legend>Clients: <br /></fieldset>")
                }
                $("#not_associated").append(station + "<br />");
            }
        }
    }
    window.overlay_data['client_data'] = "";
}

function draw_ap_data(ap_list)
{
    $(".overlay_col").html("");
    var col = 1;
    for (var bssid in ap_list) {
        var SSID = (ap_list[bssid]['ESSID'] == "") ? "<span class='error'>Hidden SSID</span>" : "<a href='#' class='success' onclick='recon_ap_action(\"" + ap_list[bssid]['ESSID'] + "\"); return false;'>" + ap_list[bssid]['ESSID'] + "</a>"; 
        var ap_item = "<div class='overlay_ap'><fieldset id='"+bssid.replace(/:/g,'')+"'>";
        ap_item += "<legend>" + SSID + " - "+ ap_list[bssid]['quality'] +"&nbsp;</legend>";
        if(typeof ap_list[bssid]['security'] !== "undefined"){
            if(typeof ap_list[bssid]['security']['WEP'] !== "undefined"){
                var security = "WEP";
            } else if(typeof ap_list[bssid]['security']['WPA2'] !== "undefined") {
                var security = "WPA2";
            } else if(typeof ap_list[bssid]['security']['WPA'] !== "undefined") {
                var security = "WPA";
            }
        }else{
            var security = "Open";
        }
        ap_item += bssid + "<br />Security: " +  security + "<br />Channel: " + ap_list[bssid]['channel'];
        ap_item += "<br /><br />";
        ap_item += "</fieldset></div>";
        $("#"+col+".overlay_col").append(ap_item);
        $(".overlay_loading").hide();
        col++;
        if(col > 4) col = 1;
    }
    window.overlay_data['ap_data'] = "";
}


function initial_scan() {
    $(".overlay_loading").show();
    ap_scan();
    window.overlay_data['running'] = true;
    draw_data(false);
}

function get_least_full_col(){
    var cols = [];

    for(var i = 0; i < 4; i++){
      cols[i] = $('.overlay_col#'+(i+1)+' > div').length
    }
    return cols.indexOf(Math.min.apply(Math, cols))+1;
}

function overlay_start_scan() {
    var scan_type = $("[name=scan_type]:checked").val();
    var scan_duration = parseInt($("[name=scan_duration]").val());
    var scan_auto = $("[name=auto_scan]").prop("checked");
    
    if (scan_auto) {
        window.overlay_data['auto_scan'] = true;
        window.overlay_data['refresh_id'] = setInterval(function(){
            if (window.overlay_data['auto_scan']) {
                if (!window.overlay_data['running']) {
                    overlay_scan(scan_duration, scan_type);
                }
            } else {
                clearInterval(window.overlay_data['refresh_id']);
                window.overlay_data['refresh_id'] = "";
                window.overlay_data['auto_scan'] = false;
            }
        }, 2500, "overlay");

    } else {
        overlay_scan(scan_duration, scan_type);
    }
}


function overlay_scan(duration, type) {
    $(".overlay_message").html("<img style='width: 1.5em;' src='/includes/img/throbber.gif'><br />Performing Scan");
    window.overlay_data['running'] = true;

    switch(type){
        case "ap_client":
            client_scan(duration);
            draw_data(true);

            setTimeout(function(){
                ap_scan();
            }, (duration-8)*1000);

        break;
        default:
            ap_scan();
            draw_data(false);
        break;
    }

}


function overlay_stop_scan() {
    clearInterval(window.overlay_data['refresh_id']);
    window.overlay_data['stop'] = true;
    $(".overlay_message").html("");
}

function recon_ap_action(ssid) {
    var popup_html = "";

    popup_html += "<b>Access Point Actions:</b><br /><br />";
    popup_html += "<a href='#sys/pineap/pineap_add_ssid/" + encodeURIComponent(ssid) + "/pineAP_add_ssid_callback'>Add to PineAP SSID list</a>";
    popup(popup_html);
}

function pineAP_add_ssid_callback(ssid) {
    if (ssid.length) {
        notify("'"+ decodeURIComponent(ssid) +"' added to PineAP SSID list.");
        close_popup();
    } else {
        popup("<span class='error'>Error adding SSID. PineAP must be turned on to add SSIDs to the list.</span>");
    }
    
}