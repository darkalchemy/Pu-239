<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
global $CURUSER, $site_config;

$res = sql_query("SELECT COUNT(*) FROM users WHERE enabled = 'yes' AND invitedby =" . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
$invitedcount = $arr['0'];
sql_query('UPDATE usersachiev SET invited = ' . sqlesc($invitedcount) . ' WHERE userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
setSessionVar('is-success', "Your invited count has been updated! [{$invitedcount}]");
header("Location: {$site_config['baseurl']}/achievementhistory.php?id={$CURUSER['id']}");

