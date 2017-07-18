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
beta tues july 20 2010 v0.1
update tue 11 aug added the rest of the staff tools (multi select ones)
 "View Topic" with Forum Polls
STILL TO DO:
fix getting to last post... I seem to have messed it up
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
//$lang = array_merge($lang, load_language('global') , load_language('forums_global'), load_language('forums')); 
$colour = $class = $attachments = $members_votes = $status = $topic_poll = $stafflocked = $child = $parent_forum_name = $math_image = $math_text = $staff_tools = $staff_link = $now_viewing = '';
$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0));
if (!is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
//=== get errors
$upload_errors_size = (isset($_GET['se']) ? intval($_GET['se']) : 0);
$upload_errors_type = (isset($_GET['ee']) ? intval($_GET['ee']) : 0);
//=== get forum_sort
$_forum_sort = (isset($CURUSER['forum_sort']) ? $CURUSER['forum_sort'] : 'DESC');
//=== Get topic info
$res = sql_query('SELECT t.id AS topic_id, t.user_id, t.topic_name, t.locked, t.last_post, t.sticky, t.status, t.views, t.poll_id, t.num_ratings, t.rating_sum, t.topic_desc, t.forum_id, t.anonymous, f.name AS forum_name, f.min_class_read, f.min_class_write, f.parent_forum FROM topics AS t LEFT JOIN forums AS f ON t.forum_id = f.id WHERE  ' . ($CURUSER['class'] < UC_STAFF ? 't.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? ' t.status != \'deleted\'  AND' : '')) . ' t.id =' . sqlesc($topic_id));
$arr = mysqli_fetch_assoc($res);
//=== stop them, they shouldn't be here lol
if ($CURUSER['class'] < $arr['min_class_read'] || !is_valid_id($arr['topic_id']) || $CURUSER['class'] < $min_delete_view_class && $status == 'deleted' || $CURUSER['class'] < UC_STAFF && $status == 'recycled') {
    stderr($lang['gl_error'], $lang['gl_bad_id']); //=== why tell them there is a forum here...
    
}
//=== topic status
$status = htmlsafechars($arr['status']);
switch ($status) {
case 'ok':
    $status = '';
    $status_image = '';
    break;

case 'recycled':
    $status = 'recycled';
    $status_image = '<img src="' . $INSTALLER09['pic_base_url'] . 'forums/recycle_bin.gif" alt="'.$lang['fe_recycled'].'" title="'.$lang['fe_this_thread_is_currently'].' '.$lang['fe_in_the_recycle_bin'].'" />';
    break;

case 'deleted':
    $status = 'deleted';
    $status_image = '<img src="' . $INSTALLER09['pic_base_url'] . 'forums/delete_icon.gif" alt="'.$lang['fe_deleted'].'" title="'.$lang['fe_this_thread_is_currently'].' '.$lang['fe_deleted'].'" />';
    break;
}
//=== topics stuff
$forum_id = (int)$arr['forum_id'];
$topic_owner = (int)$arr['user_id'];
$topic_name = htmlsafechars($arr['topic_name'], ENT_QUOTES);
$topic_desc1 = htmlsafechars($arr['topic_desc'], ENT_QUOTES);
//=== poll stuff
$members_votes = array();
if ($arr['poll_id'] > 0) {
    //=== get the poll info
    $res_poll = sql_query('SELECT * FROM forum_poll WHERE id = ' . sqlesc($arr['poll_id']));
    $arr_poll = mysqli_fetch_assoc($res_poll);
    //=== get the stuff for just staff
    if ($CURUSER['class'] >= UC_STAFF) {
        $res_poll_voted = sql_query('SELECT DISTINCT fpv.user_id, fpv.ip, fpv.added, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.offensive_avatar FROM forum_poll_votes AS fpv LEFT JOIN users AS u ON u.id = fpv.user_id WHERE u.id > 0 AND poll_id = ' . sqlesc($arr['poll_id']));
        //=== let's see who's voted will add IP and time later :P
        $who_voted = (mysqli_num_rows($res_poll_voted) > 0 ? '<hr />' : 'no votes yet');
        while ($arr_poll_voted = mysqli_fetch_assoc($res_poll_voted)) {
            $who_voted.= print_user_stuff($arr_poll_voted);
        }
    }
    //=== see if they voted yet
    $res_did_they_vote_yet = sql_query('SELECT `option` FROM `forum_poll_votes` WHERE `poll_id` = ' . sqlesc($arr['poll_id']) . ' AND `user_id` = ' . sqlesc($CURUSER['id']));
    $voted = 0;
    $members_vote = 1000;
    if (mysqli_num_rows($res_did_they_vote_yet) > 0) {
        $voted = 1;
        while ($members_vote = mysqli_fetch_assoc($res_did_they_vote_yet)) {
            $members_votes[] = $members_vote['option'];
        }
    }
    $change_vote = ($arr_poll['change_vote'] === 'no' ? 0 : 1);
    $poll_open = (($arr_poll['poll_closed'] === 'yes' || $arr_poll['poll_starts'] > TIME_NOW || $arr_poll['poll_ends'] < TIME_NOW) ? 0 : 1);
    $poll_options = unserialize($arr_poll['poll_answers']);
    $multi_options = $arr_poll['multi_options'];
    $total_votes_res = sql_query('SELECT COUNT(id) FROM forum_poll_votes WHERE `option` < 21 AND poll_id = ' . sqlesc($arr['poll_id']));
    $total_votes_arr = mysqli_fetch_row($total_votes_res);
    $total_votes = $total_votes_arr[0];
    $res_non_votes = sql_query('SELECT COUNT(id) FROM `forum_poll_votes` WHERE `option` > 20 AND `poll_id` = ' . sqlesc($arr['poll_id']));
    $arr_non_votes = mysqli_fetch_row($res_non_votes);
    $num_non_votes = $arr_non_votes[0];
    $total_non_votes = ($num_non_votes > 0 ? ' [ ' . number_format($num_non_votes) . ' member' . ($num_non_votes == 1 ? '' : 's') . ' just wanted to see the results ]' : '');
    //=== if they voted show them the resaults, if not, let them vote
    $topic_poll.= (($voted === 1 || $poll_open === 0) ? '<br /><br />' : '<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=poll" method="post" name="poll">
	<fieldset class="poll_select">
	<input type="hidden" name="topic_id" value="' . $topic_id . '" />
	<input type="hidden" name="action_2" value="poll_vote" />') . '
	<table border="0" cellspacing="5" cellpadding="5" style="max-width:80%;" align="center">
	<tr>
	<td class="forum_head_dark" colspan="2" align="left"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/poll.gif" alt="" /><span style="font-weight: bold;">Poll
	' . ($arr_poll['poll_closed'] === 'yes' ? 'closed</span>' : ($arr_poll['poll_starts'] > TIME_NOW ? 'starts:</span> ' . get_date($arr_poll['poll_starts'], '') : ($arr_poll['poll_ends'] == 1356048000 ? '</span>' : ($arr_poll['poll_ends'] > TIME_NOW ? ' ends:</span> ' . get_date($arr_poll['poll_ends'], '', 0, 1) : '</span>')))) . '</td>
	<td class="forum_head_dark" colspan="3" align="right">' . ($CURUSER['class'] < UC_STAFF ? '' : '<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_edit&amp;topic_id=' . $topic_id . '" class="altlink"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/modify.gif" alt="" width="20px" /> '.$lang['fe_edit'].'</a>  
	<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_reset&amp;topic_id=' . $topic_id . '" class="altlink"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/stop_watch.png" alt=" " width="20px" /> '.$lang['fe_reset'].'</a> 
	' . (($arr_poll['poll_ends'] > TIME_NOW || $arr_poll['poll_closed'] === 'no') ? '<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_close&amp;topic_id=' . $topic_id . '" class="altlink"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/clock.png" alt="" width="20px" /> close</a>' : '<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_open&amp;topic_id=' . $topic_id . '" class="altlink"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/clock.png" alt="" width="20px" /> '.$lang['fe_start'].'</a>') . '
	<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_delete&amp;topic_id=' . $topic_id . '" class="altlink"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/delete.gif" alt="" width="20px" /> '.$lang['fe_delete'].'</a>') . '</td>
	</tr>
	<tr>
	<td class="three" width="5px" align="center"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/poll_question.png" alt="" width="25px" /></td>
	<td class="three" align="left" valign="top" colspan="4"><br />' . format_comment($arr_poll['question']) . '<br /><br /></td>
	</tr>
	<tr>
	<td class="three" colspan="5" align="center">' . (($voted === 1 || $poll_open === 0) ? '' : '<p>you may select up to <span style="font-weight: bold;">' . $multi_options . ' </span>option' . ($multi_options == 1 ? '' : 's') . '.</p>') . '</td>
	</tr>';
    $number_of_options = (int)$arr_poll['number_of_options'];
    for ($i = 0; $i < $number_of_options; $i++) {
        //=== change colors
        $colour = (++$colour) % 2;
        $class = ($colour == 0 ? 'two' : 'one');
        //=== if they have voted
        if ($voted === 1) {
            //=== do the math for the votes
            $math_res = sql_query('SELECT COUNT(id) FROM `forum_poll_votes` WHERE poll_id = ' . sqlesc($arr['poll_id']) . ' AND `option` = ' . sqlesc($i));
            $math_row = mysqli_fetch_row($math_res);
            $vote_count = $math_row[0];
            $math = $vote_count > 0 ? round(($vote_count / $total_votes) * 100) : 0;
            $math_text = $math . '% with ' . $vote_count . ' vote' . ($vote_count == 1 ? '' : 's');
            $math_image = '<table border="0" width="200px">
		<tr>
		<td style="padding: 0px; background-image: url(' . $INSTALLER09['pic_base_url'] . 'forums/vote_img_bg.gif); background-repeat: repeat-x">
	   <img src="' . $INSTALLER09['pic_base_url'] . 'forums/vote_img.gif" width="' . $math . '%" height="8" alt="' . $math_text . '" title="' . $math_text . '"  /></td>
	   </tr></table>';
        }
        $topic_poll.= '<tr><td class="' . $class . '" width="5px" align="center">' . (($voted === 1 || $poll_open === 0) ? '<span style="font-weight: bold;">' . ($i + 1) . '.</span>' : ($multi_options == 1 ? '<input type="radio" name="vote" value="' . $i . '" />' : '<input type="checkbox" name="vote[]" id="vote[]" value="' . $i . '" />')) . '</td>
		<td class="' . $class . '" align="left" valign="middle">' . format_comment($poll_options[$i]) . '</td>
		<td class="' . $class . '" align="left">' . $math_image . '</td>
		<td class="' . $class . '" align="center"><span style="white-space:nowrap;">' . $math_text . '</span></td>
		<td class="' . $class . '" align="center">' . (in_array($i, $members_votes) ? '<img src="' . $INSTALLER09['pic_base_url'] . 'forums/check.gif" width="20px" alt=" " /> <span style="font-weight: bold;">'.$lang['fe_your_vote'].'!</span>' : '') . '</td></tr>';
    }
    $class = ($class == 'one' ? 'two' : 'one');
    $topic_poll.= (($change_vote === 1 && $voted === 1) ? '<tr><td class="three" colspan="5" align="center">
			<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=poll&amp;action_2=reset_vote&amp;topic_id=' . $topic_id . '" class="altlink"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/stop_watch.png" alt="" width="20px" /> '.$lang['fe_reset_your_vote'].'!</a> 
			</td></tr>' : '') . ($voted === 1 ? '
	     <tr>
			<td class="three" colspan="5" align="center">'.$lang['fe_total_votes'].': ' . number_format($total_votes) . $total_non_votes . ($CURUSER['class'] < UC_STAFF ? '' : '<br />
			<a class="altlink"  title="'.$lang['fe_list_voters'].'" id="toggle_voters" style="font-weight:bold;cursor:pointer;">'.$lang['fe_list_voters'].'</a>
			<div id="voters" style="display:none">' . $who_voted . '</div>') . '</td>
	</tr>
</table><br />' : ($poll_open === 0 ? '' : '<tr>
			<td class="' . $class . '" width="5px" align="center">' . ($multi_options == 1 ? '<input type="radio" name="vote" value="666" />' : '<input type="checkbox" name="vote[]" id="vote[]" value="666" />') . '</td>
			<td class="' . $class . '" align="left" valign="middle" colspan="4"><span style="font-weight: bold;">'.$lang['fe_i_just_want_to_see_the_results'].'!</span></td>
		</tr>') . (($voted === 1 || $poll_open === 0) ? '</table><br />' : '<tr><td class="three" colspan="5" align="center">
			<input type="submit" name="button" class="button" value="'.$lang['fe_vote'].'!" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" /></td>
		</tr></table></fieldset></form>'));
}
if (isset($_GET['search'])) {
    $search = htmlsafechars($_GET['search']);
    $topic_name = highlightWords($topic_name, $search);
}
$forum_desc = ($arr['topic_desc'] !== '' ? '<span style="font-weight: bold;">' . htmlsafechars($arr['topic_desc'], ENT_QUOTES) . '</span><br /><br />' : '');
$locked = ($arr['locked'] === 'yes' ? 'yes' : 'no');
$sticky = ($arr['sticky'] === 'yes' ? 'yes' : 'no');
$views = number_format($arr['views']);
//=== forums stuff
$forum_name = htmlsafechars($arr['forum_name'], ENT_QUOTES);
//=== staff options
if ($CURUSER['class'] >= UC_STAFF) {
    $staff_link = '<a class="altlink"  title="'.$lang['fe_staff_tools'].'" id="tool_open" style="font-weight:bold;cursor:pointer;">'.$lang['fe_staff_tools'].'</a>';
}
//=== rate topic \o/
if ($arr['num_ratings'] != 0) $rating = ROUND($arr['rating_sum'] / $arr['num_ratings'], 1);
//=== see if member is subscribed to topic
$res_subscriptions = sql_query('SELECT id FROM subscriptions WHERE topic_id=' . sqlesc($topic_id) . ' AND user_id=' . sqlesc($CURUSER['id']));
$row_subscriptions = mysqli_fetch_row($res_subscriptions);
$subscriptions = ($row_subscriptions[0] > 0 ? ' <a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=delete_subscription&amp;topic_id=' . $topic_id . '"> 
		<img src="' . $INSTALLER09['pic_base_url'] . 'forums/unsubscribe.gif" alt="+" title="+" width="12" /> '.$lang['fe_unsubscribe_from_this_topic'].'</a>' : '<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=add_subscription&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">
		<img src="' . $INSTALLER09['pic_base_url'] . 'forums/subscribe.gif" alt="+" title="+" width="12" /> '.$lang['fe_subscribe_to_this_topic'].'</a>');
//=== who is here
sql_query('DELETE FROM now_viewing WHERE user_id =' . sqlesc($CURUSER['id']));
sql_query('INSERT INTO now_viewing (user_id, forum_id, topic_id, added) VALUES(' . sqlesc($CURUSER['id']) . ', ' . sqlesc($forum_id) . ', ' . sqlesc($topic_id) . ', ' . TIME_NOW . ')');
//=== now_viewing
$keys['now_viewing'] = 'now_viewing_topic';
if (($topic_users_cache = $mc1->get_value($keys['now_viewing'])) === false) {
    $topicusers = '';
    $topic_users_cache = array();
    $res = sql_query('SELECT n_v.user_id, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.perms FROM now_viewing AS n_v LEFT JOIN users AS u ON n_v.user_id = u.id WHERE topic_id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    $actcount = mysqli_num_rows($res);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($topicusers) $topicusers.= ",\n";
        $topicusers.= ($arr['perms'] & bt_options::PERMS_STEALTH ? '<i>'.$lang['fe_unkn0wn'].'</i>' : format_username($arr));
    }
    $topic_users_cache['topic_users'] = $topicusers;
    $topic_users_cache['actcount'] = $actcount;
    $mc1->cache_value($keys['now_viewing'], $topic_users_cache, $INSTALLER09['expires']['forum_users']);
}
if (!$topic_users_cache['topic_users']) $topic_users_cache['topic_users'] = $lang['fe_there_not_been_active_visit_15'];
//$forum_users = '&nbsp;('.$forum_users_cache['actcount'].')';
$topic_users = $topic_users_cache['topic_users'];
if ($topic_users != '') {
    $topic_users = ''.$lang['fe_currently_viewing_this_topic'].': ' . $topic_users;
}
//=== Update views column
sql_query('UPDATE topics SET views = views + 1 WHERE id=' . sqlesc($topic_id));
//=== must get count for pager... mini query
$res_count = sql_query('SELECT COUNT(id) AS count FROM posts WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'status != \'deleted\' AND' : '')) . ' topic_id=' . sqlesc($topic_id));
$arr_count = mysqli_fetch_row($res_count);
$count = $arr_count[0];
//=== get stuff for the pager
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 15;
$subscription_on_off = (isset($_GET['s']) ? ($_GET['s'] == 1 ? '<br /><div style="font-weight: bold;">'.$lang['fe_sub_to_topic'].' <img src="' . $INSTALLER09['pic_base_url'] . 'forums/subscribe.gif" alt="'.$lang['fe_subscribed'].'" title="'.$lang['fe_subscribed'].'"  width="25" /></div>' : '<br /><div style="font-weight: bold;">'.$lang['fe_unsub_to_topic'].' <img src="' . $INSTALLER09['pic_base_url'] . 'forums/unsubscribe.gif" alt="'.$lang['fe_unsubscribe'].'" title="'.$lang['fe_unsubscribe'].'" width="25" /></div>') : '');
list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'forums.php?action=view_topic&amp;topic_id=' . $topic_id . (isset($_GET['perpage']) ? '&amp;perpage=' . $perpage : ''));
$res = sql_query('SELECT p.id AS post_id, p.topic_id, p.user_id, p.staff_lock, p.added, p.body, p.edited_by, p.edit_date, p.icon, p.post_title, p.bbcode, p.post_history, p.edit_reason, p.ip, p.status AS post_status, p.anonymous, u.seedbonus, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.chatpost, u.leechwarn, u.pirate, u.king, u.enabled, u.email, u.website, u.icq, u.msn, u.aim, u.yahoo, u.last_access, u.show_email, u.paranoia, u.hit_and_run_total, u.avatar, u.title, u.uploaded, u.downloaded, u.signature, u.google_talk, u.icq, u.msn, u.aim, u.yahoo, u.website, u.mood, u.perms, u.reputation, u.offensive_avatar FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND' : '')) . ' topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id '.$_forum_sort.' ' . $LIMIT) or sqlerr(__FILE__, __LINE__);
//=== make sure they can reply here
$may_post = ($CURUSER['class'] >= $arr['min_class_write'] && $CURUSER['forum_post'] == 'yes' && $CURUSER['suspended'] == 'no');
//=== reply button
$locked_or_reply_button = ($locked === 'yes' ? '<span style="font-weight: bold; font-size: x-small;"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/thread_locked.gif" alt="'.$lang['fe_thread_locked'].'" title="'.$lang['fe_thread_locked'].'" width="22" />'.$lang['fe_this_topic_is_locked'].', you may not post in this thread.</span>' : ($CURUSER['forum_post'] == 'no' ? '<span style="font-weight: bold; font-size: x-small;">Your posting rights have been removed. You may not post.</span>' : '<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=post_reply&amp;topic_id=' . $topic_id . '" class="btn">Add Reply</a>'));
/*
    $quick_reply ="<table style='border:1px solid #000000;' align='center'>
      <tr>
		<td style='padding:10px;text-align:center;'>
		<b>Quick Reply</b>
		<form name='compose' method='post' action='forums.php?action=post_reply'>
		<input type='hidden' name='topic_id' value='".$topic_id."' />
		<textarea name='body' rows='4' cols='70'></textarea><br />
		<input type='submit' class='btn' value='Submit' /><br />
		<!--'.$lang['fe_anonymous'].'<input type='checkbox' name='anonymous' value='yes' ".($CURUSER['anonymous'] == 'yes' ? "checked='checked'":'')." />-->
		</form></td></tr></table>";
*/
if ($arr['parent_forum'] > 0) {
    //=== now we need the parent forums stuff
    $parent_forum_res = sql_query('SELECT name AS parent_forum_name FROM forums WHERE id=' . sqlesc($arr['parent_forum']));
    $parent_forum_arr = mysqli_fetch_row($parent_forum_res);
    $child = ($arr['parent_forum'] > 0 ? '<span style="font-size: x-small;"> [ '.$lang['fe_child_board'].' ]</span>' : '');
    $parent_forum_name = '<img src="' . $INSTALLER09['pic_base_url'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" /> 
		<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $forum_id . '">' . htmlsafechars($parent_forum_arr[0], ENT_QUOTES) . '</a>';
}
//=== top and bottom stuff
$the_top_and_bottom = '<tr><td class="three" width="33%" align="left" valign="middle">&nbsp;&nbsp;' . $subscriptions . '</td>
		<td class="three" width="33%" align="center">' . (($count > $perpage) ? $menu : '') . '</td>
		<td class="three" align="right">' . ($may_post ? $locked_or_reply_button : '<span style="font-weight: bold; font-size: x-small;">
		You are not permitted to post in this thread.</span>') . '</td></tr>';
$location_bar = '<a name="top"></a>' . $status_image . ' <a class="altlink" href="index.php">' . $INSTALLER09['site_name'] . '</a>  <img src="' . $INSTALLER09['pic_base_url'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" /> 
			<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php">'.$lang['fe_forums'].'</a> ' . $parent_forum_name . ' 
			<img src="' . $INSTALLER09['pic_base_url'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" /> 
			<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $forum_id . '">' . $forum_name . $child . '</a>
			<img src="' . $INSTALLER09['pic_base_url'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" /> 
			<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . $topic_name . '</a> ' . $status_image . '<br />' . $forum_desc . '
			<span style="text-align: center;">' . $mini_menu . (($topic_owner == $CURUSER['id'] && $arr['poll_id'] == 0 || $CURUSER['class'] >= UC_STAFF && $arr['poll_id'] == 0) ? '  |<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_add&amp;topic_id=' . $topic_id . '" class="altlink">&nbsp;'.$lang['fe_add_poll'].'</a>' : '') . '</span><br /><br />';
$HTMLOUT.= ($upload_errors_size > 0 ? ($upload_errors_size === 1 ? '<div style="text-align: center;">One file was not uploaded. The maximum file size allowed is. ' . mksize($max_file_size) . '.</div>' : '<div style="text-align: center;">' . $upload_errors_size . ' file were not uploaded. The maximum file size allowed is. ' . mksize($max_file_size) . '.</div>') : '') . ($upload_errors_type > 0 ? ($upload_errors_type === 1 ? '<div style="text-align: center;">One file was not uploaded. The accepted formats are zip and rar.</div>' : '<div style="text-align: center;">' . $upload_errors_type . ' files were not uploaded. The accepted formats are zip and rar.</div>') : '') . $location_bar . $topic_poll . '<br />' . $subscription_on_off . '<br />
		' . ($CURUSER['class'] < UC_STAFF ? '' : '

      <form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post" name="checkme" onsubmit="return SetChecked(this,\'post_to_mess_with\')" enctype="multipart/form-data">') . (isset($_GET['count']) ? '<div style="text-align: center;">' . intval($_GET['count']) . ' PMs Sent</div>' : '') . '


		<!--<table border="0" cellspacing="5" cellpadding="10" width="100%">-->
		<table border="0" cellspacing="0" cellpadding="4" width="100%">
		' . $the_top_and_bottom . '
		<tr><td class="forum_head_dark" align="left" width="100"> <img src="' . $INSTALLER09['pic_base_url'] . 'forums/topic_normal.gif" alt="'.$lang['fe_topic'].'" title="'.$lang['fe_topic'].'" />&nbsp;&nbsp;'.$lang['fe_author'].'</td>
		<td class="forum_head_dark" align="left" colspan="2">&nbsp;&nbsp;'.$lang['fe_topic'].': ' . $topic_name . '  [ '.$lang['fe_read'].' ' . $views . ' '.$lang['fe_times'].' ] </td></tr>
		<tr><td class="three" align="left" colspan="3">'.$lang['fe_topic_rating'].': ' . (getRate($topic_id, "topic")) . '</td></tr>
      <tr><td class="three" align="left" colspan="3">' . $topic_users . '</td></tr>';
//=== lets start the loop \o/
while ($arr = mysqli_fetch_assoc($res)) {
    //=== change colors
    $colour = (++$colour) % 2;
    $class = ($colour == 0 ? 'one' : 'two');
    $class_alt = ($colour == 0 ? 'two' : 'one');
    $moodname = (isset($mood['name'][$arr['mood']]) ? htmlsafechars($mood['name'][$arr['mood']]) : 'is feeling neutral');
    $moodpic = (isset($mood['image'][$arr['mood']]) ? htmlsafechars($mood['image'][$arr['mood']]) : 'noexpression.gif');
    $post_icon = ($arr['icon'] !== '' ? '<img src="' . $INSTALLER09['pic_base_url'] . 'smilies/' . htmlsafechars($arr['icon']) . '.gif" alt="icon" title="icon" /> ' : '<img src="' . $INSTALLER09['pic_base_url'] . 'forums/topic_normal.gif" alt="icon" title="icon" /> ');
    $post_title = ($arr['post_title'] !== '' ? ' <span style="font-weight: bold; font-size: x-small;">' . htmlsafechars($arr['post_title'], ENT_QUOTES) . '</span>' : '');
    $stafflocked = ( /*$CURUSER['class'] == UC_SYSOP && */
    $arr["staff_lock"] == 1 ? "<img src='{$INSTALLER09['pic_base_url']}locked.gif' border='0' alt='".$lang['fe_post_locked']."' title='".$lang['fe_post_locked']."' />" : "");
    $member_reputation = $arr['username'] != '' ? get_reputation($arr, 'posts', TRUE, (int)$arr['post_id']) : '';
    $edited_by = '';
    if ($arr['edit_date'] > 0) {
        $res_edited = sql_query('SELECT username FROM users WHERE id=' . sqlesc($arr['edited_by']));
        $arr_edited = mysqli_fetch_assoc($res_edited);
        //== Anonymous
        if ($arr['anonymous'] == 'yes') {
            if ($CURUSER['class'] < UC_STAFF && $arr['user_id'] != $CURUSER['id']) $edited_by = '<br /><br /><br /><span style="font-weight: bold; font-size: x-small;">'.$lang['vmp_last_edit_by_anony'].'
				 at ' . get_date($arr['edit_date'], '') . ' GMT ' . ($arr['edit_reason'] !== '' ? ' </span>[ '.$lang['fe_reason'].': ' . htmlsafechars($arr['edit_reason']) . ' ] <span style="font-weight: bold; font-size: x-small;">' : '') . '
				 ' . (($CURUSER['class'] >= UC_STAFF && $arr['post_history'] !== '') ? ' <a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int)$arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">'.$lang['fe_read_post_history'].'</a></span><br />' : '</span>');
            else $edited_by = '<br /><br /><br /><span style="font-weight: bold; font-size: x-small;">'.$lang['vmp_last_edit_by_anony'].' [<a class="altlink" href="userdetails.php?id=' . (int)$arr['edited_by'] . '">' . htmlsafechars($arr_edited['username']) . '</a>]
				 at ' . get_date($arr['edit_date'], '') . ' GMT ' . ($arr['edit_reason'] !== '' ? ' </span>[ '.$lang['fe_reason'].': ' . htmlsafechars($arr['edit_reason']) . ' ] <span style="font-weight: bold; font-size: x-small;">' : '') . '
				 ' . (($CURUSER['class'] >= UC_STAFF && $arr['post_history'] !== '') ? ' <a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int)$arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">'.$lang['fe_read_post_history'].'</a></span><br />' : '</span>');
        } else {
            $edited_by = '<br /><br /><br /><span style="font-weight: bold; font-size: x-small;">'.$lang['fe_last_edited_by'].' <a class="altlink" href="userdetails.php?id=' . (int)$arr['edited_by'] . '">' . htmlsafechars($arr_edited['username']) . '</a>
				 at ' . get_date($arr['edit_date'], '') . ' GMT ' . ($arr['edit_reason'] !== '' ? ' </span>[ '.$lang['fe_reason'].': ' . htmlsafechars($arr['edit_reason']) . ' ] <span style="font-weight: bold; font-size: x-small;">' : '') . '
				 ' . (($CURUSER['class'] >= UC_STAFF && $arr['post_history'] !== '') ? ' <a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int)$arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">'.$lang['fe_read_post_history'].'</a></span><br />' : '</span>');
        }
        //==
        
    }
    //==== highlight for search
    $body = ($arr['bbcode'] == 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body']));
    if (isset($_GET['search'])) {
        $body = highlightWords($body, $search);
        $post_title = highlightWords($post_title, $search);
    }
    $post_id = (int)$arr['post_id'];
    //=== if there are attachments, let's get them!
    $attachments_res = sql_query('SELECT id, file_name, extension, size FROM attachments WHERE post_id =' . sqlesc($post_id) . ' AND user_id = ' . sqlesc($arr['id']));
    if (mysqli_num_rows($attachments_res) > 0) {
        $attachments = '<table align="center" width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td class="' . $class . '" align="left"><span style="font-weight: bold;">'.$lang['fe_attachments'].':</span><hr />';
        while ($attachments_arr = mysqli_fetch_assoc($attachments_res)) {
            $attachments.= '<span style="white-space:nowrap;">' . ($attachments_arr['extension'] === 'zip' ? ' <img src="' . $INSTALLER09['pic_base_url'] . 'forums/zip.gif" alt="'.$lang['fe_zip'].'" title="'.$lang['fe_zip'].'" width="18" style="vertical-align: middle;" /> ' : ' <img src="' . $INSTALLER09['pic_base_url'] . 'forums/rar.gif" alt="'.$lang['fe_rar'].'" title="'.$lang['fe_rar'].'" width="18" /> ') . ' 
					<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=download_attachment&amp;id=' . (int)$attachments_arr['id'] . '" title="'.$lang['fe_download_attachment'].'" target="_blank">
					' . htmlsafechars($attachments_arr['file_name']) . '</a> <span style="font-weight: bold; font-size: xx-small;">[' . mksize($attachments_arr['size']) . ']</span>&nbsp;&nbsp;</span>';
        }
        $attachments.= '</td></tr></table>';
    }
    $width = 300;
    $height = 100;
    //=== signature stuff
    $signature = (($CURUSER['opt1'] & user_options::SIGNATURES) ? '' : ($arr['signature'] == '' ? '' : ($arr['anonymous'] == 'yes' || $arr['perms'] & bt_options::PERMS_STEALTH ? '<table align="center" width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td class="' . $class . '" align="left"><hr /><img style="max-width:' . $width . 'px;max-height:' . $height . 'px;" src="' . $INSTALLER09['pic_base_url'] . 'anonymous_2.jpg" alt="Signature" /></td></tr></table>' : '<table align="center" width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td class="' . $class . '" align="left"><hr />' . format_comment($arr['signature']) . '</td></tr></table>')));
    //=== post status
    $post_status = htmlsafechars($arr['post_status']);
    switch ($post_status) {
    case 'ok':
        $post_status = $class;
        break;

    case 'recycled':
        $post_status = 'recycled';
        break;

    case 'deleted':
        $post_status = 'deleted';
        break;

    case 'postlocked':
        $post_status = 'postlocked';
        break;
    }
    $width = 100;
    $HTMLOUT.= '<tr><td class="' . $class . '" align="left" valign="top" colspan="3"><table border="0" cellspacing="5" cellpadding="10" width="100%"><tr><td class="forum_head" align="left" width="100" valign="middle">
			<span style="white-space:nowrap;"><a name="' . $post_id . '"></a>
			' . ($CURUSER['class'] >= UC_STAFF ? '<input type="checkbox" name="post_to_mess_with[]" value="' . $post_id . '" />' : '') . '
			<a href="javascript:window.alert(\''.$lang['fe_direct_link_to_this_post'].':\n ' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#' . $post_id . '\');">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/link.gif" alt="'.$lang['fe_direct_link_to_this_post'].'" title="'.$lang['fe_direct_link_to_this_post'].'" width="12px" /></a>
			<span style="font-weight: bold;">' . ($arr['anonymous'] == 'yes' ? '<i>'.$lang['fe_anonymous'].'</i>' : '' . htmlsafechars($arr['username']) . '') . '&nbsp;</span>
			<!-- Mood -->
         <span class="tool"><a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);"><img src="' . $INSTALLER09['pic_base_url'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" border="0" />
      <span class="tip">' . ($arr['anonymous'] == 'yes' ? '<i>'.$lang['fe_anonymous'].'</i>' : htmlsafechars($arr['username'])) . ' ' . $moodname . ' !</span></a>&nbsp;</span>
			' . (($arr['paranoia'] >= 2 && $CURUSER['class'] < UC_STAFF) ? '<img src="' . $INSTALLER09['pic_base_url'] . 'smilies/tinfoilhat.gif" alt="'.$lang['fe_i_wear_a_tinfoil_hat'].'!" title="'.$lang['fe_i_wear_a_tinfoil_hat'].'!" />' : get_user_ratio_image($arr['uploaded'], ($INSTALLER09['ratio_free'] ? "0" : $arr['downloaded']))) . '</span>
			</td>
			<td class="forum_head" align="left" valign="middle"><span style="white-space:nowrap;">' . $post_icon . $post_title . '&nbsp;&nbsp;&nbsp;&nbsp; '.$lang['fe_posted_on'].': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span></td>
			<td class="forum_head" align="right" valign="middle"><span style="white-space:nowrap;"> 
			<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=post_reply&amp;topic_id=' . $topic_id . '&amp;quote_post=' . $post_id . '&amp;key=' . $arr['added'] . '"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/quote.gif" alt="'.$lang['fe_quote'].'" title="'.$lang['fe_quote'].'" /> '.$lang['fe_quote'].'</a>
			' . (($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $arr['id']) ? ' <a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=edit_post&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/modify.gif" alt="'.$lang['fe_modify'].'" title="'.$lang['fe_modify'].'" /> '.$lang['fe_modify'].'</a> 
			 <a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=delete_post&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/delete.gif" alt="'.$lang['fe_delete'].'" title="'.$lang['fe_delete'].'" /> '.$lang['fe_remove'].'</a>' : '') . '
			 <!--<a class="altlink" href="' . $INSTALLER09['baseurl'] . '/forums.php?action=report_post&amp;topic_id=' . $topic_id . '&amp;post_id=' . $post_id . '"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/report.gif" alt="'.$lang['fe_report'].'" title="'.$lang['fe_report'].'" width="22" /> '.$lang['fe_report'].'</a>-->
			 <a href="' . $INSTALLER09['baseurl'] . '/report.php?type=Post&amp;id=' . $post_id . '&amp;id_2=' . $topic_id . '"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/report.gif" alt="'.$lang['fe_report'].'" title="'.$lang['fe_report'].'" width="22" /> '.$lang['fe_report'].'</a>
	     ' . ($CURUSER['class'] == UC_MAX && $arr['staff_lock'] == 1 ? '<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_lock&amp;mode=unlock&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $INSTALLER09['pic_base_url'] . 'key.gif" alt="'.$lang['fe_un_lock'].'" title="'.$lang['fe_un_lock'].'" /> '.$lang['fe_unlock_post'].'</a>&nbsp;' : '') . '
			 ' . ($CURUSER['class'] == UC_MAX && $arr['staff_lock'] == 0 ? '<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_lock&amp;mode=lock&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $INSTALLER09['pic_base_url'] . 'key.gif" alt="'.$lang['fe_lock'].'" title="'.$lang['fe_lock'].'" /> '.$lang['fe_lock_post'].'</a>' : '') . $stafflocked . '
			<a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#top"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/up.gif" alt="'.$lang['fe_top'].'" title="'.$lang['fe_top'].'" /></a> 
		  <a href="' . $INSTALLER09['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#bottom"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/down.gif" alt="'.$lang['fe_bottom'].'" title="'.$lang['fe_bottom'].'" /></a> 
			</span></td>
			</tr>	
			<tr>
         <td class="' . $class_alt . '" align="center" valign="top">' . ($arr['anonymous'] == 'yes' ? '<img style="max-width:' . $width . 'px;" src="' . $INSTALLER09['pic_base_url'] . 'anonymous_1.jpg" alt="avatar" />' : avatar_stuff($arr)) . '<br />
			' . ($arr['anonymous'] == 'yes' ? '<i>'.$lang['fe_anonymous'].'</i>' : print_user_stuff($arr)) . ($arr['anonymous'] == 'yes' || $arr['title'] == '' ? '' : '<br /><span style=" font-size: xx-small;">[' . htmlsafechars($arr['title']) . ']</span>') . '<br />
			<span style="font-weight: bold;">' . ($arr['anonymous'] == 'yes' ? '' : get_user_class_name($arr['class'])) . '</span><br />
			' . ($arr['last_access'] > (TIME_NOW - 300) && $arr['perms'] < bt_options::PERMS_STEALTH ? ' <img src="' . $INSTALLER09['pic_base_url'] . 'online.gif" alt="Online" title="Online" border="0" /> Online' : ' <img src="' . $INSTALLER09['pic_base_url'] . 'offline.gif" border="0" alt="'.$lang['fe_offline'].'" title="'.$lang['fe_offline'].'" /> '.$lang['fe_offline'].'') . '<br />
			'.$lang['fe_karma'].': ' . number_format($arr['seedbonus']) . '<br /><br />' . $member_reputation . '<br />' . ($arr['google_talk'] !== '' ? ' <a href="http://talkgadget.google.com/talkgadget/popout?member=' . htmlsafechars($arr['google_talk']) . '" title="'.$lang['fe_click_for_google_talk_gadget'].'"  target="_blank"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/google_talk.gif" alt="'.$lang['fe_google_talk'].'" /></a> ' : '') . ($arr['icq'] !== '' ? ' <a href="http://people.icq.com/people/&amp;uin=' . htmlsafechars($arr['icq']) . '" title="'.$lang['fe_click_to_open_icq_page'].'" target="_blank"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/icq.gif" alt="icq" /></a> ' : '') . ($arr['msn'] !== '' ? ' <a href="http://members.msn.com/' . htmlsafechars($arr['msn']) . '" target="_blank" title="'.$lang['fe_click_to_see_msn_details'].'"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/msn.gif" alt="msn" /></a> ' : '') . ($arr['aim'] !== '' ? ' <a href="http://aim.search.aol.com/aol/search?s_it=searchbox.webhome&amp;q=' . htmlsafechars($arr['aim']) . '" target="_blank" title="'.$lang['fe_click_to_search_on_aim'].'"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/aim.gif" alt="AIM" /></a> ' : '') . ($arr['yahoo'] !== '' ? ' <a href="http://webmessenger.yahoo.com/?im=' . htmlsafechars($arr['yahoo']) . '" target="_blank" title="'.$lang['fe_click_to_open_yahoo'].'"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/yahoo.gif" alt="yahoo" /></a> ' : '') . '<br /><br />' . ($arr['website'] !== '' ? ' <a href="' . htmlsafechars($arr['website']) . '" target="_blank" title="'.$lang['fe_click_to_go_to_website'].'"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/website.gif" alt="website" /></a> ' : '') . ($arr['show_email'] == 'yes' ? ' <a href="mailto:' . htmlsafechars($arr['email']) . '"  title="'.$lang['fe_click_to_email'].'" target="_blank"><img src="' . $INSTALLER09['pic_base_url'] . 'email.gif" alt="email" width="25" /> </a>' : '') . '<br /><br />
			' . ($CURUSER['class'] >= UC_STAFF ? '   
			<ul class="makeMenu">
				<li>' . htmlsafechars($arr['ip']) . '
					<ul>
					<li><a href="https://ws.arin.net/?queryinput=' . htmlsafechars($arr['ip']) . '" title="'.$lang['vt_whois_to_find_isp_info'].'" target="_blank">'.$lang['vt_ip_whois'].'</a></li>
					<li><a href="http://www.infosniper.net/index.php?ip_address=' . htmlsafechars($arr['ip']) . '" title="'.$lang['vt_ip_to_map_using_infosniper'].'!" target="_blank">'.$lang['vt_ip_to_map'].'</a></li>
				</ul>
				</li>
			</ul>' : '') . '
			</td>
			<td class="' . $post_status . '" align="left" valign="top" colspan="2">' . $body . $edited_by . '</td></tr>
			<tr><td class="' . $class_alt . '" width="100"></td><td class="' . $class . '" align="left" valign="top" colspan="2">' . $signature . '</td></tr>
			<tr><td class="' . $class_alt . '" width="100"></td><td class="' . $class . '" align="left" valign="top" colspan="2">' . $attachments . '</td></tr>
			<tr><td class="' . $class_alt . '" align="right" valign="middle" colspan="3">' . (($arr['paranoia'] >= 1 && $CURUSER['class'] < UC_STAFF) ? '' : '
			<span style="color: green;"><img src="' . $INSTALLER09['pic_base_url'] . 'up.png" alt="'.$lang['vt_uploaded'].'" title="'.$lang['vt_uploaded'].'" /> ' . mksize($arr['uploaded']) . '</span>&nbsp;&nbsp;  
			' . ($INSTALLER09['ratio_free'] ? '' : '<span style="color: red;"><img src="' . $INSTALLER09['pic_base_url'] . 'dl.png" alt="'.$lang['vt_downloaded'].'" title="'.$lang['vt_downloaded'].'" /> ' . mksize($arr['downloaded']) . '</span>') . '&nbsp;&nbsp;') . (($arr['paranoia'] >= 2 && $CURUSER['class'] < UC_STAFF) ? '' : ''.$lang['vt_ratio'].': ' . member_ratio($arr['uploaded'], $INSTALLER09['ratio_free'] ? '0' : $arr['downloaded']) . '&nbsp;&nbsp;
			' . ($arr['hit_and_run_total'] == 0 ? '<img src="' . $INSTALLER09['pic_base_url'] . 'no_hit_and_runs2.gif" width="22" alt="' . ($arr['anonymous'] == 'yes' ? ''.$lang['fe_anonymous'].'' : htmlsafechars($arr['username'])) . ' '.$lang['vt_has_never_hit'].' &amp; ran!" title="' . ($arr['anonymous'] == 'yes' ? ''.$lang['fe_anonymous'].'' : htmlsafechars($arr['username'])) . ' '.$lang['vt_has_never_hit'].' &amp; ran!" />' : '') . '
			&nbsp;&nbsp;&nbsp;&nbsp;') . '
			<a class="altlink" href="pm_system.php?action=send_message&amp;receiver=' . $arr['id'] . '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) . '"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/send_pm.png" alt="'.$lang['vt_send_pm'].'" title="'.$lang['vt_send_pm'].'" width="18" /> '.$lang['vt_send_message'].'</a></td></tr></table></td></tr>';
    $attachments = '';
} //=== end while loop
//=== update the last post read by CURUSER
sql_query('DELETE FROM `read_posts` WHERE user_id =' . sqlesc($CURUSER['id']) . ' AND `topic_id` = ' . sqlesc($topic_id));
sql_query('INSERT INTO `read_posts` (`user_id` ,`topic_id` ,`last_post_read`) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($topic_id) . ', ' . sqlesc($post_id) . ')');
$mc1->delete_value('last_read_post_' . $topic_id . '_' . $CURUSER['id']);
$mc1->delete_value('sv_last_read_post_' . $topic_id . '_' . $CURUSER['id']);
//=== set up jquery show hide here
//$HTMLOUT .= $the_top_and_bottom.'</table>'.$quick_reply.'
$HTMLOUT.= $the_top_and_bottom . '</table>
    <span style="text-align: center;">' . $location_bar . '</span><a name="bottom"></a>
    <br />' . ($CURUSER['class'] >= UC_STAFF ? '<img src="' . $INSTALLER09['pic_base_url'] . 'forums/tools.png" alt="'.$lang['vt_tools'].'" title="'.$lang['vt_tools'].'" width="22" /> ' . $staff_link . ' <img src="' . $INSTALLER09['pic_base_url'] . 'forums/tools.png" alt="'.$lang['vt_tools'].'" title="'.$lang['vt_tools'].'" width="22" /><br /><br />
	 <div id="tools" style="display:none">
    <br />
    <table border="0" cellspacing="5" cellpadding="5" width="800" align="center">
	 <tr>
    <td class="forum_head_dark" colspan="4" align="center">'.$lang['fe_staff_tools'].'</td>
    </tr>
		  <tr>
			<td class="two" align="left" colspan="3">
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="hidden" name="forum_id" value="' . $forum_id . '" />
      <table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">
		  <tr>
			<td class="two" align="center" valign="middle" width="18">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/recycle_bin.gif" alt="'.$lang['vt_recycle'].'" title="'.$lang['vt_recycle'].'" width="22" /></td>
			<td class="two" align="left" valign="middle">
			<input type="radio" name="action_2" value="send_to_recycle_bin" />'.$lang['vt_send_to_recycle_bin'].'  <br />
			<input type="radio" name="action_2" value="remove_from_recycle_bin" />'.$lang['fe_remove'].' '.$lang['vt_from_recycle_bin'].' 
			</td>
			<td class="two" align="center" valign="middle" width="18"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/delete.gif" alt="'.$lang['fe_delete'].'" title="'.$lang['fe_delete'].'" /></td>
			<td class="two" align="left" valign="middle">
			<input type="radio" name="action_2" value="delete_posts" />'.$lang['fe_delete'].'
			' . ($CURUSER['class'] < $min_delete_view_class ? '' : '<br />
			<input type="radio" name="action_2" value="un_delete_posts" /><span style="font-weight:bold;color:red;">*</span>Un-'.$lang['fe_delete'].'') . '
			</td>
			<td class="two" align="center" valign="middle" width="18">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/merge.gif" alt="'.$lang['vt_merge'].'" title="'.$lang['vt_merge'].'" /></td>
			<td class="two" align="left" valign="middle">
			<input type="radio" name="action_2" value="merge_posts" />'.$lang['vt_merge_with'].'<br />
			<input type="radio" name="action_2" value="append_posts" />'.$lang['vt_append_to'].'
			</td>
			<td class="two" align="left" valign="middle">
			'.$lang['fe_topic'].':<input type="text" size="2" name="new_topic" value="' . $topic_id . '" />
		  </td>
		  </tr>
	    </table>
      <table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">
		  <tr>
			<td class="two" align="center" valign="middle" width="18">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/split.gif" alt="'.$lang['vt_split'].'" title="'.$lang['vt_split'].'" width="18" /></td>
			<td class="two" align="left" valign="middle">
			<input type="radio" name="action_2" value="split_topic" />'.$lang['vt_split_topic'].'
			</td>
			<td class="two" align="left" valign="middle">
			'.$lang['fe_new_topic_name'].':<input type="text" size="20" maxlength="120" name="new_topic_name" value="' . ($topic_name !== '' ? $topic_name : '') . '" /> [required]<br />
			'.$lang['fe_new_topic_desc'].':<input type="text" size="20" maxlength="120" name="new_topic_desc" value="" />
			</td>
			<td class="two" align="center" valign="middle" width="18"><img src="' . $INSTALLER09['pic_base_url'] . 'forums/send_pm.png" alt="'.$lang['vt_send_pm'].'" title="'.$lang['vt_send_pm'].'" width="18" /></td>
			<td class="two" align="center" valign="middle">
			<a class="altlink"  title="'.$lang['vt_send_pm_select_mem'].' - click" id="pm_open" style="font-weight:bold;cursor:pointer;">'.$lang['vt_send_pm'].' </a><br />[click]
			</td>
		  </tr>
	    </table>
      <div id="pm" style="display:none"><br />
      <table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">
		  <tr>
			<td class="forum_head_dark" align="left" colspan="2">'.$lang['vt_send_pm_select_mem'].'</td>
		  </tr>
		  <tr>
			<td class="three" align="right" valign="top">
		  <span style="font-weight: bold;">'.$lang['vt_subject'].':</span>
			</td>
			<td class="three" align="left" valign="top">
			<input type="text" size="20" maxlength="120" class="text_default" name="subject" value="" />
			<input type="radio" name="action_2" value="send_pm" />
			<span style="font-weight: bold;">'.$lang['vt_select_to_send'].'.</span> 
			</td>
		  </tr>
		  <tr>
			<td class="three" align="right" valign="top">
			<span style="font-weight: bold;">'.$lang['vt_message'].':</span>
			</td>
			<td class="three" align="left" valign="top">
			<textarea cols="30" rows="4" name="message" class="text_area_small"></textarea>
			</td>
		  </tr>
		  <tr>
			<td class="three" align="right" valign="top">
			<span style="font-weight: bold;">'.$lang['vt_from'].':</span>
			</td>
			<td class="three" align="left" valign="top">
			<input type="radio" name="pm_from" value="0" checked="checked" /> '.$lang['vt_system'].'  
			<input type="radio" name="pm_from" value="1" /> ' . print_user_stuff($CURUSER) . '
			</td>
      </tr>
      </table>
      </div>
      <hr /></td>
			<td class="two" align="center">
			<a class="altlink" href="javascript:SetChecked(1,\'post_to_mess_with[]\')" title="'.$lang['vt_select_all_posts_and_use_the_following_options'].'"> '.$lang['vt_select_all'].'</a> <br />
			<a class="altlink" href="javascript:SetChecked(0,\'post_to_mess_with[]\')" title="'.$lang['vt_unselect_all_posts'].'">'.$lang['vt_un_select_all'].'</a><br />
			<input type="submit" name="button" class="button" value="'.$lang['vt_with_selected'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
		</td>
      </tr>
      <tr>
			<td class="two" align="center" width="28" valign="top">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/pinned.gif" alt="'.$lang['fe_pinned'].'" title="'.$lang['fe_pinned'].'" /></td>
			<td class="two" align="right" valign="top">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['vt_pin'].' '.$lang['fe_topic'].':</span></td>
			<td class="two" align="left" valign="top">
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="set_pinned" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="radio" name="pinned" value="yes" ' . ($sticky === 'yes' ? 'checked="checked"' : '') . ' /> Yes  
			<input type="radio" name="pinned" value="no" ' . ($sticky === 'no' ? 'checked="checked"' : '') . ' /> No</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Set '.$lang['fe_pinned'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form></td>
      </tr>
      <tr>
			<td class="two" align="center" width="28" valign="top">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/thread_locked.gif" alt="'.$lang['fe_locked'].'" title="'.$lang['fe_locked'].'" width="22" /></td>
			<td class="two" align="right" valign="top">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['fe_lock'].' '.$lang['fe_topic'].':</span></td>
			<td class="two" align="left" valign="top">
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="set_locked" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="radio" name="locked" value="yes" ' . ($locked === 'yes' ? 'checked="checked"' : '') . ' /> Yes  
			<input type="radio" name="locked" value="no" ' . ($locked === 'no' ? 'checked="checked"' : '') . ' /> No</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="'.$lang['vt_lock_topic'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form></td>
      </tr>
       <tr>
			<td class="two" align="center" width="28" valign="top">
	<!-- needed to add later RS.	 -->				
         <img src="' . $INSTALLER09['pic_base_url'] . 'forums/move.gif" alt="'.$lang['vt_move'].'" title="'.$lang['vt_move'].'" width="22" /></td>
			<td class="two" align="right" valign="top">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['vt_move'].' '.$lang['fe_topic'].':</span></td>
			<td class="two" align="left" valign="top">
