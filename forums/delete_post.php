<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $lang, $site_config, $CURUSER;

$post_id = isset($_GET['post_id']) ? (int) $_GET['post_id'] : (isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0);
$topic_id = isset($_GET['topic_id']) ? (int) $_GET['topic_id'] : (isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0);
$sanity_check = isset($_GET['sanity_check']) ? (int) $_GET['sanity_check'] : 0;
if (!is_valid_id($post_id) || !is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
$fluent = $container->get(Database::class);
$arr_post = $fluent->from('posts AS p')
                   ->select(null)
                   ->select('p.user_id')
                   ->select('p.staff_lock')
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
if ($CURUSER['forum_post'] === 'no' || $CURUSER['status'] !== 0) {
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
if ($sanity_check > 0) {
    if ($site_config['forum_config']['delete_for_real']) {
        $arr = $fluent->from('posts AS p')
                      ->select(null)
                      ->select('p.id')
                      ->select('t.forum_id')
                      ->leftJoin('topics AS t ON p.topic_id = t.id')
                      ->where('p.topic_id = ?', $topic_id)
                      ->orderBy('p.id DESC')
                      ->limit(1)
                      ->fetch();
        if (empty($arr['id'])) {
            $fluent->deleteFrom('topics')
                   ->where('id = ?', $topic_id)
                   ->execute();
        } else {
            sql_query('UPDATE topics SET last_post = ' . sqlesc($arr['id']) . ", post_count = (SELECT COUNT(id) FROM posts WHERE topic_id = topics.id) WHERE status = 'ok'") or sqlerr(__FILE__, __LINE__);
        }
        sql_query('UPDATE forums SET post_count = post_count - 1 WHERE id = ' . sqlesc($arr['forum_id'])) or sqlerr(__FILE__, __LINE__);
        sql_query('DELETE FROM posts WHERE id = ' . sqlesc($post_id)) or sqlerr(__FILE__, __LINE__);
        sql_query('UPDATE usersachiev SET forumposts = forumposts - 1 WHERE userid = ' . sqlesc($arr_post['user_id'])) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache((int) $arr['forum_id']);
        clr_forums_cache((int) $post_id);
        $cache = $container->get(Cache::class);
        for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
            $cache->delete('forum_last_post_' . $arr['forum_id'] . '_' . $i);
        }
    } else {
        sql_query("UPDATE posts SET status = 'deleted'  WHERE id = " . sqlesc($post_id) . ' AND topic_id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        sql_query('UPDATE forums SET post_count = post_count - 1 WHERE id = ' . sqlesc($arr_post['forum_id'])) or sqlerr(__FILE__, __LINE__);
        sql_query('UPDATE usersachiev SET forumposts = forumposts - 1 WHERE userid = ' . sqlesc($arr_post['user_id'])) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache((int) $post_id);
        $cache = $container->get(Cache::class);
        for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
            $cache->delete('forum_last_post_' . $arr_post['forum_id'] . '_' . $i);
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
    die();
} else {
    stderr($lang['fe_sanity_check'], '' . $lang['fe_are_you_sure_del_post'] . ' 
	<a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=delete_post&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '&amp;sanity_check=1">Here</a>.');
}
