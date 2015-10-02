<?php
if (file_exists('/pineapple/includes/welcome/')) {
    include('/pineapple/includes/welcome/welcome.php');
    exit(0);
}

if (isset($_GET['noJS'])) {
    echo "You need to have JavaScript enabled to use the webinterface. <a href='/'>Refresh</a>";
    die();
}
?>
<html>


<head>
    <title>WiFi Pineapple - Management</title>
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <meta name="_csrfToken" content="<?=$_SESSION['_csrfToken']?>">
    <link rel="stylesheet" type="text/css" href="includes/css/styles.php?version=<?=file_get_contents("/etc/pineapple/pineapple_version")?>" />
    <?php echo (file_exists('/pineapple/includes/css/disable_help')) ? '<style>help:before{display: none;}</style>' : ''; ?>
    <script src="includes/js/jquery.min.js"></script>
    <script src="includes/js/functions.js?version=<?=file_get_contents("/etc/pineapple/pineapple_version")?>" type="text/javascript" ></script>
    <noscript><meta http-equiv="refresh" content="0;url=index.php?noJS" /></noscript>
    <link rel="shortcut icon" href="/includes/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/includes/img/favicon.ico" type="image/x-icon">
</head>

<body onload="init()">
    <div class="statusBar"> 
        <div class="statusBar_widget" >
            <span class="statusBar_notification_toggle">
                <a href='#' onClick="toggle_notifications(); return false;"><span id='notification_toggle'>&#x25B6;</span> <span id='notification_text'>Notifications</span> {<span id="num_notifications">-</span>}</a>
                <div class="notification_center">
                    <div class="notifications"></div>
                    <span><a href="#" onclick="clear_notifications()">- clear -</a></span>
                </div>
            </span>
            <span class="statusBar_clock">Clients: {-}</span>
            <span class='logout'><a href="/?logout"><img src="/includes/img/exit.png"></a></span>
        </div>
        <span>
            <img class='notification_img' src="/includes/img/notification.gif">
        </span>
        <div class="statusBar_view_toggle">
            <a href='#' onClick="toggle_views(); return false;">
                <span id='views_toggle'>&#x25B6;</span> <span id='views_text'>Infusions</span>
            </a>
                <div class='view_selection'>
                    <div class='view_item' onclick="select_view('infusions')">Infusions</div>
                    <div class='view_item' onclick="select_view('overlay')">Recon Mode</div>
                </div>
        </div>
    </div>
     <div class='popup'>
        <a id='close' href='JAVASCRIPT: close_popup()'>[X]</a>
        <div class='popup_content'></div>
    </div>
    <div class="tiles">
        <div class="tiles_wrapper">
            <div class="tile_expanded"></div>
        </div>
    </div>
    <div class="hidden_bar"></div>
    <div class="hidden_bar_mobile">
        <a href='#' onClick='toggle_hidden_bar_mobile()' class='hidden_bar_link'></a>
    </div>
</body>


</html>