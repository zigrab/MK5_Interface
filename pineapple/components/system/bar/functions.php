<?php
include('/pineapple/includes/api/tile_functions.php');
$directory = realpath(dirname(__FILE__)).'/';
$rel_dir = str_replace('/pineapple', '', $directory);

if(isset($_GET['action'])){
  switch ($_GET['action']) {
    case 'create':
    echo createNewInfusion($_POST['name'], $_POST['title'], $_POST['updatable'], $_POST['version'], $_POST['external']);
    break;
    case 'getInfusionList':
    echo getInfusionList();
    break;
    case 'save':
    echo save_edited_infusion($_POST['name'], $_POST['small_tile'], $_POST['large_tile'], $_POST['functions']);
    break;
  }
}

if(isset($_GET['preinstall'])){
  echo preinstall($_GET['preinstall']);
}
if(isset($_GET['install_internal'])){
  echo install_internal($_GET['install_internal']);
}
if(isset($_GET['install_external'])){
  echo install_external($_GET['install_external']);
}
if(isset($_GET['download_internal'])){
  echo download_internal($_GET['download_internal']);
}
if(isset($_GET['download_external'])){
  echo download_external($_GET['download_external']);
}
if(isset($_GET['download_status'])){
  echo download_status($_GET['download_status'], $_GET['infusion']);
}
if(isset($_GET['install_status'])){
  echo install_status($_GET['install_status']);
}
if(isset($_GET['remove_infusion'])){
  echo remove_infusion($_GET['remove_infusion']);
}
if(isset($_GET['remove_created_infusion'])){
  echo remove_created_infusion($_GET['remove_created_infusion']);
}
if(isset($_GET['edit_infusion'])){
  echo edit_infusion($_GET['edit_infusion']);
}
if(isset($_GET['package_infusion'])){
  echo package_infusion($_GET['package_infusion']);
}
if(isset($_GET['package_status'])){
  if(file_exists('/tmp/packager.status')){
    echo "working";
  }
}
if(isset($_GET['download'])){
  download_packaged_infusion($_GET['download']);
}
if(isset($_GET['get_small_updates'])){
  if(online()){
    $infusions = json_decode(file_get_contents("http://wifipineapple.com/?downloads&list_infusions&mk5"));
    $infusions_updatable = array();
    foreach($infusions->sys as $infusion){
      if(file_exists('/pineapple/components/system/'.$infusion->name)){
        $existing_version = str_replace(array("'", ";"), '', exec('cat /pineapple/components/system/'.$infusion->name.'/handler.php | grep "version" | awk \'{print $3}\''));
        if($existing_version < $infusion->version){
          $html .= "$infusion->name";
          array_push($infusions_updatable, $infusion->name);
        }
      }
    }
    if(sizeof($infusions_updatable)){
      echo "The following infusions are ready to be updated:<ul>";
      foreach($infusions_updatable as $infusion){
        echo "<li>".$infusion;
      }
    }else{
      echo "No updates found.";
    }
  }else{
    echo "<font color='red'>Error connecting.</font>";
  }
}

function createNewInfusion($name, $title, $updatable, $version, $external='false'){
  $name = strtolower(str_replace(' ', '', $name));
  if(!file_exists('/pineapple/components/infusions/'.$name)){
    if($external == 'true' && sd_available()){
      exec('mkdir -p /sd/infusions/');
      $created = mkdir('/sd/infusions/'.$name);
      exec('ln -s /sd/infusions/'.$name.' /pineapple/components/infusions/'.$name);
    }else{
      $created = mkdir('/pineapple/components/infusions/'.$name);
    }
    if($created){
      copy('files/handler.php', '/pineapple/components/infusions/'.$name.'/handler.php');
      exec("sed -i 's/BARTENDER_NAME/".$title."/g' /pineapple/components/infusions/$name/handler.php");
      exec("sed -i 's/BARTENDER_UPDATABLE/".$updatable."/g' /pineapple/components/infusions/$name/handler.php");
      exec("sed -i 's/BARTENDER_VERSION/".$version."/g' /pineapple/components/infusions/$name/handler.php");      
      exec("touch /pineapple/components/infusions/$name/large_tile.php");
      exec("touch /pineapple/components/infusions/$name/small_tile.php");
      exec("touch /pineapple/components/infusions/$name/functions.php");
      exec("echo '$name' >> /pineapple/components/system/bar/files/infusions");
      return '<font color="lime">Infusion created successfully! Please refresh the UI to see it.</font>';
    }else{
      return '<font color="red">There was an error. Please try again.</font>';
    }
  }else{
    return '<font color="red">An Infusion with that name already exists.</font>';
  }
}

