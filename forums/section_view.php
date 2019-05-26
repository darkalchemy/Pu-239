<?php

declare(strict_types = 1);

use Pu239\Cache;

$child_boards = $now_viewing = $colour = '';
$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));
if (!is_valid_id($forum_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

$over_forums_res = sql_query('SELECT name, min_class_view FROM over_forums WHERE id = ' . sqlesc($forum_id)) or sqlerr(__FILE__, __LINE__);
$over_forums_arr = mysqli_fetch_assoc($over_forums_res);
global $container, $CURUSER, $site_config;

if ($CURUSER['class'] < $over_forums_arr['min_class_view']) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

$HTMLOUT .= $mini_menu;

$HTMLOUT .= "
    <h1 class='has-text-centered'pan>{$lang['sv_section_view_for']} " . htmlsafechars($over_forums_arr['name']) . '</h1>';
$forums_res = sql_query('SELECT name AS forum_name, description AS forum_description, id AS forum_id, post_count, topic_count FROM forums WHERE min_class_read < ' . sqlesc($CURUSER['class']) . ' AND forum_id=' . sqlesc($forum_id) . ' AND parent_forum = 0 ORDER BY sort') or sqlerr(__FILE__, __LINE__);
$body = '';
$cache = $container->get(Cache::class);
while ($forums_arr = mysqli_fetch_assoc($forums_res)) {
    //=== Get last post info
    if (($last_post_arr = $cache->get('sv_last_post_' . $forums_arr['forum_id'] . '_' . $CURUSER['class'])) === false) {
        $query = sql_query('SELECT t.last_post, t.topic_name, t.id AS topic_id, t.anonymous AS tan, p.user_id, p.added, p.anonymous AS pan, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.perms, u.offensive_avatar FROM topics AS t LEFT JOIN posts AS p ON t.last_post = p.id LEFT JOIN users AS u ON p.user_id=u.id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? 'p.status != \'deleted\' AND t.status != \'deleted\' AND' : '')) . ' forum_id=' . sqlesc($forums_arr['forum_id']) . ' ORDER BY last_post DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $last_post_arr = mysqli_fetch_assoc($query);
        $cache->set('sv_last_post_' . $forums_arr['forum_id'] . '_' . $CURUSER['class'], $last_post_arr, $site_config['expires']['sv_last_post']);
    }
    //=== only do more if there is a stuff here...
    if ($last_post_arr['last_post'] > 0) {
        //=== get the last post read by CURUSER
        if (($last_read_post_arr = $cache->get('sv_last_read_post_' . $last_post_arr['topic_id'] . '_' . $CURUSER['id'])) === false) {
            $query = sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($last_post_arr['topic_id'])) or sqlerr(__FILE__, __LINE__);
            $last_read_post_arr = mysqli_fetch_row($query);
            $cache->set('sv_last_read_post_' . $last_post_arr['topic_id'] . '_' . $CURUSER['id'], $last_read_post_arr, $site_config['expires']['sv_last_read_post']);
        }
        $image_and_link = ($last_post_arr['added'] > (TIME_NOW - $site_config['forum_config']['readpost_expiry'])) ? (!$last_read_post_arr || $last_post_arr['last_post'] > $last_read_post_arr[0]) : 0;
        $img = ($image_and_link ? 'unlockednew' : 'unlocked');
        //=== get '.$lang['sv_child_boards'].' if any
        $keys['child_boards'] = 'sv_child_boards_' . $forums_arr['forum_id'] . '_' . $CURUSER['class'];
        if (($child_boards_cache = $cache->get($keys['child_boards'])) === false) {
            $child_boards = '';
            $child_boards_cache = [];
            $res = sql_query('SELECT name, id FROM forums WHERE parent_forum = ' . sqlesc($forums_arr['forum_id']) . ' ORDER BY sort ASC') or sqlerr(__FILE__, __LINE__);
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($child_boards) {
                    $child_boards .= ', ';
                }
                $child_boards .= '<a href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . (int) $arr['id'] . '" title="click to view!" class="altlink">' . htmlsafechars($arr['name']) . '</a>';
            }
            $child_boards_cache['child_boards'] = $child_boards;
            $cache->set($keys['child_boards'], $child_boards_cache, $site_config['expires']['sv_child_boards']);
        }
        $child_boards = $child_boards_cache['child_boards'];
        if ($child_boards !== '') {
            $child_boards = '<hr><span style="font-size: xx-small;">' . $lang['sv_child_boards'] . ':</span> ' . $child_boards;
        }
        //=== now_viewing
        if (($now_viewing_cache = $cache->get('now_viewing_section_view')) === false) {
            $nowviewing = '';
            $now_viewing_cache = [];
            $res = sql_query('SELECT n_v.user_id, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.perms FROM now_viewing AS n_v LEFT JOIN users AS u ON n_v.user_id=u.id WHERE forum_id=' . sqlesc($forums_arr['forum_id'])) or sqlerr(__FILE__, __LINE__);
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($nowviewing) {
                    $nowviewing .= ",\n";
                }
                $nowviewing .= ($arr['perms'] & bt_options::PERMS_STEALTH ? '<i>' . $lang['fe_unkn0wn'] . '</i>' : format_username((int) $arr['user_id']));
            }
            $now_viewing_cache['now_viewing'] = $nowviewing;
            $cache->set('now_viewing_section_view', $now_viewing_cache, $site_config['expires']['section_view']);
        }
        if (!$now_viewing_cache['now_viewing']) {
            $now_viewing_cache['now_viewing'] = $lang['fe_there_not_been_active_visit_15'];
        }
        $now_viewing = $now_viewing_cache['now_viewing'];
        if ($now_viewing !== '') {
            $now_viewing = '<hr><span style="font-size: xx-small;">' . $lang['sv_now_viewing'] . ': </span>' . $now_viewing;
        }
        if ($last_post_arr['tan'] === 'yes') {
            if ($CURUSER['class'] < UC_STAFF && $last_post_arr['user_id'] != $CURUSER['id']) {
                $last_post = '' . $lang['fe_last_post_by'] . ': ' . $lang['sv_anonymous_in'] . ' &#9658; <a class="altlink" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=p' . (int) $last_post_arr['last_post'] . '#' . (int) $last_post_arr['last_post'] . '" title="' . htmlsafechars($last_post_arr['topic_name']) . '">
		<span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name']), 30) . '</span></a><br>
		' . get_date((int) $last_post_arr['added'], '') . '<br>';
            } else {
                $last_post = '' . $lang['fe_last_post_by'] . ': ' . get_anonymous_name() . ' [' . format_username((int) $last_post_arr['user_id']) . ']</span><br>
		in &#9658; <a class="altlink" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=p' . (int) $last_post_arr['last_post'] . '#' . (int) $last_post_arr['last_post'] . '" title="' . htmlsafechars($last_post_arr['topic_name']) . '">
		<span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name']), 30) . '</span></a><br>
		' . get_date((int) $last_post_arr['added'], '') . '<br>';
            }
        } else {
            $last_post = '' . $lang['fe_last_post_by'] . ': ' . format_username((int) $last_post_arr['user_id']) . '</span><br>
		in &#9658; <a class="altlink" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=p' . (int) $last_post_arr['last_post'] . '#' . (int) $last_post_arr['last_post'] . '" title="' . htmlsafechars($last_post_arr['topic_name']) . '">
		<span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name']), 30) . '</span></a><br>
		' . get_date((int) $last_post_arr['added'], '') . '<br>';
        }
    } else {
        $img = 'unlocked';
        $now_viewing = '';
        $last_post = $lang['fe_na'];
    }
    $body .= "
    <tr>
        <td>
            <img src='{$site_config['paths']['images_baseurl']}forums/{$img}.gif' alt='" . ucfirst($img) . "' title='" . ucfirst($img) . "' class='tooltipper'>
        </td>
		<td>
    		<a class='altlink' href='{$site_config['paths']['baseurl']}/forums.php?action=view_forum&amp;forum_id={$forums_arr['forum_id']}'>" . htmlsafechars($forums_arr['forum_name']) . "</a><p class='top10'>" . htmlsafechars($forums_arr['forum_description']) . $child_boards . $now_viewing . '</p>
        </td>
        <td>' . number_format($forums_arr['post_count']) . "{$lang['fe_posts']}<br>" . number_format($forums_arr['topic_count']) . "{$lang['fe_topics']}</td>
        <td>
		    <span>{$last_post}</span>
        </td>
    </tr>";
}

$HTMLOUT .= main_table($body);
