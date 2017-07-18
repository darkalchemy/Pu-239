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
history ot TBsourse and TBDev and the many many 
coders who helped develop them over time.
proper credits to follow :)

beta fri june 11th 2010 v0.1
delete post... thinking of changing this...

Powered by Bunnies!!!
**********************************************************/
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
$post_id = (isset($_GET['post_id']) ? intval($_GET['post_id']) : (isset($_POST['post_id']) ? intval($_POST['post_id']) : 0));
$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
$sanity_check = (isset($_GET['sanity_check']) ? intval($_GET['sanity_check']) : 0);
if (!is_valid_id($post_id) || !is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
//=== make sure it's their post or they are staff... this may change
$res_post = sql_query('SELECT p.user_id, p.staff_lock, u.id, u.class, u.suspended, t.locked, t.user_id AS owner_id, t.first_post, f.min_class_read, f.min_class_write, f.id AS forum_id FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id LEFT JOIN topics AS t ON t.id = p.topic_id LEFT JOIN forums AS f ON t.forum_id = f.id WHERE p.id=' . sqlesc($post_id));
$arr_post = mysqli_fetch_assoc($res_post);
//=== if staff or post owner let them delete post
$can_delete = ($arr_post['user_id'] === $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF);
//=== stop them, they shouldn't be here lol
//=== this is kinda long, but seems like a switch thing would be pointless, as you have to check them all...
if ($CURUSER['class'] < $arr_post['min_class_read'] || $CURUSER['class'] < $arr_post['min_class_write']) {
    stderr($lang['gl_error'], $lang['fe_topic_not_found']);
}
if ($CURUSER['forum_post'] == 'no' || $CURUSER['suspended'] == 'yes') {
    stderr($lang['gl_error'], $lang['fe_your_no_post_right']);
}
if (!$can_delete) {
    stderr($lang['gl_error'], $lang['fe_no_your_post_del']);
}
if ($arr_post['locked'] == 'yes') {
    stderr($lang['gl_error'], $lang['fe_this_topic_is_locked']);
}
if ($arr_post['staff_lock'] == 1) {
    stderr($lang['gl_error'], $lang['fe_this_topic_is_locked_staff']);
}
if ($arr_post['first_post'] == $post_id && $CURUSER['class'] < UC_STAFF) {
    stderr($lang['gl_error'], $lang['fe_cant_del_1st_post_staff']);
}
if ($arr_post['first_post'] == $post_id && $CURUSER['class'] >= UC_STAFF) {
    stderr($lang['gl_error'], $lang['fe_this_is_1st_post_topic'].' <a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=forums_admin&amp;action_2=delete_topic&amp;topic_id=' . $topic_id . '">'.$lang['fe_del_topic'].'</a>.');
}
//=== ok... they made it this far, so let's delete the damned post!
if ($sanity_check > 0) {
    //=== if you want the un-delete option (only admin and up can see "deleted" posts)
    if ($delete_for_real === 1) {
        //=== re-do that last post thing ;)
        $res = sql_query('SELECT p.id, t.forum_id FROM posts AS p LEFT JOIN topics as t ON p.topic_id = t.id WHERE p.topic_id = ' . sqlesc($topic_id) . ' ORDER BY id DESC LIMIT 1');
        $arr = mysqli_fetch_assoc($res);
        sql_query('UPDATE topics SET last_post = ' . sqlesc($arr['id']) . ', post_count = post_count - 1 WHERE id = ' . sqlesc($topic_id));
        sql_query('UPDATE forums SET post_count = post_count - 1 WHERE id = ' . sqlesc($arr['forum_id']));
        sql_query('DELETE FROM posts WHERE id = ' . sqlesc($post_id));
        sql_query("UPDATE usersachiev SET forumposts=forumposts-1 WHERE id=" . sqlesc($arr_post['user_id'])) or sqlerr(__FILE__, __LINE__);
        clr_forums_cache($arr['forum_id']);
        clr_forums_cache($post_id);
    } else {
        sql_query('UPDATE posts SET status = \'deleted\'  WHERE id = ' . sqlesc($post_id) . ' AND topic_id = ' . sqlesc($topic_id));
    }
    //=== ok, all done here, send them back! \o/
    header('Location: ' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&topic_id=' . $topic_id);
    die();
} else {
    stderr($lang['fe_sanity_check'], ''.$lang['fe_are_you_sure_del_post'].' 
	<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=delete_post&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '&amp;sanity_check=1">Here</a>.');
}
?>