<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Post;

$posted_staff_action = strip_tags((isset($_POST['action_2']) ? $_POST['action_2'] : ''));
$valid_staff_actions = [
    'delete_posts',
    'un_delete_posts',
    'split_topic',
    'merge_posts',
    'append_posts',
    'send_to_recycle_bin',
    'send_pm',
    'set_pinned',
    'set_locked',
    'move_topic',
    'rename_topic',
    'change_topic_desc',
    'merge_topic',
    'move_to_recycle_bin',
    'remove_from_recycle_bin',
    'delete_topic',
    'un_delete_topic',
];
$staff_action = (in_array($posted_staff_action, $valid_staff_actions) ? $posted_staff_action : 1);
global $container, $site_config, $CURUSER;

if ($CURUSER['class'] < UC_STAFF) {
    stderr($lang['gl_error'], $lang['fe_no_access_for_you_mr']);
}
if ($staff_action == 1) {
    stderr($lang['gl_error'], $lang['fe_no_action_selected']);
}
$post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$topic_id = isset($_POST['topic_id']) ? (int) $_POST['topic_id'] : 0;
$forum_id = isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0;
if ($topic_id > 0) {
    $res_check = sql_query('SELECT f.min_class_read FROM forums AS f LEFT JOIN topics AS t ON t.forum_id=f.id WHERE f.id=t.forum_id AND t.id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    $arr_check = mysqli_fetch_row($res_check);
    if ($CURUSER['class'] < $arr_check[0]) {
        stderr($lang['gl_error'], $lang['gl_bad_id']);
        exit();
    }
}
switch ($staff_action) {
    case 'delete_posts':
        if (isset($_POST['post_to_mess_with'])) {
            $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
            $post_to_mess_with = [];
            foreach ($_POST['post_to_mess_with'] as $var) {
                $post_to_mess_with[] = (int) $var;
            }
            $post_to_mess_with = array_unique($post_to_mess_with);
            $posts_count = count($post_to_mess_with);
            if ($posts_count > 0) {
                if ($site_config['forum_config']['delete_for_real']) {
                    sql_query('UPDATE posts SET status = "deleted" WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                } else {
                    sql_query('DELETE FROM posts WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                    clr_forums_cache($topic_id);
                    $res = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                    $arr = mysqli_fetch_assoc($res);
                    if (empty($arr['id'])) {
                        sql_query('DELETE FROM topics WHERE topic_id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                    } else {
                        sql_query('UPDATE topics SET last_post = ' . sqlesc($arr['id']) . ', post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                    }
                    sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id=' . sqlesc($arr['forum_id'])) or sqlerr(__FILE__, __LINE__);
                }
            } else {
                stderr($lang['gl_error'], $lang['fe_nothing_deleted']);
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
            die();
        }
        break;

    case 'un_delete_posts':
        if (isset($_POST['post_to_mess_with'])) {
            $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
            $post_to_mess_with = [];
            foreach ($_POST['post_to_mess_with'] as $var) {
                $post_to_mess_with[] = (int) $var;
            }
            $post_to_mess_with = array_unique($post_to_mess_with);
            $posts_count = count($post_to_mess_with);
            if ($posts_count > 0) {
                sql_query('UPDATE posts SET status = "ok" WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                clr_forums_cache($topic_id);
            } else {
                stderr($lang['gl_error'], $lang['fe_nothing_removed_from_the_trash']);
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
            die();
        }
        break;

    case 'split_topic':
        if (!is_valid_id($topic_id) || !is_valid_id($forum_id)) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        $new_topic_name = strip_tags((isset($_POST['new_topic_name']) ? trim($_POST['new_topic_name']) : ''));
        $new_topic_desc = strip_tags((isset($_POST['new_topic_desc']) ? trim($_POST['new_topic_desc']) : ''));
        if ($new_topic_name === '') {
            stderr($lang['gl_error'], $lang['fe_to_split_this_topic_you_must_supply_a_name_for_the_new_topic']);
        }
        if (isset($_POST['post_to_mess_with'])) {
            sql_query('INSERT INTO topics (topic_name, forum_id, topic_desc) VALUES (' . sqlesc($new_topic_name) . ', ' . sqlesc($forum_id) . ', ' . sqlesc($new_topic_desc) . ')') or sqlerr(__FILE__, __LINE__);
            $new_topic_id = ((is_null($___mysqli_res = mysqli_insert_id($mysqli))) ? false : $___mysqli_res);
            $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
            $post_to_mess_with = [];
            foreach ($_POST['post_to_mess_with'] as $var) {
                $post_to_mess_with[] = (int) $var;
            }
            $post_to_mess_with = array_unique($post_to_mess_with);
            $posts_count = count($post_to_mess_with);
            if ($posts_count > 0) {
                sql_query('UPDATE posts SET topic_id=' . $new_topic_id . ' WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                clr_forums_cache($topic_id);

                $res_split_from = sql_query('SELECT p.id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $arr_split_from = mysqli_fetch_row($res_split_from);
                sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_split_from[0]) . ', post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                $res_split_to = sql_query('SELECT p.id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($new_topic_id) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $arr_split_to = mysqli_fetch_row($res_split_to);
                $res_owner = sql_query('SELECT p.user_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($new_topic_id) . ' ORDER BY p.id ASC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $arr_owner = mysqli_fetch_row($res_owner);
                sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_split_to[0]) . ', post_count = ' . sqlesc($posts_count) . ', user_id=' . sqlesc($arr_owner[0]) . ' WHERE id=' . sqlesc($new_topic_id)) or sqlerr(__FILE__, __LINE__);
            } else {
                stderr($lang['gl_error'], $lang['fe_topic_not_split']);
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $new_topic_id);
            die();
        }
        break;

    case 'merge_posts':
        $topic_to_merge_with = isset($_POST['new_topic']) ? (int) $_POST['new_topic'] : 0;
        $topic_res = sql_query('SELECT id  FROM topics WHERE id=' . sqlesc($topic_to_merge_with)) or sqlerr(__FILE__, __LINE__);
        $topic_arr = mysqli_fetch_row($topic_res);
        if (!is_valid_id((int) $topic_arr[0])) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        if (isset($_POST['post_to_mess_with'])) {
            $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
            $post_to_mess_with = [];
            foreach ($_POST['post_to_mess_with'] as $var) {
                $post_to_mess_with[] = intval($var);
            }
            $post_to_mess_with = array_unique($post_to_mess_with);
            $posts_count = count($post_to_mess_with);
            if ($posts_count > 0) {
                sql_query('UPDATE posts SET topic_id=' . $topic_to_merge_with . ' WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                clr_forums_cache($topic_id);
                $res_from = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $arr_from = mysqli_fetch_assoc($res_from);
                sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_from['id']) . ', post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id=' . sqlesc($arr_from['forum_id'])) or sqlerr(__FILE__, __LINE__);
                $res_to = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($topic_to_merge_with) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $arr_to = mysqli_fetch_assoc($res_to);
                sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_to['id']) . ', post_count = post_count + ' . sqlesc($posts_count) . ' WHERE id=' . sqlesc($topic_to_merge_with)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE forums SET post_count = post_count + ' . sqlesc($posts_count) . ' WHERE id=' . sqlesc($arr_to['forum_id'])) or sqlerr(__FILE__, __LINE__);
            } else {
                stderr($lang['gl_error'], $lang['fe_posts_were_not_merged']);
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_to_merge_with);
            die();
        }
        break;

    case 'append_posts':
        $topic_to_append_to = isset($_POST['new_topic']) ? (int) $_POST['new_topic'] : 0;
        $topic_res = sql_query('SELECT id  FROM topics WHERE id=' . sqlesc($topic_to_append_to)) or sqlerr(__FILE__, __LINE__);
        $topic_arr = mysqli_fetch_row($topic_res);
        if (!is_valid_id((int) $topic_arr[0])) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        if (isset($_POST['post_to_mess_with'])) {
            $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
            $post_to_mess_with = [];
            $count = 0;
            foreach ($_POST['post_to_mess_with'] as $var) {
                $post_to_mess_with = intval($var);
                $post_res = sql_query('SELECT * FROM posts WHERE id=' . sqlesc($post_to_mess_with)) or sqlerr(__FILE__, __LINE__);
                $post_arr = mysqli_fetch_array($post_res);
                $values = [
                    'topic_id' => $topic_to_append_to,
                    'user_id' => $post_arr['user_id'],
                    'added' => $post_arr['added'],
                    'body' => $post_arr['body'],
                    'edited_by' => $post_arr['edited_by'],
                    'edit_date' => $post_arr['edit_date'],
                    'icon' => $post_arr['icon'],
                    'post_title' => $post_arr['post_title'],
                    'bbcode' => $post_arr['bbcode'],
                    'post_history' => $post_arr['post_history'],
                    'edit_reason' => $post_arr['edit_reason'],
                    'ip' => inet_pton($post_arr['ip']),
                    'status' => $post_arr['status'],
                    'anonymous' => $post_arr['anonymous'],
                ];
                $posts_class = $container->get(Post::class);
                $posts_class->insert($values);
                $count = $count + 1;
                $posts_class->delete($post_to_mess_with, $topic_id);
                clr_forums_cache($topic_id);
            }
            if ($count > 0) {
                $res_from = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $arr_from = mysqli_fetch_assoc($res_from);
                sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_from['id']) . ', post_count = post_count - ' . sqlesc($count) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($count) . ' WHERE id=' . sqlesc($arr_from['forum_id'])) or sqlerr(__FILE__, __LINE__);
                $res_to = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($topic_to_append_to) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                $arr_to = mysqli_fetch_assoc($res_to);
                sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_to['id']) . ', post_count = post_count + ' . sqlesc($count) . ' WHERE id=' . sqlesc($topic_to_append_to)) or sqlerr(__FILE__, __LINE__);
                sql_query('UPDATE forums SET post_count = post_count + ' . sqlesc($count) . ' WHERE id=' . sqlesc($arr_to['forum_id'])) or sqlerr(__FILE__, __LINE__);
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_to_append_to);
            die();
        }
        break;

    case 'send_to_recycle_bin':
        if (isset($_POST['post_to_mess_with'])) {
            $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
            $post_to_mess_with = [];
            foreach ($_POST['post_to_mess_with'] as $var) {
                $post_to_mess_with[] = intval($var);
            }
            $post_to_mess_with = array_unique($post_to_mess_with);
            $posts_count = count($post_to_mess_with);
            if ($posts_count > 0) {
                sql_query('UPDATE posts SET status = "recycled" WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                clr_forums_cache($topic_id);
            } else {
                stderr($lang['gl_error'], $lang['fe_nothing_sent_to_recy']);
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
            die();
        }
        break;

    case 'remove_from_recycle_bin':
        if (isset($_POST['post_to_mess_with'])) {
            $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
            $post_to_mess_with = [];
            foreach ($_POST['post_to_mess_with'] as $var) {
                $post_to_mess_with[] = intval($var);
            }
            $post_to_mess_with = array_unique($post_to_mess_with);
            $posts_count = count($post_to_mess_with);
            if ($posts_count > 0) {
                sql_query('UPDATE posts SET status = "ok" WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
                clr_forums_cache($topic_id);
            } else {
                stderr($lang['gl_error'], $lang['fe_nothing_removed_from_the_recy']);
            }
            header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
            die();
        }
        break;

    case 'send_pm':
        if (!is_valid_id($topic_id)) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        $subject = strip_tags(isset($_POST['subject']) ? trim($_POST['subject']) : '');
        $message = (isset($_POST['message']) ? htmlsafechars($_POST['message']) : '');
        $from = ((isset($_POST['pm_from']) && $_POST['pm_from'] == 0) ? 0 : $CURUSER['id']);
        if ($subject == '' || $message == '') {
            stderr($lang['gl_error'], $lang['fe_you_must_enter_both_a_subj_mes']);
        }
        if (isset($_POST['post_to_mess_with'])) {
            $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
            $post_to_mess_with = [];
            $count = 0;
            foreach ($_POST['post_to_mess_with'] as $var) {
                $post_to_mess_with = intval($var);
                $post_res = sql_query('SELECT user_id FROM posts WHERE id=' . sqlesc($post_to_mess_with)) or sqlerr(__FILE__, __LINE__);
                $post_arr = mysqli_fetch_row($post_res);
                sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, location, poster) VALUES (' . sqlesc($from) . ', ' . sqlesc($post_arr[0]) . ', ' . TIME_NOW . ', ' . sqlesc($message) . ', ' . sqlesc($subject) . ', 1, ' . sqlesc($from) . ')') or sqlerr(__FILE__, __LINE__);
                $count = $count + 1;
            }
        }
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id . '&count=' . $count);
        die();
        break;

    case 'set_pinned':
        if (!is_valid_id($topic_id)) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        sql_query('UPDATE topics SET sticky = "' . ($_POST['pinned'] === 'yes' ? 'yes' : 'no') . '" WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache($topic_id);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break;

    case 'set_locked':
        if (!is_valid_id($topic_id)) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        sql_query('UPDATE topics SET locked = "' . ($_POST['locked'] === 'yes' ? 'yes' : 'no') . '" WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache($topic_id);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break;

    case 'move_topic':
        $res = sql_query('SELECT id FROM forums WHERE id=' . sqlesc($forum_id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_row($res);

        if (!is_valid_id($arr[0])) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        sql_query('UPDATE topics SET forum_id=' . sqlesc($forum_id) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache($topic_id);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break;

    case 'rename_topic':
        $new_topic_name = strip_tags((isset($_POST['new_topic_name']) ? trim($_POST['new_topic_name']) : ''));
        if ($new_topic_name === '') {
            stderr($lang['gl_error'], $lang['fe_if_you_want_to_ren_topic_must_sup_a_name']);
        }
        sql_query('UPDATE topics SET topic_name = ' . sqlesc($new_topic_name) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache($topic_id);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break;

    case 'change_topic_desc':
        $new_topic_desc = strip_tags((isset($_POST['new_topic_desc']) ? trim($_POST['new_topic_desc']) : ''));
        sql_query('UPDATE topics SET topic_desc = ' . sqlesc($new_topic_desc) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache($topic_id);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break;

    case 'merge_topic':
        $topic_to_merge_with = (isset($_POST['topic_to_merge_with']) ? (int) $_POST['topic_to_merge_with'] : 0);
        $topic_res = sql_query('SELECT COUNT(p.id) AS count, t.id, t.forum_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE t.id=' . sqlesc($topic_id) . ' GROUP BY p.topic_id') or sqlerr(__FILE__, __LINE__);
        $topic_arr = mysqli_fetch_assoc($topic_res);
        $count = $topic_arr['count'];
        if (!is_valid_id($topic_arr['id'])) {
            stderr($lang['gl_error'], $lang['gl_bad_id']);
        }
        sql_query('UPDATE posts SET topic_id=' . sqlesc($topic_to_merge_with) . ' WHERE topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        sql_query('UPDATE subscriptions SET topic_id=' . sqlesc($topic_to_merge_with) . ' WHERE topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $res = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id=t.id WHERE p.topic_id=' . sqlesc($topic_to_merge_with) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        sql_query('UPDATE topics SET last_post = ' . sqlesc($arr['id']) . ', post_count = post_count + ' . sqlesc($count) . ' WHERE id=' . sqlesc($topic_to_merge_with)) or sqlerr(__FILE__, __LINE__);
        if ($topic_arr['forum_id'] != $arr['forum_id']) {
            sql_query('UPDATE forums SET post_count = post_count + ' . sqlesc($count) . ' WHERE id=' . sqlesc($arr['forum_id'])) or sqlerr(__FILE__, __LINE__);
            sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($count) . ', topic_count = topic_count -1 WHERE id=' . sqlesc($topic_arr['forum_id'])) or sqlerr(__FILE__, __LINE__);
        } else {
            sql_query('UPDATE forums SET topic_count = topic_count -1 WHERE id=' . sqlesc($arr['forum_id'])) or sqlerr(__FILE__, __LINE__);
        }
        sql_query('DELETE FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_to_merge_with);
        die();
        break;

    case 'move_to_recycle_bin':
        $status = $_POST['status'] === 'yes' ? 'recycled' : 'ok';
        sql_query('UPDATE topics SET status = ' . sqlesc($status) . ' WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        sql_query('DELETE FROM subscriptions WHERE topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache($topic_id);
        header('Location: ' . $_SERVER['PHP_SELF'] . ($_POST['status'] == 'yes' ? '?action=view_forum&forum_id=' . $forum_id : '?action=view_topic&topic_id=' . $topic_id));
        die();
        break;

    case 'delete_topic':
        if (!isset($_POST['sanity_check'])) {
            stderr($lang['fe_sanity_check'], '' . $lang['fe_are_you_sure_you_want_to_delete_this_topic_msg'] . '<br>
	<form action="forums.php?action=staff_actions" method="post" accept-charset="utf-8">
	<input type="hidden" name="action_2" value="delete_topic">
	<input type="hidden" name="sanity_check" value="1">
	<input type="hidden" name="topic_id" value="' . $topic_id . '">
	<input type="submit" name="button" class="top20 button is-small" value="' . $lang['fe_del_topic'] . '">
	</form>');
        }
        if ($site_config['forum_config']['delete_for_real']) {
            sql_query('UPDATE topics SET status = "deleted" WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        } else {
            $res_count = sql_query('SELECT post_count, forum_id, poll_id FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
            $arr_count = mysqli_fetch_assoc($res_count);
            sql_query('DELETE FROM subscriptions WHERE topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
            sql_query('DELETE FROM forum_poll WHERE id=' . sqlesc($arr_count['poll_id'])) or sqlerr(__FILE__, __LINE__);
            sql_query('DELETE FROM forum_poll_votes WHERE poll_id=' . sqlesc($arr_count['poll_id'])) or sqlerr(__FILE__, __LINE__);
            sql_query('DELETE FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
            sql_query('DELETE FROM posts WHERE topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
            clr_forums_cache($topic_id);
            sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($arr_count['post_count']) . ', topic_count = topic_count - 1 WHERE id=' . sqlesc($arr_count['forum_id'])) or sqlerr(__FILE__, __LINE__);

            $cache = $container->get(Cache::class);
            for ($i = UC_MIN; $i <= UC_MAX; ++$i) {
                $cache->delete('forum_last_post_' . $arr_count['forum_id'] . '_' . $i);
            }
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        }
        break;

    case 'un_delete_topic':
        sql_query('UPDATE topics SET status = "ok" WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $res_count = sql_query('SELECT post_count FROM topics WHERE id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $arr_count = mysqli_fetch_row($res_count);
        sql_query('UPDATE forums SET post_count = post_count + ' . sqlesc($arr_count[0]) . ', topic_count = topic_count + 1 WHERE id=' . sqlesc($arr_count['forum_id'])) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache($topic_id);
        header('Location: ' . $_SERVER['PHP_SELF'] . '?action=view_topic&topic_id=' . $topic_id);
        die();
        break;
}
