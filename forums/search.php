<?php

global $lang, $site_config, $user_stuffs, $CURUSER, $fluent;

$author_error = $content = $count = $count2 = $edited_by = $row_count = $over_forum_id = $author_id = '';
$search_where = $selected_forums = [];

$search = isset($_GET['search']) ? strip_tags(trim($_GET['search'])) : '';
$search_post = str_replace(' ', '+', $search);
$author = isset($_GET['author']) ? trim(htmlsafechars($_GET['author'])) : '';
$valid = [
    'body',
    'title',
    'all',
];
$search_what = !empty($_GET['search_what']) && in_array($_GET['search_what'], $valid) ? $_GET['search_what'] : 'all';
$search_when = isset($_GET['search_when']) ? intval($_GET['search_when']) : 0;
$sort_by = isset($_GET['sort_by']) && $_GET['sort_by'] === 'date' ? 'date' : '';
$asc_desc = isset($_GET['asc_desc']) && $_GET['asc_desc'] === 'ASC' ? 'ASC' : 'DESC';
$show_as = isset($_GET['show_as']) && $_GET['show_as'] === 'posts' ? 'posts' : 'list';
$pager_links = '';
$pager_links .= $search ? '&amp;search=' . $search : '';
$pager_links .= $author ? '&amp;author=' . $author : '';
$pager_links .= $search_what ? '&amp;search_what=' . $search_what : '';
$pager_links .= $search_when ? '&amp;search_when=' . $search_when : '';
$pager_links .= $sort_by ? '&amp;sort_by=' . $sort_by : '';
$pager_links .= $asc_desc ? '&amp;asc_desc=' . $asc_desc : '';
$pager_links .= $show_as ? '&amp;show_as=' . $show_as : '';
$author_id = 0;
if ($author) {
    $author_id = $user_stuffs->getUserIdFromName($author);
    $author_error = empty($author_id) ? $lang['sea_sorry_no_member_found_with_that_username.'] . ' ' . $lang['sea_please_check_the_spelling.'] : '';
}
if ($search || $author_id) {
    $count = $fluent->from('posts AS p')
                    ->select(null)
                    ->select('COUNT(*) AS count')
                    ->where('f.min_class_read <= ?', $CURUSER['class'])
                    ->leftJoin('topics AS t ON p.topic_id=t.id')
                    ->leftJoin('forums AS f ON t.forum_id=f.id');

    $results = $fluent->from('posts AS p')
                      ->select(null)
                      ->select('p.user_id AS userid')
                      ->select('p.id AS post_id')
                      ->select('p.body')
                      ->select('p.post_title')
                      ->select('p.added')
                      ->select('p.icon')
                      ->select('p.edited_by')
                      ->select('p.edit_reason')
                      ->select('p.edit_date')
                      ->select('p.bbcode')
                      ->select('p.anonymous AS pan')
                      ->select('t.anonymous AS anonymous')
                      ->select('t.id AS topic_id')
                      ->select('t.topic_name AS topic_title')
                      ->select('t.topic_desc')
                      ->select('t.post_count')
                      ->select('t.views')
                      ->select('t.locked')
                      ->select('t.sticky')
                      ->select('t.poll_id')
                      ->select('t.num_ratings')
                      ->select('t.rating_sum')
                      ->select('f.id AS forum_id')
                      ->select('f.name AS forum_name')
                      ->select('f.description AS forum_desc')
                      ->where('f.min_class_read <= ?', $CURUSER['class'])
                      ->leftJoin('topics AS t ON p.topic_id=t.id')
                      ->leftJoin('forums AS f ON t.forum_id=f.id');
    if ($CURUSER['class'] < UC_STAFF) {
        $count = $count->where('p.status = "ok"')
                       ->where('t.status = "ok"');
        $results = $results->where('p.status = "ok"')
                           ->where('t.status = "ok"');
    } elseif ($CURUSER['class'] < $min_delete_view_class) {
        $count = $count->where('p.status != "deleted"')
                       ->where('t.status != "deleted"');
        $results = $results->where('p.status != "deleted"')
                           ->where('t.status != "deleted"');
    }
    if (!empty($search)) {
        if ($search_what === 'all') {
            $count = $count->where('(MATCH (p.body) AGAINST (? IN NATURAL LANGUAGE MODE) OR MATCH (p.post_title) AGAINST (? IN NATURAL LANGUAGE MODE) OR MATCH (t.topic_name) AGAINST (? IN NATURAL LANGUAGE MODE))', [
                $search,
                $search,
                $search,
            ]);
            $results = $results->where('(MATCH (p.body) AGAINST (? IN NATURAL LANGUAGE MODE) OR MATCH (p.post_title) AGAINST (? IN NATURAL LANGUAGE MODE) OR MATCH (t.topic_name) AGAINST (? IN NATURAL LANGUAGE MODE))', [
                $search,
                $search,
                $search,
            ]);
        } elseif ($search_what === 'body') {
            $count = $count->where('MATCH (p.body) AGAINST (? IN NATURAL LANGUAGE MODE)', $search);
            $results = $results->where('MATCH (p.body) AGAINST (? IN NATURAL LANGUAGE MODE)', $search);
        } elseif ($search_what === 'title') {
            $count = $count->where('(MATCH (p.post_title) AGAINST (? IN NATURAL LANGUAGE MODE) OR MATCH (t.topic_name) AGAINST (? IN NATURAL LANGUAGE MODE))', [
                $search,
                $search,
            ]);
            $results = $results->where('(MATCH (p.post_title) AGAINST (? IN NATURAL LANGUAGE MODE) OR MATCH (t.topic_name) AGAINST (? IN NATURAL LANGUAGE MODE))', [
                $search,
                $search,
            ]);
        }
    }

    $query = $fluent->from('forums')
                    ->select(null)
                    ->select('id');

    foreach ($query as $arr_forum_ids) {
        if (isset($_GET['f' . $arr_forum_ids['id']])) {
            $selected_forums[] = $arr_forum_ids['id'];
        }
    }
    $selected_forums_undone = '';
    if (!empty($selected_forums)) {
        $count = $count->where('t.forum_id', implode(', ', $selected_forums));
        $results = $results->where('t.forum_id', implode(', ', $selected_forums));
    }
    if ($author_id) {
        $count = $count->where('p.user_id=?', $author_id);
        $results = $results->where('p.user_id=?', $author_id);
    }
    if ($search_when) {
        $count = $count->where('p.added>= ?' . (TIME_NOW - $search_when));
        $results = $results->where('p.added>= ?' . (TIME_NOW - $search_when));
    }
    $count = $count->fetch('count');
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
    $perpage = 15;
    $link = $site_config['paths']['baseurl'] . '/forums.php?action=search' . $pager_links . (isset($_GET['perpage']) ? "&amp;perpage={$perpage}&amp;" : '');
    $pager = pager($perpage, $count, $link);
    $menu_top = $pager['pagertop'];
    $menu_bottom = $pager['pagerbottom'];
    if (isset($_GET['sort_by']) && $_GET['sort_by'] === 'date') {
        $order = isset($_GET['asc_desc']) && $_GET['asc_desc'] === 'ASC' ? 'ASC' : 'DESC';
        $results = $result->orderBy('p.added ?', $order);
    }
    $results = $results->limit($pager['pdo']);
    if ($count === 0) {
        $content .= stdmsg($lang['sea_nothing_found'], $lang['sea_please_try_again_with_a_refined_search_string.'], 'top20');
    } else {
        if (empty($author_error)) {
            if ($show_as === 'list') {
                $content .= "<div class='top20'></div>" . ($count > $perpage ? $menu_top : '') . '
        <a id="results"></a>';
                $heading = '
        <tr>
            <th class="w-1"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" class="emoticon tooltipper"></th>
            <th class="w-1"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" alt="' . $lang['fe_thread_icon'] . '" title="' . $lang['fe_thread_icon'] . '" class="emoticon tooltipper"></th>
            <th class="w-40">' . $lang['sea_topic_post'] . '</th>
            <th class="w-40">' . $lang['sea_in_forum'] . '</th>
            <th class="w-1">' . $lang['fe_replies'] . '</th>
            <th class="w-1">' . $lang['fe_views'] . '</th>
            <th class="w-1">' . $lang['sea_date'] . '</th>
        </tr>';
                foreach ($results as $arr) {
                    $table_body = '';
                    if ($search_what === 'all' || $search_what === 'title') {
                        $topic_title = highlightWords(htmlsafechars($arr['topic_title'], ENT_QUOTES), $search);
                        $topic_desc = highlightWords(htmlsafechars($arr['topic_desc'], ENT_QUOTES), $search);
                        $post_title = highlightWords(htmlsafechars($arr['post_title'], ENT_QUOTES), $search);
                    } else {
                        $topic_title = htmlsafechars($arr['topic_title'], ENT_QUOTES);
                        $topic_desc = htmlsafechars($arr['topic_desc'], ENT_QUOTES);
                        $post_title = htmlsafechars($arr['post_title'], ENT_QUOTES);
                    }
                    $body = format_comment($arr['body'], true, false);
                    $post_id = $arr['post_id'];
                    $posts = $arr['post_count'];
                    $post_text = bubble("<i class='icon-search icon' aria-hidden='true'></i>", $body, '' . $lang['fe_post_preview'] . '');
                    $rpic = ($arr['num_ratings'] != 0 ? ratingpic_forums(round($arr['rating_sum'] / $arr['num_ratings'], 1)) : '');
                    $table_body .= '
        <tr>
            <td><img src="' . $site_config['paths']['images_baseurl'] . 'forums/' . ($posts < 30 ? ($arr['locked'] === 'yes' ? 'locked' : 'topic') : 'hot_topic') . '.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" class="emoticon tooltipper"></td>
            <td>' . (empty($arr['icon']) ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" alt="' . $lang['fe_topic'] . '" title="' . $lang['fe_topic'] . '" class="emoticon">' : '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($arr['icon']) . '.gif" alt="' . htmlsafechars($arr['icon']) . '" title="' . htmlsafechars($arr['icon']) . '" class="emoticon tooltipper">') . '</td>
            <td>
                <div class="padding20">
                    <div class="columns">
                        <div class="column is-one-fifth">
                            <span class="has-text-weight-bold">' . $lang['fe_post'] . ': </span>
                        </div>
                        <div class="column">
                            <a class="altlink tooltipper" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=15&amp;page=p' . $arr['post_id'] . '&amp;search=' . $search_post . '#' . $arr['post_id'] . '" title="' . $lang['sea_go_to_the_post'] . '">' . (empty($post_title) ? '' . $lang['fe_link_to_post'] . '' : $post_title) . '</a>
                        </div>
                    </div>
                    <div class="columns">
                        <div class="column is-one-fifth">
                            <span style="font-style: italic;">by: </span>
                        </div>
                        <div class="column">
                            ' . ($arr['pan'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : format_username($arr['userid'])) . '
                        </div>
                    </div>
                    <div class="columns">
                        <div class="column is-one-fifth">
                            <span style="font-style: italic;">' . $lang['ep_in_topic'] . ': </span>
                        </div>
                        <div class="column">
                            ' . ($arr['sticky'] === 'yes' ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/pinned.gif" alt="' . $lang['fe_pinned'] . '" title="' . $lang['fe_pinned'] . '" class="emoticon tooltipper">' : '') . ($arr['poll_id'] > 0 ? '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/poll.gif" alt="Poll" title="Poll" class="emoticon tooltipper">' : '') . '
                                <a class="altlink tooltipper" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $arr['topic_id'] . '" title="' . $lang['sea_go_to_topic'] . '">' . $topic_title . '</a>' . $post_text . '
                        </div>' . (!empty($rpic) ? '
                        <div class="column is-1">
                            ' . $rpic . '
                        </div>' : '') . '
                    </div>' . (!empty($topic_desc) ? '
                    <div class="columns">
                        <div class="column is-one-fifth"></div>
                        <div class="column">
                            &#9658; <span style="font-size: x-small;">' . $topic_desc . '</span>
                        </div>
                    </div>' : '') . '
                </div>
            </td>
            <td>
                <a class="altlink tooltipper" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $arr['forum_id'] . '" title="' . $lang['sea_go_to_forum'] . '">' . htmlsafechars($arr['forum_name'], ENT_QUOTES) . '</a>
                ' . ($arr['forum_desc'] != '' ? '&#9658; <span style="font-size: x-small;">' . htmlsafechars($arr['forum_desc'], ENT_QUOTES) . '</span>' : '') . '
            </td>
            <td>' . number_format($posts - 1) . '</td>
            <td>' . number_format($arr['views']) . '</td>
            <td><span style="white-space:nowrap;">' . get_date($arr['added'], '') . '</span></td>
        </tr>';
                    $content .= main_table($table_body, $heading, 'top20') . ($count > $perpage ? $menu_bottom : '');
                }
            } elseif ($show_as === 'posts') {
                $content .= "<div class='top20'></div>" . ($count > $perpage ? $menu_top : '') . '
        <a id="results"></a>';
                $x = 0;
                foreach ($results as $arr) {
                    $user = $user_stuffs->getUserFromId($arr['userid']);
                    $post_title = (!empty($arr['post_title']) ? '<span style="font-weight: bold; font-size: x-small;">' . htmlsafechars($arr['post_title'], ENT_QUOTES) . '</span>' : 'Link to Post');
                    if ($search_what === 'all' || $search_what === 'title') {
                        $topic_title = highlightWords(htmlsafechars($arr['topic_title'], ENT_QUOTES), $search);
                        $topic_desc = highlightWords(htmlsafechars($arr['topic_desc'], ENT_QUOTES), $search);
                        $post_title = highlightWords($post_title, $search);
                    } else {
                        $topic_title = htmlsafechars($arr['topic_title'], ENT_QUOTES);
                        $topic_desc = htmlsafechars($arr['topic_desc'], ENT_QUOTES);
                    }
                    $post_id = $arr['post_id'];
                    $posts = $arr['post_count'];
                    $post_icon = ($arr['icon'] != '' ? '<img src="' . $site_config['paths']['images_baseurl'] . 'smilies/' . htmlsafechars($arr['icon']) . '.gif" alt="icon" title="icon" class="emoticon tooltipper"> ' : '<img src="' . $site_config['paths']['images_baseurl'] . 'forums/topic_normal.gif" alt="Normal Topic" class="emoticon"> ');
                    $edited_by = '';
                    if ($arr['edit_date'] > 0) {
                        $edited_username = $user_stuffs->get_item('username', $arr['edited_by']);
                        $edited_by = '<span style="font-weight: bold; font-size: x-small;">Last edited by <a class="altlink" href="' . $site_config['paths']['baseurl'] . '/member_details.php?id=' . $arr['edited_by'] . '">' . htmlsafechars($edited_username) . '</a> at ' . get_date($arr['edit_date'], '') . ' GMT ' . ('' != $arr['edit_reason'] ? ' </span>[ Reason: ' . htmlsafechars($arr['edit_reason']) . ' ] <span style="font-weight: bold; font-size: x-small;">' : '');
                    }
                    $body = ($arr['bbcode'] === 'yes' ? highlightWords(format_comment($arr['body']), $search) : highlightWords(format_comment_no_bbcode($arr['body']), $search));
                    $table_body = '
        <tr>
            <td colspan="3">in:
                <a class="altlink tooltipper" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $arr['forum_id'] . '" title="' . sprintf($lang['sea_link_to_x'], 'Forum') . '">
                    <span>' . htmlsafechars($arr['forum_name'], ENT_QUOTES) . '</span>
                </a> in:
                <a class="altlink tooltipper" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $arr['topic_id'] . '" title="' . sprintf($lang['sea_link_to_x'], 'topic') . '">
                    <span>' . $topic_title . '</span>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                <a id="' . $post_id . '"></a>
            </td>
            <td>
                <span style="white-space:nowrap;">' . $post_icon . '
                    <a class="altlink tooltipper" href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $arr['topic_id'] . '&amp;page=' . $page . '#' . $arr['post_id'] . '" title="Link to Post">' . $post_title . '
                    </a>
                    <span class="left20">' . $lang['fe_posted_on'] . ': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span>
                </span>
            </td>
            <td>
                <span>
                    <a href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_my_GETs&amp;page=' . $page . '#top"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/up.gif" alt="' . $lang['fe_top'] . '" title="' . $lang['fe_top'] . '" class="emoticon tooltipper"></a>
                    <a href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_my_GETs&amp;page=' . $page . '#bottom"><img src="' . $site_config['paths']['images_baseurl'] . 'forums/down.gif" alt="' . $lang['fe_bottom'] . '" title="' . $lang['fe_bottom'] . '" class="emoticon tooltipper"></a>
                </span>
            </td>
        </tr>
        <tr>
            <td class="has-text-centered w-15 mw-150">' . get_avatar($arr) . ($arr['anonymous'] === 'yes' ? '<i>' . get_anonymous_name() . '</i>' : format_username($arr['userid'])) . ($arr['anonymous'] === 'yes' || empty($user['title']) ? '' : '
                <span class="size_2">[' . htmlsafechars($user['title']) . ']</span>') . '<span> ' . ($arr['anonymous'] === 'yes' ? '' : get_user_class_name($user['class'])) . '</span>
            </td>
            <td colspan="2">' . $body . $edited_by . '</td>
        </tr>';
                    $content .= main_table($table_body, '', ($x++ === 0 ? '' : 'top20'));
                }
                $content .= ($count > $perpage ? $menu_bottom : '');
            }
        }
    }
}

