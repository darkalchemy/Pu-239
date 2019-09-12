<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;

global $container, $lang, $site_config, $CURUSER;

$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : (isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0);
$topic_id = isset($_GET['topic_id']) ? (int) $_GET['topic_id'] : (isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0);
$fluent = $container->get(Database::class);
$arr_post = $fluent->from('posts AS p')
                   ->select(null)
                   ->select('p.user_id')
                   ->select('p.staff_lock')
                   ->select('p.status AS post_status')
                   ->select('u.class')
                   ->select('u.status')
                   ->select('t.locked')
                   ->select('t.user_id as owner_id')
                   ->select('t.first_post')
                   ->select('f.min_class_read')
                   ->select('f.min_class_write')
                   ->select('f.id AS forum_id')
                   ->leftJoin('users AS u ON p.user_id = u.id')
                   ->leftJoin('topics AS t ON p.topic_id = t.id')
                   ->leftJoin('forums AS f ON t.forum_id = f.id')
                   ->where('p.id = ?', $post_id)
                   ->fetch();

$can_delete = $arr_post['user_id'] === $CURUSER['id'] || has_access($CURUSER['class'], UC_STAFF, 'forum_mod');
if (!has_access($CURUSER['class'], (int) $arr_post['min_class_read'], '') || !has_access($CURUSER['class'], (int) $arr_post['min_class_write'], '')) {
    stderr($lang['gl_error'], $lang['fe_topic_not_found']);
}
if (!has_access($CURUSER['class'], (int) $arr_post['min_class_read'], '') || !has_access($CURUSER['class'], (int) $arr_post['min_class_write'], '')) {
    stderr($lang['gl_error'], $lang['fe_topic_not_found']);
}
if ($CURUSER['forum_post'] === 'no' || $CURUSER['status'] === 5) {
    stderr($lang['gl_error'], $lang['fe_your_no_post_right']);
}
if (!$can_delete) {
    stderr($lang['gl_error'], $lang['fe_no_your_post_del']);
}
if ($arr_post['locked'] === 'yes') {
    stderr($lang['gl_error'], $lang['fe_this_topic_is_locked']);
}
if ($arr_post['staff_lock'] === 1) {
    stderr($lang['gl_error'], $lang['fe_this_topic_is_locked_staff']);
}
if ($arr_post['first_post'] == $post_id && $CURUSER['class'] < UC_STAFF) {
    stderr($lang['gl_error'], $lang['fe_cant_del_1st_post_staff']);
}
if ($arr_post['first_post'] == $post_id && $CURUSER['class'] >= UC_STAFF) {
    stderr($lang['gl_error'], $lang['fe_this_is_1st_post_topic'] . ' <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=forums_admin&amp;action_2=delete_topic&amp;topic_id=' . $topic_id . '">' . $lang['fe_del_topic'] . '</a>.');
}
if ($arr_post['post_status'] !== 'deleted') {
    stderr($lang['gl_error'], $lang['fm_not_soft_deleted']);
}

$update = [
    'status' => 'ok',
];
$fluent->update('posts')
       ->set($update)
       ->where('id =?', $post_id)
       ->execute();
$update = [
    'post_count' => new Literal('post_count + 1'),
];
$fluent->update('forums')
       ->set($update)
       ->where('id = ?', $arr_post['forum_id'])
       ->execute();
$update = [
    'forumposts' => new Literal('forumposts + 1'),
];
$fluent->update('usersachiev')
       ->set($update)
       ->where('userid = ?', $arr_post['user_id'])
       ->execute();

clr_forums_cache((int) $post_id);
$cache = $container->get(Cache::class);
for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
    $cache->delete('forum_last_post_' . $arr_post['forum_id'] . '_' . $i);
}

header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
die();
