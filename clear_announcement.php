<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER, $site_config, $cache;

$query1 = sprintf('UPDATE users SET curr_ann_id = 0, curr_ann_last_check = \'0\' ' . 'WHERE id = %s AND curr_ann_id != 0', sqlesc($CURUSER['id']));
sql_query($query1);
$cache->update_row('user' . $CURUSER['id'], [
    'curr_ann_id'         => 0,
    'curr_ann_last_check' => 0,
], $site_config['expires']['user_cache']);
header("Location: {$site_config['baseurl']}/index.php");
