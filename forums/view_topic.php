<?php

global $lang, $fluent, $CURUSER, $site_config;

$attachments = $members_votes = $status = $topic_poll = $stafflocked = $child = $parent_forum_name = $math_image = $math_text = $staff_tools = $now_viewing = '';
$topic_id    = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0);
if (!is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

$upload_errors_size = isset($_GET['se']) ? intval($_GET['se']) : 0;
$upload_errors_type = isset($_GET['ee']) ? intval($_GET['ee']) : 0;

$_forum_sort = isset($CURUSER['forum_sort']) ? $CURUSER['forum_sort'] : 'DESC';
$where       = $CURUSER['class'] < UC_STAFF ? 'topics.status = "ok" AND ' : $CURUSER['class'] < $min_delete_view_class ? 'topics.status != "deleted" AND ' : '';
$arr         = $fluent->from('topics')
    ->select(null)
    ->select('topics.id AS topic_id')
    ->select('topics.user_id')
    ->select('topics.topic_name')
    ->select('topics.locked')
    ->select('topics.last_post')
    ->select('topics.sticky')
    ->select('topics.status')
    ->select('topics.views')
    ->select('topics.poll_id')
    ->select('topics.num_ratings')
    ->select('topics.rating_sum')
    ->select('topics.topic_desc')
    ->select('topics.forum_id')
    ->select('topics.anonymous')
    ->select('forums.name AS forum_name')
    ->select('forums.min_class_read')
    ->select('forums.min_class_write')
    ->select('forums.parent_forum')
    ->innerJoin('forums ON topics.forum_id = forums.id')
    ->where("{$where}topics.id = ?", $topic_id)
    ->fetch();

if ($CURUSER['class'] < $arr['min_class_read'] || !is_valid_id($arr['topic_id']) || $CURUSER['class'] < $min_delete_view_class && $status === 'deleted' || $CURUSER['class'] < UC_STAFF && $status === 'recycled') {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

$status = htmlsafechars($arr['status']);
switch ($status) {
    case 'ok':
        $status       = '';
        $status_image = '';
        break;

    case 'recycled':
        $status       = 'recycled';
        $status_image = '<img src="' . $site_config['pic_baseurl'] . 'forums/recycle_bin.gif" alt="' . $lang['fe_recycled'] . '" title="' . $lang['fe_this_thread_is_currently'] . ' ' . $lang['fe_in_the_recycle_bin'] . '" class="tooltipper emoticon" />';
        break;

    case 'deleted':
        $status       = 'deleted';
        $status_image = '<img src="' . $site_config['pic_baseurl'] . 'forums/delete_icon.gif" alt="' . $lang['fe_deleted'] . '" title="' . $lang['fe_this_thread_is_currently'] . ' ' . $lang['fe_deleted'] . '" class="tooltipper emoticon" />';
        break;
}

$forum_id    = $arr['forum_id'];
$topic_owner = $arr['user_id'];
$topic_name  = htmlsafechars($arr['topic_name'], ENT_QUOTES);
$topic_desc1 = htmlsafechars($arr['topic_desc'], ENT_QUOTES);

$members_votes = [];
if ($arr['poll_id'] > 0) {
    $arr_poll = $fluent->from('forum_poll')
        ->where('id = ?', $arr['poll_id'])
        ->fetch();

    if ($CURUSER['class'] >= UC_STAFF) {
        $query = $fluent->from('forum_poll_votes')
            ->where('forum_poll_votes.id > 0')
            ->where('poll_id = ?', $arr['poll_id']);
        $who_voted = $query ? '<hr>' : 'no votes yet';
        foreach ($query as $arr_poll_voted) {
            $who_voted .= format_username($arr_poll_voted['user_id']);
        }
    }

    $query = $fluent->from('forum_poll_votes')
        ->select(null)
        ->select('option')
        ->where('poll_id = ?', $arr['poll_id'])
        ->where('user_id = ?', $CURUSER['id'])
        ->fetchAll();

    $voted        = 0;
    $members_vote = 1000;
    if ($query) {
        $voted = 1;
        foreach ($query as $members_vote) {
            $members_votes[] = $members_vote['option'];
        }
    }
    $change_vote   = $arr_poll['change_vote'] === 'no' ? 0 : 1;
    $poll_open     = $arr_poll['poll_closed'] === 'yes' || $arr_poll['poll_starts'] > TIME_NOW || $arr_poll['poll_ends'] < TIME_NOW ? 0 : 1;
    $poll_options  = unserialize($arr_poll['poll_answers']);
    $multi_options = $arr_poll['multi_options'];
    $total_votes   = $fluent->from('forum_poll_votes')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('option < 21')
        ->where('poll_id = ?', $arr['poll_id'])
        ->fetch('count');

    $num_non_votes = $fluent->from('forum_poll_votes')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('option > 20')
        ->where('poll_id = ?', $arr['poll_id'])
        ->fetch('count');

    $total_non_votes = $num_non_votes > 0 ? ' [ ' . number_format($num_non_votes) . ' member' . plural($num_non_votes) . ' just wanted to see the results ]' : '';

    $topic_poll .= ($voted || $poll_open === 0 ? '' : '
    <form action="' . $site_config['baseurl'] . '/forums.php?action=poll" method="post" name="poll">
	    <fieldset class="poll_select">
	        <input type="hidden" name="topic_id" value="' . $topic_id . '" />
	        <input type="hidden" name="action_2" value="poll_vote" />') . '
	        <table border="0" cellspacing="5" cellpadding="5">
	            <tr>
	                <td colspan="2">
	                    <img src="' . $site_config['pic_baseurl'] . 'forums/poll.gif" alt="" class="tooltipper emoticon" />
	                    <span>Poll' . ($arr_poll['poll_closed'] === 'yes' ? '
                            closed
                        </span>' : ($arr_poll['poll_starts'] > TIME_NOW ? '
                            starts:
                        </span> ' . get_date($arr_poll['poll_starts'], '') : ($arr_poll['poll_ends'] == 1356048000 ? '
                        </span>' : ($arr_poll['poll_ends'] > TIME_NOW ? ' 
                            ends:
                        </span> ' . get_date($arr_poll['poll_ends'], '', 0, 1) : '
                        </span>')))) . '
	                </td>
	                <td colspan="3">' . ($CURUSER['class'] < UC_STAFF ? '' : '
	                    <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_edit&amp;topic_id=' . $topic_id . '" class="altlink">
	                        <img src="' . $site_config['pic_baseurl'] . 'forums/modify.gif" alt="" class="tooltipper emoticon" /> ' . $lang['fe_edit'] . '
	                    </a>  
                    	<a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_reset&amp;topic_id=' . $topic_id . '" class="altlink">
                    	    <img src="' . $site_config['pic_baseurl'] . 'forums/stop_watch.png" alt=" " class="tooltipper emoticon" /> ' . $lang['fe_reset'] . '
                    	</a>' . (($arr_poll['poll_ends'] > TIME_NOW || $arr_poll['poll_closed'] === 'no') ? '
                    	<a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_close&amp;topic_id=' . $topic_id . '" class="altlink">
                    	    <img src="' . $site_config['pic_baseurl'] . 'forums/clock.png" alt="" width="20px" /> close</a>' : '<a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_open&amp;topic_id=' . $topic_id . '" class="altlink"><img src="' . $site_config['pic_baseurl'] . 'forums/clock.png" alt="" width="20px" /> ' . $lang['fe_start'] . '
                        </a>') . '
	                    <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_delete&amp;topic_id=' . $topic_id . '" class="altlink">
	                        <img src="' . $site_config['pic_baseurl'] . 'forums/delete.gif" alt="" class="tooltipper emoticon" /> ' . $lang['fe_delete'] . '
	                    </a>') . '
	                </td>
	            </tr>
	            <tr>
                    <td><img src="' . $site_config['pic_baseurl'] . 'forums/poll_question.png" alt="" class="tooltipper emoticon" /></td>
                    <td colspan="5">' . format_comment($arr_poll['question']) . '</td>
            	</tr>
	            <tr>
	                <td colspan="5">' . (($voted || $poll_open === 0) ? '' : '
                        <p>you may select up to <span >' . $multi_options . ' </span>option' . plural($multi_options) . '.</p>') . '
	                </td>
	            </tr>';
    $number_of_options = $arr_poll['number_of_options'];
    for ($i = 0; $i < $number_of_options; ++$i) {
        if ($voted) {
            $vote_count = $fluent->from('forum_poll_votes')
                ->select(null)
                ->select('COUNT(*) AS count')
                ->where('option = ?', $i)
                ->where('poll_id = ?', $arr['poll_id'])
                ->fetch('count');

            $math       = $vote_count > 0 ? round(($vote_count / $total_votes) * 100) : 0;
            $math_text  = $math . '% with ' . $vote_count . ' vote' . plural($vote_count);
            $math_image = '
            <table border="0" width="200px">
		        <tr>
		            <td style="padding: 0px; background-image: url(' . $site_config['pic_baseurl'] . 'forums/vote_img_bg.gif); background-repeat: repeat-x">
                	   <img src="' . $site_config['pic_baseurl'] . 'forums/vote_img.gif" width="' . $math . '%" height="8" alt="' . $math_text . '" title="' . $math_text . '" class="tooltipper" />
                	</td>
        	   </tr>
        	</table>';
        }
        $topic_poll .= '
            <tr>
                <td>' . (($voted || $poll_open === 0) ? '
                    <span>' . ($i + 1) . '.</span>' : ($multi_options === 1 ? '
                    <input type="radio" name="vote" value="' . $i . '" />' : '
                    <input type="checkbox" name="vote[]" id="vote[]" value="' . $i . '" />')) . '
                </td>
		        <td>' . format_comment($poll_options[$i]) . '</td>
		        <td>' . $math_image . '</td>
		        <td><span>' . $math_text . '</span></td>
		        <td>' . (in_array($i, $members_votes) ? '
		            <img src="' . $site_config['pic_baseurl'] . 'forums/check.gif" alt=" " class="tooltipper emoticon" />
		            <span>' . $lang['fe_your_vote'] . '!</span>' : '') . '
		        </td>
		    </tr>';
    }
    $topic_poll .= (($change_vote === 1 && $voted) ? '
            <tr>
                <td colspan="5">
			        <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=reset_vote&amp;topic_id=' . $topic_id . '" class="altlink"><img src="' . $site_config['pic_baseurl'] . 'forums/stop_watch.png" alt="" class="tooltipper emoticon" /> ' . $lang['fe_reset_your_vote'] . '!</a> 
			</td>
			</tr>' : '') . ($voted ? '
	        <tr>
			    <td colspan="5">' . $lang['fe_total_votes'] . ': ' . number_format($total_votes) . $total_non_votes . ($CURUSER['class'] < UC_STAFF ? '' : '<br>
        			<a class="altlink"  title="' . $lang['fe_list_voters'] . '" id="toggle_voters">' . $lang['fe_list_voters'] . '</a>
		        	<div id="voters" style="display:none">' . $who_voted . '</div>') . '
			    </td>
	        </tr>
        </table><br>' : ($poll_open === 0 ? '' : '
            <tr>
                <td>' . ($multi_options == 1 ? '
                    <input type="radio" name="vote" value="666" />' : '
                    <input type="checkbox" name="vote[]" id="vote[]" value="666" />') . '
                </td>
                <td colspan="5">
                    <span>' . $lang['fe_i_just_want_to_see_the_results'] . '!</span>
                </td>
            </tr>') . (($voted || $poll_open === 0) ? '
        </table><br>' : '
            <tr>
                <td colspan="5">
			        <input type="submit" name="button" class="button is-small w-100" value="' . $lang['fe_vote'] . '!"  />
			    </td>
		    </tr>
		</table>
    </fieldset>
</form>'));
}
if (isset($_GET['search'])) {
    $search     = htmlsafechars($_GET['search']);
    $topic_name = highlightWords($topic_name, $search);
}
$forum_desc = ($arr['topic_desc'] !== '' ? '<span >' . htmlsafechars($arr['topic_desc'], ENT_QUOTES) . '</span>' : '');
$locked     = ($arr['locked'] === 'yes' ? 'yes' : 'no');
$sticky     = ($arr['sticky'] === 'yes' ? 'yes' : 'no');
$views      = number_format($arr['views']);

$forum_name = htmlsafechars($arr['forum_name'], ENT_QUOTES);

if (0 != $arr['num_ratings']) {
    $rating = round($arr['rating_sum'] / $arr['num_ratings'], 1);
}

$res_subscriptions = sql_query('SELECT id FROM subscriptions WHERE topic_id = ' . sqlesc($topic_id) . ' AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$row_subscriptions = mysqli_fetch_row($res_subscriptions);
$subscriptions     = $row_subscriptions[0] > 0 ? '
        <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=delete_subscription&amp;topic_id=' . $topic_id . '"> 
		<img src="' . $site_config['pic_baseurl'] . 'forums/unsubscribe.gif" alt="+" title="+" class="tooltipper emoticon" /> ' . $lang['fe_unsubscribe_from_this_topic'] . '</a>' : '<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=add_subscription&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">
		<img src="' . $site_config['pic_baseurl'] . 'forums/subscribe.gif" alt="+" title="+" class="tooltipper emoticon" /> ' . $lang['fe_subscribe_to_this_topic'] . '</a>';

$fluent->deleteFrom('now_viewing')
    ->where('user_id = ?', $CURUSER['id'])
    ->execute();

$values = [
    'user_id'  => $CURUSER['id'],
    'forum_id' => $forum_id,
    'topic_id' => $topic_id,
    'added'    => TIME_NOW,
];
$fluent->insertInto('now_viewing')
    ->values($values)
    ->execute();

$topic_users_cache = $cache->get('now_viewing_topic');
if ($topic_users_cache === false || is_null($topic_users_cache)) {
    $topicusers        = '';
    $topic_users_cache = [];
    $query             = $fluent->from('now_viewing')
        ->select(null)
        ->select('now_viewing.user_id')
        ->select('users.perms')
        ->innerJoin('users ON now_viewing.user_id = users.id')
        ->where('topic_id = ?', $topic_id)
        ->where('users.perms < ?', bt_options::PERMS_STEALTH);

    foreach ($query as $row) {
        $list[] = format_username($row['user_id']);
    }

    $topicusers                       = implode(',&nbsp;&nbsp;', $list);
    $topic_users_cache['topic_users'] = $topicusers;
    $topic_users_cache['actcount']    = count($list);
    $cache->set('now_viewing_topic', $topic_users_cache, $site_config['expires']['forum_users']);
}
if (!$topic_users_cache['topic_users']) {
    $topic_users_cache['topic_users'] = $lang['fe_there_not_been_active_visit_15'];
}

$topic_users = $topic_users_cache['topic_users'];
if (!empty($topic_users)) {
    $topic_users = $lang['fe_currently_viewing_this_topic'] . ': ' . $topic_users;
}

$set = [
        'views' => new Envms\FluentPDO\Literal('views + 1')
];
$fluent->update('topics')
    ->set($set)
    ->where('id = ?', $topic_id)
    ->execute();

$res_count = sql_query('SELECT COUNT(id) AS count FROM posts WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'status != \'deleted\' AND' : '')) . ' topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
$arr_count = mysqli_fetch_row($res_count);
$count     = $arr_count[0];

$page                = isset($_GET['page']) ? intval($_GET['page']) : 0;
$perpage             = isset($_GET['perpage']) ? intval($_GET['perpage']) : 15;
$subscription_on_off = (isset($_GET['s']) ? (1 == $_GET['s'] ? '<br><div>' . $lang['fe_sub_to_topic'] . ' <img src="' . $site_config['pic_baseurl'] . 'forums/subscribe.gif" alt="' . $lang['fe_subscribed'] . '" title="' . $lang['fe_subscribed'] . '" class="tooltipper emoticon" /></div>' : '<br><div >' . $lang['fe_unsub_to_topic'] . ' <img src="' . $site_config['pic_baseurl'] . 'forums/unsubscribe.gif" alt="' . $lang['fe_unsubscribe'] . '" title="' . $lang['fe_unsubscribe'] . '" class="tooltipper emoticon" /></div>') : '');
list($menu, $LIMIT)  = pager_new($count, $perpage, $page, 'forums.php?action=view_topic&amp;topic_id=' . $topic_id . (isset($_GET['perpage']) ? '&amp;perpage=' . $perpage : ''));

$res = sql_query('SELECT p.id AS post_id, p.topic_id, p.user_id, p.staff_lock, p.added, p.body, p.edited_by, p.edit_date, p.icon, p.post_title, p.bbcode, p.post_history, p.edit_reason, INET6_NTOA(p.ip) AS ip, p.status AS post_status, p.anonymous, u.seedbonus, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.chatpost, u.leechwarn, u.pirate, u.king, u.enabled, u.email, u.website, u.icq, u.msn, u.aim, u.yahoo, u.last_access, u.show_email, u.paranoia, u.hit_and_run_total, u.avatar, u.title, u.uploaded, u.downloaded, u.signature, u.google_talk, u.icq, u.msn, u.aim, u.yahoo, u.website, u.mood, u.perms, u.reputation, u.offensive_avatar FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND' : '')) . ' topic_id = ' . sqlesc($topic_id) . ' ORDER BY p.id ' . $_forum_sort . ' ' . $LIMIT) or sqlerr(__FILE__, __LINE__);

$may_post = ($CURUSER['class'] >= $arr['min_class_write'] && $CURUSER['forum_post'] === 'yes' && $CURUSER['suspended'] === 'no');

$locked_or_reply_button = ($locked === 'yes' ? '<span><img src="' . $site_config['pic_baseurl'] . 'forums/thread_locked.gif" alt="' . $lang['fe_thread_locked'] . '" title="' . $lang['fe_thread_locked'] . '" class="tooltipper emoticon" />' . $lang['fe_this_topic_is_locked'] . ', you may not post in this thread.</span>' : ('no' == $CURUSER['forum_post'] ? '<span>Your posting rights have been removed. You may not post.</span>' : '<a href="' . $site_config['baseurl'] . '/forums.php?action=post_reply&amp;topic_id=' . $topic_id . '" class="button is-small margin10">Add Reply</a>'));

if ($arr['parent_forum'] > 0) {
    $parent_forum_res  = sql_query('SELECT name AS parent_forum_name FROM forums WHERE id = ' . sqlesc($arr['parent_forum'])) or sqlerr(__FILE__, __LINE__);
    $parent_forum_arr  = mysqli_fetch_row($parent_forum_res);
    $child             = ($arr['parent_forum'] > 0 ? '<span> [ ' . $lang['fe_child_board'] . ' ]</span>' : '');
    $parent_forum_name = '<img src="' . $site_config['pic_baseurl'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" class="tooltipper emoticon" /> 
		<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $forum_id . '">' . htmlsafechars($parent_forum_arr[0], ENT_QUOTES) . '</a>';
}

$the_top_and_bottom = '
    <tr>
        <td>' . $subscriptions . '</td>
		<td>' . (($count > $perpage) ? $menu : '') . '</td>
		<td class="has-text-right">' . ($may_post ? $locked_or_reply_button : '
            <span>
		        You are not permitted to post in this thread.
		    </span>') . '
		</td>
    </tr>';

$HTMLOUT .= ($upload_errors_size > 0 ? ($upload_errors_size === 1 ? '
        <div>One file was not uploaded. The maximum file size allowed is. ' . mksize($max_file_size) . '.</div>' : '
        <div>' . $upload_errors_size . ' file were not uploaded. The maximum file size allowed is. ' . mksize($max_file_size) . '.</div>') : '') . ($upload_errors_type > 0 ? (1 === $upload_errors_type ? '<div>One file was not uploaded. The accepted formats are zip and rar.</div>' : '<div>' . $upload_errors_type . ' files were not uploaded. The accepted formats are zip and rar.</div>') : '') . $mini_menu . $topic_poll . '<br>' . $subscription_on_off . '<br>
		' . ($CURUSER['class'] < UC_STAFF ? '' : '
        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post" name="checkme" onsubmit="return SetChecked(this,\'post_to_mess_with\')" enctype="multipart/form-data">') . (isset($_GET['count']) ? '
            <div>' . intval($_GET['count']) . ' PMs Sent</div>' : '') . '

    		<table class="table table-bordered table-striped">
		        ' . $the_top_and_bottom . '
		        <tr>
		            <td>
		                <img src="' . $site_config['pic_baseurl'] . 'forums/topic_normal.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" class="tooltipper emoticon" />' . $lang['fe_author'] . '
		            </td>
		            <td colspan="2">' . $lang['fe_topic'] . ': ' . $topic_name . '  [ ' . $lang['fe_read'] . ' ' . $views . ' ' . $lang['fe_times'] . ' ]</td>
		        </tr>
		        <tr>
		            <td colspan="3">' . $lang['fe_topic_rating'] . ': ' . (getRate($topic_id, 'topic')) . '</td>
		        </tr>
                <tr>
                    <td colspan="3">' . $topic_users . '</td>
                </tr>';

while ($arr = mysqli_fetch_assoc($res)) {
    $moodname          = isset($mood['name'][$arr['mood']]) ? htmlsafechars($mood['name'][$arr['mood']]) : 'is feeling neutral';
    $moodpic           = isset($mood['image'][$arr['mood']]) ? htmlsafechars($mood['image'][$arr['mood']]) : 'noexpression.gif';
    $post_icon         = $arr['icon']       !== '' ? '<img src="' . $site_config['pic_baseurl'] . 'smilies/' . htmlsafechars($arr['icon']) . '.gif" alt="icon" title="icon" class="tooltipper emoticon" /> ' : '<img src="' . $site_config['pic_baseurl'] . 'forums/topic_normal.gif" alt="icon" title="icon" class="tooltipper emoticon" /> ';
    $post_title        = $arr['post_title'] !== '' ? ' <span>' . htmlsafechars($arr['post_title'], ENT_QUOTES) . '</span>' : '';
    $stafflocked       = $arr['staff_lock'] === 1 ? "<img src='{$site_config['pic_baseurl']}locked.gif' alt='" . $lang['fe_post_locked'] . "' title='" . $lang['fe_post_locked'] . "' class='tooltipper emoticon' />" : '';
    $member_reputation = $arr['username'] != '' ? get_reputation($arr, 'posts', true, $arr['post_id']) : '';
    $edited_by         = '';
    if ($arr['edit_date'] > 0) {
        if ($arr['anonymous'] == 'yes') {
            if ($CURUSER['class'] < UC_STAFF && $arr['user_id'] != $CURUSER['id']) {
                $edited_by = '<span>' . $lang['vmp_last_edit_by_anony'] . '
				 at ' . get_date($arr['edit_date'], '') . ' UTC ' . ('' !== $arr['edit_reason'] ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span>' : '') . '
				 ' . (($CURUSER['class'] >= UC_STAFF && '' !== $arr['post_history']) ? ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int) $arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">' . $lang['fe_read_post_history'] . '</a></span><br>' : '</span>');
            } else {
                $edited_by = '<span>' . $lang['vmp_last_edit_by_anony'] . ' [' . format_username($arr['edited_by']) . ']
				 at ' . get_date($arr['edit_date'], '') . ' UTC ' . ('' !== $arr['edit_reason'] ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span>' : '') . '
				 ' . (($CURUSER['class'] >= UC_STAFF && '' !== $arr['post_history']) ? ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int) $arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">' . $lang['fe_read_post_history'] . '</a></span><br>' : '</span>');
            }
        } else {
            $edited_by = '<span>' . $lang['fe_last_edited_by'] . ' ' . format_username($arr['edited_by']) . '
				 at ' . get_date($arr['edit_date'], '') . ' UTC ' . ('' !== $arr['edit_reason'] ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span>' : '') . '
				 ' . (($CURUSER['class'] >= UC_STAFF && '' !== $arr['post_history']) ? ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int) $arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">' . $lang['fe_read_post_history'] . '</a></span><br>' : '</span>');
        }
    }
    $body = ($arr['bbcode'] === 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body']));
    if (isset($_GET['search'])) {
        $body       = highlightWords($body, $search);
        $post_title = highlightWords($post_title, $search);
    }
    $post_id = $arr['post_id'];

    $attachments_res = sql_query('SELECT id, file_name, extension, size FROM attachments WHERE post_id =' . sqlesc($post_id) . ' AND user_id = ' . sqlesc($arr['id'])) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($attachments_res) > 0) {
        $attachments = '<table width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td><span >' . $lang['fe_attachments'] . ':</span><hr>';
        while ($attachments_arr = mysqli_fetch_assoc($attachments_res)) {
            $attachments .= '<span>' . ($attachments_arr['extension'] === 'zip' ? ' <img src="' . $site_config['pic_baseurl'] . 'forums/zip.gif" alt="' . $lang['fe_zip'] . '" title="' . $lang['fe_zip'] . '" class="tooltipper emoticon" /> ' : ' <img src="' . $site_config['pic_baseurl'] . 'forums/rar.gif" alt="' . $lang['fe_rar'] . '" title="' . $lang['fe_rar'] . '" class="tooltipper emoticon" /> ') . ' 
					<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=download_attachment&amp;id=' . (int) $attachments_arr['id'] . '" title="' . $lang['fe_download_attachment'] . '" target="_blank">
					' . htmlsafechars($attachments_arr['file_name']) . '</a> <span style="font-weight: bold; font-size: xx-small;">[' . mksize($attachments_arr['size']) . ']</span></span>';
        }
        $attachments .= '</td></tr></table>';
    }
    $width  = 300;
    $height = 100;

    $signature = (($CURUSER['opt1'] & user_options::SIGNATURES) ? '' : ($arr['signature']== '' ? '' : ($arr['anonymous'] == 'yes' || $arr['perms'] & bt_options::PERMS_STEALTH ? '<table width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td><hr><img style="max-width:' . $width . 'px;max-height:' . $height . 'px;" src="' . $site_config['pic_baseurl'] . 'anonymous_2.jpg" alt="Signature" /></td></tr></table>' : '<table width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td ><hr>' . format_comment($arr['signature']) . '</td></tr></table>')));

    $post_status = htmlsafechars($arr['post_status']);
    switch ($post_status) {
        case 'ok':
            $post_status = '';
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
    $HTMLOUT .= '
        <tr>
            <td colspan="3">
                <div class="columns is-marginless level level-center">
                    <div class="column is-one-third is-marginless level">
                        <a name="' . $post_id . '"></a>
                        ' . ($CURUSER['class'] >= UC_STAFF ? '<input type="checkbox" name="post_to_mess_with[]" value="' . $post_id . '" />' : '') . '
                        <a href="javascript:window.alert(\'' . $lang['fe_direct_link_to_this_post'] . ':\n ' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#' . $post_id . '\');">
                        <img src="' . $site_config['pic_baseurl'] . 'forums/link.gif" alt="' . $lang['fe_direct_link_to_this_post'] . '" title="' . $lang['fe_direct_link_to_this_post'] . '" class="tooltipper emoticon left5 right5" /></a>
                        <span>' . ($arr['anonymous'] === 'yes' ? '<i>' . $lang['fe_anonymous'] . '</i>' : '' . htmlsafechars($arr['username']) . '') . '</span>
                        <span class="tool left5 right5">
                            <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);">
                                <img src="' . $site_config['pic_baseurl'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" title="' . ($arr['anonymous'] === 'yes' ? get_anonymous_name() : htmlsafechars($arr['username'])) . ' ' . $moodname . '!" class="tooltipper emoticon" />
                            </a>
                        </span>' . (($arr['paranoia'] >= 2 && $CURUSER['class'] < UC_STAFF) ? '
                        <img src="' . $site_config['pic_baseurl'] . 'smilies/tinfoilhat.gif" alt="' . $lang['fe_i_wear_a_tinfoil_hat'] . '!" title="' . $lang['fe_i_wear_a_tinfoil_hat'] . '!" class="tooltipper emoticon" />' : get_user_ratio_image($arr['uploaded'], ($site_config['ratio_free'] ? '0' : $arr['downloaded']))) . '
                    </div>
                    <div class="column is-one-third is-marginless">
                        <span>' . $post_icon . $post_title . ' ' . $lang['fe_posted_on'] . ': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span>
                    </div>
                    <div class="column is-marginless">
                        <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=post_reply&amp;topic_id=' . $topic_id . '&amp;quote_post=' . $post_id . '&amp;key=' . $arr['added'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/quote.gif" alt="' . $lang['fe_quote'] . '" title="' . $lang['fe_quote'] . '" class="tooltipper emoticon" /> ' . $lang['fe_quote'] . '</a>
                        ' . (($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $arr['id']) ? ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=edit_post&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '"><img src="' . $site_config['pic_baseurl'] . 'forums/modify.gif" alt="' . $lang['fe_modify'] . '" title="' . $lang['fe_modify'] . '" class="tooltipper emoticon" /> ' . $lang['fe_modify'] . '</a> 
                        <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=delete_post&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $site_config['pic_baseurl'] . 'forums/delete.gif" alt="' . $lang['fe_delete'] . '" title="' . $lang['fe_delete'] . '" class="tooltipper emoticon" /> ' . $lang['fe_remove'] . '</a>' : '') . '
                        <!--<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=report_post&amp;topic_id=' . $topic_id . '&amp;post_id=' . $post_id . '"><img src="' . $site_config['pic_baseurl'] . 'forums/report.gif" alt="' . $lang['fe_report'] . '" title="' . $lang['fe_report'] . '" class="tooltipper emoticon" /> ' . $lang['fe_report'] . '</a>-->
                        <a href="' . $site_config['baseurl'] . '/report.php?type=Post&amp;id=' . $post_id . '&amp;id_2=' . $topic_id . '"><img src="' . $site_config['pic_baseurl'] . 'forums/report.gif" alt="' . $lang['fe_report'] . '" title="' . $lang['fe_report'] . '" class="tooltipper emoticon" /> ' . $lang['fe_report'] . '</a>
                        ' . (UC_MAX == $CURUSER['class'] && 1 == $arr['staff_lock'] ? '<a href="' . $site_config['baseurl'] . '/forums.php?action=staff_lock&amp;mode=unlock&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $site_config['pic_baseurl'] . 'key.gif" alt="' . $lang['fe_un_lock'] . '" title="' . $lang['fe_un_lock'] . '" class="tooltipper emoticon" /> ' . $lang['fe_unlock_post'] . '</a>' : '') . '
                        ' . (UC_MAX == $CURUSER['class'] && 0 == $arr['staff_lock'] ? '<a href="' . $site_config['baseurl'] . '/forums.php?action=staff_lock&amp;mode=lock&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $site_config['pic_baseurl'] . 'key.gif" alt="' . $lang['fe_lock'] . '" title="' . $lang['fe_lock'] . '" class="tooltipper emoticon" /> ' . $lang['fe_lock_post'] . '</a>' : '') . $stafflocked . '
                        <a href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#top"><img src="' . $site_config['pic_baseurl'] . 'forums/up.gif" alt="' . $lang['fe_top'] . '" title="' . $lang['fe_top'] . '" class="tooltipper emoticon" /></a> 
                        <a href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#bottom"><img src="' . $site_config['pic_baseurl'] . 'forums/down.gif" alt="' . $lang['fe_bottom'] . '" title="' . $lang['fe_bottom'] . '" class="tooltipper emoticon" /></a> 
                    </div>
                </div>
            </td>
        </tr>
		<tr>
         <td class="w-10">' . ($arr['anonymous'] == 'yes' ? '<img style="max-width:' . $width . 'px;" src="' . $site_config['pic_baseurl'] . 'anonymous_1.jpg" alt="avatar" />' : avatar_stuff($arr)) . '<br>
			' . ($arr['anonymous'] == 'yes' ? '<i>' . $lang['fe_anonymous'] . '</i>' : format_username($arr['user_id'])) . ($arr['anonymous'] == 'yes' || $arr['title'] == '' ? '' : '<br><span style=" font-size: xx-small;">[' . htmlsafechars($arr['title']) . ']</span>') . '<br>
			<span >' . ($arr['anonymous'] == 'yes' ? '' : get_user_class_name($arr['class'])) . '</span><br>
			' . ($arr['last_access'] > (TIME_NOW - 300) && $arr['perms'] < bt_options::PERMS_STEALTH ? ' <img src="' . $site_config['pic_baseurl'] . 'forums/online.gif" alt="Online" title="Online" class="tooltipper emoticon" /> Online' : ' <img src="' . $site_config['pic_baseurl'] . 'forums/offline.gif" alt="' . $lang['fe_offline'] . '" title="' . $lang['fe_offline'] . '" class="tooltipper emoticon" /> ' . $lang['fe_offline'] . '') . '<br>
			' . $lang['fe_karma'] . ': ' . number_format($arr['seedbonus']) . $member_reputation . '<br>' . ($arr['google_talk'] !== '' ? ' <a href="http://talkgadget.google.com/talkgadget/popout?member=' . htmlsafechars($arr['google_talk']) . '" title="' . $lang['fe_click_for_google_talk_gadget'] . '"  target="_blank"><img src="' . $site_config['pic_baseurl'] . 'forums/google_talk.gif" alt="' . $lang['fe_google_talk'] . '" class="tooltipper emoticon" /></a> ' : '') . ('' !== $arr['icq'] ? ' <a href="http://people.icq.com/people/&amp;uin=' . htmlsafechars($arr['icq']) . '" title="' . $lang['fe_click_to_open_icq_page'] . '" target="_blank"><img src="' . $site_config['pic_baseurl'] . 'forums/icq.gif" alt="icq" class="tooltipper emoticon" /></a> ' : '') . ('' !== $arr['msn'] ? ' <a href="http://members.msn.com/' . htmlsafechars($arr['msn']) . '" target="_blank" title="' . $lang['fe_click_to_see_msn_details'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/msn.gif" alt="msn" title="msn" class="tooltipper emoticon" /></a> ' : '') . ('' !== $arr['aim'] ? ' <a href="http://aim.search.aol.com/aol/search?s_it=searchbox.webhome&amp;q=' . htmlsafechars($arr['aim']) . '" target="_blank" title="' . $lang['fe_click_to_search_on_aim'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/aim.gif" alt="AIM" title="AIM" class="tooltipper emoticon" /></a> ' : '') . ('' !== $arr['yahoo'] ? ' <a href="http://webmessenger.yahoo.com/?im=' . htmlsafechars($arr['yahoo']) . '" target="_blank" title="' . $lang['fe_click_to_open_yahoo'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/yahoo.gif" alt="yahoo" title="Yahoo!" class="tooltipper emoticon" /></a> ' : '') . ($arr['website'] !== '' ? ' <a href="' . htmlsafechars($arr['website']) . '" target="_blank" title="' . $lang['fe_click_to_go_to_website'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/website.gif" alt="website" /></a> ' : '') . ($arr['show_email'] === 'yes' ? ' <a href="mailto:' . htmlsafechars($arr['email']) . '"  title="' . $lang['fe_click_to_email'] . '" target="_blank"><img src="' . $site_config['pic_baseurl'] . 'email.gif" alt="email" title="email" class="tooltipper emoticon" /> </a>' : '') . '
			' . ($CURUSER['class'] >= UC_STAFF ? '
			<ul class="makeMenu">
				<li>' . htmlsafechars($arr['ip']) . '
					<ul>
					<li><a href="' . $site_config['anonymizer_url'] . 'https://ws.arin.net/?queryinput=' . htmlsafechars($arr['ip']) . '" title="' . $lang['vt_whois_to_find_isp_info'] . '" target="_blank">' . $lang['vt_ip_whois'] . '</a></li>
					<li><a href="' . $site_config['anonymizer_url'] . 'http://www.infosniper.net/index.php?ip_address=' . htmlsafechars($arr['ip']) . '" title="' . $lang['vt_ip_to_map_using_infosniper'] . '!" target="_blank">' . $lang['vt_ip_to_map'] . '</a></li>
				</ul>
				</li>
			</ul>' : '') . '
			</td>
			<td class="' . $post_status . '"   colspan="2">' . $body . $edited_by . '</td></tr>' . (!empty($signature) ? '
			<tr>
			    <td></td>
			    <td colspan="2">' . $signature . '</td>
			</tr>' : '') . (!empty($attachments) ? '
			<tr>
			    <td></td>
			    <td colspan="2">' . $attachments . '</td>
			</tr>' : '') . '
			<tr>
			    <td colspan="3">' . (($arr['paranoia'] >= 1 && $CURUSER['class'] < UC_STAFF) ? '' : '
                    <span><img src="' . $site_config['pic_baseurl'] . 'up.png" alt="' . $lang['vt_uploaded'] . '" title="' . $lang['vt_uploaded'] . '" class="tooltipper emoticon" /> ' . mksize($arr['uploaded']) . '</span>  
                    ' . ($site_config['ratio_free'] ? '' : '<span style="color: red;"><img src="' . $site_config['pic_baseurl'] . 'dl.png" alt="' . $lang['vt_downloaded'] . '" title="' . $lang['vt_downloaded'] . '" class="tooltipper emoticon" /> ' . mksize($arr['downloaded']) . '</span>') . '') . (($arr['paranoia'] >= 2 && $CURUSER['class'] < UC_STAFF) ? '' : '' . $lang['vt_ratio'] . ': ' . member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']) . '
                    ' . (0 == $arr['hit_and_run_total'] ? '<img src="' . $site_config['pic_baseurl'] . 'forums/no_hit_and_runs2.gif"  alt="' . ($arr['anonymous'] == 'yes' ? '' . $lang['fe_anonymous'] . '' : htmlsafechars($arr['username'])) . ' ' . $lang['vt_has_never_hit'] . ' &amp; ran!" title="' . ($arr['anonymous'] == 'yes' ? $lang['fe_anonymous'] : htmlsafechars($arr['username'])) . ' ' . $lang['vt_has_never_hit'] . ' &amp; ran!" class="tooltipper emoticon" />' : '') . '
                    ') . '
                    <a class="altlink" href="pm_system.php?action=send_message&amp;receiver=' . $arr['id'] . '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) . '"><img src="' . $site_config['pic_baseurl'] . 'forums/send_pm.png" alt="' . $lang['vt_send_pm'] . '" title="' . $lang['vt_send_pm'] . '" class="tooltipper emoticon" /> ' . $lang['vt_send_message'] . '</a>
                </td>
            </tr>
        </table>
     </td></tr>';
    $attachments = '';
}

