function save_reporting_config() {
    refresh_current_tab(function(){
        $("#reporting_message").html("<span class='success'>Settings saved successfully</div>");
    })
}
function update_log(message) {
    $('#log_message').text(message);
}

function load_infusion_log(message) {
    $('#infusion_log').html(message);
}