<?php

if (strpos($_SERVER['REQUEST_URI'], '/includes/css/styles.php') === false) {
    if (!empty($_POST)) {
        if (urldecode($_POST['_csrfToken']) != $_SESSION['_csrfToken']) {
            echo "Invalid CSRF token.";
            exit();
        }
        unset($_POST['_csrfToken']);
    } else {
        if (!empty($_GET)) {
            if (urldecode($_GET['_csrfToken']) != $_SESSION['_csrfToken']) {
                echo "Invalid CSRF token.";
                exit();
            }
            unset($_GET['_csrfToken']);
        }
    }
}