$search_in_forums = '<table class="table-striped">';
$row_count = 0;
$forums = $fluent->from('over_forums AS o_f')
                 ->select(null)
                 ->select('o_f.name AS over_forum_name')
                 ->select('o_f.id AS over_forum_id')
                 ->select('f.id AS real_forum_id')
                 ->select('f.name')
                 ->select('f.description')
                 ->select('f.forum_id')
                 ->leftJoin('forums AS f ON o_f.id=f.forum_id')
                 ->where('o_f.min_class_view <= ?', $CURUSER['class'])
                 ->where('f.min_class_read <= ?', $CURUSER['class'])
                 ->orderBy('o_f.sort')
                 ->orderBy('f.sort ASC');

foreach ($forums as $arr_forums) {
    $search_in_forums .= ($arr_forums['over_forum_id'] != $over_forum_id ? '<tr>
    <td class="has-no-border" colspan="3"><span style="color: white;">' . htmlsafechars($arr_forums['over_forum_name'], ENT_QUOTES) . '</span></td></tr>' : '');
    if ($arr_forums['forum_id'] === $arr_forums['over_forum_id']) {
        $search_in_forums .= '
                <tr>
                    <td class="has-no-border">
                        <div class="is-flex level-left">
                            <input name="f' . $arr_forums['real_forum_id'] . '" type="checkbox"' . ($selected_forums ? ' checked' : '') . ' value="1">
                            <a href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $arr_forums['real_forum_id'] . '" class="altlink tooltipper left10" title="' . htmlsafechars($arr_forums['description'], ENT_QUOTES) . '">' . htmlsafechars($arr_forums['name'], ENT_QUOTES) . '
                            </a>
                        </div>
                    </td>
                </tr>';
    }
    $over_forum_id = $arr_forums['over_forum_id'];
}
$search_in_forums .= '
                <tr>
                    <td class="has-no-border">
                        <span class="has-text-weight-bold">' . $lang['sea_if_none_are_selected_all_are_searched.'] . '</span>
                    </td>
                </tr>
            </table>';
$search_when_drop_down = '
        <select name="search_when">
            <option class="body" value="0"' . (0 === $search_when ? ' selected' : '') . '>' . $lang['sea_no_time_frame'] . '</option>
            <option class="body" value="604800"' . (604800 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_week_ago'], '1') . '</option>
            <option class="body" value="1209600"' . (1209600 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_weeks_ago'], '2') . '</option>
            <option class="body" value="1814400"' . (1814400 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_weeks_ago'], '3') . '</option>
            <option class="body" value="2419200"' . (2419200 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_month_ago'], '1') . '</option>
            <option class="body" value="4838400"' . (4838400 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '2') . '</option>
            <option class="body" value="7257600"' . (7257600 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '3') . '</option>
            <option class="body" value="9676800"' . (9676800 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '4') . '</option>
            <option class="body" value="12096000"' . (12096000 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '5') . '</option>
            <option class="body" value="14515200"' . (14515200 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '6') . '</option>
            <option class="body" value="16934400"' . (16934400 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '7') . '</option>
            <option class="body" value="19353600"' . (19353600 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '8') . '</option>
            <option class="body" value="21772800"' . (21772800 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '9') . '</option>
            <option class="body" value="24192000"' . (24192000 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '10') . '</option>
            <option class="body" value="26611200"' . (26611200 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_months_ago'], '11') . '</option>
            <option class="body" value="30800000"' . (30800000 === $search_when ? ' selected' : '') . '>' . sprintf($lang['sea_x_year_ago'], '1') . '</option>
            <option class="body" value="0">' . $lang['sea_13.73_billion_years_ago'] . '</option>
        </select>';
