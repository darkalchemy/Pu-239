<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
global $CURUSER, $site_config;

$res = sql_query('SELECT COUNT(id) FROM posts WHERE user_id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
$forumposts = $arr['0'];
sql_query('UPDATE usersachiev SET forumposts = ' . sqlesc($forumposts) . ' WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$session->set('is-success', "Your forum posts count has been updated! [{$forumposts}]");
header("Location: {$site_config['paths']['baseurl']}/achievementhistory.php?id={$CURUSER['id']}");
