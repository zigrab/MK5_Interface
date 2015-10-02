<?php
include('tile_functions.php');
//Action handler
if (isset($_GET['action'])) {
  switch ($_GET['action']) {
    case 'getTiles': 
    get_tiles();
    break;
  }
}


function get_tiles(){
	$tiles = array();
	$count = 0;

	$system_dir = opendir('/pineapple/components/system/');
	$infusion_dir = opendir('/pineapple/components/infusions/');

  //Fix any broken links with USB infusions
  if(usb_available()){
    if(!file_exists('/sd/infusions')){
      mkdir('/sd/infusions');
    }
    $external_dir = opendir('/sd/infusions/');
    while(false !== ($tile = readdir($external_dir))){
      if(file_exists('/sd/infusions/'.$tile.'/handler.php')){
        if(!file_exists('/pineapple/components/infusions/'.$tile)){
          exec('ln -s /sd/infusions/'.$tile.' /pineapple/components/infusions/'.$tile);
        }
      }
    }    
  }


	//Get any system tiles
  while(false !== ($tile = readdir($system_dir))){
    if(file_exists('/pineapple/components/system/'.$tile.'/handler.php')){
      $tile_data = array('id' => $count, 'name' => $tile, 'type' => "system");
      array_push($tiles, $tile_data);
      $count++;
    }
  }

  //Get any infusion tiles
  while(false !== ($tile = readdir($infusion_dir))){
    if(file_exists('/pineapple/components/infusions/'.$tile.'/handler.php')){
      $tile_data = array('id' => $count, 'name' => $tile, 'type' => "infusions");
      array_push($tiles, $tile_data);
      $count++;
    }
  }

echo json_encode($tiles);

}

?>
