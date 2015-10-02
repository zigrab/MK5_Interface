<?php
include('/pineapple/includes/api/tile_functions.php');
//Action handler
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_tiles':
            get_tiles();
            break;
        case 'get_hidden_tiles':
            get_hidden_tiles();
            break;
        case 'hide_tile':
            hide_tile();
            break;
        case 'unhide_tile':
            unhide_tile();
            break;
    }
}


function hide_tile()
{
    $name = htmlspecialchars($_GET['tile']);
    $db = new SQLite3("/etc/pineapple/mk5.db");
    $db->query("INSERT INTO infusions_hidden (name) VALUES ('".$name."');");
    $db->close();
}

function unhide_tile()
{
    $name = htmlspecialchars($_GET['tile']);
    $db = new SQLite3("/etc/pineapple/mk5.db");
    $db->query("DELETE FROM infusions_hidden WHERE name='".$name."'");
    $db->close();
}

function get_tiles()
{

    $tiles = array();
    $hidden = get_hidden_tiles_array();

    foreach (glob("/pineapple/components/*/*") as $infusion) {
        if (file_exists($infusion . '/handler.php')) {
            $infusion = explode("/", $infusion);
            if (!in_array($infusion[4], $hidden)) {
                $tiles[$infusion[4]] = $infusion[3];
            }
        }
    }

    if (!empty($tiles)) {
        ksort($tiles);
        echo json_encode($tiles);
    } else {
        echo "none";
    }

}

function get_hidden_tiles()
{

    $tiles = array();
    $hidden = get_hidden_tiles_array();

    

    $system_dir = opendir('/pineapple/components/system/');
    $infusion_dir = opendir('/pineapple/components/infusions/');

    foreach (glob('/pineapple/components/*/*') as $infusion) {
        if (file_exists($infusion . '/handler.php')) {
            $infusion = explode("/", $infusion);
            if (in_array($infusion[4], $hidden)) {
                array_push($tiles, $infusion[4]);
            }
        }
    }

    if (!empty($tiles)) {
        ksort($tiles);
        echo json_encode($tiles);
    } else {
        echo "none";
    }

}

function get_hidden_tiles_array()
{
    $hidden = array();
    $db = new SQLite3("/etc/pineapple/mk5.db");
    $db->query("create table if not exists infusions_hidden (ID INT PRIMARY KEY, name TEXT);");
    $result = $db->query("SELECT * FROM infusions_hidden");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        array_push($hidden, $row['name']);
    }
    $db->close();
    return $hidden;
}
