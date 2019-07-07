<?php

declare(strict_types = 1);

use Delight\Auth\Auth;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
global $container, $site_config;

$auth = $container->get(Auth::class);
if (!$auth->isLoggedIn()) {
    get_template();
} else {
    check_user_status();
}

$lang = array_merge(load_language('global'), load_language('useragreement'));

$HTMLOUT = "
        <h1 class='has-text-centered'>{$site_config['site']['name']} {$lang['frame_usragrmnt']}</h1>";
$HTMLOUT .= main_div($lang['text_usragrmnt'], 'has-text-justified', 'padding20');

echo stdhead($lang['stdhead_usragrmnt']) . wrapper($HTMLOUT) . stdfoot();
