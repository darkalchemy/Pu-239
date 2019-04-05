<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $CURUSER, $site_config, $cache, $fluent;

$set = [
    'curr_ann_id' => 0,
    'curr_ann_last_check' => 0,
];
$fluent->update('users')
    ->set($set)
    ->where('id=?', $CURUSER['id'])
    ->where('curr_ann_id != 0')
    ->execute();

$cache->update_row('user_' . $CURUSER['id'], [
    'curr_ann_id' => 0,
    'curr_ann_last_check' => 0,
], $site_config['expires']['user_cache']);
header("Location: {$site_config['paths']['baseurl']}/index.php");
