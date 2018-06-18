<?php

global $lang;
$posts = $lppostid = $topicpoll = $rpic = $content = '';
$HTMLOUT .= $mini_menu . '<h1 class="has-text-centered">Subscribed Forums for ' . format_username($CURUSER['id']) . '</h1>';
//=== Get count
$res = sql_query('SELECT COUNT(id) FROM subscriptions WHERE user_id=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row[0];
//=== nothing here? kill the page
if ($count == 0) {
    $HTMLOUT .= main_div("
        <h1 class='has-text-centered'>{$lang['sub_no_subscript_found']}!</h1>
        <p>{$lang['sub_you_have_yet_sub_forums']} {$lang['sub_subscrib_to_forum']} {$lang['sub_no_subscript_found_msg1']}.</p>
		<p>{$lang['sub_to_be_notified_via_pm']} <a class='altlink has-text-lime' href='usercp.php?action=default'>{$lang['sub_profile']}</a>
		{$lang['sub_page_and_set']} {$lang['sub_pm_on_subcript']} {$lang['sub_to_yes']}.</p>", 'bottom20');

    return;
}
//=== get stuff for the pager
$page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
$perpage = (isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20);
list($menu, $LIMIT) = pager_new($count, $perpage, $page, $site_config['baseurl'] . '/forums.php?action=subscriptions' . (isset($_GET['perpage']) ? '&amp;perpage=' . $perpage : ''));
//=== top and bottom stuff
$the_top_and_bottom = '<table border="0" cellspacing="0" cellpadding="0" width="90%">
	<tr><td  valign="middle">' . (($count > $perpage) ? $menu : '') . '</td>
	</tr></table>';
//=== get the info
$res = sql_query('SELECT s.id AS subscribed_id, t.id AS topic_id, t.topic_name, t.topic_desc, t.last_post, t.views, t.post_count, t.locked, t.sticky, t.poll_id, t.user_id, t.anonymous AS tan, p.id AS post_id, p.added, p.user_id, p.anonymous AS pan, u.username, u.id, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.perms, u.offensive_avatar FROM subscriptions AS s LEFT JOIN topics AS t ON s.topic_id = t.id LEFT JOIN posts AS p ON t.last_post = p.id LEFT JOIN forums AS f ON f.id = t.forum_id LEFT JOIN users AS u ON u.id = p.user_id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' s.user_id = ' . $CURUSER['id'] . ' AND f.min_class_read < ' . sqlesc($CURUSER['class']) . ' AND s.user_id = ' . sqlesc($CURUSER['id']) . '  ORDER BY t.id DESC ' . $LIMIT) or sqlerr(__FILE__, __LINE__);
while ($topic_arr = mysqli_fetch_assoc($res)) {
    $topic_id = (int) $topic_arr['topic_id'];
    $locked = $topic_arr['locked'] === 'yes';
    $sticky = $topic_arr['sticky'] === 'yes';
    $topic_poll = $topic_arr['poll_id'] > 0;
    $last_post_username = ($topic_arr['pan'] === 'no' && $topic_arr['username'] !== '' ? format_username($topic_arr['id']) : '[<i>' . $lang['fe_anonymous'] . '</i>]');
    $last_post_id = (int) $topic_arr['last_post'];
    //=== Get author / first post info
    $first_post_res = sql_query('SELECT p.added, p.icon, p.body, p.user_id, p.anonymous, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND' : '')) . ' topic_id = ' . sqlesc($topic_id) . ' ORDER BY id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $first_post_arr = mysqli_fetch_assoc($first_post_res);
    if ($topic_arr['tan'] === 'yes') {
        if ($CURUSER['class'] < UC_STAFF && $first_post_arr['user_id'] != $CURUSER['id']) {
            $thread_starter = ($first_post_arr['username'] !== '' ? '<i>' . $lang['fe_anonymous'] . '</i>' : '' . $lang['fe_lost'] . ' [' . (int) $first_post_arr['id'] . ']') . '<br>' . get_date($first_post_arr['added'], '');
        } else {
            $thread_starter = ($first_post_arr['username'] !== '' ? '<i>' . $lang['fe_anonymous'] . '</i> [' . format_username($first_post_arr['id']) . ']' : '' . $lang['fe_lost'] . ' [' . (int) $first_post_arr['id'] . ']') . '<br>' . get_date($first_post_arr['added'], '');
        }
    } else {
        $thread_starter = ($first_post_arr['username'] !== '' ? format_username($first_post_arr['id']) : '' . $lang['fe_lost'] . ' [' . (int) $first_post_arr['id'] . ']') . '<br>' . get_date($first_post_arr['added'], '');
    }
    $icon = ($first_post_arr['icon'] === '' ? '<img src="' . $site_config['pic_baseurl'] . 'forums/topic_normal.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" />' : '<img src="' . $site_config['pic_baseurl'] . 'smilies/' . htmlsafechars($first_post_arr['icon']) . '.gif" alt="' . htmlsafechars($first_post_arr['icon']) . '" title="' . htmlsafechars($first_post_arr['icon']) . '" />');
    $first_post_text = bubble(' <img src="' . $site_config['pic_baseurl'] . 'forums/mg.gif" height="14" alt="' . $lang['fe_preview'] . '" />', format_comment($first_post_arr['body'], true, false, false), '' . $lang['fe_first_post'] . ' ' . $lang['fe_preview'] . '');
    //=== last post read in topic
    $last_unread_post_res = sql_query('SELECT last_post_read FROM read_posts WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND topic_id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    $last_unread_post_arr = mysqli_fetch_row($last_unread_post_res);
    $did_i_post_here = sql_query('SELECT user_id FROM posts WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND topic_id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    $posted = (mysqli_num_rows($did_i_post_here) > 0 ? 1 : 0);
    //=== make the multi pages thing...
    $total_pages = floor($posts / $perpage);
    switch (true) {
        case $total_pages == 0:
            $multi_pages = '';
            break;

        case $total_pages > 11:
            $multi_pages = ' <span style="font-size: xx-small;"> <img src="' . $site_config['pic_baseurl'] . 'forums/multipage.gif" alt="+" title="+" />';
            for ($i = 1; $i < 5; ++$i) {
                $multi_pages .= ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $i . '">' . $i . '</a>';
            }
            $multi_pages .= ' ... ';
            for ($i = ($total_pages - 2); $i <= $total_pages; ++$i) {
                $multi_pages .= ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $i . '">' . $i . '</a>';
            }
            $multi_pages .= '</span>';
            break;

        case $total_pages < 11:
            $multi_pages = ' <span style="font-size: xx-small;"> <img src="' . $site_config['pic_baseurl'] . 'forums/multipage.gif" alt="+" title="+" />';
            for ($i = 1; $i < $total_pages; ++$i) {
                $multi_pages .= ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $i . '">' . $i . '</a>';
            }
            $multi_pages .= '</span>';
            break;
    }
    $new = ($topic_arr['added'] > (TIME_NOW - $readpost_expiry)) ? (!$last_unread_post_arr || $lppostid > $last_unread_post_arr[0]) : 0;
    $topicpic = ($posts < 30 ? ($locked ? ($new ? 'lockednew' : 'locked') : ($new ? 'topicnew' : 'topic')) : ($locked ? ($new ? 'lockednew' : 'locked') : ($new ? 'hot_topic_new' : 'hot_topic')));
    $topic_name = ($sticky ? '<img src="' . $site_config['pic_baseurl'] . 'forums/pinned2.gif" alt="' . $lang['fe_pinned'] . '" title="' . $lang['fe_pinned'] . '" /> ' : ' ') . ($topicpoll ? '<img src="' . $site_config['pic_baseurl'] . 'forums/poll.gif" alt="' . $lang['fe_poll'] . '" title="' . $lang['fe_poll'] . '" /> ' : ' ') . ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($topic_arr['topic_name'], ENT_QUOTES) . '</a> ' . $multi_pages;
    //=== change colors
    $content .= '<tr>
		<td><img src="' . $site_config['pic_baseurl'] . 'forums/' . $topicpic . '.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" /></td>
		<td>' . $icon . '</td>
		<td align="left" valign="middle">
		<table border="0" cellspacing="0" cellpadding="0">
		<tr>
		<td  align="left">' . $topic_name . $first_post_text . ($new ? ' <img src="' . $site_config['pic_baseurl'] . 'forums/new.gif" alt="' . $lang['fe_new_post_in_topic'] . '!" title="' . $lang['fe_new_post_in_topic'] . '!" />' : '') . '</td>
		<td align="right">' . $rpic . '</td>
		</tr>
		</table>
		' . ($topic_arr['topic_desc'] !== '' ? '&#9658; <span style="font-size: x-small;">' . htmlsafechars($topic_arr['topic_desc'], ENT_QUOTES) . '</span>' : '') . '</td>
		<td>' . $thread_starter . '</td>
		<td>' . number_format($topic_arr['post_count'] - 1) . '</td>
		<td>' . number_format($topic_arr['views']) . '</td>
		<td><span style="white-space:nowrap;">' . get_date($topic_arr['added'], '') . '</span><br>by&nbsp;' . $last_post_username . '</td>
		<td><a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=p' . $last_post_id . '#' . $last_post_id . '">
		<img src="' . $site_config['pic_baseurl'] . 'forums/last_post.gif" alt="Last post" title="Last post" /></a></td>
		<td><input type="checkbox" name="remove[]" value="' . (int) $topic_arr['subscribed_id'] . '" /></td>
		</tr>';
}
$HTMLOUT .= $the_top_and_bottom . '<form action="' . $site_config['baseurl'] . '/forums.php?action=delete_subscription" method="post" name="checkme">
		<table border="0" cellspacing="0" cellpadding="5" width="90%">
		<tr>
		<td valign="middle" width="10"><img src="' . $site_config['pic_baseurl'] . 'forums/topic.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" /></td>
		<td valign="middle" width="10"><img src="' . $site_config['pic_baseurl'] . 'forums/topic_normal.gif" alt=' . $lang['fe_thread_icon'] . '" title=' . $lang['fe_thread_icon'] . '" /></td>
		<td align="left">' . $lang['fe_topic'] . '</td>
		<td>' . $lang['fe_started_by'] . '</td>
		<td width="10">' . $lang['fe_replies'] . '</td>
		<td width="10">' . $lang['fe_views'] . '</td>
		<td width="140">' . $lang['fe_last_post'] . '</td>
		<td valign="middle" width="10"><img src="' . $site_config['pic_baseurl'] . 'forums/last_post.gif" alt="Last post" title="Last post" /></td>
		<td valign="middle" width="10"></td>
		</tr>' . $content . '
		<tr>
		<td valign="middle" colspan="9">
		<a class="altlink" href="javascript:SetChecked(1,\'remove[]\')"> <span style="color: black;">' . $lang['sub_select_all'] . '</span></a> - 
		<a class="altlink" href="javascript:SetChecked(0,\'remove[]\')"><span style="color: black;">' . $lang['sub_un_select_all'] . '</span></a>  
		<input type="submit" name="button" class="button is-small" value="' . $lang['fe_remove'] . ' Selected"  /></td>
		</tr></table></form>
		' . $the_top_and_bottom;