function getInfusionList(){
  if(online()){
    $infusions = json_decode(file_get_contents("http://wifipineapple.com/?downloads&list_infusions&mk5"));
    $html .= "
    <fieldset>
      <legend>System Infusions</legend>";

    if(sizeof($infusions->sys)){
      $html .= "
      <table style='border-spacing: 15px'>
        <th>Name</th><th>Version</th><th>Description</th><th>Author</th><th>Size</th>";
        foreach($infusions->sys as $infusion){
          if(file_exists('/pineapple/components/system/'.$infusion->name)){
            $existing_version = str_replace(array("'", ";"), '', exec('cat /pineapple/components/system/'.$infusion->name.'/handler.php | grep "version" | awk \'{print $3}\''));
            if($existing_version < $infusion->version){
              $link = "<a href='#sys/bar/download_internal/".json_encode($infusion)."/popup'>Update this Infusion</a>";
              $html .= "<tr><td>$infusion->name</td><td>$infusion->version</td><td>$infusion->description</td><td>$infusion->author</td><td>".$infusion->size."kb</td><td>$link</td></tr>";
            }
          }else{
            $link = "<a href='#sys/bar/preinstall/".json_encode($infusion)."/popup'>Install</a>";
            $html .= "<tr><td>$infusion->name</td><td>$infusion->version</td><td>$infusion->description</td><td>$infusion->author</td><td>".$infusion->size."kb</td><td>$link</td></tr>";
          }
        }
        $html .= "</table>";
    }else{
      $html .= "No system infusions found. Check again later.";
    }
        $html .= "</fieldset>
    <br />
    <fieldset>
      <legend>User Infusions</legend>
      <table style='border-spacing: 15px'>
        <th>Name</th><th>Version</th><th>Description</th><th>Author</th><th>Size</th>";
        foreach($infusions->usr as $infusion){
          if(file_exists('/pineapple/components/infusions/'.$infusion->name)){
            $existing_version = str_replace(array("'", ";"), '', exec('cat /pineapple/components/infusions/'.$infusion->name.'/handler.php | grep "version" | awk \'{print $3}\''));
            if($existing_version < $infusion->version){
              if(is_link('/pineapple/components/infusions/'.$infusion->name)){
                $link = "<a href='#sys/bar/download_external/".json_encode($infusion)."/popup'>Update this Infusion</a>";
              }else{
                $link = "<a href='#sys/bar/download_internal/".json_encode($infusion)."/popup'>Update this Infusion</a>";
              }
              $html .= "<tr><td>$infusion->name</td><td>$infusion->version</td><td>$infusion->description</td><td>$infusion->author</td><td>".$infusion->size."kb</td><td>$link</td></tr>";
            }
          }else{
            $link = "<a href='#sys/bar/preinstall/".json_encode($infusion)."/popup'>Install</a>";
            $html .= "<tr><td>$infusion->name</td><td>$infusion->version</td><td>$infusion->description</td><td>$infusion->author</td><td>".$infusion->size."kb</td><td>$link</td></tr>";
          }
        }
        $html .= "
      </table>
    </fieldset>
    ";
    return $html;
  }else{
    return "Error connecting. Please check your internet connection!";
  }
  
}


function preinstall($install_string){
  $infusion = json_decode(stripslashes($install_string));
  $space_left = (disk_free_space('/')/1024)-($infusion->size);
  $html .= "Installing infusion '".$infusion->name."':<br /><br />";
  $html .= "Free space required: ".$infusion->size."kb<br />";
  $html .= "Free internal space available: ".(disk_free_space('/')/1024)."kb<br />";

  if($space_left > 80){
    $html .= "After the installation you will have ".$space_left."kb of free space remaining.<br />";
    $html .= "<br /><br /><br /><br />";
    $html .= "<center><a href='#sys/bar/download_internal/".json_encode($infusion)."/popup'>Install to internal storage</a>";
    if(sd_available() && $infusion->type != 'sys'){
      $html .= " | <a href='#sys/bar/download_external/".json_encode($infusion)."/popup'>Install to SD storage</a></center>";
    }else{
      $html .= "</center>";
    }
  }else{
    $html .= "There is not enough free internal space. Please install to usb!<br />";
    $html .= "<br /><br /><br /><br />";
    if(sd_available()){
      if($infusion->type == 'sys'){
        $html .= "<center>System infusions cannot be installed to USB.</center>";
      }else{
        $html .= "<center><a href='#sys/bar/download_external/".json_encode($infusion)."/popup'>Install to SD storage</a></center>";
      }
    }else{
      $html .= "<center>Please insert SD storage and try again.</center>";
    }    
  }

  return $html;
}

