<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$stdfoot = [
    'js' => [
        get_file_name('iframe_js'),
    ],
];
$lang = load_language('global');

global $CURUSER, $site_config;

$html = "<iframe src='{$site_config['paths']['baseurl']}/ajax/view_sql.php?username={$CURUSER['username']}&db={$site_config['db']['database']}' id='iframe_adminer' name='iframe_adminer' onload='resizeIframe(this)' class='iframe'></iframe>";
echo stdhead('Adminer') . wrapper($html) . stdfoot($stdfoot);
