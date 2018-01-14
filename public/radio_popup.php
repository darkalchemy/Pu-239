<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
//require_once INCL_DIR . 'html_functions.php';
check_user_status();
$lang = load_language('global');
require_once ROOT_DIR . 'radio.php';
global $CURUSER, $site_config;

$body_class = 'background-16 h-style-9 text-9 skin-2';
$HTMLOUT = '';
$HTMLOUT = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>{$site_config['site_name']} Radio</title>
    <link rel='stylesheet' href='" . get_file_name('css') . "' />
</head>
<body class='$body_class has-text-centered'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
        function roll_over(img_name, img_src) {
            document[img_name].src=img_src;
        }
    </script>
    <h2>{$site_config['site_name']} Site Radio</h2>
    <div>
        <a href='http://{$radio['host']}:{$radio['port']}/listen.pls' onmouseover=\"roll_over('winamp', '{$site_config['pic_base_url']}winamp_over.png')\" onmouseout=\"roll_over('winamp', '{$site_config['pic_base_url']}winamp.png')\">
            <img src='{$site_config['pic_base_url']}winamp.png' name='winamp' alt='Click here to listen with Winamp' title='Click here to listen with Winamp' />
        </a>
        <a href='http://{$radio['host']}:{$radio['port']}/listen.asx' onmouseover=\"roll_over('wmp', '{$site_config['pic_base_url']}wmp_over.png')\" onmouseout=\"roll_over('wmp', '{$site_config['pic_base_url']}wmp.png')\">
            <img src='{$site_config['pic_base_url']}wmp.png' name='wmp' alt='Click here to listen with Windows Media Player' title='Click here to listen with Windows Media Player' />
        </a>
    </div>" .
    radioinfo($radio) . "
    <div class='has-text-centered'>
        <a class='altlink' href='javascript: window.close()'><b>[ Close window ]</b></a>
    </div>
</body>
</html>";
echo $HTMLOUT;
