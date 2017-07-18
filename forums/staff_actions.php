<?php
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
/**********************************************************
New 2010 forums that don't suck for TB based sites....
pretty much coded page by page, but coming from a 
history ot TBsource and TBDev and the many many 
coders who helped develope them over time.
proper credits to follow :)
beta sun aug 1st 2010 v0.1
Staff actions
should I add this to the admin folder?
Powered by Bunnies!!!
***************************************************************/
if (!defined('BUNNY_FORUMS')) {
    $HTMLOUT = '';
    $HTMLOUT.= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>ERROR</title>
        </head><body>
        <h1 style="text-align:center;">ERROR</h1>
        <p style="text-align:center;">How did you get here? silly rabbit Trix are for kids!.</p>
        </body></html>';
    echo $HTMLOUT;
    exit();
}
global $lang;
//=== post  action posted so we know what to do :P
$posted_staff_action = strip_tags((isset($_POST['action_2']) ? $_POST['action_2'] : ''));
//=== add all possible actions here and check them to be sure they are ok
$valid_staff_actions = array(
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
    'un_delete_topic'
);
//=== check posted action, and if no match, kill it
$staff_action = (in_array($posted_staff_action, $valid_staff_actions) ? $posted_staff_action : 1);
if ($CURUSER['class'] < UC_STAFF) {
    stderr($lang['gl_error'], $lang['fe_no_access_for_you_mr']);
}
if ($staff_action == 1) {
    stderr($lang['gl_error'], $lang['fe_no_action_selected']);
}
$post_id = (isset($_POST['post_id']) ? intval($_POST['post_id']) : 0);
$topic_id = (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0);
$forum_id = (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0);
//=== stop any rogue staff tomfoolery
if ($topic_id > 0) {
    //print_r($_POST);
    //print_r($_GET);
    //exit();
    $res_check = sql_query('SELECT f.min_class_read FROM forums AS f LEFT JOIN topics AS t ON t.forum_id = f.id WHERE f.id = t.forum_id AND t.id = ' . sqlesc($topic_id));
    $arr_check = mysqli_fetch_row($res_check);
    if ($CURUSER['class'] < $arr_check[0]) {
        stderr($lang['gl_error'], $lang['gl_bad_id']);
    exit();
    }
}
switch ($staff_action) {
    //=== with selected
    
case 'delete_posts':
    if (isset($_POST['post_to_mess_with'])) {
        $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
        $post_to_mess_with = array();
        foreach ($_POST['post_to_mess_with'] as $var) $post_to_mess_with[] = intval($var);
        $post_to_mess_with = array_unique($post_to_mess_with);
        $posts_count = count($post_to_mess_with);
        if ($posts_count > 0) {
            //=== if you want the un-delete option (only admin and up can see "deleted" posts)
            if ($delete_for_real < 1) {
                sql_query('UPDATE posts SET status = \'deleted\' WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id = ' . sqlesc($topic_id));
            } else {
                //=== if you just want the damned things deleted
                sql_query('DELETE FROM posts WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id = ' . sqlesc($topic_id));
                clr_forums_cache($topic_id);
                //=== re-do that last post thing ;)
                $res = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1');
                $arr = mysqli_fetch_assoc($res);
                sql_query('UPDATE topics SET last_post = ' . sqlesc($arr['id']) . ', post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id = ' . sqlesc($topic_id));
                sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id = ' . sqlesc($arr['forum_id']));
            }
        } else {
            stderr($lang['gl_error'], $lang['fe_nothing_deleted']);
        }
        header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
        die();
    }
    break;

case 'un_delete_posts': //=== only if you don't actually delete posts in delete_posts
    if (isset($_POST['post_to_mess_with'])) {
        $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
        $post_to_mess_with = array();
        foreach ($_POST['post_to_mess_with'] as $var) $post_to_mess_with[] = intval($var);
        $post_to_mess_with = array_unique($post_to_mess_with);
        $posts_count = count($post_to_mess_with);
        if ($posts_count > 0) {
            sql_query('UPDATE posts SET status = \'ok\' WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id = ' . sqlesc($topic_id));
            clr_forums_cache($topic_id);
        } else {
            stderr($lang['gl_error'], $lang['fe_nothing_removed_from_the_trash']);
        }
        header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
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
        //=== make the new topic:
        sql_query('INSERT INTO topics (topic_name, forum_id, topic_desc) VALUES (' . sqlesc($new_topic_name) . ', ' . sqlesc($forum_id) . ', ' . sqlesc($new_topic_desc) . ')');
        $new_topic_id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
        $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
        $post_to_mess_with = array();
        foreach ($_POST['post_to_mess_with'] as $var) $post_to_mess_with[] = intval($var);
        $post_to_mess_with = array_unique($post_to_mess_with);
        $posts_count = count($post_to_mess_with);
        if ($posts_count > 0) {
            //=== move posts to new topic
            sql_query('UPDATE posts SET topic_id = ' . $new_topic_id . ' WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id = ' . sqlesc($topic_id));
            clr_forums_cache($topic_id);
            
            //=== update post counts... topic split FROM
            $res_split_from = sql_query('SELECT p.id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1');
            $arr_split_from = mysqli_fetch_row($res_split_from);
            sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_split_from[0]) . ', post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id = ' . sqlesc($topic_id));
            //=== update post counts... new topic from split
            $res_split_to = sql_query('SELECT p.id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($new_topic_id) . ' ORDER BY p.id DESC LIMIT 1');
            $arr_split_to = mysqli_fetch_row($res_split_to);
            //=== get topic owner for new split topic based on first poster in new topic
            $res_owner = sql_query('SELECT p.user_id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($new_topic_id) . ' ORDER BY p.id ASC LIMIT 1');
            $arr_owner = mysqli_fetch_row($res_owner);
            sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_split_to[0]) . ', post_count = ' . sqlesc($posts_count) . ', user_id = ' . sqlesc($arr_owner[0]) . ' WHERE id = ' . sqlesc($new_topic_id));
        } else {
            stderr($lang['gl_error'], $lang['fe_topic_not_split']);
        }
        header('Location: forums.php?action=view_topic&topic_id=' . $new_topic_id);
        die();
    }
    break;

