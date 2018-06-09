<?php

global $lang;
$colour    = $post_status_image    = '';
$ASC_DESC  = ((isset($_GET['ASC_DESC']) && 'ASC' === $_GET['ASC_DESC']) ? 'ASC ' : 'DESC ');
$res_count = sql_query('SELECT COUNT(p.id) FROM posts AS p 
								LEFT JOIN topics AS t ON p.topic_id = t.id 
								LEFT JOIN forums AS f ON f.id = t.forum_id 
								WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . '
								p.user_id = ' . $CURUSER['id'] . ' AND f.min_class_read <= ' . $CURUSER['class']) or sqlerr(__FILE__, __LINE__);
$arr_count = mysqli_fetch_row($res_count);
$count     = $arr_count[0];
//=== get stuff for the pager
$page                = isset($_GET['page']) ? (int) $_GET['page'] : 0;
$perpage             = isset($_GET['perpage']) ? (int) $_GET['perpage'] : 20;
$subscription_on_off = (isset($_GET['s']) ? (1 == $_GET['s'] ? '<br><div style="font-weight: bold;">' . $lang['fe_sub_to_topic'] . ' <img src="pic/forums/subscribe.gif" alt=" " width="25"></div>' : '<br><div style="font-weight: bold;">' . $lang['fe_unsub_to_topic'] . ' <img src="pic/forums/unsubscribe.gif" alt=" " width="25"></div>') : '');
list($menu, $LIMIT)  = pager_new($count, $perpage, $page, 'forums.php?action=view_my_posts' . (isset($_GET['perpage']) ? '&amp;perpage=' . $perpage : ''));
$res                 = sql_query('SELECT p.id AS post_id, p.topic_id, p.user_id, p.added, p.body, p.edited_by, p.edit_date, p.icon, p.post_title, p.bbcode, p.post_history, p.edit_reason, p.ip, p.status AS post_status, p.anonymous, t.id AS topic_id, t.topic_name, t.forum_id, t.sticky, t.locked, t.poll_id, t.status AS topic_status, f.name AS forum_name, f.description FROM posts AS p LEFT JOIN topics AS t ON p.topic_id = t.id LEFT JOIN forums AS f ON f.id = t.forum_id WHERE  ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')) . ' p.user_id = ' . $CURUSER['id'] . ' AND f.min_class_read <= ' . $CURUSER['class'] . ' ORDER BY p.id ' . $ASC_DESC . $LIMIT) or sqlerr(__FILE__, __LINE__);
$links               = '<span><a class="altlink" href="forums.php">' . $lang['fe_forums_main'] . '</a> |  ' . $mini_menu . '<br><br></span>';
$the_top_and_bottom  = '<tr><td  colspan="3">' . (($count > $perpage) ? $menu : '') . '</td></tr>';
$HTMLOUT .= '<h1>' . $count . ' ' . $lang['fe_posts_by'] . ' ' . format_username($CURUSER) . '</h1>' . $links . '
			  <div><a class="altlink" href="forums.php?action=view_my_posts" title="' . $lang['vmp_view_posts_new_to_old'] . '">' . $lang['vmp_sort_by_newest_posts_1st'] . '</a> || 
			  <a class="altlink" href="forums.php?action=view_my_posts&amp;ASC_DESC=ASC" title="' . $lang['vmp_view_posts_old_to_new'] . '">' . $lang['vmp_sort_by_oldest_posts_1st'] . '</a></div><br>';
