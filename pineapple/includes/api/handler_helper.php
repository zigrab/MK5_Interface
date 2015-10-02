<?php
//Check if logged in
include_once('/pineapple/includes/api/auth.php');

#Action handler
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_small_tile':
            get_small_tile();
            break;
        case 'get_large_tile':
            get_large_tile();
            break;
        case 'update_small_tile':
            update_small_tile();
            break;
        case 'get_version':
            get_version();
            break;
    }
}


#Function to retreive initial small tile data
function get_small_tile()
{
    global $name, $updatable;
    $content = array('title' => $name, 'update' => $updatable);
    echo json_encode($content);
}

function get_version()
{
    global $version;
    echo $version;
}

#Function to draw large tile
function get_large_tile()
{
    global $directory;
    global $rel_dir;
    if (substr($directory, 0, 3) == "/sd") {
        $rel_dir = str_replace('/sd', '/components', $directory);
    }
    include($directory."large_tile.php");
}

#Function to draw small tile
function update_small_tile()
{
    global $directory;
    global $rel_dir;
    if (substr($directory, 0, 3) == "/sd") {
        $rel_dir = str_replace('/sd', '/components', $directory);
    }
    include($directory."small_tile.php");
}
