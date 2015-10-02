<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<h2>PineAP Client Report<help id='pineap:intelligence_report'></help></h2>
<center>
    <fieldset style="display: inline-block; text-align: left; min-width: 450px;">
        <legend><a href='#' onclick="refresh_current_tab()">Regenerate</a></legend>
            <center><div id='pineap_client_report'>Loading data, please wait.</div></center>
    </fieldset>
</center>

<script type="text/javascript">
  setTimeout(function(){
    refresh_report();
  }, 0);
</script>