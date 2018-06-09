<?php

global $lang;

$topic_id     = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
$last_post    = (isset($_GET['last_post']) ? intval($_GET['last_post']) : (isset($_POST['last_post']) ? intval($_POST['last_post']) : 0));
$check_it     = sql_query('SELECT id, last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
$check_it_arr = mysqli_fetch_assoc($check_it);
//===  update read posts
if ($check_it_arr['last_post_read'] > 0) {
    sql_query('UPDATE read_posts SET last_post_read = ' . sqlesc($last_post) . ' WHERE topic_id = ' . sqlesc($topic_id) . ' AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->delete('last_read_post_' . $topic_id . '_' . $CURUSER['id']);
    $cache->delete('sv_last_read_post_' . $topic_id . '_' . $CURUSER['id']);
} else {
    sql_query('INSERT INTO read_posts (`user_id` ,`topic_id` ,`last_post_read`) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($topic_id) . ', ' . sqlesc($last_post) . ')') or sqlerr(__FILE__, __LINE__);
    $cache->delete('last_read_post_' . $topic_id . '_' . $CURUSER['id']);
    $cache->delete('sv_last_read_post_' . $topic_id . '_' . $CURUSER['id']);
}
//=== ok, all done here, send them back! \o/
header('Location: ' . $site_config['baseurl'] . '/forums.php?action=view_unread_posts');
die();
