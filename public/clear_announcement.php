<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $container, $site_config, $CURUSER;
$set = [
    'curr_ann_id' => 0,
    'curr_ann_last_check' => 0,
];
$fluent = $container->get(Database::class);
$fluent->update('users')
       ->set($set)
       ->where('id = ?', $CURUSER['id'])
       ->where('curr_ann_id != 0')
       ->execute();

$cache = $container->get(Cache::class);
$cache->update_row('user_' . $CURUSER['id'], [
    'curr_ann_id' => 0,
    'curr_ann_last_check' => 0,
], $site_config['expires']['user_cache']);
header("Location: {$site_config['paths']['baseurl']}");
