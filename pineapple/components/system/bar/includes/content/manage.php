<?php include_once('/pineapple/includes/api/tile_functions.php'); ?>
<?php

/*Grab all installed infusions by name*/
$infusions = array();
$infusion_dir = opendir('/pineapple/components/infusions/');
while(false !== ($infusion = readdir($infusion_dir))){
  if($infusion != ".." && $infusion != "."){
    array_push($infusions, $infusion);
  }
}

if(empty($infusions) || file_get_contents('/pineapple/components/system/bar/files/infusions') == ''){
  echo "<center>You have not created any infusions yet!</center>";
}else{
  $created_infusions = explode("\n", trim(file_get_contents('/pineapple/components/system/bar/files/infusions')));

  echo "
  <fieldset>
  <legend>Your Infusions</legend>
  <table style='border-spacing: 15px'>
  <th>Name</th><th>Version</th><th>Installed Size</th>";
  foreach($infusions as $infusion){

    if(in_array($infusion, $created_infusions)){
      $size = exec('du /pineapple/components/infusions/'.$infusion.'/ | awk \'{print $1}\'');
      $version = str_replace(array("'", ";"), '', exec('cat /pineapple/components/infusions/'.$infusion.'/handler.php | grep "version" | awk \'{print $3}\''));

      $remove_link = "<a href='#sys/bar/remove_created_infusion/$infusion/remove_infusion' onclick='return confirm(\"Are you sure you want to remove \\\"$infusion\\\"?\")'>Remove</a>";
      $edit_link = "<a href='#sys/bar/edit_infusion/$infusion/edit_infusion'>Edit</a>";
      $package_link = "<a href='#sys/bar/package_infusion/$infusion/popup'>Package</a>";
      echo "<tr><td>$infusion</td><td><center>$version</center></td><td><center>".$size."kb</center></td><td>$remove_link</td><td>$edit_link</td><td>$package_link</td></tr>";   
    }

  }
}


?>