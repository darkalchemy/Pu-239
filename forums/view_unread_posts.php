<?php

global $lang;
//=== start page
$colour = $topicpoll = $topic_status_image = '';
$links  = '<span><a class="altlink" href="' . $site_config['baseurl'] . '/forums.php">' . $lang['fe_forums_main'] . '</a> |  ' . $mini_menu . '<br><br></span>';
$HTMLOUT .= '<h1>' . $lang['vup_unread_post_since_visit'] . '</h1>' . $links;
$time      = $readpost_expiry;
$res_count = sql_query('SELECT t.id, t.last_post FROM topics AS t LEFT JOIN posts AS p ON t.last_post = p.id LEFT JOIN forums AS f ON f.id = t.forum_id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' f.min_class_read <= ' . $CURUSER['class'] . ' AND p.added > ' . $time);
//=== lets do the loop / Check if post is read / get count there must be a beter way to do this lol
$count = 0;
while ($arr_count = mysqli_fetch_assoc($res_count)) {
    $res_post_read = sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_count['id']));
    $arr_post_read = mysqli_fetch_row($res_post_read);
    if ($arr_post_read[0] < $arr_count['last_post']) {
        ++$count;
    }
}
//=== nothing here? kill the page
if (0 == $count) {
    $HTMLOUT .= '<br><br><table border="0" cellspacing="10" cellpadding="10" width="400px">
    <tr><tdalign="center">
    ' . $lang['fe_no_unread_posts'] . '
    </td></tr>
    <tr><td>
    ' . $lang['fe_you_are_uptodate_topics'] . '.<br><br>
    </td></tr></table><br><br>';
    $HTMLOUT .= $links . '<br>';
} else {
    //=== get stuff for the pager
    $page               = isset($_GET['page']) ? (int) $_GET['page'] : 0;
    $perpage            = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20;
    list($menu, $LIMIT) = pager_new($count, $perpage, $page, $site_config['baseurl'] . '/forums.php?action=view_unread_posts' . (isset($_GET['perpage']) ? '&amp;perpage=' . $perpage : ''));
    //=== top and bottom stuff
    $the_top_and_bottom = '<br><table border="0" cellspacing="0" cellpadding="0" width="90%">
   <tr><td  valign="middle">' . (($count > $perpage) ? $menu : '') . '</td>
   </tr></table>';
    //=== main huge query:
    $res_unread = sql_query('SELECT t.id AS topic_id, t.topic_name AS topic_name, t.last_post, t.post_count, t.views, t.topic_desc, t.locked, t.sticky, t.poll_id, t.forum_id, t.rating_sum, t.num_ratings, t.status, t.anonymous AS tan, f.name AS forum_name, f.description AS forum_desc, p.post_title, p.body, p.icon, p.user_id, p.anonymous AS pan,
   u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king FROM topics AS t LEFT JOIN posts AS p ON t.last_post = p.id LEFT JOIN forums AS f ON f.id = t.forum_id LEFT JOIN users AS u ON u.id = t.user_id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' f.min_class_read <= ' . $CURUSER['class'] . ' AND p.added > ' . $time . ' ORDER BY t.last_post DESC ' . $LIMIT);
    $HTMLOUT .= $the_top_and_bottom . '<table border="0" cellspacing="5" cellpadding="10" width="90%">
    <tr>
    <td valign="middle" width="10"><img src="' . $site_config['pic_baseurl'] . 'forums/topic.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" /></td>
    <td valign="middle" width="10"><img src="' . $site_config['pic_baseurl'] . 'forums/topic_normal.gif" alt=' . $lang['fe_thread_icon'] . '" title=' . $lang['fe_thread_icon'] . '" /></td>
    <td align="left">' . $lang['fe_new_posts'] . '!</td>
    <td width="10">' . $lang['fe_replies'] . '</td>
    <td width="10">' . $lang['fe_views'] . '</td>
    <td>' . $lang['fe_started_by'] . '</td>
    </tr>';
    //=== ok let's show the posts...
    while ($arr_unread = mysqli_fetch_assoc($res_unread)) {
        $res_post_read = sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_unread['topic_id']));
        $arr_post_read = mysqli_fetch_row($res_post_read);
        if ($arr_post_read[0] < $arr_unread['last_post']) {
            //=== change colors
            $colour = (++$colour) % 2;
            $class  = (0 == $colour ? 'one' : 'two');
            //=== topic status
            $topic_status = htmlsafechars($arr_unread['status']);
            switch ($topic_status) {
                case 'ok':
                    $topic_status_image = '';
                    break;

                case 'recycled':
                    $topic_status_image = '<img src="' . $site_config['pic_baseurl'] . 'forums/recycle_bin.gif" alt="' . $lang['fe_recycled'] . '" title="' . $lang['fe_this_thread_is_currently'] . ' ' . $lang['fe_in_the_recycle_bin'] . '" />';
                    break;

                case 'deleted':
                    $topic_status_image = '<img src="' . $site_config['pic_baseurl'] . 'forums/delete_icon.gif" alt="' . $lang['fe_deleted'] . '" title="' . $lang['fe_this_thread_is_currently'] . ' ' . $lang['fe_deleted'] . '" />';
                    break;
            }
            $locked                  = 'yes' == $arr_unread['locked'];
            $sticky                  = 'yes' == $arr_unread['sticky'];
            $topic_poll              = $arr_unread['poll_id'] > 0;
            $first_unread_poster     = sql_query('SELECT added FROM posts WHERE topic_id=' . sqlesc($arr_unread['topic_id']) . ' ORDER BY id ASC LIMIT 1');
            $first_unread_poster_arr = mysqli_fetch_row($first_unread_poster);
            //==Anonymous
            if ('yes' == $arr_unread['tan']) {
                if ($CURUSER['class'] < UC_STAFF && $arr_unread['user_id'] != $CURUSER['id']) {
                    $thread_starter = ('' !== $arr_unread['username'] ? '<i>' . $lang['fe_anonymous'] . '</i>' : '' . $lang['fe_lost'] . ' [' . (int) $arr_unread['id'] . ']') . '<br>' . get_date($first_unread_poster_arr[0], '');
                } else {
                    $thread_starter = ('' !== $arr_unread['username'] ? '<i>' . $lang['fe_anonymous'] . '</i> [' . format_username($arr_unread) . ']' : '' . $lang['fe_lost'] . ' [' . (int) $arr_unread['id'] . ']') . '<br>' . get_date($first_unread_poster_arr[0], '');
                }
            } else {
                $thread_starter = ('' !== $arr_unread['username'] ? format_username($arr_unread) : '' . $lang['fe_lost'] . ' [' . (int) $arr_unread['id'] . ']') . '<br>' . get_date($first_unread_poster_arr[0], '');
            }
            $topicpic        = ($arr_unread['post_count'] < 30 ? ($locked ? 'lockednew' : 'topicnew') : ($locked ? 'lockednew' : 'hot_topic_new'));
            $rpic            = (0 != $arr_unread['num_ratings'] ? ratingpic_forums(round($arr_unread['rating_sum'] / $arr_unread['num_ratings'], 1)) : '');
            $did_i_post_here = sql_query('SELECT user_id FROM posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_unread['topic_id']));
            $posted          = (mysqli_num_rows($did_i_post_here) > 0 ? 1 : 0);
            $sub             = sql_query('SELECT user_id FROM subscriptions WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($arr_unread['topic_id']));
            $subscriptions   = (mysqli_num_rows($sub) > 0 ? 1 : 0);
            $icon            = ('' == $arr_unread['icon'] ? '<img src="' . $site_config['pic_baseurl'] . 'forums/topic_normal.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" />' : '<img src="' . $site_config['pic_baseurl'] . 'smilies/' . htmlsafechars($arr_unread['icon']) . '.gif" alt="' . $lang['fe_unread'] . '" title="' . $lang['fe_unread'] . '" />');
            $first_post_text = bubble(' <img src="' . $site_config['pic_baseurl'] . 'forums/mg.gif" height="14" alt="' . $lang['fe_preview'] . '" />', format_comment($arr_unread['body'], true, false, false), '' . $lang['fe_last_post'] . ' ' . $lang['fe_preview'] . '');
            $topic_name      = ($sticky ? '<img src="' . $site_config['pic_baseurl'] . 'forums/pinned.gif" alt="' . $lang['fe_pinned'] . '" title="' . $lang['fe_pinned'] . '" /> ' : ' ') . ($topicpoll ? '<img src="' . $site_config['pic_baseurl'] . 'forums/poll.gif" alt="' . $lang['fe_poll'] . '" title="' . $lang['fe_poll'] . '" /> ' : ' ') . ' <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $arr_unread['topic_id'] . '" title="' . $lang['fe_1st_post_in_tread'] . '">' . htmlsafechars($arr_unread['topic_name'], ENT_QUOTES) . '</a><a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $arr_unread['topic_id'] . '&amp;page=0#' . (int) $arr_post_read[0] . '" title="' . $lang['fe_1st_unread_post_topic'] . '"><img src="' . $site_config['pic_baseurl'] . 'forums/last_post.gif" alt="' . $lang['fe_last_post'] . '" title="' . $lang['fe_last_post'] . '" /></a>' . ($posted ? '<img src="' . $site_config['pic_baseurl'] . 'forums/posted.gif" alt="Posted" title="Posted" /> ' : ' ') . ($subscriptions ? '<img src="' . $site_config['pic_baseurl'] . 'forums/subscriptions.gif" alt="' . $lang['fe_subscribed'] . '" title="' . $lang['fe_subscribed'] . '" /> ' : ' ') . ' <img src="' . $site_config['pic_baseurl'] . 'forums/new.gif" alt="' . $lang['fe_new_post_in_topic'] . '!" title="' . $lang['fe_new_post_in_topic'] . '!" />';
            //=== print here
            $HTMLOUT .= '<tr>
        <td class="' . $class . '"><img src="' . $site_config['pic_baseurl'] . 'forums/' . $topicpic . '.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" /></td>
        <td class="' . $class . '">' . $icon . '</td>
        <td align="left" valign="middle" class="' . $class . '">
        <table border="0" cellspacing="0" cellpadding="0">
        <tr>
        <td  class="' . $class . '" align="left">' . $topic_name . $first_post_text . ' 
         [ <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=clear_unread_post&amp;topic_id=' . (int) $arr_unread['topic_id'] . '&amp;last_post=' . (int) $arr_unread['last_post'] . '" title="' . $lang['fe_remove'] . ' ' . $lang['vup_remove_topic_from_unread_list'] . '.">' . $lang['fe_remove'] . '</a> ] ' . $topic_status_image . '</td>
        <td class="' . $class . '" align="right">' . $rpic . '</td>
        </tr>
        </table>
        ' . ('' !== $arr_unread['topic_desc'] ? '&#9658; <span style="font-size: x-small;">' . htmlsafechars($arr_unread['topic_desc'], ENT_QUOTES) . '</span>' : '') . '  
        <hr>in: <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . (int) $arr_unread['forum_id'] . '">' . htmlsafechars($arr_unread['forum_name'], ENT_QUOTES) . '</a>
        ' . ('' !== $arr_unread['topic_desc'] ? ' [ <span style="font-size: x-small;">' . htmlsafechars($arr_unread['topic_desc'], ENT_QUOTES) . '</span> ]' : '') . '</td>
        <td class="' . $class . '">' . number_format($arr_unread['post_count'] - 1) . '</td>
        <td class="' . $class . '">' . number_format($arr_unread['views']) . '</td>
        <td class="' . $class . '">' . $thread_starter . '</td>
        </tr>';
        }
    }
    $HTMLOUT .= '</table>' . $the_top_and_bottom . '<br><br>' . $links . '<br>';
}
