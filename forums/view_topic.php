<?php
global $CURUSER, $ste_config, $lang, $fluent, $pdo;

$attachments = $members_votes = $status = $topic_poll = $stafflocked = $child = $parent_forum_name = $math_image = '';
$math_text = $staff_tools = $staff_link = $now_viewing = '';
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : (isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0);
if (!is_valid_id($topic_id)) {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

$upload_errors_size = isset($_GET['se']) ? intval($_GET['se']) : 0;
$upload_errors_type = isset($_GET['ee']) ? intval($_GET['ee']) : 0;

$_forum_sort = isset($CURUSER['forum_sort']) ? $CURUSER['forum_sort'] : 'DESC';

$arr = $fluent->from('topics')
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
    ->leftJoin('forums ON topics.forum_id = forums.id')
    ->where('topics.id = ?', $topic_id)
    ->fetch();

if (($CURUSER['class'] < UC_STAFF && $arr['status'] != 'ok') || ($CURUSER['class'] < $min_delete_view_class && $arr['status'] === 'deleted')) {
    $arr = false;
}

if ($CURUSER['class'] < $arr['min_class_read'] || !is_valid_id($arr['topic_id']) || $CURUSER['class'] < $min_delete_view_class && $status == 'deleted' || $CURUSER['class'] < UC_STAFF && $status == 'recycled') {
    stderr($lang['gl_error'], $lang['gl_bad_id']);
}

$status = htmlsafechars($arr['status']);
switch ($status) {
    case 'ok':
        $status = '';
        $status_image = '';
        break;

    case 'recycled':
        $status = 'recycled';
        $status_image = '<img src="' . $site_config['pic_base_url'] . 'forums/recycle_bin.gif" alt="' . $lang['fe_recycled'] . '" class="tooltipper" title="' . $lang['fe_this_thread_is_currently'] . ' ' . $lang['fe_in_the_recycle_bin'] . '" />';
        break;

    case 'deleted':
        $status = 'deleted';
        $status_image = '<img src="' . $site_config['pic_base_url'] . 'forums/delete_icon.gif" alt="' . $lang['fe_deleted'] . '" class="tooltipper" title="' . $lang['fe_this_thread_is_currently'] . ' ' . $lang['fe_deleted'] . '" />';
        break;
}

$forum_id = $arr['forum_id'];
$topic_owner = $arr['user_id'];
$topic_name = htmlsafechars($arr['topic_name'], ENT_QUOTES);
$topic_desc1 = htmlsafechars($arr['topic_desc'], ENT_QUOTES);

$members_votes = [];
if ($arr['poll_id'] > 0) {
    $arr_poll = $fluent->from('forum_poll')
        ->where('id = ?', $arr['poll_id'])
        ->fetch();

    if ($CURUSER['class'] >= UC_STAFF) {
        $res_poll_voted = sql_query('SELECT DISTINCT user_id, ip, added
                    FROM forum_poll_votes
                    WHERE user_id > 0 AND poll_id = ' . sqlesc($arr['poll_id'])) or sqlerr(__FILE__, __LINE__);
        $who_voted = (mysqli_num_rows($res_poll_voted) > 0 ? '<hr>' : 'no votes yet');
        while ($arr_poll_voted = mysqli_fetch_assoc($res_poll_voted)) {
            $who_voted .= format_username($arr_poll_voted['user_id']);
        }
    }

    $res_did_they_vote_yet = sql_query('SELECT `option`
                            FROM forum_poll_votes
                            WHERE poll_id = ' . sqlesc($arr['poll_id']) . ' AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $voted = 0;
    $members_vote = 1000;
    if (mysqli_num_rows($res_did_they_vote_yet) > 0) {
        $voted = 1;
        while ($members_vote = mysqli_fetch_assoc($res_did_they_vote_yet)) {
            $members_votes[] = $members_vote['option'];
        }
    }
    $change_vote = ($arr_poll['change_vote'] === 'no' ? 0 : 1);
    $poll_open = (($arr_poll['poll_closed'] === 'yes' || $arr_poll['poll_starts'] > TIME_NOW || $arr_poll['poll_ends'] < TIME_NOW) ? 0 : 1);
    $poll_options = unserialize($arr_poll['poll_answers']);
    $multi_options = $arr_poll['multi_options'];
    $total_votes_res = sql_query('SELECT COUNT(id)
                FROM forum_poll_votes
                WHERE `option` < 21 AND poll_id = ' . sqlesc($arr['poll_id'])) or sqlerr(__FILE__, __LINE__);
    $total_votes_arr = mysqli_fetch_row($total_votes_res);
    $total_votes = $total_votes_arr[0];
    $res_non_votes = sql_query('SELECT COUNT(id)
                FROM forum_poll_votes
                WHERE `option` > 20 AND poll_id = ' . sqlesc($arr['poll_id'])) or sqlerr(__FILE__, __LINE__);
    $arr_non_votes = mysqli_fetch_row($res_non_votes);
    $num_non_votes = $arr_non_votes[0];
    $total_non_votes = ($num_non_votes > 0 ? ' [ ' . number_format($num_non_votes) . ' member' . ($num_non_votes == 1 ? '' : 's') . ' just wanted to see the results ]' : '');

    $topic_poll .= (($voted === 1 || $poll_open === 0) ? '' : '
    <form action="' . $site_config['baseurl'] . '/forums.php?action=poll" method="post" name="poll">
        <fieldset class="poll_select">
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="hidden" name="action_2" value="poll_vote" />') . '
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="forum_head_dark" colspan="5">
                            <div class="level-center">
                                <div class="flex flex-left padding10">
                                    <img src="' . $site_config['pic_base_url'] . 'forums/poll.gif" alt="" class="right10" />
                                    <span>Poll
                                        ' . ($arr_poll['poll_closed'] === 'yes' ? 'closed
                                    </span>' : ($arr_poll['poll_starts'] > TIME_NOW ? 'starts:
                                    </span> ' . get_date($arr_poll['poll_starts'], '') : ($arr_poll['poll_ends'] == 1356048000 ? '
                                    </span>' : ($arr_poll['poll_ends'] > TIME_NOW ? ' ends:
                                    </span> ' . get_date($arr_poll['poll_ends'], '', 0, 1) : '
                                    </span>')))) . '
                                </div>
                                <div class="flex flex-right">
                                    ' . ($CURUSER['class'] < UC_STAFF ? '' : '
                                    <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_edit&amp;topic_id=' . $topic_id . '" class="altlink bordered">
                                        <img src="' . $site_config['pic_base_url'] . 'forums/modify.gif" alt="" width="20px" />' . $lang['fe_edit'] . '
                                    </a>
                                    <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_reset&amp;topic_id=' . $topic_id . '" class="altlink bordered">
                                        <img src="' . $site_config['pic_base_url'] . 'forums/stop_watch.png" alt=" " width="20px" />' . $lang['fe_reset'] . '
                                    </a>
                                    ' . (($arr_poll['poll_ends'] > TIME_NOW || $arr_poll['poll_closed'] === 'no') ? '
                                    <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_close&amp;topic_id=' . $topic_id . '" class="altlink bordered">
                                        <img src="' . $site_config['pic_base_url'] . 'forums/clock.png" alt="" width="20px" />' . $lang['fe_close'] . '
                                    </a>' : '
                                    <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_open&amp;topic_id=' . $topic_id . '" class="altlink bordered">
                                        <img src="' . $site_config['pic_base_url'] . 'forums/clock.png" alt="" width="20px" />' . $lang['fe_start'] . '
                                    </a>') . '
                                    <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_delete&amp;topic_id=' . $topic_id . '" class="altlink bordered">
                                        <img src="' . $site_config['pic_base_url'] . 'forums/delete.gif" alt="" width="20px" />' . $lang['fe_delete'] . '
                                    </a>') . '
                                </div>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="5">
                            <div class="flex flex-left">
                                <span>
                                    <img src="' . $site_config['pic_base_url'] . 'forums/poll_question.png" alt="" width="25px" class="right10" />
                                </span>
                                <span>
                                    ' . format_comment($arr_poll['question']) . '
                                </span>
                            </div>
                        </th>
                    </tr>' . (($voted === 1 || $poll_open === 0) ? '' : '
                    <tr>
                        <th colspan="5">
                            <p>you may select up to <span>' . $multi_options . ' </span>option' . ($multi_options == 1 ? '' : 's') . '.</p>
                        </th>
                    </tr>') . '
                </thead>';
    $number_of_options = (int)$arr_poll['number_of_options'];
    for ($i = 0; $i < $number_of_options; ++$i) {
        if ($voted === 1) {
            $math_res = sql_query('SELECT COUNT(id)
                        FROM forum_poll_votes
                        WHERE poll_id = ' . sqlesc($arr['poll_id']) . ' AND OPTION = ' . sqlesc($i)) or sqlerr(__FILE__, __LINE__);
            $math_row = mysqli_fetch_row($math_res);
            $vote_count = $math_row[0];
            $math = $vote_count > 0 ? round(($vote_count / $total_votes) * 100) : 0;
            $math_text = $math . '% with ' . $vote_count . ' vote' . ($vote_count == 1 ? '' : 's');
            $math_image = '
            <table class="table table-bordered table-striped second">
                <tr>
                    <td>
                        <img src="' . $site_config['pic_base_url'] . 'forums/vote_img.gif" width="' . $math . '%" height="8" alt="' . $math_text . '" class="tooltipper" title="' . $math_text . '"  />
                    </td>
                </tr>
            </table>';
        }
        $topic_poll .= '
                <tr>
                    <td>' . (($voted === 1 || $poll_open === 0) ? '
                        <span>' . ($i + 1) . '.</span>' : ($multi_options == 1 ? '
                        <input type="radio" name="vote" value="' . $i . '" />' : '
                        <input type="checkbox" name="vote[]" id="vote[]" value="' . $i . '" />')) . '
                    </td>
                    <td>' . format_comment($poll_options[ $i ]) . '</td>
                    <td>' . $math_image . '</td>
                    <td><span>' . $math_text . '</span></td>
                    <td>' . (in_array($i, $members_votes) ? '
                        <img src="' . $site_config['pic_base_url'] . 'forums/check.gif" width="20px" alt=" " />
                        <span>' . $lang['fe_your_vote'] . '!</span>' : '') . '
                    </td>
                </tr>';
    }
    $topic_poll .= (($change_vote === 1 && $voted === 1) ? '
                <tr>
                    <td colspan="5">
                        <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=reset_vote&amp;topic_id=' . $topic_id . '" class="altlink bordered">
                            <img src="' . $site_config['pic_base_url'] . 'forums/stop_watch.png" alt="" width="20px" />' . $lang['fe_reset_your_vote'] . '!
                        </a>
                    </td>
                </tr>' : '') . ($voted === 1 ? '
                <tr>
                    <td colspan="5">' . $lang['fe_total_votes'] . ': ' . number_format($total_votes) . $total_non_votes . ($CURUSER['class'] < UC_STAFF ? '' : '
                        <a class="altlink bordered tooltipper"  title="' . $lang['fe_list_voters'] . '" id="toggle_voters">' . $lang['fe_list_voters'] . '</a>
                        <div id="voters">' . $who_voted . '</div>') . '
                    </td>
                </tr>
            </table>' : ($poll_open === 0 ? '' : '
                <tr>
                    <td>' . ($multi_options == 1 ? '
                        <input type="radio" name="vote" value="666" />' : '
                        <input type="checkbox" name="vote[]" id="vote[]" value="666" />') . '
                    </td>
                    <td colspan="4">
                        <span>' . $lang['fe_i_just_want_to_see_the_results'] . '!</span>
                    </td>
                </tr>') . (($voted === 1 || $poll_open === 0) ? '
            </table>' : '
                <tr>
                    <td colspan="5">
                        <input type="submit" name="button" class="button is-small" value="' . $lang['fe_vote'] . '!" />
                    </td>
                </tr>
            </table>
        </fieldset>
    </form>'));
}
if (isset($_GET['search'])) {
    $search = htmlsafechars($_GET['search']);
    $topic_name = highlightWords($topic_name, $search);
}
$forum_desc = ($arr['topic_desc'] !== '' ? '<span>' . htmlsafechars($arr['topic_desc'], ENT_QUOTES) . '</span>' : '');
$locked = ($arr['locked'] === 'yes' ? 'yes' : 'no');
$sticky = ($arr['sticky'] === 'yes' ? 'yes' : 'no');
$views = number_format($arr['views']);

$forum_name = htmlsafechars($arr['forum_name'], ENT_QUOTES);

if ($CURUSER['class'] >= UC_STAFF) {
    $staff_link = '<a class="altlink bordered" class="tooltipper" title="' . $lang['fe_staff_tools'] . '" id="tool_open">' . $lang['fe_staff_tools'] . '</a>';
}

if ($arr['num_ratings'] != 0) {
    $rating = round($arr['rating_sum'] / $arr['num_ratings'], 1);
}

$res_subscriptions = sql_query('SELECT id
            FROM subscriptions
            WHERE topic_id = ' . sqlesc($topic_id) . ' AND user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
$row_subscriptions = mysqli_fetch_row($res_subscriptions);
$subscriptions = ($row_subscriptions[0] > 0 ? '
        <a class="tooltipper right10" href="' . $site_config['baseurl'] . '/forums.php?action=delete_subscription&amp;topic_id=' . $topic_id . '" title="' . $lang['fe_unsubscribe_from_this_topic'] . '">
            <img src="' . $site_config['pic_base_url'] . 'forums/unsubscribe.gif" alt="-" width="20" />
        </a>' : '
        <a class="tooltipper right10" href="' . $site_config['baseurl'] . '/forums.php?action=add_subscription&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '" title="' . $lang['fe_subscribe_to_this_topic'] . '">
            <img src="' . $site_config['pic_base_url'] . 'forums/subscribe.gif" alt="+" width="20" />
        </a>');

sql_query('DELETE FROM now_viewing WHERE user_id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
sql_query('INSERT INTO now_viewing (user_id, forum_id, topic_id, added)
        VALUES(' . sqlesc($CURUSER['id']) . ', ' . sqlesc($forum_id) . ', ' . sqlesc($topic_id) . ', ' . TIME_NOW . ')') or sqlerr(__FILE__, __LINE__);

$keys['now_viewing'] = 'now_viewing_topic';
$topic_users_cache = $cache->get($keys['now_viewing']);
if ($topic_users_cache === false || is_null($topic_users_cache)) {
    $topicusers = '';
    $topic_users_cache = [];
    $res = sql_query('SELECT user_id
                FROM now_viewing
                WHERE topic_id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
    $actcount = mysqli_num_rows($res);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($topicusers) {
            $topicusers .= ", ";
        }
        $topicusers .= ($arr['perms'] & bt_options::PERMS_STEALTH ? '<i>' . $lang['fe_unkn0wn'] . '</i>' : format_username($arr['user_id']));
    }
    $topic_users_cache['topic_users'] = $topicusers;
    $topic_users_cache['actcount'] = $actcount;
    $cache->set($keys['now_viewing'], $topic_users_cache, $site_config['expires']['forum_users']);
}
if (!$topic_users_cache['topic_users']) {
    $topic_users_cache['topic_users'] = $lang['fe_there_not_been_active_visit_15'];
}
$topic_users = $topic_users_cache['topic_users'];
if ($topic_users != '') {
    $topic_users = '' . $lang['fe_currently_viewing_this_topic'] . ': ' . $topic_users;
}
sql_query('UPDATE topics SET views = views + 1 WHERE id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);

$res_count = sql_query('SELECT COUNT(id) AS count
                FROM posts
                WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'status = "ok" AND' : ($CURUSER['class'] < $min_delete_view_class ? 'status != "deleted" AND' : '')) . ' topic_id = ' . sqlesc($topic_id)) or sqlerr(__FILE__, __LINE__);
$arr_count = mysqli_fetch_row($res_count);
$count = $arr_count[0];

$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 15;
$subscription_on_off = (isset($_GET['s']) ? ($_GET['s'] == 1 ? '
        <div>' . $lang['fe_sub_to_topic'] . '
            <img src="' . $site_config['pic_base_url'] . 'forums/subscribe.gif" alt="' . $lang['fe_subscribed'] . '" class="tooltipper" title="' . $lang['fe_subscribed'] . '"  width="25" />
        </div>' : '
        <div>' . $lang['fe_unsub_to_topic'] . '
            <img src="' . $site_config['pic_base_url'] . 'forums/unsubscribe.gif" alt="' . $lang['fe_unsubscribe'] . '" class="tooltipper" title="' . $lang['fe_unsubscribe'] . '" width="25" />
        </div>') : '');
list($menu, $LIMIT) = pager_new($count, $perpage, $page, './forums.php?action=view_topic&amp;topic_id=' . $topic_id . (isset($_GET['perpage']) ? '&amp;perpage=' . $perpage : ''));

$res = $fluent->from('posts')
    ->select('posts.id AS post_id')
    ->select('posts.topic_id')
    ->select('posts.user_id')
    ->select('posts.staff_lock')
    ->select('posts.added')
    ->select('posts.body')
    ->select('posts.edited_by')
    ->select('posts.edit_date')
    ->select('posts.icon')
    ->select('posts.post_title')
    ->select('posts.bbcode')
    ->select('posts.post_history')
    ->select('posts.edit_reason')
    ->select('INET6_NTOA(posts.ip) AS ip')
    ->select('posts.status AS post_status')
    ->select('posts.anonymous')
    ->where('posts.topic_id = ?', $topic_id)
    ->orderBy("posts.id $_forum_sort")
    ->limit(str_replace('LIMIT ', $LIMIT));

if (($CURUSER['class'] < UC_STAFF && $arr['status'] != 'ok') || ($CURUSER['class'] < $min_delete_view_class && $arr['status'] === 'deleted')) {
    $arr = false;
}

$may_post = ($CURUSER['class'] >= $arr['min_class_write'] && $CURUSER['forum_post'] == 'yes' && $CURUSER['suspended'] == 'no');

$locked_or_reply_button = ($locked === 'yes' ? '
        <span>
            <img src="' . $site_config['pic_base_url'] . 'forums/thread_locked.gif" alt="' . $lang['fe_thread_locked'] . '" class="tooltipper" title="' . $lang['fe_thread_locked'] . '" width="22" />
            <span>' . $lang['fe_this_topic_is_locked'] . ', you may not post in this thread.</span>' : ($CURUSER['forum_post'] == 'no' ? '
            <span>Your posting rights have been removed. You may not post.</span>
        </span>' : '
        <a href="' . $site_config['baseurl'] . '/forums.php?action=post_reply&amp;topic_id=' . $topic_id . '" class="button is-small">
            Add Reply
        </a>'));

if ($arr['parent_forum'] > 0) {
    $parent_forum_res = sql_query('SELECT name AS parent_forum_name
                        FROM forums WHERE id = ' . sqlesc($arr['parent_forum'])) or sqlerr(__FILE__, __LINE__);
    $parent_forum_arr = mysqli_fetch_row($parent_forum_res);
    $child = ($arr['parent_forum'] > 0 ? '
        <span> [ ' . $lang['fe_child_board'] . ' ]</span>' : '');
    //$parent_forum_name = '
    //    <img src="' . $site_config['pic_base_url'] . 'forums/arrow_next.gif" alt="&#9658;" class="tooltipper" title="&#9658;" />
    //    <a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . $forum_id . '">' . htmlsafechars($parent_forum_arr[0], ENT_QUOTES) . '</a>';
}

$the_top_and_bottom = "
        <tr>
            <th colspan='5'>
                <div class='level-center'>
                    <div class='flex-left padding10'>
                        $subscriptions
                    </div>
                    <div class='flex-right padding10'>
                        " . (($count > $perpage) ? $menu : '') . "
                        " . ($may_post ? $locked_or_reply_button : "
                        <span>
                            You are not permitted to post in this thread.
                        </span>") . "
                    </div>
                </div>
            </th>
        </tr>";

$location_bar = '
        <div class="level-center">
        ' . $mini_menu . (($topic_owner == $CURUSER['id'] && $arr['poll_id'] == 0 || $CURUSER['class'] >= UC_STAFF && $arr['poll_id'] == 0) ? '
            <a href="' . $site_config['baseurl'] . '/forums.php?action=poll&amp;action_2=poll_add&amp;topic_id=' . $topic_id . '" class="altlink bordered">' . $lang['fe_add_poll'] . '</a>' : '') . '
        </div>';

$HTMLOUT .= "
    <div class='container is-fluid portlet'>" . ($upload_errors_size > 0 ? ($upload_errors_size === 1 ? '
        <div>One file was not uploaded. The maximum file size allowed is: ' . mksize($max_file_size) . '.</div>' : '
        <div>' . $upload_errors_size . ' file were not uploaded. The maximum file size allowed is: ' . mksize($max_file_size) . '.</div>') : '') . ($upload_errors_type > 0 ? ($upload_errors_type === 1 ? '
        <div>One file was not uploaded. The accepted formats are zip and rar.</div>' : '
        <div>' . $upload_errors_type . ' files were not uploaded. The accepted formats are zip and rar.</div>') : '') . $location_bar . $topic_poll . $subscription_on_off . ($CURUSER['class'] < UC_STAFF ? '' : '
        <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post" name="checkme" onsubmit="return SetChecked(this,\'post_to_mess_with\')" enctype="multipart/form-data">') . (isset($_GET['count']) ? '
            <div>' . intval($_GET['count']) . ' PMs Sent</div>' : '') . '
            <table class="table table-bordered no_hover third">
                <thead>
                ' . $the_top_and_bottom . '
                    <tr>
                        <th colspan="5">
                            <div class="level-center">
                                <div class="flex flex-left padding10">
                                    <img src="' . $site_config['pic_base_url'] . 'forums/topic_normal.gif" alt="' . $lang['fe_topic'] . '" class="right10 tooltipper" title="' . $lang['fe_topic'] . '" />' . $lang['fe_author'] . '
                                    <span class="left10">
                                        ' . (getRate($topic_id, 'topic')) . '
                                    </span>
                                </div>
                                <div class="flex flex-right padding10">
                                    ' . $lang['fe_topic'] . ': ' . $topic_name . '  [ ' . $lang['fe_read'] . ' ' . $views . ' ' . $lang['fe_times'] . ' ]
                                </div>
                            </div>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3">' . $topic_users . '</th>
                    </tr>
                </thead>';

foreach ($res as $arr) {
    $post_user = get_user_data($arr['user_id']);
    unset($post_user['bonuscomment']);
    unset($post_user['modcomment']);
    unset($post_user['where_is']);
    unset($post_user['ip']);
    $arr = array_merge($arr, $post_user);
    echo json_encode($arr);
    die();

    $moodname = (isset($mood['name'][ $arr['mood'] ]) ? htmlsafechars($mood['name'][ $arr['mood'] ]) : 'is feeling neutral');
    $moodpic = (isset($mood['image'][ $arr['mood'] ]) ? htmlsafechars($mood['image'][ $arr['mood'] ]) : 'noexpression.gif');
    $post_icon = ($arr['icon'] !== '' ? '<img src="' . $site_config['pic_base_url'] . 'smilies/' . htmlsafechars($arr['icon']) . '.gif" alt="icon" class="tooltipper" title="icon" /> ' : '<img src="' . $site_config['pic_base_url'] . 'forums/topic_normal.gif" alt="icon" class="tooltipper" title="icon" /> ');
    $post_title = ($arr['post_title'] !== '' ? ' <span>' . htmlsafechars($arr['post_title'], ENT_QUOTES) . '</span>' : '');
    $stafflocked = ( /*$CURUSER['class'] == UC_SYSOP && */
    $arr['staff_lock'] == 1 ? "<img src='{$site_config['pic_base_url']}locked.gif' border='0' alt='" . $lang['fe_post_locked'] . "' class='tooltipper' title='" . $lang['fe_post_locked'] . "' />" : '');
    $member_reputation = $arr['username'] != '' ? get_reputation($arr, 'posts', true, (int)$arr['post_id']) : '';
    $edited_by = '';
    if ($arr['edit_date'] > 0) {
        $res_edited = sql_query('SELECT username FROM users WHERE id=' . sqlesc($arr['edited_by']));
        $arr_edited = mysqli_fetch_assoc($res_edited);
        //== Anonymous
        if ($arr['anonymous'] == 'yes') {
            if ($CURUSER['class'] < UC_STAFF && $arr['user_id'] != $CURUSER['id']) {
                $edited_by = '<span>' . $lang['vmp_last_edit_by_anony'] . '
                 at ' . get_date($arr['edit_date'], '') . ' UTC ' . ($arr['edit_reason'] !== '' ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span>' : '') . '
                 ' . (($CURUSER['class'] >= UC_STAFF && $arr['post_history'] !== '') ? ' <a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int)$arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">' . $lang['fe_read_post_history'] . '</a></span>' : '</span>');
            } else {
                $edited_by = '<span>' . $lang['vmp_last_edit_by_anony'] . ' [<a class="altlink bordered" href="userdetails.php?id=' . (int)$arr['edited_by'] . '">' . htmlsafechars($arr_edited['username']) . '</a>]
                 at ' . get_date($arr['edit_date'], '') . ' UTC ' . ($arr['edit_reason'] !== '' ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span>' : '') . '
                 ' . (($CURUSER['class'] >= UC_STAFF && $arr['post_history'] !== '') ? ' <a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int)$arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">' . $lang['fe_read_post_history'] . '</a></span>' : '</span>');
            }
        } else {
            $edited_by = '<span>' . $lang['fe_last_edited_by'] . ' <a class="altlink bordered" href="userdetails.php?id=' . (int)$arr['edited_by'] . '">' . htmlsafechars($arr_edited['username']) . '</a>
                 at ' . get_date($arr['edit_date'], '') . ' UTC ' . ($arr['edit_reason'] !== '' ? ' </span>[ ' . $lang['fe_reason'] . ': ' . htmlsafechars($arr['edit_reason']) . ' ] <span>' : '') . '
                 ' . (($CURUSER['class'] >= UC_STAFF && $arr['post_history'] !== '') ? ' <a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=view_post_history&amp;post_id=' . (int)$arr['post_id'] . '&amp;forum_id=' . $forum_id . '&amp;topic_id=' . $topic_id . '">' . $lang['fe_read_post_history'] . '</a></span>' : '</span>');
        }
        //==
    }
    //==== highlight for search
    $body = ($arr['bbcode'] == 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body']));
    if (isset($_GET['search'])) {
        $body = highlightWords($body, $search);
        $post_title = highlightWords($post_title, $search);
    }
    $post_id = (int)$arr['post_id'];
    //=== if there are attachments, let's get them!
    $attachments_res = sql_query('SELECT id, file_name, extension, size FROM attachments WHERE post_id =' . sqlesc($post_id) . ' AND user_id = ' . sqlesc($arr['id']));
    if (mysqli_num_rows($attachments_res) > 0) {
        $attachments = '<table class="table table-bordered table-striped fourth"><tr><td><span>' . $lang['fe_attachments'] . ':</span><hr>';
        while ($attachments_arr = mysqli_fetch_assoc($attachments_res)) {
            $attachments .= '<span>' . ($attachments_arr['extension'] === 'zip' ? ' <img src="' . $site_config['pic_base_url'] . 'forums/zip.gif" alt="' . $lang['fe_zip'] . '" class="tooltipper" title="' . $lang['fe_zip'] . '" width="18" /> ' : ' <img src="' . $site_config['pic_base_url'] . 'forums/rar.gif" alt="' . $lang['fe_rar'] . '" class="tooltipper" title="' . $lang['fe_rar'] . '" width="18" /> ') . '
                    <a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=download_attachment&amp;id=' . (int)$attachments_arr['id'] . '" class="tooltipper" title="' . $lang['fe_download_attachment'] . '" target="_blank">
                    ' . htmlsafechars($attachments_arr['file_name']) . '</a> <span>[' . mksize($attachments_arr['size']) . ']</span>&#160;&#160;</span>';
        }
        $attachments .= '</td></tr></table>';
    }
    $width = 300;
    $height = 100;
    //=== signature stuff
    $signature = (($CURUSER['opt1'] & user_options::SIGNATURES) ? '' : ($arr['signature'] == '' ? '' : ($arr['anonymous'] == 'yes' || $arr['perms'] & bt_options::PERMS_STEALTH ? '<table class="table table-bordered table-striped fifth"><tr><td><hr><img style="max-width:' . $width . 'px;max-height:' . $height . 'px;" src="' . $site_config['pic_base_url'] . 'anonymous_2.jpg" alt="Signature" /></td></tr></table>' : '<table class="table table-bordered table-striped sixth"><tr><td><hr>' . format_comment($arr['signature']) . '</td></tr></table>')));
    //=== post status
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
        <tr class="no_hover">
            <td colspan="3">
                <table class="table table-bordered table-striped seventh">
                    <thead>
                        <tr>
                            <th colspan="2">
                                <div class="level-center">
                                    <div class="flex flex-left padding10">
                                        <a name="' . $post_id . '"></a>
                                        ' . ($CURUSER['class'] >= UC_STAFF ? '
                                        <input type="checkbox" name="post_to_mess_with[]" value="' . $post_id . '" />' : '') . '
                                        <a href="javascript:window.alert(\'' . $lang['fe_direct_link_to_this_post'] . ':\n ' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#' . $post_id . '\');">
                                            <img src="' . $site_config['pic_base_url'] . 'forums/link.gif" alt="' . $lang['fe_direct_link_to_this_post'] . '" class="tooltipper" title="' . $lang['fe_direct_link_to_this_post'] . '" width="12px" class="tooltipper" />
                                        </a>
                                        <span>' . ($arr['anonymous'] == 'yes' ? '<i>' . $lang['fe_anonymous'] . '</i>' : '' . htmlsafechars($arr['username']) . '') . '</span>
                                        <span class="tool">
                                            <a href="javascript:;" onclick="PopUp(\'usermood.php\',\'Mood\',530,500,1,1);">
                                                <img src="' . $site_config['pic_base_url'] . 'smilies/' . $moodpic . '" alt="' . $moodname . '" />
                                                <span class="tip">
                                                    ' . ($arr['anonymous'] == 'yes' ? '<i>' . $lang['fe_anonymous'] . '</i>' : htmlsafechars($arr['username'])) . ' ' . $moodname . '!
                                                </span>
                                            </a>
                                        </span>
                                        ' . (($arr['paranoia'] >= 2 && $CURUSER['class'] < UC_STAFF) ? '
                                        <img src="' . $site_config['pic_base_url'] . 'smilies/tinfoilhat.gif" alt="' . $lang['fe_i_wear_a_tinfoil_hat'] . '!" class="tooltipper" title="' . $lang['fe_i_wear_a_tinfoil_hat'] . '!" />' : get_user_ratio_image($arr['uploaded'], ($site_config['ratio_free'] ? '0' : $arr['downloaded']))) . '</span>
                                        <span>' . $post_icon . $post_title . '&#160;&#160;&#160;&#160; ' . $lang['fe_posted_on'] . ': ' . get_date($arr['added'], '') . ' [' . get_date($arr['added'], '', 0, 1) . ']</span>
                                    </div>
                                    <div class="flex flex-right padding10">
                            <span>

            <a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=post_reply&amp;topic_id=' . $topic_id . '&amp;quote_post=' . $post_id . '&amp;key=' . $arr['added'] . '"><img src="' . $site_config['pic_base_url'] . 'forums/quote.gif" alt="' . $lang['fe_quote'] . '" class="tooltipper" title="' . $lang['fe_quote'] . '" /> ' . $lang['fe_quote'] . '</a>
            ' . (($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $arr['id']) ? ' <a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=edit_post&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '"><img src="' . $site_config['pic_base_url'] . 'forums/modify.gif" alt="' . $lang['fe_modify'] . '" class="tooltipper" title="' . $lang['fe_modify'] . '" /> ' . $lang['fe_modify'] . '</a>
             <a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=delete_post&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $site_config['pic_base_url'] . 'forums/delete.gif" alt="' . $lang['fe_delete'] . '" class="tooltipper" title="' . $lang['fe_delete'] . '" /> ' . $lang['fe_remove'] . '</a>' : '') . '
             <!--<a class="altlink bordered" href="' . $site_config['baseurl'] . '/forums.php?action=report_post&amp;topic_id=' . $topic_id . '&amp;post_id=' . $post_id . '"><img src="' . $site_config['pic_base_url'] . 'forums/report.gif" alt="' . $lang['fe_report'] . '" class="tooltipper" title="' . $lang['fe_report'] . '" width="22" /> ' . $lang['fe_report'] . '</a>-->
             <a href="' . $site_config['baseurl'] . '/report.php?type=Post&amp;id=' . $post_id . '&amp;id_2=' . $topic_id . '"><img src="' . $site_config['pic_base_url'] . 'forums/report.gif" alt="' . $lang['fe_report'] . '" class="tooltipper" title="' . $lang['fe_report'] . '" width="22" /> ' . $lang['fe_report'] . '</a>
         ' . ($CURUSER['class'] == UC_MAX && $arr['staff_lock'] == 1 ? '<a href="' . $site_config['baseurl'] . '/forums.php?action=staff_lock&amp;mode=unlock&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $site_config['pic_base_url'] . 'key.gif" alt="' . $lang['fe_un_lock'] . '" class="tooltipper" title="' . $lang['fe_un_lock'] . '" /> ' . $lang['fe_unlock_post'] . '</a>&#160;' : '') . '
             ' . ($CURUSER['class'] == UC_MAX && $arr['staff_lock'] == 0 ? '<a href="' . $site_config['baseurl'] . '/forums.php?action=staff_lock&amp;mode=lock&amp;post_id=' . $post_id . '&amp;topic_id=' . $topic_id . '"><img src="' . $site_config['pic_base_url'] . 'key.gif" alt="' . $lang['fe_lock'] . '" class="tooltipper" title="' . $lang['fe_lock'] . '" /> ' . $lang['fe_lock_post'] . '</a>' : '') . $stafflocked . '
            <a href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#top"><img src="' . $site_config['pic_base_url'] . 'forums/up.gif" alt="' . $lang['fe_top'] . '" class="tooltipper" title="' . $lang['fe_top'] . '" /></a>
          <a href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . $topic_id . '&amp;page=' . $page . '#bottom"><img src="' . $site_config['pic_base_url'] . 'forums/down.gif" alt="' . $lang['fe_bottom'] . '" class="tooltipper" title="' . $lang['fe_bottom'] . '" /></a>
            </span>
            </div>
            </div>
                            </th>
            </tr>
                            </thead>
            <tr class="no_hover">
            <td class="w-10">' . ($arr['anonymous'] == 'yes' ? '<img style="max-width:' . $width . 'px;" src="' . $site_config['pic_base_url'] . 'anonymous_1.jpg" alt="avatar" />' : avatar_stuff($arr)) . '
            ' . ($arr['anonymous'] == 'yes' ? '<i>' . $lang['fe_anonymous'] . '</i>' : format_username($arr)) . ($arr['anonymous'] == 'yes' || $arr['title'] == '' ? '' : '<span>[' . htmlsafechars($arr['title']) . ']</span>') . '
            <span>' . ($arr['anonymous'] == 'yes' ? '' : get_user_class_name($arr['class'])) . '</span>
            ' . ($arr['last_access'] > (TIME_NOW - 300) && $arr['perms'] < bt_options::PERMS_STEALTH ? ' <img src="' . $site_config['pic_base_url'] . 'forums/online.gif" alt="Online" class="tooltipper" title="Online" border="0" /> Online' : ' <img src="' . $site_config['pic_base_url'] . 'forums/offline.gif" border="0" alt="' . $lang['fe_offline'] . '" class="tooltipper" title="' . $lang['fe_offline'] . '" /> ' . $lang['fe_offline'] . '') . '
            ' . $lang['fe_karma'] . ': ' . number_format($arr['seedbonus']) . '' . $member_reputation . '' . ($arr['google_talk'] !== '' ? ' <a href="http://talkgadget.google.com/talkgadget/popout?member=' . htmlsafechars($arr['google_talk']) . '" class="tooltipper" title="' . $lang['fe_click_for_google_talk_gadget'] . '"  target="_blank"><img src="' . $site_config['pic_base_url'] . 'forums/google_talk.gif" alt="' . $lang['fe_google_talk'] . '" /></a> ' : '') . ($arr['icq'] !== '' ? ' <a href="http://people.icq.com/people/&amp;uin=' . htmlsafechars($arr['icq']) . '" class="tooltipper" title="' . $lang['fe_click_to_open_icq_page'] . '" target="_blank"><img src="' . $site_config['pic_base_url'] . 'forums/icq.gif" alt="icq" /></a> ' : '') . ($arr['msn'] !== '' ? ' <a href="http://members.msn.com/' . htmlsafechars($arr['msn']) . '" target="_blank" class="tooltipper" title="' . $lang['fe_click_to_see_msn_details'] . '"><img src="' . $site_config['pic_base_url'] . 'forums/msn.gif" alt="msn" /></a> ' : '') . ($arr['aim'] !== '' ? ' <a href="http://aim.search.aol.com/aol/search?s_it=searchbox.webhome&amp;q=' . htmlsafechars($arr['aim']) . '" target="_blank" class="tooltipper" title="' . $lang['fe_click_to_search_on_aim'] . '"><img src="' . $site_config['pic_base_url'] . 'forums/aim.gif" alt="AIM" /></a> ' : '') . ($arr['yahoo'] !== '' ? ' <a href="http://webmessenger.yahoo.com/?im=' . htmlsafechars($arr['yahoo']) . '" target="_blank" class="tooltipper" title="' . $lang['fe_click_to_open_yahoo'] . '"><img src="' . $site_config['pic_base_url'] . 'forums/yahoo.gif" alt="yahoo" /></a> ' : '') . '' . ($arr['website'] !== '' ? ' <a href="' . htmlsafechars($arr['website']) . '" target="_blank" class="tooltipper" title="' . $lang['fe_click_to_go_to_website'] . '"><img src="' . $site_config['pic_base_url'] . 'forums/website.gif" alt="website" /></a> ' : '') . ($arr['show_email'] == 'yes' ? ' <a href="mailto:' . htmlsafechars($arr['email']) . '" class="tooltipper" title="' . $lang['fe_click_to_email'] . '" target="_blank"><img src="' . $site_config['pic_base_url'] . 'email.gif" alt="email" width="25" /> </a>' : '') . '
            ' . ($CURUSER['class'] >= UC_STAFF ? '
            <ul class="makeMenu">
                <li>' . htmlsafechars($arr['ip']) . '
                    <ul>
                    <li><a href="https://ws.arin.net/?queryinput=' . htmlsafechars($arr['ip']) . '" class="tooltipper" title="' . $lang['vt_whois_to_find_isp_info'] . '" target="_blank">' . $lang['vt_ip_whois'] . '</a></li>
                    <li><a href="http://www.infosniper.net/index.php?ip_address=' . htmlsafechars($arr['ip']) . '" class="tooltipper" title="' . $lang['vt_ip_to_map_using_infosniper'] . '!" target="_blank">' . $lang['vt_ip_to_map'] . '</a></li>
                </ul>
                </li>
            </ul>' : '') . '
            </td>
            <td class="w-50 ' . $post_status . '" colspan="2">' . $body . $edited_by . '</td></tr>
            <tr class="no_hover"><td></td><td colspan="2">' . $signature . '</td></tr>
            <tr class="no_hover"><td></td><td colspan="2">' . $attachments . '</td></tr>
            <tr class="no_hover"><td colspan="3">' . (($arr['paranoia'] >= 1 && $CURUSER['class'] < UC_STAFF) ? '' : '
            <span><img src="' . $site_config['pic_base_url'] . 'up.png" alt="' . $lang['vt_uploaded'] . '" class="tooltipper" title="' . $lang['vt_uploaded'] . '" /> ' . mksize($arr['uploaded']) . '</span>&#160;&#160;
            ' . ($site_config['ratio_free'] ? '' : '<span><img src="' . $site_config['pic_base_url'] . 'dl.png" alt="' . $lang['vt_downloaded'] . '" class="tooltipper" title="' . $lang['vt_downloaded'] . '" /> ' . mksize($arr['downloaded']) . '</span>') . '&#160;&#160;') . (($arr['paranoia'] >= 2 && $CURUSER['class'] < UC_STAFF) ? '' : '' . $lang['vt_ratio'] . ': ' . member_ratio($arr['uploaded'], $site_config['ratio_free'] ? '0' : $arr['downloaded']) . '&#160;&#160;
            ' . ($arr['hit_and_run_total'] == 0 ? '<img src="' . $site_config['pic_base_url'] . 'forums/no_hit_and_runs2.gif" width="22" alt="' . ($arr['anonymous'] == 'yes' ? '' . $lang['fe_anonymous'] . '' : htmlsafechars($arr['username'])) . ' ' . $lang['vt_has_never_hit'] . ' &amp; ran!" class="tooltipper" title="' . ($arr['anonymous'] == 'yes' ? '' . $lang['fe_anonymous'] . '' : htmlsafechars($arr['username'])) . ' ' . $lang['vt_has_never_hit'] . ' &amp; ran!" />' : '') . '
            &#160;&#160;&#160;&#160;') . '
            <a class="altlink bordered" href="pm_system.php?action=send_message&amp;receiver=' . $arr['id'] . '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) . '"><img src="' . $site_config['pic_base_url'] . 'forums/send_pm.png" alt="' . $lang['vt_send_pm'] . '" class="tooltipper" title="' . $lang['vt_send_pm'] . '" width="18" /> ' . $lang['vt_send_message'] . '</a></td></tr></table></td></tr>';
    $attachments = '';
} //=== end while loop
//=== update the last post read by CURUSER
sql_query('DELETE FROM `read_posts` WHERE user_id =' . sqlesc($CURUSER['id']) . ' AND `topic_id` = ' . sqlesc($topic_id));
sql_query('INSERT INTO `read_posts` (`user_id` ,`topic_id` ,`last_post_read`) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($topic_id) . ', ' . sqlesc($post_id) . ')');
$cache->delete('last_read_post_' . $topic_id . '_' . $CURUSER['id']);
$cache->delete('sv_last_read_post_' . $topic_id . '_' . $CURUSER['id']);

$HTMLOUT .= $the_top_and_bottom . '</table>
    <span>' . $location_bar . '</span><a name="bottom"></a>
    ' . ($CURUSER['class'] >= UC_STAFF ? '<img src="' . $site_config['pic_base_url'] . 'forums/tools.png" alt="' . $lang['vt_tools'] . '" class="tooltipper" title="' . $lang['vt_tools'] . '" width="22" /> ' . $staff_link . ' <img src="' . $site_config['pic_base_url'] . 'forums/tools.png" alt="' . $lang['vt_tools'] . '" class="tooltipper" title="' . $lang['vt_tools'] . '" width="22" />
     <div id="tools">

    <table class="table table-bordered table-striped eighth">
     <tr>
    <td class="forum_head_dark" colspan="4">' . $lang['fe_staff_tools'] . '</td>
    </tr>
          <tr>
            <td colspan="3">
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="hidden" name="forum_id" value="' . $forum_id . '" />
      <table class="table table-bordered table-striped ninth">
          <tr>
            <td width="18">
            <img src="' . $site_config['pic_base_url'] . 'forums/recycle_bin.gif" alt="' . $lang['vt_recycle'] . '" class="tooltipper" title="' . $lang['vt_recycle'] . '" width="22" /></td>
            <td>
            <input type="radio" name="action_2" value="send_to_recycle_bin" />' . $lang['vt_send_to_recycle_bin'] . '
            <input type="radio" name="action_2" value="remove_from_recycle_bin" />' . $lang['fe_remove'] . ' ' . $lang['vt_from_recycle_bin'] . '
            </td>
            <td width="18"><img src="' . $site_config['pic_base_url'] . 'forums/delete.gif" alt="' . $lang['fe_delete'] . '" class="tooltipper" title="' . $lang['fe_delete'] . '" /></td>
            <td>
            <input type="radio" name="action_2" value="delete_posts" />' . $lang['fe_delete'] . '
            ' . ($CURUSER['class'] < $min_delete_view_class ? '' : '
            <input type="radio" name="action_2" value="un_delete_posts" /><span>*</span>Un-' . $lang['fe_delete'] . '') . '
            </td>
            <td width="18">
            <img src="' . $site_config['pic_base_url'] . 'forums/merge.gif" alt="' . $lang['vt_merge'] . '" class="tooltipper" title="' . $lang['vt_merge'] . '" /></td>
            <td>
            <input type="radio" name="action_2" value="merge_posts" />' . $lang['vt_merge_with'] . '
            <input type="radio" name="action_2" value="append_posts" />' . $lang['vt_append_to'] . '
            </td>
            <td>
            ' . $lang['fe_topic'] . ':<input type="text" size="2" name="new_topic" value="' . $topic_id . '" />
          </td>
          </tr>
        </table>
      <table class="table table-bordered table-striped tenth">
          <tr>
            <td width="18">
            <img src="' . $site_config['pic_base_url'] . 'forums/split.gif" alt="' . $lang['vt_split'] . '" class="tooltipper" title="' . $lang['vt_split'] . '" width="18" /></td>
            <td>
            <input type="radio" name="action_2" value="split_topic" />' . $lang['vt_split_topic'] . '
            </td>
            <td>
            ' . $lang['fe_new_topic_name'] . ':<input type="text" size="20" maxlength="120" name="new_topic_name" value="' . ($topic_name !== '' ? $topic_name : '') . '" /> [required]
            ' . $lang['fe_new_topic_desc'] . ':<input type="text" size="20" maxlength="120" name="new_topic_desc" value="" />
            </td>
            <td width="18"><img src="' . $site_config['pic_base_url'] . 'forums/send_pm.png" alt="' . $lang['vt_send_pm'] . '" class="tooltipper" title="' . $lang['vt_send_pm'] . '" width="18" /></td>
            <td>
            <a class="altlink bordered tooltipper" title="' . $lang['vt_send_pm_select_mem'] . ' - click" id="pm_open">' . $lang['vt_send_pm'] . ' </a>[click]
            </td>
          </tr>
        </table>
      <div id="pm">
      <table class="table table-bordered table-striped eleventh">
          <tr>
            <td class="forum_head_dark" colspan="2">' . $lang['vt_send_pm_select_mem'] . '</td>
          </tr>
          <tr>
            <td>
          <span>' . $lang['vt_subject'] . ':</span>
            </td>
            <td>
            <input type="text" size="20" maxlength="120" class="text_default" name="subject" value="" />
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
            <input type="radio" name="pm_from" value="0" checked /> ' . $lang['vt_system'] . '
            <input type="radio" name="pm_from" value="1" /> ' . format_username($CURUSER) . '
            </td>
      </tr>
      </table>
      </div>
      <hr></td>
            <td>
            <a class="altlink bordered tooltipper" href="javascript:SetChecked(1,\'post_to_mess_with[]\')" title="' . $lang['vt_select_all_posts_and_use_the_following_options'] . '"> ' . $lang['vt_select_all'] . '</a>
            <a class="altlink bordered tooltipper" href="javascript:SetChecked(0,\'post_to_mess_with[]\')" title="' . $lang['vt_unselect_all_posts'] . '">' . $lang['vt_un_select_all'] . '</a>
            <input type="submit" name="button" class="button is-small" value="' . $lang['vt_with_selected'] . '" />
        </td>
      </tr>
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/pinned.gif" alt="' . $lang['fe_pinned'] . '" class="tooltipper" title="' . $lang['fe_pinned'] . '" /></td>
            <td>
            <span>' . $lang['vt_pin'] . ' ' . $lang['fe_topic'] . ':</span></td>
            <td>
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="set_pinned" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="radio" name="pinned" value="yes" ' . ($sticky === 'yes' ? 'checked' : '') . ' /> Yes
            <input type="radio" name="pinned" value="no" ' . ($sticky === 'no' ? 'checked' : '') . ' /> No</td>
            <td>
            <input type="submit" name="button" class="button is-small" value="Set ' . $lang['fe_pinned'] . '" />
            </form></td>
      </tr>
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/thread_locked.gif" alt="' . $lang['fe_locked'] . '" class="tooltipper" title="' . $lang['fe_locked'] . '" width="22" /></td>
            <td>
            <span>' . $lang['fe_lock'] . ' ' . $lang['fe_topic'] . ':</span></td>
            <td>
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="set_locked" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="radio" name="locked" value="yes" ' . ($locked === 'yes' ? 'checked' : '') . ' /> Yes
            <input type="radio" name="locked" value="no" ' . ($locked === 'no' ? 'checked' : '') . ' /> No</td>
            <td>
            <input type="submit" name="button" class="button is-small" value="' . $lang['vt_lock_topic'] . '" />
            </form></td>
      </tr>
       <tr>
            <td width="28">
    <!-- needed to add later RS.     -->
         <img src="' . $site_config['pic_base_url'] . 'forums/move.gif" alt="' . $lang['vt_move'] . '" class="tooltipper" title="' . $lang['vt_move'] . '" width="22" /></td>
            <td>
            <span>' . $lang['vt_move'] . ' ' . $lang['fe_topic'] . ':</span></td>
            <td>
<form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="move_topic" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <select name="forum_id">
            ' . insert_quick_jump_menu($forum_id, $staff = true) . '</select></td>
            <td>
            <input type="submit" name="button" class="button is-small" value="' . $lang['vt_move_topic'] . '" />
            </form> <!--//-->
            </td>
      </tr>
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/modify.gif" alt="' . $lang['fe_modify'] . '" class="tooltipper" title="' . $lang['fe_modify'] . '" /></td>
            <td>
            <span>' . $lang['vt_rename'] . ' ' . $lang['fe_topic'] . ':</span></td>
            <td>
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="rename_topic" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="text" size="40" maxlength="120" name="new_topic_name" value="' . ($topic_name !== '' ? $topic_name : '') . '" /></td>
            <td>
            <input type="submit" name="button" class="button is-small" value="' . $lang['vt_rename_topic'] . '" />
            </form>
            </td>
      </tr>
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/modify.gif" alt="' . $lang['fe_modify'] . '" class="tooltipper" title="' . $lang['fe_modify'] . '" /></td>
            <td>
            <span>' . $lang['vt_change_topic_desc'] . ':</span></td>
            <td>
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="change_topic_desc" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="text" size="40" maxlength="120" name="new_topic_desc" value="' . ($topic_desc1 !== '' ? $topic_desc1 : '') . '" /></td>
            <td>
            <input type="submit" name="button" class="button is-small" value="' . $lang['vt_change_desc'] . '" />
            </form>
            </td>
      </tr>
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/merge.gif" alt="' . $lang['vt_merge'] . '" class="tooltipper" title="' . $lang['vt_merge'] . '" /></td>
            <td>
            <span>' . $lang['vt_merge'] . ' ' . $lang['fe_topic'] . ':</span></td>
            <td>' . $lang['vt_with_topic_num'] . '
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="merge_topic" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="text" size="4" name="topic_to_merge_with" value="' . $topic_id . '" />
            ' . $lang['vt_enter_the_destination_topic_id_to_merge_into'] . '
            ' . $lang['vt_topic_id_can_be_found_in_the_address_bar_above'] . ' ' . $topic_id . '
            [' . $lang['vt_this_option_will_mix_the_two_topics_together'] . ']</td>
            <td>
            <input type="submit" name="button" class="button is-small" value="' . $lang['vt_merge_topic'] . '" />
            </form>
            </td>
      </tr>
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/merge.gif" alt="' . $lang['vt_merge'] . '" class="tooltipper" title="' . $lang['vt_merge'] . '" /></td>
            <td>
            <span>' . $lang['vt_append'] . ' ' . $lang['fe_topic'] . ':</span></td>
            <td>' . $lang['vt_with_topic_num'] . '
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="append_topic" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="text" size="4" name="topic_to_append_into" value="' . $topic_id . '" />
            ' . $lang['vt_enter_the_destination_topic_id_to_append_to.'] . '
            ' . $lang['vt_topic_id_can_be_found_in_the_address_bar_above'] . ' ' . $topic_id . '
            [' . $lang['vt_this_option_will_append_this_topic_to_the_end_of_the_new_topic'] . ']</td>
            <td>
            <input type="submit" name="button" class="button is-small" value="' . $lang['vt_append_topic'] . '" />
            </form>
            </td>
      </tr>
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/recycle_bin.gif" alt="' . $lang['vt_recycle'] . '" class="tooltipper" title="' . $lang['vt_recycle'] . '" width="22" /></td>
            <td>
            <span>' . $lang['vt_move_to_recycle_bin'] . ':</span></td>
            <td>
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="move_to_recycle_bin" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="hidden" name="forum_id" value="' . $forum_id . '" />
            <input type="radio" name="status" value="yes" ' . ($status === 'recycled' ? 'checked' : '') . ' /> Yes
            <input type="radio" name="status" value="no" ' . ($status !== 'recycled' ? 'checked' : '') . ' /> No
            ' . $lang['vt_this_option_will_send_this_thread_to_the_hidden_recycle_bin'] . '
            ' . $lang['vt_all_subscriptions_to_this_thread_will_be_deleted'] . '</td>
            <td>
            <input type="submit" name="button" class="button is-small" value="' . $lang['vt_recycle_it'] . '" />
            </form>
            </td>
      </tr>
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/delete.gif" alt="' . $lang['fe_delete'] . '" class="tooltipper" title="' . $lang['fe_delete'] . '" /></td>
            <td>
            <span>' . $lang['fe_del_topic'] . ':</span></td>
            <td>' . $lang['vt_are_you_really_sure_you_want_to_delete_this_topic'] . '</td>
            <td>
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="delete_topic" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="submit" name="button" class="button is-small" value="' . $lang['fe_del_topic'] . '" />
            </form>
            </td>
      </tr>
            ' . ($CURUSER['class'] < $min_delete_view_class ? '' : '
      <tr>
            <td width="28">
            <img src="' . $site_config['pic_base_url'] . 'forums/delete.gif" alt="' . $lang['fe_delete'] . '" class="tooltipper" title="' . $lang['fe_delete'] . '" /></td>
            <td>
            <span><span>*</span>' . $lang['fe_undel_topic'] . ':</span></td>
            <td></td>
            <td>
            <form action="' . $site_config['baseurl'] . '/forums.php?action=staff_actions" method="post">
            <input type="hidden" name="action_2" value="un_delete_topic" />
            <input type="hidden" name="topic_id" value="' . $topic_id . '" />
            <input type="submit" name="button" class="button is-small" value="' . $lang['fe_undel_topic'] . '" />
            </form>
            </td>
      </tr>
      <tr>
            <td colspan="4"><span>*</span>
            only <span>' . get_user_class_name($min_delete_view_class) . '</span> ' . $lang['vt_and_above_can_see_these_options'] . '</td>
      </tr>') . '
      </table></form></div>' : '');

$HTMLOUT .= "
    </div>";
