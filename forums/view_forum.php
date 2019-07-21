<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_html.php';
$posts = $delete_me = $rpic = $content = $child = $parent_forum_name = $first_post_arr = $post_status_image = $sub_forums = $locked = '';
$forum_id = isset($_GET['forum_id']) ? (int) $_GET['forum_id'] : (isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0);
global $container, $site_config, $CURUSER, $lang;

if (!is_valid_id($forum_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
$fluent = $container->get(Database::class);
$fluent->deleteFrom('now_viewing')
       ->where('user_id = ?', $CURUSER['id'])
       ->execute();
$values = [
    'user_id' => $CURUSER['id'],
    'forum_id' => $forum_id,
    'added' => TIME_NOW,
];
$fluent->insertInto('now_viewing')
       ->values($values)
       ->execute();

$arr = $fluent->from('forums')
              ->where('min_class_read <= ?', $CURUSER['class'])
              ->where('id = ?', $forum_id)
              ->limit(1)
              ->fetch();

$forum_name = !empty($arr['name']) ? htmlsafechars($arr['name']) : '';

$parent_forum_id = $arr['parent_forum'];
if ($CURUSER['class'] < $arr['min_class_read']) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}
$may_post = $CURUSER['class'] >= $arr['min_class_write'] && $CURUSER['class'] >= $arr['min_class_create'] && $CURUSER['forum_post'] == 'yes' && $CURUSER['status'] != 5;

$query = $fluent->from('forums')
                ->select(null)
                ->select('id AS sub_forum_id')
                ->select('name AS sub_form_name')
                ->select('description AS sub_form_description')
                ->select('min_class_read')
                ->select('post_count AS sub_form_post_count')
                ->select('topic_count AS sub_form_topic_count')
                ->where('min_class_read <= ?', $CURUSER['class'])
                ->where('parent_forum = ?', $forum_id)
                ->orderBy('sort')
                ->fetchAll();

$sub_forums_stuff = '';

foreach ($query as $sub_forums_arr) {
    if ($sub_forums_arr['min_class_read'] > $CURUSER['class']) {
        die();
    }

    $where = $CURUSER['class'] < UC_STAFF ? 'posts.status = "ok" AND topics.status = "ok"' : $CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? 'posts.status != "deleted"  AND topics.status != "deleted"' : '';
    $post_arr = $fluent->from('topics')
                       ->select(null)
                       ->select('topics.id AS topic_id')
                       ->select('topics.topic_name')
                       ->select('topics.status AS topic_status')
                       ->select('topics.anonymous AS tan')
                       ->select('posts.id AS last_post_id')
                       ->select('posts.topic_id')
                       ->select('posts.added')
                       ->select('posts.anonymous AS pan')
                       ->select('posts.id as post_id')
                       ->select('users.id AS user_id')
                       ->select('users.class')
                       ->innerJoin('posts ON topics.id=posts.topic_id')
                       ->leftJoin('users ON posts.user_id=users.id')
                       ->where($where)
                       ->where('topics.forum_id = ?', $sub_forums_arr['sub_forum_id'])
                       ->orderBy('posts.id DESC')
                       ->limit(1)
                       ->fetch();

    if ($post_arr['last_post_id'] > 0) {
        $last_topic_id = (int) $post_arr['topic_id'];
        $last_post_id = (int) $post_arr['last_post_id'];

        $topic_status = htmlsafechars($post_arr['topic_status']);
        switch ($topic_status) {
            case 'ok':
                $topic_status_image = '';
                break;

            case 'recycled':
                $topic_status_image = ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/recycle_bin.gif" alt="' . $lang['fe_recycled'] . '" title="' . $lang['fe_this_topic_is_currently'] . ' ' . $lang['fe_in_the_recycle_bin'] . '" class="tooltipper icon">';
                break;

            case 'deleted':
                $topic_status_image = ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/delete_icon.gif" alt="' . $lang['fe_deleted'] . '" title="' . $lang['fe_this_topic_is_currently'] . ' ' . $lang['fe_deleted'] . '" class="tooltipper icon">';
                break;
        }

        if ($post_arr['tan'] == 'yes') {
            if ($CURUSER['class'] < UC_STAFF && $post_arr['user_id'] != $CURUSER['id']) {
                $last_post = '<span style="white-space:nowrap;">' . $lang['fe_last_post_by'] . ': <i>' . get_anonymous_name() . '</i> in &#9658; <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $last_topic_id . '&amp;page=last#' . $last_post_id . '" title="' . htmlsafechars($post_arr['topic_name']) . '">
						<span style="font-weight: bold;">' . CutName(htmlsafechars($post_arr['topic_name']), 30) . '</span></a>' . $topic_status_image . '<br>
						' . get_date((int) $post_arr['added'], '') . '<br></span>';
            } else {
                $last_post = '<span style="white-space:nowrap;">' . $lang['fe_last_post_by'] . ': <i>' . get_anonymous_name() . '</i> [' . format_username((int) $post_arr['user_id']) . ']
						<span style="font-size: x-small;"> [ ' . get_user_class_name((int) $post_arr['class']) . ' ] </span><br>
						in &#9658; <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $last_topic_id . '&amp;page=last#' . $last_post_id . '" title="' . htmlsafechars($post_arr['topic_name']) . '">
						<span style="font-weight: bold;">' . CutName(htmlsafechars($post_arr['topic_name']), 30) . '</span></a>' . $topic_status_image . '<br>
						' . get_date((int) $post_arr['added'], '') . '<br></span>';
            }
        } else {
            $last_post = '<span style="white-space:nowrap;">' . $lang['fe_last_post_by'] . ': ' . format_username((int) $post_arr['user_id']) . '
						<span style="font-size: x-small;"> [ ' . get_user_class_name((int) $post_arr['class']) . ' ] </span><br>
						in &#9658; <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $last_topic_id . '&amp;page=last#' . $last_post_id . '" title="' . htmlsafechars($post_arr['topic_name']) . '">
						<span style="font-weight: bold;">' . CutName(htmlsafechars($post_arr['topic_name']), 30) . '</span></a>' . $topic_status_image . '<br>
						' . get_date((int) $post_arr['added'], '') . '<br></span>';
        }

        $first_post_arr = [
            'first_post_id' => 0,
        ];
        $last_unread_post_res = sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($last_post_id)) or sqlerr(__FILE__, __LINE__);
        $last_unread_post_arr = mysqli_fetch_row($last_unread_post_res);
        $last_unread_post_id = ($last_unread_post_arr[0] >= 0 ? $last_unread_post_arr[0] : $first_post_arr['first_post_id']);
        $image_to_use = ($post_arr['added'] > (TIME_NOW - $site_config['forum_config']['readpost_expiry'])) ? (!$last_unread_post_arr || $last_post_id > $last_unread_post_arr[0]) : 0;
        $img = ($image_to_use ? 'unlockednew' : 'unlocked');
    } else {
        $last_post = $lang['fe_na'];
        $img = 'unlocked';
    }

    $sub_forums_stuff .= "
        <tr>
            <td>
                <table>
                    <tr>
                        <td>
                            <img src='{$site_config['paths']['images_baseurl']}forums/{$img}.gif' alt='{$img}' title='{$img}' class='tooltipper icon'>
                        </td>
                        <td>
                            <a class='is-link' href='?action=view_forum&amp;forum_id={$sub_forums_arr['sub_forum_id']}'>" . htmlsafechars($sub_forums_arr['sub_form_name']) . '</a>' . ($CURUSER['class'] >= UC_ADMINISTRATOR ? "
                            <span class='level-right'>
                                <span class='left10'>
                                    <a href='staffpanel.php?tool=forum_manage&amp;action=forum_manage&amp;action2=edit_forum_page&amp;id={$sub_forums_arr['sub_forum_id']}'>
                                        <i class='icon-edit icon'></i>
                                    </a>
                                </span>
                                <span>
                                    <a class='is-link' href='{$site_config['paths']['baseurl']}/forums.php?action=delete_forum&amp;forum_id={$sub_forums_arr['sub_forum_id']}'>
                                        <i class='icon-cancel icon has-text-danger'></i>
                                    </a>
                                </span>
                            </span>" : '') . '
                            <span>' . htmlsafechars($sub_forums_arr['sub_form_description']) . '</span>
                        </td>
                    </tr>
                </table>

            </td>
            <td>
                <span>
                    ' . number_format($sub_forums_arr['sub_form_post_count']) . " {$lang['fe_posts']}<br>
                    " . number_format($sub_forums_arr['sub_form_topic_count']) . " {$lang['fe_topics']}
                </span>
            </td>
            <td><span>{$last_post}</span></td>
        </tr>";

    $sub_forums = !empty($sub_forums_stuff) ? '
    <table class="table table-bordered table-striped">
	    <tr>
	        <td colspan="3">' . $forum_name . ' ' . $lang['fe_child_boards2'] . '</td>
		</tr>' . $sub_forums_stuff . '
    </table>' : '';

    $parent_forum_res = sql_query('SELECT name AS parent_forum_name FROM forums WHERE id=' . sqlesc($parent_forum_id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $parent_forum_arr = mysqli_fetch_assoc($parent_forum_res);

    if ($arr['parent_forum'] > 0) {
        $child = "<span>[ {$lang['fe_child_board']} ]</span>";
        $parent_forum_name = "
            <img src='{$site_config['paths']['images_baseurl']}arrow_next.gif' alt='&#9658;' title='&#9658;' class='tooltipper icon'>
		    <a class='is-link' href='{$site_config['paths']['baseurl']}/forums.php?action=view_forum&amp;forum_id={$parent_forum_id}'>" . htmlsafechars($parent_forum_arr['parent_forum_name']) . '</a>';
    }
}

$res = sql_query('SELECT COUNT(id) FROM topics WHERE  ' . ($CURUSER['class'] < UC_STAFF ? ' status = \'ok\' AND' : ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? ' status != \'deleted\'  AND' : '')) . '  forum_id=' . sqlesc($forum_id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $posts = (int) $row[0];

$page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
$perpage = $CURUSER['topicsperpage'] !== 0 ? $CURUSER['topicsperpage'] : (isset($_GET['perpage']) ? (int) $_GET['perpage'] : 15);
$link = $site_config['paths']['baseurl'] . "/forums.php?action=view_forum&amp;forum_id=$forum_id&amp;" . (isset($_GET['perpage']) ? "perpage={$perpage}&amp;" : '');
$pager = pager($perpage, $count, $link);
$menu_top = $pager['pagertop'];
$menu_bottom = $pager['pagerbottom'];
$LIMIT = $pager['limit'];

$query = $fluent->from('topics AS t')
                ->select(null)
                ->select('t.id')
                ->select('t.user_id')
                ->select('t.topic_name')
                ->select('t.locked')
                ->select('t.forum_id')
                ->select('t.last_post')
                ->select('t.sticky')
                ->select('t.views')
                ->select('t.poll_id')
                ->select('t.num_ratings')
                ->select('t.rating_sum')
                ->select('t.topic_desc')
                ->select('t.post_count')
                ->select('t.first_post')
                ->select('t.status')
                ->select('t.main_forum_id')
                ->select('t.anonymous')
                ->select('p.id AS post_id')
                ->select('p.added AS post_added')
                ->select('p.topic_id AS post_topic_id')
                ->leftJoin('posts AS p ON t.id=p.topic_id')
                ->where('forum_id = ?', $forum_id);
if ($CURUSER['class'] < UC_STAFF) {
    $query = $query->where('p.status = "ok"');
}
if ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class']) {
    $query = $query->where('p.status != "deleted"');
}
$query = $query->orderBy('sticky, post_added DESC')
               ->limit($pager['pdo']['limit'])
               ->offset($pager['pdo']['offset'])
               ->fetchAll();

$topic_arrs = $topic_ids = [];
foreach ($query as $topic) {
    if (!empty($topic['post_id']) && !in_array($topic['id'], $topic_ids)) {
        $topic_arrs[] = $topic;
        $topic_ids[] = $topic['id'];
    }
}
if (!empty($topic_arrs)) {
    foreach ($topic_arrs as $topic_arr) {
        $topic_id = (int) $topic_arr['id'];
        $locked = $topic_arr['locked'] == 'yes';
        $sticky = $topic_arr['sticky'] == 'yes';
        $topic_poll = (int) $topic_arr['poll_id'] > 0;
        $topic_status = htmlsafechars($topic_arr['status']);
        switch ($topic_status) {
            case 'ok':
                $topic_status_image = '';
                break;

            case 'recycled':
                $topic_status_image = ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/recycle_bin.gif" alt="' . $lang['fe_recycled'] . '" title="' . $lang['fe_this_topic_is_currently'] . ' ' . $lang['fe_in_the_recycle_bin'] . '" class="tooltipper icon">';
                break;

            case 'deleted':
                $topic_status_image = ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/delete_icon.gif" alt="' . $lang['fe_deleted'] . '" title="' . $lang['fe_this_topic_is_currently'] . ' ' . $lang['fe_deleted'] . '" class="tooltipper icon">';
                break;
        }

        $res_post_stuff = sql_query('SELECT p.id AS last_post_id, p.added, p.user_id,  p.status, p.anonymous,
												u.id, u.username, u.class, u.donor, u.status, u.warned, u.chatpost, u.leechwarn, u.pirate, u.king
												FROM posts AS p 
												LEFT JOIN users AS u ON p.user_id=u.id 
												WHERE  ' . ($CURUSER['class'] < UC_STAFF ? ' p.status = \'ok\' AND' : ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? ' p.status != \'deleted\'  AND' : '')) . '  topic_id=' . sqlesc($topic_id) . '
												ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $arr_post_stuff = mysqli_fetch_assoc($res_post_stuff);

        $post_status = htmlsafechars($arr_post_stuff['status']);
        switch ($post_status) {
            case 'ok':
                $post_status_image = '';
                break;

            case 'recycled':
                $post_status_image = ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/recycle_bin.gif" alt="' . $lang['fe_recycled'] . '" title="' . $lang['fe_this_post_is_currently'] . ' ' . $lang['fe_in_the_recycle_bin'] . '" width="18px" class="tooltipper icon">';
                break;

            case 'deleted':
                $post_status_image = ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/delete_icon.gif" alt="' . $lang['fe_deleted'] . '" title="' . $lang['fe_this_post_is_currently'] . ' ' . $lang['fe_deleted'] . '" width="18px" class="tooltipper icon">';
                break;

            case 'postlocked':
                $post_status = 'postlocked';
                $post_status_image = ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/thread_locked.gif" alt="' . $lang['fe_locked'] . '" title="' . $lang['fe_this_post_is_currently'] . ' ' . $lang['fe_locked'] . '" width="18px" class="tooltipper icon">';
                break;
        }

        if ($arr_post_stuff['anonymous'] == 'yes') {
            if ($CURUSER['class'] < UC_STAFF && $arr_post_stuff['user_id'] != $CURUSER['id']) {
                $last_post_username = ($arr_post_stuff['username'] !== '' ? '<i>' . get_anonymous_name() . '</i>' : '' . $lang['fe_lost'] . ' [' . (int) $arr_post_stuff['id'] . ']');
            } else {
                $last_post_username = ($arr_post_stuff['username'] !== '' ? '<i>' . get_anonymous_name() . '</i> [' . format_username((int) $arr_post_stuff['user_id']) . ']' : '' . $lang['fe_lost'] . ' [' . (int) $arr_post_stuff['id'] . ']');
            }
        } else {
            $last_post_username = ($arr_post_stuff['username'] !== '' ? format_username((int) $arr_post_stuff['user_id']) : '' . $lang['fe_lost'] . ' [' . (int) $arr_post_stuff['id'] . ']');
        }

        $last_post_id = (int) $arr_post_stuff['last_post_id'];

        $first_post_res = sql_query('SELECT p.id AS first_post_id, p.added, p.icon, p.body, p.anonymous, p.user_id,
												u.id, u.username, u.class, u.donor, u.warned, u.status, u.chatpost, u.leechwarn, u.pirate, u.king
												FROM posts AS p
												LEFT JOIN users AS u ON p.user_id=u.id
												WHERE  ' . ($CURUSER['class'] < UC_STAFF ? ' p.status = \'ok\' AND' : ($CURUSER['class'] < $site_config['forum_config']['min_delete_view_class'] ? ' p.status != \'deleted\'  AND' : '')) . '
												topic_id=' . sqlesc($topic_id) . ' ORDER BY p.id LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $first_post_arr = mysqli_fetch_assoc($first_post_res);

        if ($first_post_arr['anonymous'] === 'yes') {
            if ($CURUSER['class'] < UC_STAFF && $first_post_arr['user_id'] != $CURUSER['id']) {
                $thread_starter = ($first_post_arr['username'] !== '' ? '<i>' . get_anonymous_name() . '</i>' : '' . $lang['fe_lost'] . ' [' . $topic_arr['user_id'] . ']') . '<br>' . get_date((int) $first_post_arr['added'], '');
            } else {
                $thread_starter = ($first_post_arr['username'] !== '' ? '<i>' . get_anonymous_name() . '</i> [' . format_username((int) $first_post_arr['user_id']) . ']' : '' . $lang['fe_lost'] . ' [' . $topic_arr['user_id'] . ']') . '<br>' . get_date((int) $first_post_arr['added'], '');
            }
        } else {
            $thread_starter = ($first_post_arr['username'] !== '' ? format_username((int) $first_post_arr['user_id']) : '' . $lang['fe_lost'] . ' [' . $topic_arr['user_id'] . ']') . '<br>' . get_date((int) $first_post_arr['added'], '');
        }

        $icon = (empty($first_post_arr['icon']) ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" alt="' . $lang['fe_thread_icon'] . '" title="' . $lang['fe_thread_icon'] . '" class="tooltipper icon">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($first_post_arr['icon']) . '.gif" alt="' . htmlsafechars($first_post_arr['icon']) . '">');
        $first_post_text = bubble("<i class='icon-search icon' aria-hidden='true'></i>", format_comment($first_post_arr['body'], true, true, false), '' . $lang['fe_first_post'] . ' ' . $lang['fe_preview'] . '');

        $last_unread_post_res = sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $last_unread_post_arr = mysqli_fetch_row($last_unread_post_res);
        $last_unread_post_id = ($last_unread_post_arr[0] > 0 ? $last_unread_post_arr[0] : $first_post_arr['first_post_id']);
        $did_i_post_here = sql_query('SELECT user_id FROM posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $posted = (mysqli_num_rows($did_i_post_here) > 0 ? 1 : 0);

        $sub = sql_query('SELECT user_id FROM subscriptions WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
        $subscriptions = (mysqli_num_rows($sub) > 0 ? 1 : 0);

        $total_pages = floor($posts / $perpage);
        switch (true) {
            case $total_pages == 0:
                $multi_pages = '';
                break;

            case $total_pages > 11:
                $multi_pages = ' <span style="font-size: xx-small;"> <img src="' . $site_config['paths']['images_baseurl'] . 'forums/multipage.gif" alt="+" title="+" class="tooltipper icon">';
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
                $multi_pages = ' <span style="font-size: xx-small;"> <img src="' . $site_config['paths']['images_baseurl'] . 'forums/multipage.gif" alt="+" title="+" class="tooltipper icon">';
                for ($i = 1; $i < $total_pages; ++$i) {
                    $multi_pages .= ' <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $i . '">' . $i . '</a>';
                }
                $multi_pages .= '</span>';
                break;
        }
        $new = ($arr_post_stuff['added'] > (TIME_NOW - $site_config['forum_config']['readpost_expiry'])) ? (!$last_unread_post_res || $last_post_id > $last_unread_post_id) : 0;
        $topic_pic = ($posts < 30 ? ($locked ? ($new ? 'lockednew' : 'locked') : ($new ? 'topicnew' : 'topic')) : ($locked ? ($new ? 'lockednew' : 'locked') : ($new ? 'hot_topic_new' : 'hot_topic')));
        $topic_name = ($sticky ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/pinned.gif" alt="' . $lang['fe_pinned'] . '" title="' . $lang['fe_pinned'] . '" class="tooltipper icon"> ' : ' ') . ($topic_poll ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/poll.gif" alt="Poll:" title="' . $lang['fe_poll'] . '" class="tooltipper icon"> ' : ' ') . ' <a class="is-link" href="?action=view_topic&amp;topic_id=' . $topic_id . '">' . htmlsafechars($topic_arr['topic_name']) . '</a> ' . ($posted ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/posted.gif" alt="Posted" title="Posted" class="tooltipper icon"> ' : ' ') . ($subscriptions ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/subscriptions.gif" alt="' . $lang['fe_subscribed'] . '" title="Subcribed" class="tooltipper icon"> ' : ' ') . ($new ? ' <img src="' . $site_config['paths']['images_baseurl'] . 'forums/new.gif" alt="' . $lang['fe_new_post_in_topic'] . '!" title="' . $lang['fe_new_post_in_topic'] . '!" class="tooltipper icon">' : '') . $multi_pages;

        $rpic = ($topic_arr['num_ratings'] != 0 ? ratingpic_forums(round($topic_arr['rating_sum'] / $topic_arr['num_ratings'], 1)) : '');

        if ($CURUSER['class'] >= UC_MAX && $forum_id === $site_config['staff_forums'][0]) {
            $delete_me = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font-size: x-small;">[ <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=delete_topic&amp;topic_id=' . $topic_id . '&amp;sure=1&amp;send_me_back=666">' . $lang['fe_delete'] . '</a> ]</span>';
        }

        $content .= '
        <tr>
		    <td class="has-text-centered">
                <img src="' . $site_config['paths']['images_baseurl'] . 'forums/' . $topic_pic . '.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" class="tooltipper icon">
            </td>
    		<td class="has-text-centered">' . $icon . '</td>
	    	<td>
		        <div class="level">
		            <span class="right10">
        		        ' . $topic_name . '
                    </span>
	    	        <span class="right10">
            		    ' . $first_post_text . '
                    </span>
		            <span class="right10">
        		        ' . $topic_status_image . '
                    </span>
	    	    </div>
		        <div>
		        ' . $rpic . '
    		    </div>
    		</td>' . (!empty($topic_arr['topic_desc']) ? '&#9658; <span style="font-size: x-small;">' . htmlsafechars($topic_arr['topic_desc']) . '</span>' : '') . '</td>
    		<td class="has-text-centered">' . $thread_starter . '</td>
	    	<td class="has-text-centered">' . number_format($topic_arr['post_count'] - 1) . '</td>
		    <td class="has-text-centered">' . number_format($topic_arr['views']) . '</td>
    		<td class="has-text-centered">
                <span style="white-space:nowrap;">' . get_date((int) $arr_post_stuff['added'], '') . '</span><br>
        		<a class="is-link tooltipper" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=last#' . $last_post_id . '" title="' . $lang['fe_go_to_the_last_post_in_this_thread'] . '">' . $lang['fe_last_post'] . '</a> by&nbsp;' . $last_post_username . '
            </td>
    		<td class="has-text-centered">' . $post_status_image . ' <a class="is-link" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=last#' . $last_unread_post_id . '">
	        	<img src="' . $site_config['paths']['images_baseurl'] . 'forums/last_post.gif" alt="' . $lang['fe_last_post'] . '" title="' . $lang['fe_last_unread_post_in_this_thread'] . '" class="tooltipper icon"></a>
            </td>
		</tr>';
    }
    $the_top_and_bottom = ($locked == 'yes' && $_GET['action'] == 'view_topic' ? '
                    <span>' . $lang['fe_this_topic_is_locked'] . '... ' . $lang['fe_no_new_posts_are_allowed'] . '</span>' : '');
} else {
    $content .= '
        <tr>
            <td class="clear" colspan="8">
	            <span>' . $lang['fe_no_topics_found'] . '</span>
            </td>
		</tr>';
    $the_top_and_bottom = '';
}
$HTMLOUT .= $mini_menu . $sub_forums . "<h1 class='has-text-centered'>$forum_name</h1>" . ($count > $perpage ? $menu_top : '');
$heading = $body = '';
if (!empty($content)) {
    $heading = '
        <tr>
		    <th class="has-text-centered"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '"  class="tooltipper icon"></th>
		    <th class="has-text-centered"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" alt=' . $lang['fe_thread_icon'] . '" title=' . $lang['fe_thread_icon'] . '"  class="tooltipper icon"></th>
		    <th class="has-text-centered">' . $lang['fe_topic'] . '</th>
		    <th class="has-text-centered">' . $lang['fe_started_by'] . '</th>
		    <th class="has-text-centered">' . $lang['fe_replies'] . '</th>
		    <th class="has-text-centered">' . $lang['fe_views'] . '</th>
		    <th class="has-text-centered">' . $lang['fe_last_post'] . '</th>
		    <th class="has-text-centered"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/last_post.gif" alt="' . $lang['fe_last_post'] . '" title="' . $lang['fe_last_post'] . '" class="tooltipper icon"></th>
		</tr>';
}
$table = main_table($content, $heading);
$HTMLOUT .= $table . ($may_post ? '
                    <div class="has-text-centered margin20">
                        <form action="' . $site_config['paths']['baseurl'] . '/forums.php?action=new_topic&amp;forum_id=' . $forum_id . '" method="post" name="new" accept-charset="utf-8">
		                    <input type="hidden" name="action" value="new_topic">
		                    <input type="hidden" name="forum_id" value="' . $forum_id . '">
		                    <input type="submit" name="button" class="button is-small" value="' . $lang['fe_new_topic'] . '">
		                </form>
		            </div>' : '<span>' . $lang['fe_you_are_not_permitted_to_post_in_this_forum.'] . '</span>') . $the_top_and_bottom . ($count > $perpage ? $menu_bottom : '');