$sort_by_drop_down = '
        <select name="sort_by">
            <option class="body" value="relevance"' . ('relevance' === $sort_by ? ' selected' : '') . '>' . $lang['sea_relevance'] . ' [default]</option>
            <option class="body" value="date"' . ('date' === $sort_by ? ' selected' : '') . '>' . $lang['sea_post_date'] . '</option>
        </select>';
$HTMLOUT .= $mini_menu . '
        <h1 class="has-text-centered">' . $lang['sea_forums'] . '</h1>
            <form method="get" action="forums.php?"><input type="hidden" name="action" value="search" accept-charset="utf-8">';
$table_body = '
                <tr>
                    <td>
                        <span>' . $lang['sea_search_in'] . ':</span>
                    </td>
                    <td>
                        <div class="level - left is-flex">
                            <input type="radio" id="search_title" name="search_what" value="title"' . ($search_what === 'title' ? ' checked' : '') . '>
                            <label for="search_title" class="left5">Title(s)</label>
                            <input type="radio" id="search_body" name="search_what" value="body"' . ($search_what === 'body' ? ' checked' : '') . ' class="left10">
                            <label for="search_body" class="left5">Body Text</label>
                            <input type="radio" id="search_all" name="search_what" value="all"' . ($search_what === 'all' ? ' checked' : '') . ' class="left10">
                            <label for="search_all" class="left5">All</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>' . $lang['sea_search_terms'] . ':</span>
                    </td>
                    <td>
                        <input type="text" class="search" name="search" value="' . htmlsafechars($search) . '" required> Surround with double quotes to search for a phrase.
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>' . $lang['sea_by_member'] . ':</span>
                    </td>
                    <td>
                        <input type="text" class="member" name="author" value="' . $author . '"> ' . $author_error . '
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>' . $lang['sea_time_frame'] . ':</span>
                    </td>
                    <td>
                        <span>' . $search_when_drop_down . ' ' . $lang['sea_how_far_back_to_search'] . '.</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>' . $lang['sea_sort_by'] . ':</span>
                    </td>
                    <td>' . $sort_by_drop_down . '
                        <div class="level - left is-flex top10">
                            <input type="radio" id="asc_asc" name="asc_desc" value="ASC"' . ($asc_desc === 'ASC' ? ' checked' : '') . '>
                            <label for="asc_asc" class="left5">' . $lang['sea_ascending'] . '</label>
                            <input type="radio" id="asc_desc" name="asc_desc" value="DESC"' . ($asc_desc === 'DESC' ? ' checked' : '') . ' class="left10">
                            <label for="asc_desc" class="left5">' . $lang['sea_descending'] . '</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span>' . $lang['sea_forums'] . ':</span>
                    </td>
                    <td>' . $search_in_forums . '
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="has-text-centered">
                        <div class="level-center-center is-flex">
                            <input type="radio" id="show_list" name="show_as" value="list"' . ($show_as === 'list' ? ' checked' : '') . '>
                            <label for="show_list" class="left5">' . $lang['sea_results_as_list'] . '</label>
                            <input type="radio" id="show_GETs" name="show_as" value="posts"' . ($show_as === 'posts' ? ' checked' : '') . ' class="left10">
                            <label for="show_GETs" class="left5">' . $lang['sea_results_as_posts'] . '</label>
                        </div>
                        <input type="submit" name="button" class="button is-small" value="' . $lang['gl_search'] . '">
                    </td>
                </tr>';

$HTMLOUT .= main_table($table_body) . '</form>' . $content;
