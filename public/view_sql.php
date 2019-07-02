<?php

declare(strict_types = 1);

use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_staff.php';
$user = check_user_status();
$stdfoot = [
    'js' => [
        get_file_name('iframe_js'),
    ],
];
$lang = load_language('global');
global $container, $site_config;

if (empty($user) || $user['class'] < UC_SYSOP) {
    $session = $container->get(Session::class);
    $session->set('is-danger', 'You do not have access to that page.');
    write_log($user['username'] . ' has attempted to access Adminer');
    write_info($user['username'] . ' has attempted to access a Staff Page');
    header("Location: {$site_config['paths']['baseurl']}");
    die();
} else {
    write_info($user['username'] . ' has accessed a Staff Page: Adminer');
    $html = "<iframe src='{$site_config['paths']['baseurl']}/ajax/view_sql.php?username={$user['username']}&db={$site_config['db']['database']}' id='iframe_adminer' name='iframe_adminer' onload='resizeIframe(this)' class='iframe'></iframe>";
    echo stdhead('Adminer') . wrapper($html) . stdfoot($stdfoot);
}
