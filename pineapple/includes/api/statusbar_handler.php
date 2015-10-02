<?php

//Check if logged in
include_once('/pineapple/includes/api/auth.php');

//Action handler
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_status_bar':
            get_status_bar();
            break;
        case 'get_notifications':
            get_notifications();
            break;
        case 'send_notification':
            send_notification($_POST['notification']);
            break;
        case 'clear_notifications':
            print "test";
            clear_notifications();
            break;
    }
}


function get_status_bar()
{
    $status_bar = array();
    array_push($status_bar, exec('date +"%T"'));
    array_push($status_bar, get_notifications());
    echo json_encode($status_bar);
}


function get_notifications()
{
    $db = setup_notification_db();
    $result = $db->query("SELECT * FROM notifications;");

    $notifications = array();
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row['notification'] = htmlspecialchars($row['notification']);
        array_push($notifications, $row);
    }
    $db->close();

    return $notifications;
}


function clear_notifications()
{
    $db = setup_notification_db();
    $db->query("DELETE FROM notifications;");
    $db->close();
}


function send_notification($notification)
{
    $notification = str_replace("'", '\'"\'"\'', $notification);
    exec("pineapple notify '{$notification}'");
}


function setup_notification_db()
{
    $db = new SQLite3("/etc/pineapple/mk5.db");
    $db->exec(
        "CREATE TABLE IF NOT EXISTS notifications 
        (ID INTEGER PRIMARY KEY AUTOINCREMENT,
        notification TEXT,
        time TIMESTAMP DEFAULT CURRENT_TIMESTAMP);"
    );
    return $db;
}
