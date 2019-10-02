<?php

declare(strict_types = 1);

global $CURUSER, $site_config;

$topic_id = isset($_GET['topic_id']) ? (int) $_GET['topic_id'] : (isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0);
$forum_id = isset($_GET['forum_id']) ? (int) $_GET['forum_id'] : (isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0);
$norty_res = sql_query('SELECT min_class_read FROM forums WHERE id = ' . sqlesc($forum_id)) or sqlerr(__FILE__, __LINE__);
$norty_arr = mysqli_fetch_row($norty_res);
if (!is_valid_id($topic_id) || $norty_arr[0] > $CURUSER['class'] || !is_valid_id($forum_id)) {
    stderr(_('Error'), _('Bad ID.'));
}

$res = sql_query('SELECT id FROM subscriptions WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND topic_id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_row($res);
if ($arr[0] > 0) {
    stderr(_('Error'), _('You are already subscribed to this topic!'));
}

sql_query('INSERT INTO `subscriptions` (`user_id`, `topic_id`) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($topic_id) . ')') or sqlerr(__FILE__, __LINE__);
header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id . '&s=1');
die();
