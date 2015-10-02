function karma_reload_config(){
    refresh_current_tab();
}

function karma_handle_form(data){
    $('#karma_message').html(data);
}

function karma_change_log_location(data){
    $('#karma_message').html(data);
}

function refresh_log(){
    $.get('/components/system/pineap/functions.php?action=get_log', function(data){
        $('#karma_log').html("<pre id='karma_log_content'></pre>");
        $.each(data.split("\n").reverse(), function(key, value) {
            if (value.length != 0) {
                $('#karma_log_content').append(value + "\n");
            }
        });
        karma_log = $("#karma_log_content").text().split("\n");
        apply_filters();
    });
}

function filter_log(log, filters, remove_duplicates){
    var filtered_log = log.slice(0);
    if(remove_duplicates){
        var unique_lines = [];
        var deduped_log = [];
        filtered_log.forEach(function(line){
            if(line){
                regex_line = line.substr(line.match("..:..:..").index+9);
                if($.inArray(regex_line, unique_lines) === -1){
                    unique_lines.push(regex_line);
                    deduped_log.push(line);
                }
            }
        });
        filtered_log = deduped_log.slice(0);
    }
    $("#karma_log_content").html("\n");

    var final_log = [];
    filters.forEach(function(filter){
        var temp_log = [];
        filtered_log.forEach(function(line){
            if(line.indexOf(filter) >= 0){
                temp_log.push(line);
            }
        });
        filtered_log = temp_log.slice(0);
    });

    filtered_log.forEach(function(line){
        $("#karma_log_content").append(document.createTextNode(line+"\n"));
    });
}

function refresh_report(){
    $.get('/components/system/pineap/functions.php?action=get_client_report', function(data){
        data = JSON.parse(data);


        var clients = [];
        for (i = 0; i < data['stations'].length; i++) {
            var station = data['stations'][i].split(' ');
            clients[station[0]] = [];
            clients[station[0]]['last_seen'] = station[1];
            clients[station[0]]['ip'] = "<div class='error'>---</div>";
            clients[station[0]]['hostname'] = "<div class='error'>---</div>";
        }

        for (i = 0; i < data['dhcp'].length; i++) {
            var dhcp_client = data['dhcp'][i].replace(/ +/g, ' ').split(' ');
            if (clients[dhcp_client[1]] !== undefined) {
                clients[dhcp_client[1]]['ip'] = (dhcp_client[2] !== undefined) ? dhcp_client[2] : "-";
                clients[dhcp_client[1]]['hostname'] = (dhcp_client[3] !== undefined) ? dhcp_client[3] : "-";;
            }
        }

        for (i = 1; i < data['arp'].length; i++) {
            var arp_client = data['arp'][i].replace(/ +/g, ' ').split(' ');
            if (clients[arp_client[3]] !== undefined) {
                if (clients[arp_client[3]]['ip'] === "<div class='error'>---</div>") {
                    clients[arp_client[3]]['ip'] = arp_client[0];
                }
            }
        }

        var table = "<table class='pineap_client_table'><tr><th>HW Address</th><th>IP Address</th><th>SSID</th><th>Hostname</th><th>Last Seen</th></tr>";
        var table_rows = "";
        for (var key in clients) {
            if (clients.hasOwnProperty(key)) {
                table_rows += "<tr align='center'><td><a href='#' onclick='filter_client_mac(this)'>"+key+"</a></td><td>"+clients[key]['ip']+"</td><td><i id='ssid'><span class='error'>loading</span></i></td><td>"+clients[key]['hostname']+"</td><td>"+clients[key]['last_seen']/1000+"s ago</td></tr>"
            }
        }
        table += table_rows;
        table += "</table>";     

        if (table_rows == "") {
            table = "No clients found.";
        }

        $('#pineap_client_report').html(table);
        load_client_ssids();
    });
}

function save_pineap_settings(data) {
    console.log(data);
    refresh_current_tab(function(){
        $("#pineap_message").html("<center><span class='success'>Settings Saved.</span></center>");
    });
}

function save_pineap_settings_wait() {

    popup("<center>Starting PineAP</center>");

    $.get('/components/system/pineap/functions.php?action=start_pineap', function(){
        setTimeout(function(){
            refresh_current_tab(close_popup);
            pineap_reload_tile();
        }, 250);
    });

}

function filter_client_mac(link) {
    var mac = $(link).text();
    select_tab_content($("li:contains('Log') a"), function(){
        $("input[name='mac_filter']").val(mac);
        apply_filters();
    });
}

function filter_client_ssid(link) {
    var ssid = $(link).text();
    select_tab_content($("li:contains('Log') a"), function(){
        $("input[name='ssid_filter']").val(ssid);
        apply_filters();
    });
}

function load_client_ssids() {
    var mac_array = [];
    $.each($(".pineap_client_table tr a"), function(value, key){
        mac_array.push($(key).text());
    });
    $.post("/components/system/pineap/functions.php?action=get_client_ssids", {mac_array: mac_array}, function(data){
        var ssid_array = JSON.parse(data);
        $.each(ssid_array, function(key, value){
            $(".pineap_client_table tr td a:contains('" + key + "')").parent().parent().find("#ssid").html("<a href='#' onclick='filter_client_ssid(this)'>" + value + "</a>");
        });

        $.each($(".pineap_client_table tr td i:contains('loading')"), function(key, value){
            $(value).html("<div class='error'>---</div>");
        });

    });
}