<?php

global $lang;
$child_boards = $now_viewing = $colour = '';
$forum_id     = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));
if (!is_valid_id($forum_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
//=== stupid query just to get overforum name :'(
$over_forums_res = sql_query('SELECT name, min_class_view FROM over_forums WHERE id =' . sqlesc($forum_id));
$over_forums_arr = mysqli_fetch_assoc($over_forums_res);
//=== make sure they can be here
if ($CURUSER['class'] < $over_forums_arr['min_class_view']) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
$location_bar = '<h1><a class="altlink" href="' . $site_config['baseurl'] . '/index.php">' . $site_config['site_name'] . '</a>  <img src="' . $site_config['pic_baseurl'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" /> 
	<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php">' . $lang['fe_forums'] . '</a> <img src="' . $site_config['pic_baseurl'] . 'arrow_next.gif" alt="&#9658;" title="&#9658;" /> 
	<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=section_view&amp;forum_id=' . $forum_id . '">' . htmlsafechars($over_forums_arr['name'], ENT_QUOTES) . '</a></h1>' . $mini_menu . '<br><br>';
$HTMLOUT .= $location_bar;
//=== top and bottom stuff
$HTMLOUT .= '<br><table border="0" cellspacing="0" cellpadding="5" width="90%">
	<tr>
	<td align="left" colspan="4"><span style="color: white;">' . $lang['sv_section_view_for'] . ' ' . htmlsafechars($over_forums_arr['name'], ENT_QUOTES) . '</span></td>
   </tr>';
//=== basic query
$forums_res = sql_query('SELECT name AS forum_name, description AS forum_description, id AS forum_id, post_count, topic_count FROM forums WHERE min_class_read < ' . sqlesc($CURUSER['class']) . ' AND forum_id=' . sqlesc($forum_id) . ' AND parent_forum = 0 ORDER BY sort');
//=== lets start the loop \o/
while ($forums_arr = mysqli_fetch_assoc($forums_res)) {
    //=== change colors
    $colour = (++$colour) % 2;
    $class  = (0 == $colour ? 'one' : 'two');
    //=== Get last post info
    if (($last_post_arr = $cache->get('sv_last_post_' . $forums_arr['forum_id'] . '_' . $CURUSER['class'])) === false) {
        $last_post_arr = mysqli_fetch_assoc(sql_query('SELECT t.last_post, t.topic_name, t.id AS topic_id, t.anonymous AS tan, p.user_id, p.added, p.anonymous AS pan, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.perms, u.offensive_avatar FROM topics AS t LEFT JOIN posts AS p ON t.last_post = p.id LEFT JOIN users AS u ON p.user_id = u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\' AND' : '')) . ' forum_id=' . sqlesc($forums_arr['forum_id']) . ' ORDER BY last_post DESC LIMIT 1'));
        $cache->set('sv_last_post_' . $forums_arr['forum_id'] . '_' . $CURUSER['class'], $last_post_arr, $site_config['expires']['sv_last_post']);
    }
    //=== only do more if there is a stuff here...
    if ($last_post_arr['last_post'] > 0) {
        //=== get the last post read by CURUSER
        if (($last_read_post_arr = $cache->get('sv_last_read_post_' . $last_post_arr['topic_id'] . '_' . $CURUSER['id'])) === false) {
            $last_read_post_arr = mysqli_fetch_row(sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($last_post_arr['topic_id'])));
            $cache->set('sv_last_read_post_' . $last_post_arr['topic_id'] . '_' . $CURUSER['id'], $last_read_post_arr, $site_config['expires']['sv_last_read_post']);
        }
        $image_and_link = ($last_post_arr['added'] > (TIME_NOW - $readpost_expiry)) ? (!$last_read_post_arr || $last_post_arr['last_post'] > $last_read_post_arr[0]) : 0;
        $img            = ($image_and_link ? 'unlockednew' : 'unlocked');
        //=== get '.$lang['sv_child_boards'].' if any
        $keys['child_boards'] = 'sv_child_boards_' . $forums_arr['forum_id'] . '_' . $CURUSER['class'];
        if (($child_boards_cache = $cache->get($keys['child_boards'])) === false) {
            $child_boards       = '';
            $child_boards_cache = [];
            $res                = sql_query('SELECT name, id FROM forums WHERE parent_forum = ' . sqlesc($forums_arr['forum_id']) . ' ORDER BY sort ASC') or sqlerr(__FILE__, __LINE__);
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($child_boards) {
                    $child_boards .= ', ';
                }
                $child_boards .= '<a href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . (int) $arr['id'] . '" title="click to view!" class="altlink">' . htmlsafechars($arr['name'], ENT_QUOTES) . '</a>';
            }
            $child_boards_cache['child_boards'] = $child_boards;
            $cache->set($keys['child_boards'], $child_boards_cache, $site_config['expires']['sv_child_boards']);
        }
        $child_boards = $child_boards_cache['child_boards'];
        if ('' !== $child_boards) {
            $child_boards = '<hr><span style="font-size: xx-small;">' . $lang['sv_child_boards'] . ':</span> ' . $child_boards;
        }
        //=== now_viewing
        $keys['now_viewing'] = 'now_viewing_section_view';
        if (($now_viewing_cache = $cache->get($keys['now_viewing'])) === false) {
            $nowviewing        = '';
            $now_viewing_cache = [];
            $res               = sql_query('SELECT n_v.user_id, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.perms FROM now_viewing AS n_v LEFT JOIN users AS u ON n_v.user_id = u.id WHERE forum_id = ' . sqlesc($forums_arr['forum_id'])) or sqlerr(__FILE__, __LINE__);
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($nowviewing) {
                    $nowviewing .= ",\n";
                }
                $nowviewing .= ($arr['perms'] & bt_options::PERMS_STEALTH ? '<i>' . $lang['fe_unkn0wn'] . '</i>' : format_username($arr['user_id']));
            }
            $now_viewing_cache['now_viewing'] = $nowviewing;
            $cache->set($keys['now_viewing'], $now_viewing_cache, $site_config['expires']['section_view']);
        }
        if (!$now_viewing_cache['now_viewing']) {
            $now_viewing_cache['now_viewing'] = $lang['fe_there_not_been_active_visit_15'];
        }
        $now_viewing = $now_viewing_cache['now_viewing'];
        if ('' !== $now_viewing) {
            $now_viewing = '<hr><span style="font-size: xx-small;">' . $lang['sv_now_viewing'] . ':</span>' . $now_viewing;
        }
        if ('yes' == $last_post_arr['tan']) {
            if ($CURUSER['class'] < UC_STAFF && $last_post_arr['user_id'] != $CURUSER['id']) {
                $last_post = '' . $lang['fe_last_post_by'] . ': ' . $lang['sv_anonymous_in'] . ' &#9658; <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=p' . (int) $last_post_arr['last_post'] . '#' . (int) $last_post_arr['last_post'] . '" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '">
		<span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . '</span></a><br>
		' . get_date($last_post_arr['added'], '') . '<br>';
            } else {
                $last_post = '' . $lang['fe_last_post_by'] . ': ' . $lang['fe_anonymous'] . ' [' . format_username($last_post_arr['user_id']) . '] <span style="font-size: x-small;"> [ ' . get_user_class_name($last_post_arr['class']) . ' ] </span><br>
		in &#9658; <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=p' . (int) $last_post_arr['last_post'] . '#' . (int) $last_post_arr['last_post'] . '" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '">
		<span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . '</span></a><br>
		' . get_date($last_post_arr['added'], '') . '<br>';
            }
        } else {
            $last_post = '' . $lang['fe_last_post_by'] . ': ' . format_username($last_post_arr['user_id']) . ' <span style="font-size: x-small;"> [ ' . get_user_class_name($last_post_arr['class']) . ' ] </span><br>
		in &#9658; <a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=p' . (int) $last_post_arr['last_post'] . '#' . (int) $last_post_arr['last_post'] . '" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '">
		<span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . '</span></a><br>
		' . get_date($last_post_arr['added'], '') . '<br>';
        }
    } else {
        $img         = 'unlocked';
        $now_viewing = '';
        $last_post   = $lang['fe_na'];
    }
    $HTMLOUT .= '<tr>
		<td class="' . $class . '" valign="middle" width="30"><img src="' . $site_config['pic_baseurl'] . 'forums/' . $img . '.gif" alt="' . $site_config['pic_baseurl'] . 'forums/' . $img . '.gif" title="' . $site_config['pic_baseurl'] . 'forums/' . $img . '.gif" /></td>
		<td class="' . $class . '" align="left">
		<a class="altlink" href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . (int) $forums_arr['forum_id'] . '">' . htmlsafechars($forums_arr['forum_name'], ENT_QUOTES) . '</a>
		<br>' . htmlsafechars($forums_arr['forum_description'], ENT_QUOTES) . $child_boards . $now_viewing . '</td>
		<td class="' . $class . '" width="80">' . number_format($forums_arr['post_count']) . ' ' . $lang['fe_posts'] . '<br>' . number_format($forums_arr['topic_count']) . ' ' . $lang['fe_topics'] . '</td>
		<td class="' . $class . '" align="left" width="140">
		<span style="white-space:nowrap;">' . $last_post . '</span>
		</td>
		</tr>';
}
$HTMLOUT .= '</table><br>' . $location_bar;
