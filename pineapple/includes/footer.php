<?php
ini_set('display_errors',1);
$containing_dir = realpath(dirname(get_included_files()[1]));

if(substr($containing_dir, 0, 4) != "/www"){
  $previous_content = ob_get_clean();
  require_once('/pineapple/includes/api/auth.php');
  echo $previous_content;
}
?>