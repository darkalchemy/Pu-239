<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $site_config;

$nick = $user ? $user['username'] : ('Guest_' . random_int(1000, 9999));
$HTMLOUT = main_div("
    <div class='padding20'>
    <p class='has-text-centered'>" . _fe('The official IRC channel is {0}#pu-239{1}</a></p>', "<a href='irc://irc.p2p-network.net'>", '</a>'));

$title = _('IRC');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
