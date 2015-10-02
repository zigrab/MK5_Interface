var notifications = [];
var tile_list = new Array();
var tile_updaters = new Array();
var loaded = false;
var interval;
var statusbar_interval;

function init(){
  //Set up everything

  if(!loaded){
    draw_statusBar();
    get_tiles();

    setup_window_listeners();
    notification_handler();
    setup_key_handerls();
    loaded = true;
  }
}

function draw_statusBar(){
  $.get("/includes/api/statusbar_handler.php", {action: "get_status_bar"}, function(data){
    $(".statusBar_content").html(data);
  });    
}

function get_tiles(){
  $.get("/includes/api/tile_handler.php", {action: "getTiles"}, function(data){
    var tiles = jQuery.parseJSON(data);
    for (var i=0;i<tiles.length;i++){
      draw_small_tile(tiles[i]['id'], tiles[i]['name'], tiles[i]['type']);
    }
  });
}

function draw_small_tile(id, name, type){
  $(".tiles_wrapper").append('<div class="tile" id="'+id+'"></div>');

  $.get('/components/'+type+'/'+name+'/handler.php', {action: "get_small_tile"}, function(data){
    var data = jQuery.parseJSON(data);

    $("div[id='"+id+"']").append('<div class="tile_title" id="'+id+'_title"><b>'+data['title']+'</b></div>');
    $("div[id='"+id+"']").append('<div class="tile_content"></div>');
    updateTile(id, name, type);

    $("[id='"+id+"_title']").bind('click', function() {
      if($('.tile_expanded').css('visibility') == 'hidden'){
        draw_large_tile(name, type);
      }
    });

    tile_list[name] = id;

    //Setup updaters
    clearInterval(tile_updaters[name]);
    if(data['update'] == 'true'){
      $("div[id='"+id+"']").bind('focusin', function(){
        var updater = tile_updaters[name];
        window.clearInterval(updater);
      });

      $("div[id='"+id+"']").bind('focusout', function(){
        clearInterval(tile_updaters[name]);
        tile_updaters[name] = setInterval(function(){
          updateTile(id, name, type);
        }, 5000);
      });
      
      clearInterval(tile_updaters[name]);
      tile_updaters[name] = setInterval(function(){
        updateTile(id, name, type);
      }, 5000);
    }
  });


}

function draw_large_tile(name, type, data){
  $("div[id='"+tile_list[name]+"']").css('box-shadow', 'none');
  $('.tile_expanded').css('visibility', 'visible');
  $('.tile_expanded').html('<center><div class="entropy">Entropy bunny is working..</div><div class="entropy" id="1"><pre>(\\___/)\n(=\'.\'=)\n(")_(")</div><div class="entropy" id="2" style="display: none"><pre> /)___(\\ \n(=\'.\'=)\n(")_(")</div><script type="text/javascript">$(function (){interval = setInterval(function(){$(".entropy#1").toggle(); $(".entropy#2").toggle();}, 200);});</script>');
  $.get('/components/'+type+'/'+name+'/handler.php?'+data, {action: "get_large_tile"}, function(data){
    clearInterval(interval);
    $('.tile_expanded').html('<a id="close" href="JAVASCRIPT: hide_large_tile()">[X]</a>'+data);
  });
}

function hide_large_tile(){
  $('.tile_expanded').html(' ');
  $('.tile_expanded').css('visibility', 'hidden');
}

function notify(message, sender, color){
  var message = [message, sender, color];
  notifications.push(message);
  return true;
}

function notification_handler(){
  clearInterval(statusbar_interval);
  statusbar_interval = setInterval(function(){
    if(notifications.length == 0){
      $.get("/includes/api/statusbar_handler.php", {action: "get_status_bar"}, function(data){
        $(".statusBar_content").html(data);
      });      
    }else{
      var notification = notifications.shift();
      if(notification[1] != null){
        if(notification[2] != null){
          $("div[id='"+tile_list[notification[1]]+"']").css('box-shadow', '2px 2px 50px 2px '+notification[2]+' inset');
        }else{
          $("div[id='"+tile_list[notification[1]]+"']").css('box-shadow', '2px 2px 50px 2px green inset');  
        }
      }
      $(".statusBar").html(notification[0]);
    }
  }, 2800);
}

function setup_key_handerls(){
  //This handler listens for the escape key
  $(document).keyup(function(e){
    if(e.keyCode == 27){
      hide_large_tile();
      clearInterval(interval);
    }
  });
}

function setup_window_listeners(){
  //This handler listens for any change in the URLs hash values
  handle_hash_change(window.location.hash);
  $(window).on('hashchange', function() {
    handle_hash_change(window.location.hash);
  });
}

function handle_hash_change(hashValue){
  //[0]:type - [1]:infusion_name - [2]:action - [3]:data - [4]:callback_function 
  var hash_array = hashValue.replace(/#/g, '').split('/');
  if(hash_array.length == 5){
    //Correct size, carry on
    $.ajaxSetup({async:false});
    if(hash_array[0] == "usr"){
      $.get('/components/infusions/'+hash_array[1]+'/functions.php?'+hash_array[2]+'='+hash_array[3], function(data){
        try{
          window[hash_array[4]](data);
        }catch(err){
          console.log("Function not found");
        }
      });
    }else if(hash_array[0] == "sys"){
      $.get('/components/system/'+hash_array[1]+'/functions.php?'+hash_array[2]+'='+hash_array[3], function(data){
        try{
          window[hash_array[4]](data);
        }catch(err){
          console.log("Function not found");
        }
      });
    }
    $.ajaxSetup({async:true});
  }

  //reset url so that we can call the same link again.
  window.location='#';
}

function refresh_small(name, type){
  var id = tile_list[name];
  updateTile(id, name, (type == 'sys' ? "system" : "infusions"), "");
}


function updateTile(id, name, type, data){

  $.get('/components/'+type+'/'+name+'/handler.php', {action: "update_small_tile"}, function(data){
    $("div[id='"+id+"'] .tile_content").html(data);
  });

}


function popup(message){
  $('.popup_content').html(message);
  $('.popup').css('visibility', 'visible');
}

function close_popup(){
  $('.popup').css('visibility', 'hidden');
  $('.popup_content').html('');
}


$.fn.AJAXifyForm = function(funct){
  this.each(function(i,el){
    var formData = new FormData();

    $("input,select,textarea",el).each(function(i,formEl){
      if(formEl.type == "file"){
        for(x=0; x<formEl.files.length; x++){
          formData.append(formEl.name,formEl.files[x]);
        }
      }
      else
      {
        formData.append(formEl.name, formEl.value);
      }
    });

    $.ajax({
      url: el.action,
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      async: false,
      type: el.method,
      success: funct
    });
  });

  return this;
}