$HTMLOUT .= '<a name="top"></a><table border="0" cellspacing="5" cellpadding="10" width="90%">' . $the_top_and_bottom;
//=== lets start the loop \o/
while ($arr = mysqli_fetch_assoc($res)) {
    //=== change colors
    $colour    = (++$colour) % 2;
    $class     = (0 == $colour ? 'one' : 'two');
    $class_alt = (0 == $colour ? 'two' : 'one');
    //=== topic status
    $topic_status = htmlsafechars($arr['topic_status']);
    switch ($topic_status) {
        case 'ok':
            $topic_status_image = '';
            break;

        case 'recycled':
            $topic_status_image = '<img src="pic/forums/recycle_bin.gif" alt="' . $lang['fe_recycled'] . '" title="' . $lang['fe_this_thread_is_currently'] . ' ' . $lang['fe_in_the_recycle_bin'] . '" />';
            break;

        case 'deleted':
            $topic_status_image = '<img src="pic/forums/delete_icon.gif" alt="' . $lang['fe_deleted'] . '" title="' . $lang['fe_this_thread_is_currently'] . ' ' . $lang['fe_deleted'] . '" />';
            break;
    }
    //=== post status
    $post_status = htmlsafechars($arr['post_status']);
    switch ($post_status) {
        case 'ok':
            $post_status       = $class;
            $post_status_image = '';
            break;

        case 'recycled':
            $post_status       = 'recycled';
            $post_status_image = ' <img src="pic/forums/recycle_bin.gif" alt="' . $lang['fe_recycled'] . '" title="' . $lang['fe_this_post_is_currently'] . ' ' . $lang['fe_in_the_recycle_bin'] . '" width="24px" />';
            break;

        case 'deleted':
            $post_status       = 'deleted';
            $post_status_image = ' <img src="pic/forums/delete_icon.gif" alt="' . $lang['fe_deleted'] . '" title="' . $lang['fe_this_post_is_currently'] . ' ' . $lang['fe_deleted'] . '" width="24px" />';
            break;

        case 'postlocked':
            $post_status       = 'postlocked';
            $post_status_image = ' <img src="pic/forums/thread_locked.gif" alt="' . $lang['fe_locked'] . '" title="' . $lang['fe_this_post_is_currently'] . ' ' . $lang['fe_locked'] . '" width="24px" />';
            break;
    }
    $post_icon  = ('' !== $arr['icon'] ? '<img src="pic/smilies/' . htmlsafechars($arr['icon']) . '.gif" alt="icon" title="icon" /> ' : '<img src="pic/forums/topic_normal.gif" alt="Normal Topic" title="Normal Topic" /> ');
    $post_title = ('' !== $arr['post_title'] ? ' <span style="font-weight: bold; font-size: x-small;">' . htmlsafechars($arr['post_title'], ENT_QUOTES) . '</span>' : '' . $lang['fe_link_to_post'] . '');
    $edited_by  = '';
    if ($arr['edit_date'] > 0) {
        $res_edited = sql_query('SELECT username FROM users WHERE id=' . sqlesc($arr['edited_by'])) or sqlerr(__FILE__, __LINE__);
        $arr_edited = mysqli_fetch_assoc($res_edited);
        if ('yes' == $arr['anonymous']) {
            if ($CURUSER['class'] < UC_STAFF && $arr['user_id'] != $CURUSER['id']) {
                $edited_by = '<br><br><br><span style="font-weight: bold; font-size: x-small;">' . $lang['fe_last_edited_by'] . ' <i>' . $lang['fe_anonymous'] . '</i> at ' . get_date($arr['edit_date'], '') . ' GMT ' . ('' !== $arr['edit_reason'] ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span style="font-weight: bold; font-size: x-small;">' : '') . '' . (($CURUSER['class'] >= UC_STAFF && '' !== $arr['post_history']) ? ' <a class="altlink" href="forums.php?action=view_post_history&amp;post_id=' . (int) $arr['post_id'] . '&amp;forum_id=' . (int) $arr['forum_id'] . '&amp;topic_id=' . (int) $arr['topic_id'] . '">' . $lang['fe_read_post_history'] . '</a></span><br>' : '');
            } else {
                $edited_by = '<br><br><br><span style="font-weight: bold; font-size: x-small;">' . $lang['fe_last_edited_by'] . ' <i>' . $lang['fe_anonymous'] . '</i>[<a class="altlink" href="userdetails.php?id=' . (int) $arr['edited_by'] . '">' . htmlsafechars($arr_edited['username']) . '</a>] at ' . get_date($arr['edit_date'], '') . ' GMT ' . ('' !== $arr['edit_reason'] ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span style="font-weight: bold; font-size: x-small;">' : '') . '' . (($CURUSER['class'] >= UC_STAFF && '' !== $arr['post_history']) ? ' <a class="altlink" href="forums.php?action=view_post_history&amp;post_id=' . (int) $arr['post_id'] . '&amp;forum_id=' . (int) $arr['forum_id'] . '&amp;topic_id=' . (int) $arr['topic_id'] . '">' . $lang['fe_read_post_history'] . '</a></span><br>' : '');
            }
        } else {
            $edited_by = '<br><br><br><span style="font-weight: bold; font-size: x-small;">' . $lang['fe_last_edited_by'] . ' <a class="altlink" href="userdetails.php?id=' . (int) $arr['edited_by'] . '">' . htmlsafechars($arr_edited['username']) . '</a> at ' . get_date($arr['edit_date'], '') . ' GMT ' . ('' !== $arr['edit_reason'] ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span style="font-weight: bold; font-size: x-small;">' : '') . '' . (($CURUSER['class'] >= UC_STAFF && '' !== $arr['post_history']) ? ' <a class="altlink" href="forums.php?action=view_post_history&amp;post_id=' . (int) $arr['post_id'] . '&amp;forum_id=' . (int) $arr['forum_id'] . '&amp;topic_id=' . (int) $arr['topic_id'] . '">' . $lang['fe_read_post_history'] . '</a></span><br>' : '');
        }
    }
    $body    = ('yes' == $arr['bbcode'] ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body']));
    $post_id = (int) $arr['post_id'];
    $width   = 100;
    $HTMLOUT .= '<tr><td colspan="3" align="left">' . $lang['fe_forum'] . ':  
			<a class="altlink" href="forums.php?action=view_forum&amp;forum_id=' . (int) $arr['forum_id'] . '" title="' . $lang['fe_link_to_forum'] . '">
			<span style="color: white;font-weight: bold;">' . htmlsafechars($arr['forum_name'], ENT_QUOTES) . '</span></a>&nbsp;&nbsp;&nbsp;&nbsp;
			' . $lang['fe_topic'] . ': <a class="altlink" href="forums.php?action=view_topic&amp;topic_id=' . (int) $arr['topic_id'] . '" title="' . $lang['fe_link_to_forum'] . '">
			<span style="color: white;font-weight: bold;">' . htmlsafechars($arr['topic_name'], ENT_QUOTES) . '</span></a>' . $topic_status_image . '</td></tr>
			<tr><td class="forum_head" align="left" width="100" valign="middle"><a name="' . $post_id . '"></a></td>
			<td class="forum_head" align="left" valign="middle">
			<span style="white-space:nowrap;">' . $post_icon . '
			<a class="altlink" href="forums.php?action=view_topic&amp;topic_id=' . (int) $arr['topic_id'] . '&amp;page=' . $page . '#' . $arr['post_id'] . '" title="' . $lang['fe_link_to_post'] . '">
			' . $post_title . '</a>&nbsp;&nbsp;' . $post_status_image . '
			&nbsp;&nbsp; ' . $lang['fe_posted_on'] . ': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span></td>
			<td class="forum_head" align="right" valign="middle"><span style="white-space:nowrap;"> 
			 <a href="forums.php?action=view_my_posts&amp;page=' . $page . '#top"><img src="pic/forums/up.gif" alt="' . $lang['fe_top'] . '" /></a> 
			 <a href="forums.php?action=view_my_posts&amp;page=' . $page . '#bottom"><img src="pic/forums/down.gif" alt="' . $lang['fe_bottom'] . '" /></a> 
			</span></td>
			</tr>	
			<tr>
		   <td class="' . $class_alt . '" width="100px" valign="top">' . ('yes' == $arr['anonymous'] ? '<img style="max-width:' . $width . 'px;" src="' . $site_config['pic_baseurl'] . 'anonymous_1.jpg" alt="avatar" />' : avatar_stuff($CURUSER)) . '<br>' . ('yes' == $arr['anonymous'] ? '<i>' . $lang['fe_anonymous'] . '</i>' : format_username($CURUSER)) . ('yes' == $arr['anonymous'] || '' == $CURUSER['title'] ? '' : '<br><span style=" font-size: xx-small;">[' . htmlsafechars($CURUSER['title']) . ']</span>') . '<br><span style="font-weight: bold;">' . ('yes' == $arr['anonymous'] ? '' : get_user_class_name($CURUSER['class'])) . '</span><br></td>
		<td class="' . $post_status . '" align="left" valign="top" colspan="2">' . $body . $edited_by . '</td>
		</tr>
			<tr><td class="' . $class_alt . '" align="right" valign="middle" colspan="3"></td></tr>';
} //=== end while loop
$HTMLOUT .= $the_top_and_bottom . '</table><a name="bottom"></a><br>' . $links . '<br>';