case 'merge_posts':
    $topic_to_merge_with = (isset($_POST['new_topic']) ? intval($_POST['new_topic']) : 0);
    //=== make sure there is a topic to merge with
    $topic_res = sql_query('SELECT id  FROM topics WHERE id = ' . sqlesc($topic_to_merge_with));
    $topic_arr = mysqli_fetch_row($topic_res);
    if (!is_valid_id($topic_arr[0])) {
        stderr($lang['gl_error'], $lang['gl_bad_id']);
    }
    if (isset($_POST['post_to_mess_with'])) {
        $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
        $post_to_mess_with = array();
        foreach ($_POST['post_to_mess_with'] as $var) $post_to_mess_with[] = intval($var);
        $post_to_mess_with = array_unique($post_to_mess_with);
        $posts_count = count($post_to_mess_with);
        if ($posts_count > 0) {
            sql_query('UPDATE posts SET topic_id = ' . $topic_to_merge_with . ' WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id = ' . sqlesc($topic_id));
            clr_forums_cache($topic_id);
            
            //=== update post counts... topic merged FROM
            $res_from = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1');
            $arr_from = mysqli_fetch_assoc($res_from);
            sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_from['id']) . ', post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id = ' . sqlesc($topic_id));
            sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($posts_count) . ' WHERE id = ' . sqlesc($arr_from['forum_id']));
            //=== update post counts... topic merged INTO
            $res_to = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($topic_to_merge_with) . ' ORDER BY p.id DESC LIMIT 1');
            $arr_to = mysqli_fetch_assoc($res_to);
            sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_to['id']) . ', post_count = post_count + ' . sqlesc($posts_count) . ' WHERE id = ' . sqlesc($topic_to_merge_with));
            sql_query('UPDATE forums SET post_count = post_count + ' . sqlesc($posts_count) . ' WHERE id = ' . sqlesc($arr_to['forum_id']));
        } else {
            stderr($lang['gl_error'], $lang['fe_posts_were_not_merged']);
        }
        header('Location: forums.php?action=view_topic&topic_id=' . $topic_to_merge_with);
        die();
    }
    break;

