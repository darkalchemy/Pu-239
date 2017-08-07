<?php
if (!defined('BUNNY_FORUMS')) {
    setSessionVar('error', 'Access Not Allowed');
    header("Location: {$INSTALLER09['baseurl']}/index.php");
    die();
}
global $lang;

$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));
//=== first see if they are being norty...
$norty_res = sql_query('SELECT min_class_read FROM forums WHERE id = ' . sqlesc($forum_id));
$norty_arr = mysqli_fetch_row($norty_res);
if (!is_valid_id($topic_id) || $norty_arr[0] > $CURUSER['class'] || !is_valid_id($forum_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
//=== see if they are subscribed already
$res = sql_query('SELECT id FROM subscriptions WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND topic_id = ' . sqlesc($topic_id));
$arr = mysqli_fetch_row($res);
if ($arr[0] > 0) {
    stderr($lang['gl_error'], $lang['fe_you_already_subscib']);
}
//=== ok, that the hell, let's add it \o/
sql_query('INSERT INTO `subscriptions` (`user_id`, `topic_id`) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($topic_id) . ')');
header('Location: ' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&topic_id=' . $topic_id . '&s=1');
die();
