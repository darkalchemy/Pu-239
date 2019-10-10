<?php

declare(strict_types = 1);

use Pu239\Database;

global $container, $site_config, $CURUSER;

$posts = $lppostid = $topicpoll = $rpic = $body = '';
$HTMLOUT .= $mini_menu . '<h1 class="has-text-centered">Subscribed Forums for ' . format_username((int) $CURUSER['id']) . '</h1>';
$fluent = $container->get(Database::class);
$count = $fluent->from('subscriptions')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->where('user_id = ?', $CURUSER['id'])
                ->fetch('count');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/forums.php'>" . _('Forums') . '</a>',
    "<a href='{$site_config['paths']['baseurl']}/forums.php?action=subscriptions'>" . _('Subscriptions') . '</a>',
];
if ($count === 0) {
    $HTMLOUT .= main_div("
        <h1 class='has-text-centered'>" . _('No Subscriptions Found') . '!</h1>
        <p>' . _("You are not yet subscribed to any forums... To subscribe to a forum, click the 'Subscribe to this Forum' link on the thread page") . '.</p>
		<p>' . _fe("To be notified via PM when there is a new post, go to your {0}profile{1} page and set 'PM on Subscriptions' to yes.", "<a class='is-link has-text-success' href='usercp.php?action=default'>", '</a>') . '</p>', '', 'padding20');

    return;
}
$page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
$perpage = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20;
$link = $site_config['paths']['baseurl'] . '/forums.php?action=subscriptions&amp;' . (isset($_GET['perpage']) ? "perpage={$perpage}$&amp;" : '');
$pager = pager($perpage, $count, $link);
$menu_top = $pager['pagertop'];
$menu_bottom = $pager['pagerbottom'];
$LIMIT = $pager['limit'];

$res = sql_query('SELECT s.id AS subscribed_id, t.id AS topic_id, t.topic_name, t.topic_desc, t.last_post, t.views, t.post_count, t.locked, t.sticky, t.poll_id, t.user_id, t.anonymous AS tan, p.id AS post_id, p.added, p.user_id, p.anonymous AS pan, u.username, u.id, u.class, u.donor, u.warned, u.status, u.chatpost, u.leechwarn, u.pirate, u.king, u.perms, u.offensive_avatar FROM subscriptions AS s LEFT JOIN topics AS t ON s.topic_id=t.id LEFT JOIN posts AS p ON t.last_post = p.id LEFT JOIN forums AS f ON f.id=t.forum_id LEFT JOIN users AS u ON u.id=p.user_id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' s.user_id=' . $CURUSER['id'] . ' AND f.min_class_read < ' . sqlesc($CURUSER['class']) . ' AND s.user_id=' . sqlesc($CURUSER['id']) . '  ORDER BY t.id DESC ' . $LIMIT) or sqlerr(__FILE__, __LINE__);
while ($topic_arr = mysqli_fetch_assoc($res)) {
    $topic_id = (int) $topic_arr['topic_id'];
    $locked = $topic_arr['locked'] === 'yes';
    $sticky = $topic_arr['sticky'] === 'yes';
    $topic_poll = $topic_arr['poll_id'] > 0;
    $last_post_username = ($topic_arr['pan'] === '0' && !empty($topic_arr['username']) ? format_username((int) $topic_arr['id']) : '[<i>' . get_anonymous_name() . '</i>]');
    $last_post_id = (int) $topic_arr['last_post'];
    $first_post_res = sql_query('SELECT p.added, p.icon, p.body, p.user_id, p.anonymous, u.id, u.username, u.class, u.donor, u.warned, u.status, u.chatpost, u.leechwarn, u.pirate, u.king FROM posts AS p LEFT JOIN users AS u ON p.user_id=u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND' : ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? 'p.status != \'deleted\' AND' : '')) . ' topic_id=' . sqlesc($topic_id) . ' ORDER BY id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $first_post_arr = mysqli_fetch_assoc($first_post_res);
    if ($topic_arr['tan'] === '1') {
        if ($CURUSER['class'] < UC_STAFF && $first_post_arr['user_id'] != $CURUSER['id']) {
            $thread_starter = (!empty($first_post_arr['username']) ? '<i>' . get_anonymous_name() . '</i>' : _('Lost') . ' [' . (int) $first_post_arr['id'] . ']') . '<br>' . get_date((int) $first_post_arr['added'], '');
        } else {
            $thread_starter = (!empty($first_post_arr['username']) ? '<i>' . get_anonymous_name() . '</i> [' . format_username((int) $first_post_arr['id']) . ']' : _('Lost') . ' [' . (int) $first_post_arr['id'] . ']') . '<br>' . get_date((int) $first_post_arr['added'], '');
        }
    } else {
        $thread_starter = (!empty($first_post_arr['username']) ? format_username((int) $first_post_arr['id']) : _('Lost') . ' [' . (int) $first_post_arr['id'] . ']') . '<br>' . get_date((int) $first_post_arr['added'], '');
    }
    $icon = (empty($first_post_arr['icon']) ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" class="icon tooltipper" alt="' . _('Topic') . '" title="' . _('Topic') . '">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($first_post_arr['icon']) . '.gif" class="icon tooltipper" alt="' . htmlsafechars($first_post_arr['icon']) . '" title="' . htmlsafechars($first_post_arr['icon']) . '">');
    $first_post_text = bubble("<i class='icon-search icon' aria-hidden='true'></i>", format_comment($first_post_arr['body'], true, true, false), _('First Post') . ' ' . _('Preview') . '');
    $last_unread_post_res = sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    $last_unread_post_arr = mysqli_fetch_row($last_unread_post_res);
    $did_i_post_here = sql_query('SELECT user_id FROM posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    $posted = (mysqli_num_rows($did_i_post_here) > 0 ? 1 : 0);
    $total_pages = floor($count / $perpage);
    switch (true) {
        case $total_pages == 0:
            $multi_pages = '';
            break;

        case $total_pages > 11:
            $multi_pages = ' <span style="font-size: xx-small;"> <img src="' . $site_config['paths']['images_baseurl'] . 'forums/multipage.gif" class="icon tooltipper" alt="+" title="+">';
            for ($i = 1; $i < 5; ++$i) {
                $multi_pages .= ' <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $i . '">' . $i . '</a>';
            }
            $multi_pages .= ' ... ';
            for ($i = ($total_pages - 2); $i <= $total_pages; ++$i) {
                $multi_pages .= ' <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $i . '">' . $i . '</a>';
            }
            $multi_pages .= '</span>';
            break;

        case $total_pages < 11:
            $multi_pages = ' <span style="font-size: xx-small;"> <img src="' . $site_config['paths']['images_baseurl'] . 'forums/multipage.gif" class="icon tooltipper" alt="+" title="+">';
            for ($i = 1; $i < $total_pages; ++$i) {
                $multi_pages .= ' <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $i . '">' . $i . '</a>';
            }
            $multi_pages .= '</span>';
            break;
    }
    $new = ($topic_arr['added'] > (TIME_NOW - $site_config['forum_config']['readpost_expiry'])) ? (!$last_unread_post_arr || $lppostid > $last_unread_post_arr[0]) : 0;
    $topicpic = ($posts < 30 ? ($locked ? ($new ? 'lockednew' : 'locked') : ($new ? 'topicnew' : 'topic')) : ($locked ? ($new ? 'lockednew' : 'locked') : ($new ? 'hot_topic_new' : 'hot_topic')));
    $topic_name = ($sticky ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/pinned2.gif" class="icon tooltipper" alt="' . _('Pinned') . '" title="' . _('Pinned') . '"> ' : ' ') . ($topicpoll ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/poll.gif" class="icon tooltipper" alt="' . _('Poll') . '" title="' . _('Poll') . '"> ' : ' ') . ' <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '">' . format_comment($topic_arr['topic_name']) . '</a> ' . $multi_pages;
    $body .= '<tr>
		<td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/' . $topicpic . '.gif" class="icon tooltipper" alt="' . _('Topic') . '" title="' . _('Topic') . '"></td>
		<td>' . $icon . '</td>
		<td>
	    	' . $topic_name . $first_post_text . ($new ? ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/new.gif" class="icon tooltipper" alt="' . _('New post in topic') . '!" title="' . _('New post in topic') . '!">' : '') . '</td>
    		' . $rpic . '
		    ' . (!empty($topic_arr['topic_desc']) ? '&#9658; <span style="font-size: x-small;">' . format_comment($topic_arr['topic_desc']) . '</span>' : '') . '
        </td>
		<td>' . $thread_starter . '</td>
		<td>' . number_format((int) $topic_arr['post_count'] - 1) . '</td>
		<td>' . number_format((int) $topic_arr['views']) . '</td>
		<td><span style="white-space:nowrap;">' . get_date((int) $topic_arr['added'], '') . '</span><br>by&nbsp;' . $last_post_username . '</td>
		<td><a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=p' . $last_post_id . '#' . $last_post_id . '">
		<img src="' . $site_config['paths']['images_baseurl'] . 'forums/last_post.gif" class="icon tooltipper" alt="Last post" title="Last post"></a></td>
		<td><input type="checkbox" name="remove[]" value="' . (int) $topic_arr['subscribed_id'] . '"></td>
		</tr>';
}

$HTMLOUT .= ($count > $perpage ? $menu_top : '') . '<form action="' . $site_config['paths']['baseurl'] . '/forums.php?action=delete_subscription" method="post" name="checkme" accept-charset="utf-8">';
$heading = '
		<tr>
		<th><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic.gif" class="icon tooltipper" alt="' . _('Topic') . '" title="' . _('Topic') . '"></th>
		<th><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" class="icon tooltipper" alt=' . _('Thread Icon') . '" title=' . _('Thread Icon') . '"></th>
		<th>' . _('Topic') . '</th>
		<th>' . _('Started By') . '</th>
		<th>' . _('Replies') . '</th>
		<th>' . _('Views') . '</th>
		<th>' . _('Last Post') . '</th>
		<th><img src="' . $site_config['paths']['images_baseurl'] . 'forums/last_post.gif" class="icon tooltipper" alt="Last post" title="Last post"></th>
		<th> <input type="checkbox" id="checkThemAll" class="tooltipper" title="Select All"></th>
		</tr>';

$HTMLOUT .= main_table($body, $heading) . '
		<div class="has-text-centered margin20">
		    <input type="submit" name="button" class="button is-small" value="' . _('Remove') . ' Selected">
        </div>
        </form>' . ($count > $perpage ? $menu_bottom : '');