case 'append_posts':
    $topic_to_append_to = (isset($_POST['new_topic']) ? intval($_POST['new_topic']) : 0);
    //=== make sure there is a topic to append to
    $topic_res = sql_query('SELECT id  FROM topics WHERE id = ' . sqlesc($topic_to_append_to));
    $topic_arr = mysqli_fetch_row($topic_res);
    if (!is_valid_id($topic_arr[0])) {
        stderr($lang['gl_error'], $lang['gl_bad_id']);
    }
    if (isset($_POST['post_to_mess_with'])) {
        $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
        $post_to_mess_with = array();
        $count = 0;
        foreach ($_POST['post_to_mess_with'] as $var) {
            $post_to_mess_with = intval($var);
            //=== get current post info
            $post_res = sql_query('SELECT * FROM posts WHERE id = ' . sqlesc($post_to_mess_with));
            $post_arr = mysqli_fetch_array($post_res);
            sql_query('INSERT INTO posts (`topic_id`, `user_id`, `added`, `body`, `edited_by`, `edit_date`, `icon`, `post_title`, `bbcode`, `post_history`, `edit_reason`, `ip`, `status`, `anonymous`) VALUES 
						(' . sqlesc($topic_to_append_to) . ', ' . sqlesc($post_arr['user_id']) . ', ' . sqlesc($post_arr['added']) . ', ' . sqlesc($post_arr['body']) . ', ' . sqlesc($post_arr['edited_by']) . ', ' . $post_arr['edit_date'] . ', 
						' . sqlesc($post_arr['icon']) . ', ' . sqlesc($post_arr['post_title']) . ', ' . sqlesc($post_arr['bbcode']) . ', ' . sqlesc($post_arr['post_history']) . ', 
						' . sqlesc($post_arr['edit_reason']) . ', ' . sqlesc($post_arr['ip']) . ', ' . sqlesc($post_arr['status']) . ', ' . sqlesc($post_arr['anonymous']) . ')');
            $count = $count + 1;
            sql_query('DELETE FROM posts WHERE id = ' . sqlesc($post_to_mess_with) . ' AND topic_id = ' . sqlesc($topic_id));
            clr_forums_cache($topic_id);    
            
        }
        //=== and delete post and update counts and boum! done \o/
        if ($count > 0) {
            //=== update post counts... topic apended from
            $res_from = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($topic_id) . ' ORDER BY p.id DESC LIMIT 1');
            $arr_from = mysqli_fetch_assoc($res_from);
            sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_from['id']) . ', post_count = post_count - ' . sqlesc($count) . ' WHERE id = ' . sqlesc($topic_id));
            sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($count) . ' WHERE id = ' . sqlesc($arr_from['forum_id']));
            //=== update post counts... topic apended to
            $res_to = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($topic_to_append_to) . ' ORDER BY p.id DESC LIMIT 1');
            $arr_to = mysqli_fetch_assoc($res_to);
            sql_query('UPDATE topics SET last_post = ' . sqlesc($arr_to['id']) . ', post_count = post_count + ' . sqlesc($count) . ' WHERE id = ' . sqlesc($topic_to_append_to));
            sql_query('UPDATE forums SET post_count = post_count + ' . sqlesc($count) . ' WHERE id = ' . sqlesc($arr_to['forum_id']));
        }
        header('Location: forums.php?action=view_topic&topic_id=' . $topic_to_append_to);
        die();
    }
    break;

case 'send_to_recycle_bin':
    if (isset($_POST['post_to_mess_with'])) {
        $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
        $post_to_mess_with = array();
        foreach ($_POST['post_to_mess_with'] as $var) $post_to_mess_with[] = intval($var);
        $post_to_mess_with = array_unique($post_to_mess_with);
        $posts_count = count($post_to_mess_with);
        if ($posts_count > 0) {
            sql_query('UPDATE posts SET status = \'recycled\' WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id = ' . sqlesc($topic_id));
            clr_forums_cache($topic_id);
        } else {
            stderr($lang['gl_error'], $lang['fe_nothing_sent_to_recy']);
        }
        header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
        die();
    }
    break;

case 'remove_from_recycle_bin':
    if (isset($_POST['post_to_mess_with'])) {
        $_POST['post_to_mess_with'] = (isset($_POST['post_to_mess_with']) ? $_POST['post_to_mess_with'] : '');
        $post_to_mess_with = array();
        foreach ($_POST['post_to_mess_with'] as $var) $post_to_mess_with[] = intval($var);
        $post_to_mess_with = array_unique($post_to_mess_with);
        $posts_count = count($post_to_mess_with);
        if ($posts_count > 0) {
            sql_query('UPDATE posts SET status = \'ok\' WHERE id IN (' . implode(', ', $post_to_mess_with) . ') AND topic_id = ' . sqlesc($topic_id));
            clr_forums_cache($topic_id);
        } else {
            stderr($lang['gl_error'], $lang['fe_nothing_removed_from_the_recy']);
        }
        header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
        die();
    }
    break;
    //=== send_pm
    
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
        $post_to_mess_with = array();
        $count = 0;
        foreach ($_POST['post_to_mess_with'] as $var) {
            $post_to_mess_with = intval($var);
            //=== get user id to send to
            $post_res = sql_query('SELECT user_id FROM posts WHERE id = ' . sqlesc($post_to_mess_with));
            $post_arr = mysqli_fetch_row($post_res);
            sql_query('INSERT INTO messages (sender, receiver, added, msg, subject, location, poster) VALUES (' . sqlesc($from) . ', ' . sqlesc($post_arr[0]) . ', ' . TIME_NOW . ', ' . sqlesc($message) . ', ' . sqlesc($subject) . ', 1, ' . sqlesc($from) . ')');
            $count = $count + 1;
        }
    }
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id . '&count=' . $count);
    die();
    break;
    //=== Set '.$lang['fe_pinned'].'
    
