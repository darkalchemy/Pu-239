<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();

$res = sql_query('SELECT COUNT(*) FROM topics WHERE user_id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$arr3 = mysqli_fetch_row($res);
$forumtopics = $arr3['0'];
sql_query('UPDATE usersachiev SET forumtopics=' . sqlesc($forumtopics) . ' WHERE id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
header("Location: {$INSTALLER09['baseurl']}/index.php");
