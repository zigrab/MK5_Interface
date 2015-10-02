<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<?php
$directory = realpath(dirname(__FILE__)).'/../../';
$rel_dir = str_replace('/pineapple', '', $directory);
?>

<div style='text-align: center; color: lime;' id='log_message' />
<h2>Custom log to follow</h2>
<form method='POST' action='<?=$rel_dir?>functions.php?update_log' id='log' onSubmit='$(this).AJAXifyForm(update_log); return false;'>
  <fieldset>
    <legend>The small tile can keep track of any file you want!</legend>
    <label>
      Log to tail and update:
      <input type="text" name='log' value="<?=file_get_contents($directory.'custom')?>">
    </label>
    <br />
    <input type="submit" value="Follow this log">
  </fieldset>
</form>
