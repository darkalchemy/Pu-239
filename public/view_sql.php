<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $site_config, $CURUSER;

$stdfoot = [
    'js' => [
        get_file_name('iframe_js'),
    ],
];
$lang = load_language('global');

$html = "<iframe src='{$site_config['baseurl']}/ajax/view_sql.php?username={$CURUSER['username']}&db={$_ENV['DB_DATABASE']}' id='iframe_adminer' name='iframe_adminer' scrolling='no' onload='resizeIframe(this)' class='iframe'></iframe>";
echo stdhead('Adminer') . wrapper($html) . stdfoot($stdfoot);
