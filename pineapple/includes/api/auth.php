<?php

check_login();


function check_login()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_GET['logout']) && isset($_SESSION['logged_in'])) {
        if ($_GET['_csrfToken'] == $_SESSION['_csrfToken']) {
            unset($_SESSION['logged_in']);
            exec("rm /tmp/sess_*");
            header("Location: /");
        }
    }
    if (!isset($_SESSION['logged_in'])) {
        if (!file_exists("/pineapple/includes/welcome/welcome.php")) {
            include('/pineapple/includes/api/login.php');
            exit();
        } else {
            $parsed_url = parse_url("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            if ($parsed_url['path'] != "/") {
                exit();
            }
        }
    }
}
