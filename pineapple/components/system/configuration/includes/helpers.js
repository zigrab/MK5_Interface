function update_message(data) {
  $('#config_message').html(data);
}

function update_execute(data) {
  $('#config_execute').html(data);
}


function update_dips(data) {
  refresh_current_tab(dips_updated);

}

function dips_updated() {
  update_message("DIPs have been updated successfully!");
}

function update_help(data) {
    update_message(data);
}

function update_tz(data) {
  update_message("<font color='lime'>Timezone changed.</font>");
  $.get("/components/system/configuration/functions.php?get_tz", function(data){
    $("#config_tz").text(data);
    $("input[name=custom_zone]").val("");
  });
}