function download_status($destination, $infusion_string){
  $infusion = json_decode(stripslashes($infusion_string));
  if($destination == 'internal'){
    $current = @round(filesize('/tmp/infusions/'.$infusion->name.'-'.$infusion->version.'.tar.gz')/1024, 0);
  }else{
    $current = @round(filesize('/sd/tmp/infusions/'.$infusion->name.'-'.$infusion->version.'.tar.gz')/1024, 0);
  }
  $percentage = round(($current/$infusion->size)*100, 1);

  $html .= "<br /><br />[ ";
  for($i = 0; $i <= $percentage/2; $i++){
    if($i != 0) $html .= "|";
  }
  for($i = 0; $i <= (100-$percentage)/2; $i++){
    $html .= "&nbsp;";
  }
  $html .= "]<br />";
  $html .= "$percentage %";
  if($percentage == 100){
    if($destination == 'internal'){
      if(!file_exists('/tmp/infusions/downloading')) return 'completed';
    }else{
      if(!file_exists('/sd/tmp/infusions/downloading')) return 'completed';
    }
  }
  return $html;
}

function install_status($destination){
  if($destination == 'internal'){
    if(file_exists('/tmp/infusions/installing')){
      return 'working';
    }else{
      return 'completed';
    }
  }else{
    if(file_exists('/sd/tmp/infusions/installing')){
      return 'working';
    }else{
      return 'completed';
    }
  }
}

