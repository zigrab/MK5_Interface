<?php
        $ref = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        if (strpos($ref, "example")){
		header('Status: 302 Found');
                header('Location: example.html');
        }

        require('error.php');

?>