case 'set_pinned':
    if (!is_valid_id($topic_id)) {
        stderr($lang['gl_error'], $lang['gl_bad_id']);
    }
    sql_query('UPDATE topics SET sticky = \'' . ($_POST['pinned'] === 'yes' ? 'yes' : 'no') . '\' WHERE id = ' . sqlesc($topic_id));
    clr_forums_cache($topic_id);
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
    die();
    break;
    //=== Set Locked
    
case 'set_locked':
    if (!is_valid_id($topic_id)) {
        stderr($lang['gl_error'], $lang['gl_bad_id']);
    }
    sql_query('UPDATE topics SET locked = \'' . ($_POST['locked'] === 'yes' ? 'yes' : 'no') . '\' WHERE id = ' . sqlesc($topic_id));
    clr_forums_cache($topic_id);
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
    die();
    break;
    //=== move topic
    
case 'move_topic':
    //=== make sure there is a forum to move it to
    $res = sql_query('SELECT id FROM forums WHERE id = ' . sqlesc($forum_id));
    $arr = mysqli_fetch_row($res);
   
    if (!is_valid_id($arr[0])) {
        stderr($lang['gl_error'], $lang['gl_bad_id']);
    }
    sql_query('UPDATE topics SET forum_id = ' . sqlesc($forum_id) . ' WHERE id = ' . sqlesc($topic_id));
    clr_forums_cache($topic_id);
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
    die();
    break;
    //=== rename topic
    
case 'rename_topic':
    $new_topic_name = strip_tags((isset($_POST['new_topic_name']) ? trim($_POST['new_topic_name']) : ''));
    if ($new_topic_name == '') {
        stderr($lang['gl_error'], $lang['fe_if_you_want_to_ren_topic_must_sup_a_name']);
    }
    sql_query('UPDATE topics SET topic_name = ' . sqlesc($new_topic_name) . ' WHERE id = ' . sqlesc($topic_id));
    clr_forums_cache($topic_id);
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
    die();
    break;
    //===  change topic desc
    
case 'change_topic_desc':
    $new_topic_desc = strip_tags((isset($_POST['new_topic_desc']) ? trim($_POST['new_topic_desc']) : ''));
    sql_query('UPDATE topics SET topic_desc = ' . sqlesc($new_topic_desc) . ' WHERE id = ' . sqlesc($topic_id));
    clr_forums_cache($topic_id);
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
    die();
    break;
    //=== '.$lang['vt_merge'].' topic
    
case 'merge_topic':
    $topic_to_merge_with = (isset($_POST['topic_to_merge_with']) ? intval($_POST['topic_to_merge_with']) : 0);
    //=== make sure there is a topic to merge with & get post count
    $topic_res = sql_query('SELECT COUNT(p.id) AS count, t.id, t.forum_id FROM posts AS p LEFT JOIN topics AS t ON p.topic_id = t.id WHERE t.id = ' . sqlesc($topic_id) . ' GROUP BY p.topic_id');
    $topic_arr = mysqli_fetch_assoc($topic_res);
    $count = $topic_arr['count'];
    if (!is_valid_id($topic_arr['id'])) {
        stderr($lang['gl_error'], $lang['gl_bad_id']);
    }
    //=== change all posts to new topic
    sql_query('UPDATE posts SET topic_id = ' . sqlesc($topic_to_merge_with) . ' WHERE topic_id = ' . sqlesc($topic_id));
    //=== change any subscriptions to the new topic
    sql_query('UPDATE subscriptions SET topic_id = ' . sqlesc($topic_to_merge_with) . ' WHERE topic_id = ' . sqlesc($topic_id));
    //=== update post counts / last post
    $res = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($topic_to_merge_with) . ' ORDER BY p.id DESC LIMIT 1');
    $arr = mysqli_fetch_assoc($res);
    sql_query('UPDATE topics SET last_post = ' . sqlesc($arr['id']) . ', post_count = post_count + ' . sqlesc($count) . ' WHERE id = ' . sqlesc($topic_to_merge_with));
    //=== if topic merged with a topic in another forum
    if ($topic_arr['forum_id'] != $arr['forum_id']) {
        sql_query('UPDATE forums SET post_count = post_count + ' . sqlesc($count) . ' WHERE id = ' . sqlesc($arr['forum_id']));
        sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($count) . ', topic_count = topic_count -1 WHERE id = ' . sqlesc($topic_arr['forum_id']));
    } else {
        sql_query('UPDATE forums SET topic_count = topic_count -1 WHERE id = ' . sqlesc($arr['forum_id']));
    }
    //=== delete the old topic
    sql_query('DELETE FROM topics WHERE id = ' . sqlesc($topic_id));
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_to_merge_with);
    die();
    break;
    //=== move to recylebin
    