function download_internal($infusion_string){
  $infusion = json_decode(stripslashes($infusion_string));
  exec("echo 'bash /pineapple/components/system/bar/files/downloader internal ".$infusion->name." ".$infusion->version."' | at now");
  $html = "<center>
  Your infusion is being downloaded.<br />
  This could take a few minutes.
  <div id='percentage'></div>

  <script type='text/javascript'>
    var interval = self.setInterval(function(){
      $.get('/components/system/bar/functions.php?download_status=internal&infusion=".json_encode($infusion)."', function(data){
        if(data.replace(\"\\n\", \"\") != 'completed'){
          $('#percentage').html(data);
        }else{
          self.clearInterval(interval);
          window.location = '#sys/bar/install_internal/".json_encode($infusion)."/popup';
        }
      });
}, 500);
</script>
";
return $html;
}

function install_internal($infusion_string){
  $infusion = json_decode(stripslashes($infusion_string));
  if(md5_file('/tmp/infusions/'.$infusion->name.'-'.$infusion->version.'.tar.gz') == $infusion->md5){
    exec("echo 'bash /pineapple/components/system/bar/files/installer internal ".$infusion->name." ".$infusion->version." ".$infusion->type."' | at now");
    $html .= "<center>MD5 check passed. The infusion is now being installed</center>";
    $html .= "<center>This could take a few minutes</center>";
    $html .= "
    <script type='text/javascript'>
      var interval = self.setInterval(function(){
        $.get('/components/system/bar/functions.php?install_status=internal', function(data){
          if(data.replace(\"\\n\", \"\") == 'completed'){
            window.location = '/';
          }
        });
}, 500);
</script>
";
}else{
  $html .= "<center><font color='red'>MD5 missmatch, please try again.</font></center>";
}
return $html;
}

function download_external($infusion_string){
  $infusion = json_decode(stripslashes($infusion_string));
  exec("echo 'bash /pineapple/components/system/bar/files/downloader external ".$infusion->name." ".$infusion->version."' | at now");
  $html = "<center>
  Your infusion is being downloaded.<br />
  This could take a few minutes.
  <div id='percentage'></div>

  <script type='text/javascript'>
    var interval = self.setInterval(function(){
      $.get('/components/system/bar/functions.php?download_status=external&infusion=".json_encode($infusion)."', function(data){
        if(data.replace(\"\\n\", \"\") != 'completed'){
          $('#percentage').html(data);
        }else{
          self.clearInterval(interval);
          window.location = '#sys/bar/install_external/".json_encode($infusion)."/popup';
        }
      });
}, 500);
</script>
";
return $html;
}

function install_external($infusion_string){
  $infusion = json_decode(stripslashes($infusion_string));
  if(md5_file('/sd/tmp/infusions/'.$infusion->name.'-'.$infusion->version.'.tar.gz') == $infusion->md5){
    exec("echo 'bash /pineapple/components/system/bar/files/installer external ".$infusion->name." ".$infusion->version." ".$infusion->type."' | at now");
    $html .= "<center>MD5 check passed. The infusion is now being installed</center>";
    $html .= "<center>This could take a few minutes</center>";
    $html .= "
    <script type='text/javascript'>
      var interval = self.setInterval(function(){
        $.get('/components/system/bar/functions.php?install_status=external', function(data){
          if(data.replace(\"\\n\", \"\") == 'completed'){
            window.location = '/';
          }
        });
}, 500);
</script>
";
}else{
  $html .= "<center><font color='red'>MD5 missmatch, please try again.</font></center>";
}
return $html;
}

function remove_infusion($infusion){
  if(is_link('/pineapple/components/infusions/'.$infusion)){
    exec('rm -rf /sd/infusions/'.$infusion);
  }
  exec('rm -rf /pineapple/components/infusions/'.$infusion);
}

function remove_created_infusion($infusion){
  $infusion_list = explode("\n", file_get_contents('/pineapple/components/system/bar/files/infusions'));
  $new_file = '';
  foreach($infusion_list as $line){
    if(trim($line) != $infusion && trim($line) != ''){
      $new_file .= trim($line).PHP_EOL;
    }
  }
  file_put_contents('/pineapple/components/system/bar/files/infusions', $new_file);
  remove_infusion($infusion);
}

function edit_infusion($infusion){
  global $rel_dir;
  $path = "/pineapple/components/infusions/$infusion/";
  $small_tile_code = file_get_contents($path."small_tile.php");
  $large_tile_code = file_get_contents($path."large_tile.php");
  $function_code = file_get_contents($path."functions.php");
  
  $html .= "
  <p><center><div id='edit_message'></div></center></p>
  <form method='POST' action='".$rel_dir."functions.php?action=save' id='edit_form' onSubmit='\$(this).AJAXifyForm(edit_infusion_form); return false;'>
    <input name='name' type='hidden' value='$infusion'>
    <p><center><input name='save' value='Save Changes' type='submit'></input></center></p>
    <fieldset>
      <legend><b>Small Tile Code</b></legend>
      <textarea name='small_tile' style='width: 100%; height: 40em'>$small_tile_code</textarea>
    </fieldset>
    <br /><br />
    <fieldset>
      <legend><b>Large Tile Code</b></legend>
      <textarea name='large_tile' style='width: 100%; height: 40em'>$large_tile_code</textarea>
    </fieldset>
    <br /><br />
    <fieldset>
      <legend><b>Functions Code</b></legend>
      <textarea name='functions' style='width: 100%; height: 40em'>$function_code</textarea>
    </fieldset>
  </form>
  ";

  return $html;
}

function save_edited_infusion($infusion, $small_tile, $large_tile, $functions){
  $path = "/pineapple/components/infusions/$infusion/";
  file_put_contents($path.'small_tile.php', $small_tile);
  file_put_contents($path.'large_tile.php', $large_tile);
  file_put_contents($path.'functions.php', $functions);
  return "<font color='lime'>Infusion saved.</font>";
}

function package_infusion($infusion){
  exec("echo '/pineapple/components/system/bar/files/packager ".$infusion."' | at now");
  echo "<center>Packaging your infusion, please wait.</center><br /><br />";
  echo "<center><div id='download_link'></div></center>";
  echo "<script type='text/javascript'>

  var package_interval = setInterval(function(){
    $.get('/components/system/bar/functions.php?package_status', function(data){
      if(data != 'working'){
        clearInterval(package_interval);
        $('#download_link').html('<a href=\"/components/system/bar/functions.php?download=$infusion\">Download \"$infusion\"</a>');
      }
    });
}, 500);

</script>";
}

function download_packaged_infusion($infusion){
  $file = "/tmp/".$infusion.".tar.gz";
  if (file_exists($file)) {
    header_remove();
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit;
  }
}

?>
