<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
global $CURUSER, $site_config, $session;

$res = sql_query('SELECT COUNT(*) FROM topics WHERE user_id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
$forumtopics = $arr['0'];
sql_query('UPDATE usersachiev SET forumtopics =' . sqlesc($forumtopics) . ' WHERE userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$session->set('is-success', "Your forum topics count has been updated! [{$forumtopics}]");
header("Location: {$site_config['paths']['baseurl']}/achievementhistory.php?id={$CURUSER['id']}");
