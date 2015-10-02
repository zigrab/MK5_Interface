function create_infusion(data){
  $('#messages').html(data);
}

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
  $.get("/components/system/bar/includes/content/"+id+".php", function(data){
    $(".tabContainer").html(data);
  });
}

function remove_infusion(){
  window.location = '/';
}

function edit_infusion(data){
  $(".tabContainer").html(data);
}

function edit_infusion_form(data){
  $('#edit_message').html(data);
}

function update_small(data){
  if(data != ""){
    $("#bar_updates").html(data); 
  }
}