<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('chat'));
global $CURUSER, $site_config;

$nick = $CURUSER ? $CURUSER['username'] : ('Guest' . random_int(1000, 9999));
$irc_url = 'irc.p2p-network.net';
$irc_channel = '#pu-239';
$HTMLOUT = "
    <div class='padding20'>
    <p>{$lang['chat_channel']}<a href='irc://{$irc_url}'>{$irc_channel}</a> {$lang['chat_network']}</p>";

echo stdhead($lang['chat_chat']) . wrapper(main_div($HTMLOUT)) . stdfoot();
