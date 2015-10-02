<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<div style='text-align: right'><a href='#' class="refresh" onclick='get_bar_updates()'> </a></div>

Refresh this tile to check for system updates.<br /><br />

<div id="bar_updates"></div>


<script type="text/javascript">
  
  function get_bar_updates(){
    $('#bar_updates').html('<br /><br /><center><img style="height: 2em; width: 2em;" src="/includes/img/throbber.gif"></center>');
    $.get('/components/system/bar/functions.php?get_small_updates', function(data){
      if(data == ""){
        $('#bar_updates').html('No updates found');
      }else{
        $('#bar_updates').html(data);
      }
    })
  }

</script>