<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="move_topic" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<select name="forum_id">
			' . insert_quick_jump_menu($forum_id, $staff = true) . '</select></td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="'.$lang['vt_move_topic'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form> <!--//-->
			</td>
      </tr>	
      <tr>
			<td class="two" align="center" width="28" valign="top">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/modify.gif" alt="'.$lang['fe_modify'].'" title="'.$lang['fe_modify'].'" /></td>
			<td class="two" align="right" valign="top">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['vt_rename'].' '.$lang['fe_topic'].':</span></td>
			<td class="two" align="left" valign="top">
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="rename_topic" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="text" size="40" maxlength="120" name="new_topic_name" value="' . ($topic_name !== '' ? $topic_name : '') . '" /></td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="'.$lang['vt_rename_topic'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
      </tr>	
      <tr>
			<td class="two" align="center" width="28" valign="top">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/modify.gif" alt="'.$lang['fe_modify'].'" title="'.$lang['fe_modify'].'" /></td>
			<td class="two" align="right" valign="top">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['vt_change_topic_desc'].':</span></td>
			<td class="two" align="left" valign="top">
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="change_topic_desc" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="text" size="40" maxlength="120" name="new_topic_desc" value="' . ($topic_desc1 !== '' ? $topic_desc1 : '') . '" /></td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="'.$lang['vt_change_desc'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
      </tr>	
      <tr>
			<td class="two" align="center" width="28" valign="top">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/merge.gif" alt="'.$lang['vt_merge'].'" title="'.$lang['vt_merge'].'" /></td>
			<td class="two" align="right" valign="top">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['vt_merge'].' '.$lang['fe_topic'].':</span></td>
			<td class="two" align="left" valign="top">'.$lang['vt_with_topic_num'].' 
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="merge_topic" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="text" size="4" name="topic_to_merge_with" value="' . $topic_id . '" /><br />
			'.$lang['vt_enter_the_destination_topic_id_to_merge_into'].'<br />
			'.$lang['vt_topic_id_can_be_found_in_the_address_bar_above'].' ' . $topic_id . '<br />
			['.$lang['vt_this_option_will_mix_the_two_topics_together'].']</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="'.$lang['vt_merge_topic'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
      </tr>	
      <tr>
			<td class="two" align="center" width="28" valign="top">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/merge.gif" alt="'.$lang['vt_merge'].'" title="'.$lang['vt_merge'].'" /></td>
			<td class="two" align="right" valign="top">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['vt_append'].' '.$lang['fe_topic'].':</span></td>
			<td class="two" align="left" valign="top">'.$lang['vt_with_topic_num'].' 
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="append_topic" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="text" size="4" name="topic_to_append_into" value="' . $topic_id . '" /><br />
			'.$lang['vt_enter_the_destination_topic_id_to_append_to.'].'<br />
			'.$lang['vt_topic_id_can_be_found_in_the_address_bar_above'].' ' . $topic_id . '<br />
			['.$lang['vt_this_option_will_append_this_topic_to_the_end_of_the_new_topic'].']</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="'.$lang['vt_append_topic'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
      </tr>	
      <tr>
			<td class="two" align="center" width="28" valign="top">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/recycle_bin.gif" alt="'.$lang['vt_recycle'].'" title="'.$lang['vt_recycle'].'" width="22" /></td>
			<td class="two" align="right" valign="top">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['vt_move_to_recycle_bin'].':</span></td>
			<td class="two" align="left" valign="top">
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="move_to_recycle_bin" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="hidden" name="forum_id" value="' . $forum_id . '" />
			<input type="radio" name="status" value="yes" ' . ($status === 'recycled' ? 'checked="checked"' : '') . ' /> Yes  
			<input type="radio" name="status" value="no" ' . ($status !== 'recycled' ? 'checked="checked"' : '') . ' /> No<br />
			'.$lang['vt_this_option_will_send_this_thread_to_the_hidden_recycle_bin'].'<br />
			'.$lang['vt_all_subscriptions_to_this_thread_will_be_deleted'].'</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="'.$lang['vt_recycle_it'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
      </tr>	
      <tr>
			<td class="two" align="center" width="28">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/delete.gif" alt="'.$lang['fe_delete'].'" title="'.$lang['fe_delete'].'" /></td>
			<td class="two" align="right">
			<span style="font-weight: bold;white-space:nowrap;">'.$lang['fe_del_topic'].':</span></td>
			<td class="two" align="left">'.$lang['vt_are_you_really_sure_you_want_to_delete_this_topic'].'</td>
			<td class="two" align="center">
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="delete_topic" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="submit" name="button" class="button" value="'.$lang['fe_del_topic'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
      </tr>			
			' . ($CURUSER['class'] < $min_delete_view_class ? '' : '
      <tr>
			<td class="two" align="center" width="28">
			<img src="' . $INSTALLER09['pic_base_url'] . 'forums/delete.gif" alt="'.$lang['fe_delete'].'" title="'.$lang['fe_delete'].'" /></td>
			<td class="two" align="right">
			<span style="font-weight: bold;white-space:nowrap;"><span style="font-weight:bold;color:red;">*</span>'.$lang['fe_undel_topic'].':</span></td>
			<td class="two" align="left"></td>
			<td class="two" align="center">
			<form action="' . $INSTALLER09['baseurl'] . '/forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="un_delete_topic" />
			<input type="hidden" name="topic_id" value="' . $topic_id . '" />
			<input type="submit" name="button" class="button" value="'.$lang['fe_undel_topic'].'" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
      </tr>
      <tr>
			<td class="two" align="center" colspan="4"><span style="font-weight:bold;color:red;">*</span>
			only <span style="font-weight:bold;">' . get_user_class_name($min_delete_view_class) . '</span> '.$lang['vt_and_above_can_see_these_options'].'</td>
      </tr>') . '
      </table></form><br /></div>
      <script type="text/javascript" src="scripts/check_selected.js"></script>
      <script src="scripts/jquery.trilemma.js" type="text/javascript"></script>
      <script type="text/javascript">
      /*<![CDATA[*/
      $(function(){
      jQuery(\'.poll_select\').trilemma({max:' . $multi_options . ',disablelabels:true});
      });
      /*]]>*/
      </script>
      <script type="text/javascript">
      /*<![CDATA[*/
      $(document).ready(function()	{
      //=== show hide staff tools
      $("#tool_open").click(function() {
      $("#tools").slideToggle("slow", function() {
      });
      });
      //=== show hide voters
      $("#toggle_voters").click(function() {
      $("#voters").slideToggle("slow", function() {
      });
      });
      });
      //=== show hide send PM
      $("#pm_open").click(function() {
      $("#pm").slideToggle("slow", function() {
      });
      });
      /*]]>*/
      </script>
      ' : '');
?>
