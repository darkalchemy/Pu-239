<?php

declare(strict_types = 1);

$colour = $topicpoll = '';
global $site_config, $CURUSER;

$HTMLOUT .= $mini_menu . '<h1 class="has-text-centered">' . _("New replies to threads you've posted in") . '</h1>';
$res_count = sql_query('SELECT t.id, t.last_post FROM topics AS t LEFT JOIN posts AS p ON t.last_post = p.id LEFT JOIN forums AS f ON f.id=t.forum_id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' f.min_class_read <= ' . $CURUSER['class']) or sqlerr(__FILE__, __LINE__);
$count = 0;
while ($arr_count = mysqli_fetch_assoc($res_count)) {
    $res_post_read = sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_count['id'])) or sqlerr(__FILE__, __LINE__);
    $arr_post_read = mysqli_fetch_row($res_post_read);
    $did_i_post_here = sql_query('SELECT user_id FROM posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_count['id'])) or sqlerr(__FILE__, __LINE__);
    $posted = (mysqli_num_rows($did_i_post_here) > 0 ? 1 : 0);
    if ($arr_post_read[0] < $arr_count['last_post'] && $posted) {
        ++$count;
    }
}
if ($count === 0) {
    $HTMLOUT .= '<br><br><table class="table table-bordered table-striped">
   <tr><td>
   <h1>' . _('No unread posts') . '!</h1>' . _("You are up to date on all topics you've posted in") . '.<br><br>
	</td></tr></table><br><br>';
} else {
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
    $perpage = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20;
    $link = $site_config['paths']['baseurl'] . '/forums.php?action=view_unread_posts&amp;' . (isset($_GET['perpage']) ? "perpage={$perpage}&amp;" : '');
    $pager = pager($perpage, $count, $link);
    $menu_top = $pager['pagertop'];
    $menu_bottom = $pager['pagerbottom'];
    $LIMIT = $pager['limit'];
    $res_unread = sql_query('SELECT t.id AS topic_id, t.topic_name AS topic_name, t.last_post, t.post_count,
   t.views, t.topic_desc, t.locked, t.sticky, t.poll_id, t.forum_id, t.rating_sum, t.num_ratings, t.anonymous AS tan,
   f.name AS forum_name, f.description AS forum_desc, p.post_title, p.body, p.icon, p.user_id, p.anonymous AS pan,
   u.id, u.username, u.class, u.donor, u.warned, u.status, u.chatpost, u.leechwarn, u.pirate, u.king
   FROM topics AS t
   LEFT JOIN posts AS p ON t.last_post = p.id
   LEFT JOIN forums AS f ON f.id=t.forum_id
   LEFT JOIN users AS u ON u.id=t.user_id
   WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' f.min_class_read <= ' . $CURUSER['class'] . ' 
   ORDER BY t.last_post DESC ' . $LIMIT) or sqlerr(__FILE__, __LINE__);

    $HTMLOUT .= ($count > $perpage ? $menu_top : '') . '<table class="table table-bordered table-striped">
	<tr>
	<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic.gif" class="icon tooltipper" alt="' . _('Topic') . '" title="' . _('Topic') . '"></td>
	<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" class="icon tooltipper" alt=' . _('Thread Icon') . '" title=' . _('Thread Icon') . '"></td>
	<td>' . _('New Posts') . '!</td>
	<td>' . _('Replies') . '</td>
	<td>' . _('Views') . '</td>
	<td>' . _('Started By') . '</td>
	</tr>';
    while ($arr_unread = mysqli_fetch_assoc($res_unread)) {
        $res_post_read = sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_unread['topic_id'])) or sqlerr(__FILE__, __LINE__);
        $arr_post_read = mysqli_fetch_row($res_post_read);
        $did_i_post_here = sql_query('SELECT user_id FROM posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_unread['topic_id'])) or sqlerr(__FILE__, __LINE__);
        $posted = (mysqli_num_rows($did_i_post_here) > 0 ? 1 : 0);
        if ($arr_post_read[0] < $arr_unread['last_post'] && $posted) {
            $locked = $arr_unread['locked'] === 'yes';
            $sticky = $arr_unread['sticky'] === 'yes';
            $topic_poll = $arr_unread['poll_id'] > 0;
            $first_unread_poster = sql_query('SELECT added FROM posts WHERE status = \'ok\'  AND topic_id=' . sqlesc($arr_unread['topic_id']) . ' ORDER BY id LIMIT 1') or sqlerr(__FILE__, __LINE__);
            $first_unread_poster_arr = mysqli_fetch_row($first_unread_poster);
            if ($arr_unread['tan'] === '1') {
                if ($CURUSER['class'] < UC_STAFF && $arr_unread['user_id'] != $CURUSER['id']) {
                    $thread_starter = (!empty($arr_unread['username']) ? '<i>' . get_anonymous_name() . '</i>' : '' . _('Lost') . ' [' . $arr_unread['id'] . ']') . '<br>' . get_date((int) $first_unread_poster_arr[0], '');
                } else {
                    $thread_starter = (!empty($arr_unread['username']) ? '<i>' . get_anonymous_name() . '</i> [' . format_username((int) $arr_unread['id']) . ']' : '' . _('Lost') . ' [' . $arr_unread['id'] . ']') . '<br>' . get_date((int) $first_unread_poster_arr[0], '');
                }
            } else {
                $thread_starter = (!empty($arr_unread['username']) ? format_username((int) $arr_unread['id']) : '' . _('Lost') . ' [' . $arr_unread['id'] . ']') . '<br>' . get_date((int) $first_unread_poster_arr[0], '');
            }
            $topicpic = ($arr_unread['post_count'] < 30 ? ($locked ? 'lockednew' : 'topicnew') : ($locked ? 'lockednew' : 'hot_topic_new'));
            $rpic = ($arr_unread['num_ratings'] != 0 ? ratingpic_forums(round($arr_unread['rating_sum'] / $arr_unread['num_ratings'], 1)) : '');
            $sub = sql_query('SELECT user_id FROM subscriptions WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_unread['topic_id'])) or sqlerr(__FILE__, __LINE__);
            $subscriptions = (mysqli_num_rows($sub) > 0 ? 1 : 0);
            $icon = ($arr_unread['icon'] === '' ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" class="icon tooltipper" alt="' . _('Topic') . '" title="' . _('Topic') . '">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($arr_unread['icon']) . '.gif" class="icon tooltipper" alt="' . htmlsafechars($arr_unread['icon']) . '" title="' . htmlsafechars($arr_unread['icon']) . '">');
            $first_post_text = bubble("<i class='icon-search icon' aria-hidden='true'></i>", format_comment($arr_unread['body'], true, true, false), '' . _('Last Post') . ' ' . _('Preview') . '');
            $topic_name = ($sticky ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/pinned.gif" class="icon tooltipper" alt="' . _('Pinned') . '" title="' . _('Pinned') . '"> ' : ' ') . ($topicpoll ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/poll.gif" class="icon tooltipper" alt="' . _('Poll') . '" title="' . _('Poll') . '"> ' : ' ') . ' <a class="is-link" href="?action=view_topic&amp;topic_id=' . (int) $arr_unread['topic_id'] . '" title="' . _('First post in thread') . '">' . htmlsafechars($arr_unread['topic_name']) . '</a><a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $arr_unread['topic_id'] . '&amp;page=0#' . (int) $arr_post_read[0] . '" title="' . _('First unread post in this thread') . '"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/last_post.gif" class="icon tooltipper" alt="First unread post" title="First unread post"></a>' . ($posted ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/posted.gif" class="icon tooltipper" alt="Posted" title="Posted"> ' : ' ') . ($subscriptions ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/subscriptions.gif" class="icon tooltipper" alt="' . _('Subscribed') . '" title="' . _('Subscribed') . '"> ' : ' ') . ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/new.gif" class="icon tooltipper" alt="' . _('New post in topic') . '!" title="' . _('New post in topic') . '!">';
            $HTMLOUT .= '<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/' . $topicpic . '.gif" class="icon tooltipper" alt="' . _('Topic') . '" title="' . _('Topic') . '"></td>
		<td>' . $icon . '</td>
		<td>
            ' . $topic_name . $first_post_text . '
            ' . $rpic . '
    		' . (!empty($arr_unread['topic_desc']) ? '&#9658; <span style="font-size: x-small;">' . htmlsafechars($arr_unread['topic_desc']) . '</span>' : '') . '
    		<hr>in: <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . (int) $arr_unread['forum_id'] . '">' . htmlsafechars($arr_unread['forum_name']) . '</a>
    		' . (!empty($arr_unread['topic_desc']) ? ' [ <span style="font-size: x-small;">' . htmlsafechars($arr_unread['topic_desc']) . '</span> ]' : '') . '
        </td>
		<td>' . number_format($arr_unread['post_count'] - 1) . '</td>
		<td>' . number_format((int) $arr_unread['views']) . '</td>
		<td>' . $thread_starter . '</td>
		</tr>';
        }
    }
    $HTMLOUT .= '</table>' . ($count > $perpage ? $menu_bottom : '');
}
