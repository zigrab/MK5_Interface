<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<h2>Karma Log</h2>

<fieldset>
  <legend><a href="JAVASCRIPT:refresh_log()">Refresh Log</a> - <a href='#sys/karma/action/clear_log/refresh_log'>Clear Log</a></legend>
  <div id='karma_log'>Loading data, please wait.</div>
</fieldset>

<script type="text/javascript">
  setTimeout(function(){
    refresh_log();
  }, 0);
</script>