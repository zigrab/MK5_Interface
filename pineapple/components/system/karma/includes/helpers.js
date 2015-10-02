$(document).ready(function () {

  $('#tabs li a:not(:first)').addClass('inactive');
  selectTabContent($('#tabs li a:first').attr('id'));
  $('#tabs li a').click(function () {
    var t = $(this).attr('id');
    if ($(this).hasClass('inactive')) {
      $('#tabs li a').addClass('inactive');
      $(this).removeClass('inactive');
      selectTabContent(t);
    }else{
      selectTabContent(t);
    }
  });

});

function selectTabContent(id){
  $.ajaxSetup({async:false});
  $.get("/components/system/karma/includes/content/"+id+".php", function(data){
    $(".tabContainer").html(data);
  });
  $.ajaxSetup({async:true});
}

function karma_reload_config(){
  selectTabContent('config');
}

function karma_handle_form(data){
  $('#karma_message').html(data);
}

function karma_change_log_location(data){
  $('#karma_message').html(data);
  $("")
}

function refresh_log(){
  $.get('/components/system/karma/functions.php?action=get_log', function(data){
    $('#karma_log').html(data);
  });
}

function refresh_report(){

  $.get('/components/system/karma/functions.php?action=get_report', function(data){

    data = JSON.parse(data);
    var clients = new Array();

    var dhcp = data[0].split('\n');
    for (var i = dhcp.length - 1; i >= 0; i--) {
      dhcp[i] = dhcp[i].split(' ');
    }

    var karma = data[2];
    for (var i = karma.length - 1; i >= 0; i--) {
      console.log(karma[i]);
      if(karma[i].indexOf("Successful") !== -1){
        var client = new Array();
        client[0] = karma[i].split(' ')[4];
        client[1] = karma[i-1].slice(60);
        console.log(client);
        var exists = false;
        for (var j = clients.length - 1; j >= 0; j--) {
          if(clients[j][0] == client[0]){
            exists = true;
          }
        }
        if(!exists){
          clients.push(client);
        }
      }
    }

    for (var i = clients.length - 1; i >= 0; i--) {
      for (var i2 = dhcp.length - 1; i2 >= 0; i2--) {
        if(dhcp[i2][1] == clients[i][0]){
          clients[i][2] = dhcp[i2][2];
          clients[i][3] = dhcp[i2][3];
        }
      }
    }


    if(clients.length != 0){
      var html = "<table style='border-spacing: 15px 2px'><tr><th>HW Address</th><th>IP Address</th><th>hostname</th><th>SSID</th></tr>";

      for (var i = clients.length - 1; i >= 0; i--) {
        if(clients[i][2] != undefined){
          html += "<tr><td>"+clients[i][0]+"</td><td text-align='center'>"+clients[i][2]+"</td><td text-align='center'>"+clients[i][3]+"</td><td>"+clients[i][1]+"</td></tr>"
        }
      }

      html += "</table>";     
    }else{
      var html = "No clients found.";
    }

    $('#karma_report').html(html);
  });
}