$fluent->deleteFrom('read_posts')
    ->where('user_id = ?', $CURUSER['id'])
    ->where('topic_id = ?', $topic_id)
    ->execute();

$values = [
    'user_id'        => $CURUSER['id'],
    'topic_id'       => $topic_id,
    'last_post_read' => $post_id,
];
$fluent->insertInto('read_posts')
    ->values($values)
    ->execute();

$cache->delete('last_read_post_' . $topic_id . '_' . $CURUSER['id']);
$cache->delete('sv_last_read_post_' . $topic_id . '_' . $CURUSER['id']);

$HTMLOUT .= '
    </table>
    <a name="bottom"></a>
    <br>';

if ($CURUSER['class'] >= UC_STAFF) {
    $HTMLOUT .= '
    <div class="level-center margin20">
        <span class="level-center">
            <img src="' . $site_config['pic_baseurl'] . 'forums/tools.png" alt="' . $lang['vt_tools'] . '" title="' . $lang['vt_tools'] . '" class="tooltipper emoticon right10" />
                <a class="altlink"  title="' . $lang['fe_staff_tools'] . '" id="staff_tools_open">' . $lang['fe_staff_tools'] . '</a>
            <img src="' . $site_config['pic_baseurl'] . 'forums/tools.png" alt="' . $lang['vt_tools'] . '" title="' . $lang['vt_tools'] . '" class="tooltipper emoticon left10" />
        </span>
    </div>
    <div id="staff_tools" style="display:none" class="bottom20">';
    $table = '
        <tr>
            <td>
                <img src="' . $site_config['pic_baseurl'] . 'forums/merge.gif" alt="' . $lang['vt_merge'] . '" title="' . $lang['vt_merge'] . '" class="tooltipper emoticon" />
            </td>
            <td>
                <input type="radio" name="action_2" value="merge_posts" />' . $lang['vt_merge_with'] . '<br>
                <input type="radio" name="action_2" value="append_posts" />' . $lang['vt_append_to'] . '
            </td>
            <td>
                ' . $lang['fe_topic'] . ':<input type="text" size="2" name="new_topic" value="' . $topic_id . '" />
            </td>
            <td class="has-text-centered">
                <a class="altlink" href="javascript:SetChecked(1,\'post_to_mess_with[]\')" title="' . $lang['vt_select_all_posts_and_use_the_following_options'] . '"> ' . $lang['vt_select_all'] . '</a><br>
                <a class="altlink" href="javascript:SetChecked(0,\'post_to_mess_with[]\')" title="' . $lang['vt_unselect_all_posts'] . '">' . $lang['vt_un_select_all'] . '</a><br>
                <input type="submit" name="button" class="button is-small w-100" value="' . $lang['vt_with_selected'] . '"  />
            </td>
        </tr>
        <tr>
            <td>
                <img src="' . $site_config['pic_baseurl'] . 'forums/split.gif" alt="' . $lang['vt_split'] . '" title="' . $lang['vt_split'] . '" class="tooltipper emoticon" />
            </td>
            <td>
                <input type="radio" name="action_2" value="split_topic" />' . $lang['vt_split_topic'] . '
            </td>
            <td>
                ' . $lang['fe_new_topic_name'] . ':<input type="text" size="20" maxlength="120" name="new_topic_name" value="' . ('' !== $topic_name ? $topic_name : '') . '" /> [required]<br>
                ' . $lang['fe_new_topic_desc'] . ':<input type="text" size="20" maxlength="120" name="new_topic_desc" value="" />
            </td>
            <td class="has-text-centered">
                <input type="submit" name="button" class="button is-small w-100" value="Fixit!" />
            </td>
        </tr>
        <tr>
            <td>
                <img src="' . $site_config['pic_baseurl'] . 'forums/send_pm.png" alt="' . $lang['vt_send_message'] . '" title="' . $lang['vt_send_message'] . '" class="tooltipper emoticon" />
            </td>
            <td colspan="2">
                <div id="pm" style="display:none">' . main_table('
                    <tr>
                        <td colspan="2">' . $lang['vt_send_pm_select_mem'] . '</td>
                    </tr>
                    <tr>
                        <td>
                            <span>' . $lang['vt_subject'] . ':</span>
                        </td>
                        <td>
                            <input type="text" size="20" maxlength="120" class="w-100" name="subject" value="" />
                            <input type="radio" name="action_2" value="send_pm" />
                            <span>' . $lang['vt_select_to_send'] . '.</span> 
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span>' . $lang['vt_message'] . ':</span>
                        </td>
                        <td>
                            <textarea cols="30" rows="4" name="message" class="text_area_small"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span>' . $lang['vt_from'] . ':</span>
                        </td>
                        <td>
                            <input type="radio" name="pm_from" value="0" checked="checked" /> ' . $lang['vt_system'] . '
                            <input type="radio" name="pm_from" value="1" /> ' . format_username($CURUSER['id']) . '
                        </td>
                    </tr>', '', 'top20') . '
                </div>
            </td>
            <td class="has-text-centered">
                <a class="button is-small w-100" title="' . $lang['vt_send_pm_select_mem'] . '" id="pm_open">' . $lang['vt_send_pm'] . '</a>
            </td>
        </tr>
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/pinned.gif" alt="' . $lang['fe_pinned'] . '" title="' . $lang['fe_pinned'] . '" class="tooltipper emoticon" />
                    </td>
                    <td>
                        <span >' . $lang['vt_pin'] . ' ' . $lang['fe_topic'] . ':</span>
                    </td>
                    <td>
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="set_pinned" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="radio" name="pinned" value="yes" ' . ($sticky === 'yes' ? 'checked' : '') . ' /> Yes
                            <input type="radio" name="pinned" value="no" ' . ($sticky === 'no' ? 'checked' : '') . ' /> No
                    </td>
                    <td class="has-text-centered">
                            <input type="submit" name="button" class="button is-small w-100" value="Set ' . $lang['fe_pinned'] . '"  />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/thread_locked.gif" alt="' . $lang['fe_locked'] . '" title="' . $lang['fe_locked'] . '" class="tooltipper emoticon" />
                    </td>
                    <td>
                        <span>' . $lang['fe_lock'] . ' ' . $lang['fe_topic'] . ':</span>
                    </td>
                    <td>
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="set_locked" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="radio" name="locked" value="yes" ' . ($locked === 'yes' ? 'checked' : '') . ' /> Yes
                            <input type="radio" name="locked" value="no" ' . ($locked === 'no' ? 'checked' : '') . ' /> No
                    </td>
                    <td class="has-text-centered">
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['vt_lock_topic'] . '" />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>

                        <img src="' . $site_config['pic_baseurl'] . 'forums/move.gif" alt="' . $lang['vt_move'] . '" title="' . $lang['vt_move'] . '" class="tooltipper emoticon" />
                    </td>
                    <td>
                        <span>' . $lang['vt_move'] . ' ' . $lang['fe_topic'] . ':</span>
                    </td>
                    <td>
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="move_topic" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <select name="forum_id">
                                ' . insert_quick_jump_menu($forum_id, true) . '
                            </select>
                    </td>
                    <td class="has-text-centered">
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['vt_move_topic'] . '"  />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/modify.gif" alt="' . $lang['fe_modify'] . '" title="' . $lang['fe_modify'] . '" class="tooltipper emoticon" />
                    </td>
                    <td>
                        <span>' . $lang['vt_rename'] . ' ' . $lang['fe_topic'] . ':</span>
                    </td>
                    <td>
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="rename_topic" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="text" size="40" maxlength="120" name="new_topic_name" value="' . ('' !== $topic_name ? $topic_name : '') . '" />
                    </td>
                    <td class="has-text-centered">
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['vt_rename_topic'] . '"  />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/modify.gif" alt="' . $lang['fe_modify'] . '" title="' . $lang['fe_modify'] . '" class="tooltipper emoticon" />
                    </td>
                    <td>
                        <span>' . $lang['vt_change_topic_desc'] . ':</span>
                    </td>
                    <td>
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="change_topic_desc" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="text" size="40" maxlength="120" name="new_topic_desc" value="' . ('' !== $topic_desc1 ? $topic_desc1 : '') . '" /></td>
                    <td class="has-text-centered">
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['vt_change_desc'] . '"  />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/merge.gif" alt="' . $lang['vt_merge'] . '" title="' . $lang['vt_merge_topic'] . '" class="tooltipper emoticon" />
                    </td>
                    <td>
                        <span >' . $lang['vt_merge'] . ' ' . $lang['fe_topic'] . ':</span>
                    </td>
                    <td>' . $lang['vt_with_topic_num'] . '
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="merge_topic" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="text" size="4" name="topic_to_merge_with" value="' . $topic_id . '" />
                            <p>' . $lang['vt_enter_the_destination_topic_id_to_merge_into'] . '<br>
                            ' . $lang['vt_topic_id_can_be_found_in_the_address_bar_above'] . ' ' . $topic_id . '</p>
                            <p>' . $lang['vt_this_option_will_mix_the_two_topics_together'] . '</p>
                    </td>
                    <td class="has-text-centered">
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['vt_merge_topic'] . '"  />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/merge.gif" alt="' . $lang['vt_append'] . '" title="' . $lang['vt_append_topic'] . '" class="tooltipper emoticon" />
                    </td>
                    <td>
                        <span>' . $lang['vt_append'] . ' ' . $lang['fe_topic'] . ':</span>
                    </td>
                    <td>' . $lang['vt_with_topic_num'] . '
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="append_topic" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="text" size="4" name="topic_to_append_into" value="' . $topic_id . '" />
                            <p>' . $lang['vt_enter_the_destination_topic_id_to_append_to.'] . '<br>
                            ' . $lang['vt_topic_id_can_be_found_in_the_address_bar_above'] . ' ' . $topic_id . '</p>
                            <p>' . $lang['vt_this_option_will_append_this_topic_to_the_end_of_the_new_topic'] . '</p>
                     </td>
                     <td class="has-text-centered">
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['vt_append_topic'] . '"  />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/recycle_bin.gif" alt="' . $lang['vt_recycle'] . '" title="' . $lang['vt_recycle'] . '" class="tooltipper emoticon" /></td>
                    <td>
                        <span>' . $lang['vt_move_to_recycle_bin'] . ':</span>
                    </td>
                    <td>
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="move_to_recycle_bin" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="hidden" name="forum_id" value="' . $forum_id . '" />
                            <input type="radio" name="status" value="yes" ' . ('recycled' === $status ? 'checked' : '') . ' /> Yes
                            <input type="radio" name="status" value="no" ' . ('recycled' !== $status ? 'checked' : '') . ' /> No<br>
                            ' . $lang['vt_this_option_will_send_this_thread_to_the_hidden_recycle_bin'] . '<br>
                            ' . $lang['vt_all_subscriptions_to_this_thread_will_be_deleted'] . '
                    </td>
                    <td class="has-text-centered">
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['vt_recycle_it'] . '"  />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/delete.gif" alt="' . $lang['fe_delete'] . '" title="' . $lang['fe_delete'] . '" class="tooltipper emoticon" /></td>
                    <td>
                        <span >' . $lang['fe_del_topic'] . ':</span>
                    </td>
                    <td>' . $lang['vt_are_you_really_sure_you_want_to_delete_this_topic'] . '</td>
                    <td class="has-text-centered">
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="delete_topic" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['fe_del_topic'] . '"  />
                        </form>
                    </td>
                </tr>' . ($CURUSER['class'] < $min_delete_view_class ? '' : '
                <tr>
                    <td>
                        <img src="' . $site_config['pic_baseurl'] . 'forums/delete_icon.gif" alt="' . $lang['fe_undel_topic'] . '" title="' . $lang['fe_undel_topic'] . '" class="tooltipper emoticon" />
                    </td>
                    <td>
                        <span>
                            <span class="has-text-red">*</span>' . $lang['fe_undel_topic'] . ':
                        </span>
                    </td>
                    <td></td>
                    <td class="has-text-centered">
                        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
                            <input type="hidden" name="action_2" value="un_delete_topic" />
                            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
                            <input type="submit" name="button" class="button is-small w-100" value="' . $lang['fe_undel_topic'] . '"  />
                        </form>
                    </td>
                </tr>
                <tr>
                    <td class="has-text-centered" colspan="4">
                        <span class="has-text-red">*</span>only <span>' . get_user_class_name($min_delete_view_class) . '</span> ' . $lang['vt_and_above_can_see_these_options'] . '
                    </td>
                </tr>');
    $HTMLOUT .= main_table($table) . '
        </form>
    </div>';
}