case 'move_to_recycle_bin':
    $status = ($_POST['status'] == 'yes' ? 'recycled' : 'ok');
    sql_query('UPDATE topics SET status = \'' . $status . '\' WHERE id = ' . sqlesc($topic_id));
    sql_query('DELETE FROM subscriptions WHERE topic_id = ' . sqlesc($topic_id));
    clr_forums_cache($topic_id);
    //=== perhaps redirect to the bin lol
    header('Location: forums.php' . ($_POST['status'] == 'yes' ? '?action=view_forum&forum_id=' . $forum_id : '?action=view_topic&topic_id=' . $topic_id));
    die();
    break;
    //=== delete topic
    
case 'delete_topic':
    //=== depending on settings, the topic can be set to  not really be deleted, OR they can just be deleted...
    //=== sanity check
    if (!isset($_POST['sanity_check'])) {
        stderr($lang['fe_sanity_check'], ''.$lang['fe_are_you_sure_you_want_to_delete_this_topic_msg'].'<br />
	<form action="forums.php?action=staff_actions" method="post">
	<input type="hidden" name="action_2" value="delete_topic" />
	<input type="hidden" name="sanity_check" value="1" />
	<input type="hidden" name="topic_id" value="' . $topic_id . '" />
	<input type="submit" name="button" class="button" value="'.$lang['fe_del_topic'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
	</form>');
    }
    //=== if you want the un-delete option (only admin and up can see "deleted" posts)
    if ($delete_for_real < 1) {
        sql_query('UPDATE topics SET status = \'deleted\' WHERE id = ' . sqlesc($topic_id));
        header('Location: forums.php');
        die();
    } else {
        //=== if you just want the damned things deleted
        //=== get post count of topic
        $res_count = sql_query('SELECT post_count, forum_id, poll_id FROM topics WHERE id = ' . sqlesc($topic_id));
        $arr_count = mysqli_fetch_assoc($res_count);
        //=== delete all the stuff
        sql_query('DELETE FROM subscriptions WHERE topic_id = ' . sqlesc($topic_id));
        sql_query('DELETE FROM forum_poll WHERE id = ' . sqlesc($arr_count['poll_id']));
        sql_query('DELETE FROM forum_poll_votes WHERE poll_id = ' . sqlesc($arr_count['poll_id']));
        sql_query('DELETE FROM topics WHERE id = ' . sqlesc($topic_id));
        sql_query('DELETE FROM posts WHERE topic_id = ' . sqlesc($topic_id));
        clr_forums_cache($topic_id);
        //=== should I delete attachments? or let the members have a management page? or do it in cleanup?
        sql_query('UPDATE forums SET post_count = post_count - ' . sqlesc($arr_count['post_count']) . ', topic_count = topic_count - 1 WHERE id = ' . sqlesc($arr_count['forum_id']));
        header('Location: forums.php');
        die();
    }
    break;
    //=== un_delete_topic
    
case 'un_delete_topic':
    sql_query('UPDATE topics SET status = \'ok\' WHERE id = ' . sqlesc($topic_id));
    //=== get post count of topic
    $res_count = sql_query('SELECT post_count FROM topics WHERE id = ' . sqlesc($topic_id));
    $arr_count = mysqli_fetch_row($res_count);
    //=== should I delete attachments? or let the members have a management page? or do it in cleanup?
    sql_query('UPDATE forums SET post_count = post_count + ' . sqlesc($arr_count[0]) . ', topic_count = topic_count + 1 WHERE id = ' . sqlesc($arr_count['forum_id']));
    clr_forums_cache($topic_id);
    header('Location: forums.php?action=view_topic&topic_id=' . $topic_id);
    die();
    break;
} //=== ends switch

?>