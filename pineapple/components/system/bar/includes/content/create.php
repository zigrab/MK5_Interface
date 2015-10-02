<?php
$directory = realpath(dirname(__FILE__)).'/../../';
$rel_dir = str_replace('/pineapple', '', $directory);
include('/pineapple/includes/api/tile_functions.php');
?>

<center>
  <div id='messages' />
  <h2>New Infusion</h2>
  <form method='POST' action='<?=$rel_dir?>functions.php?action=create' id='create_form' onSubmit='$(this).AJAXifyForm(create_infusion); return false;'>
    <table>
      <tr><td>Name:</td><td><input type='text' name='name' placeholder="Your infusion's name" /></td></tr>
      <tr><td>Title:</td><td><input type='text' name='title' placeholder="Your infusion's title" /></td></tr>
      <tr><td>Version:</td><td><input type='text' name='version' placeholder="Your infusion's version" /></td></tr>
      <tr><td>Updatable?</td><td><select name='updatable'><option value='true'>true</option><option value='false'>false</option></select></td></tr>
      <?php if(sd_available()){ echo "<tr><td>Create on SD?</td><td><select name='external'><option value='false'>false</option><option value='true'>true</option></select></td></tr>"; } ?>
    </table>
    <input type='submit' value='Create new Infusion' name='submit' />
  </form>

  <br />
  <table>
    <tr><td><b>Name</td><td>-</td><td>The name of your infusion. No spaces, one word only.</td></tr>
    <tr><td><b>Title</td><td>-</td><td>The title of the small tile.</td></tr>
    <tr><td><b>Version</td><td>-</td><td>The version of your infusion in the format X.X .</td></tr>
    <tr><td><b>Updatable</td><td>-</td><td>Should your infusion's small tile be a live tile (self updating)?</td></tr>
    <tr><td><b>Create on USB</b>*</td><td>-</td><td>Do you want to create this infusion on an external storage device?</td></tr>
  </table>
  <small>*This option will be unavailable unless external storage has been set up.</small>
</center>