window.overlay_data = {};
window.overlay_data['running'] = false;
window.overlay_data['auto_scan'] = false;


function ap_scan()
{
    window.overlay_data['ap_data'] = "";
    $.get('/overlay/overlay.php?ap_scan');
    retreive_aps();
}

function retreive_aps(){
    $.get('/overlay/overlay.php?retreive_aps', function(data){
        if(data.length){
            if(data == "0") {
                setTimeout(function(){
                    retreive_aps();
                }, 1500);
            } else {
                if(window.overlay_data['running']) {
                    window.overlay_data['ap_data'] = JSON.parse(data);
                }
            }
        }
    });
}


function client_scan(duration)
{
    var duration = parseInt(duration);
    window.overlay_data['client_data'] = "";

    $.get('/overlay/overlay.php', {generate_stations: duration});
    
    setTimeout(function(){
    
        $.get('/overlay/overlay.php', {retreive_stations: ''}, function(data){
            if (data.length) {
                if(window.overlay_data['running']) {
                    window.overlay_data['client_data'] = JSON.parse(data);
                }
            }
        });

    }, (duration+5)*1000);
}


function draw_data(advanced_scan)
{

    if (!window.overlay_data['running']) {
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
            if (!window.overlay_data['auto_scan']) {
                overlay_stop_scan();
            } else {
                window.overlay_data['auto_scan'] = false;
            }
        } else {
            setTimeout(function(){
                draw_data(true);
            }, 1000);
        }
    } else {
        if (ap_data) {
            draw_ap_data(ap_data);
            if (!window.overlay_data['auto_scan']) {
                overlay_stop_scan();
            } else {
                window.overlay_data['auto_scan'] = false;
            }
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
            $('#'+bssid.replace(/:/g,'')).find(".overlay_clients").append("<a href='#' onclick='recon_client_action($(this))'>" + station + "</a>" + "<br />");
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
        if (ap_list[bssid] === undefined || ap_list[bssid]['ESSID'] === undefined) {
            continue;
        }
        var SSID = (ap_list[bssid]['ESSID'].trim() == "") ? "<span class='error'>Hidden SSID</span>" : "<a href='#' class='success' onclick='recon_ap_action(\"" + escape_ssid(ap_list[bssid]['ESSID']) + "\"); return false;'>" + ap_list[bssid]['ESSID'] + "</a>";
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
        ap_item += bssid + "<br />Security: " +  security + "<br /><span id='recon_chan'>Channel: " + ap_list[bssid]['channel'] + '</span>';
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
    window.overlay_data['running'] = true;
    $(".overlay_loading").show();
    setTimeout(function(){
        overlay_start_scan();
    }, 500);
}

function get_least_full_col(){
    var cols = [];

    for(var i = 0; i < 4; i++){
      cols[i] = $('.overlay_col#'+(i+1)+' > div').length
    }
    return cols.indexOf(Math.min.apply(Math, cols))+1;
}


function overlay_toggle_scan() {
    if (window.overlay_data['running']) {
        overlay_stop_scan();
    } else {
        overlay_start_scan();
    }
}


function overlay_start_scan() {
    $("#overlay_start_stop").text("STOP SCAN");
    $(".overlay_message").html("<img style='width: 1.0em;' src='/includes/img/throbber.gif'><div class='progress_bar' id='overlay_progress'><span></span></div>");

    window.overlay_data['running'] = true;


    var scan_type = $("[name=scan_type]:checked").val();
    var scan_duration = parseInt($("[name=scan_duration]").val());
    var scan_auto = $("[name=auto_scan]").prop("checked");

    if (scan_auto) {
        window.overlay_data['auto_scan'] = false;
        window.overlay_data['refresh_id'] = setInterval(function(){
            if (window.overlay_data['running']) {
                if (!window.overlay_data['auto_scan']) {
                    window.overlay_data['auto_scan'] = true;
                    overlay_scan(scan_duration, scan_type);
                }
            } else {
                clearInterval(window.overlay_data['refresh_id']);
                window.overlay_data['autoscan'] = false;
            }
        }, 2500, "overlay");
    } else {
        overlay_scan(scan_duration, scan_type);
    }
}


function overlay_scan(duration, type) {
    switch(type){
        case "ap_client":
            progress_bar("overlay_progress", duration+3);
            client_scan(duration);
            draw_data(true);

            setTimeout(function(){
                ap_scan();
            }, (duration-8)*1000);

        break;
        default:
            progress_bar("overlay_progress", 12);
            ap_scan();
            draw_data(false);
        break;
    }

}


function overlay_stop_scan() {
    clearInterval(window.overlay_data['refresh_id']);
    window.overlay_data['running'] = false;

    $("#overlay_start_stop").text("START SCAN");
    $(".overlay_message").html("");
    $(".overlay_loading").hide();
}

function recon_ap_action(ssid) {
    var popup_html = "";

    popup_html += "<center><h3>Access Point Actions</h3></center>";
    popup_html += "Karma:<br>";
    popup_html += "|--> <a href='#sys/pineap/karma_ssidFilter_add/" + encodeURIComponent(escape_ssid(ssid)) + "/pineAP_add_ssid_callback'>Add to SSID filter</a><br>";
    popup_html += "|--> <a href='#sys/pineap/karma_ssidFilter_del/" + encodeURIComponent(escape_ssid(ssid)) + "/pineAP_add_ssid_callback'>Remove from SSID filter</a><br>";
    popup_html += "<br>PineAP:<br>";
    popup_html += "|--> <a href='#sys/pineap/pineap_add_ssid/" + encodeURIComponent(escape_ssid(ssid)) + "/pineAP_add_ssid_callback'>Add to SSID list</a><br>";
    popup_html += "|--> <a href='#' onclick='deauth_ap_clients()'>Deauthenticate all clients</a><br>";
    popup(popup_html, [ssid]);
}

function recon_client_action(client) {
    var source = client.parent().parent().attr('id').replace(/(.{2})/g, '$1:').slice(0, -1);
    var target = client.text();
    var channel = client.parent().parent().find("#recon_chan").text();
    var client_actions = '<center><h3>Client Actions</h3></center>';
    client_actions += '|--> <a href="#" onclick="pineAP_deauth_client(\'' + target + '\', \'' + source + '\', \'' + channel + '\')">Deauth Client</a>';

    popup(client_actions);
}

function deauth_ap_clients() {
    var ap = $("a:contains('" + popup.data[0] + "')").closest("div.overlay_ap");
    var source = ap.children().attr('id').replace(/(.{2})/g, '$1:').slice(0, -1);;
    var channel = ap.find("#recon_chan").text();
    var clients = [];
    $("a:contains('" + popup.data[0] + "')").closest("div.overlay_ap").find(".overlay_clients").find("a").each(function(key, val){
        clients.push($(val).text());
    });
    pineAP_deauth_client(clients, source, channel);
}

function pineAP_send_deauth() {
    var multiplier = $("select[name='deauth_multiplier']").val();
    $.post('/components/system/pineap/functions.php?deauth',
        {
            'target' : popup.data[0],
            'source' : popup.data[1],
            'channel' : popup.data[2],
            'multiplier' : multiplier
        }, function(data) {
            if (data.length) {
                popup("<center><img style='width: 2.0em;' src='/includes/img/throbber.gif'></center>");
                setTimeout(close_popup, 1000);
            } else {
                popup("<center><span class='error'>Error sending deauth. PineAP must be started.</span></center>");
            }
        }
    );
}

function pineAP_deauth_client(target, source, channel) {
    var popup_html = "<center><h3>Deauthenticating Client(s)</h3></center>";
    if (target.constructor === Array) {
        popup_html += "You are about to deauthenticate multiple clients from " + source + ".<br><br>"
    } else {
        popup_html += "You are about to deauthenticate " + target + " from " + source + ".<br><br>"
    }

    popup_html += "Deauth Multiplier: <select name='deauth_multiplier'>";
    for (var i = 1; i <= 10; i++) {
        popup_html += "<option>" + i + "</option>";
    }
    popup_html += "<select><br><br>";
    popup_html += "<a href='#' onclick='pineAP_send_deauth()'>Start Deauth</a>";
    popup(popup_html, [target, source, channel]);
}


function pineAP_add_ssid_callback(ssid) {
    if (ssid.length) {
        popup("<center><img style='width: 2.0em;' src='/includes/img/throbber.gif'></center>");
        setTimeout(close_popup, 1000);
    } else {
        popup("<span class='error'>Error adding SSID. PineAP must be turned on to add SSIDs to the list.</span>");
    }

}

function escape_ssid(ssid) {
    return ssid
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}