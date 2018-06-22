<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_rating.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_new.php';
require_once CLASS_DIR . 'class_user_options.php';
check_user_status();
global $CURUSER, $site_config, $cache, $fluent;

$lang = array_merge(load_language('global'), load_language('forums'), load_language('forums_global'));
$stdhead = [
    'css' => [
        'forums',
        'jquery.lightbox-0.5',
        'style',
        'style2',
        'bbcode',
        'rating_style',
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('forums_js'),
    ],
];
$over_forum_id = $count = $now_viewing = $child_boards = '';
if ($site_config['forums_online'] == 0 && $CURUSER['class'] < UC_STAFF) {
    stderr($lang['fm_information'], $lang['fm_the_forums_are_currently_offline']);
}
$HTMLOUT = '';
$fluent->update('users')
    ->set(['forum_access' => TIME_NOW])
    ->where('id = ?', $CURUSER['id'])
    ->execute();

$config_id = 1;
$config_arr = $fluent->from('forum_config')
    ->where('id = ?', $config_id)
    ->fetch();

$delete_for_real = ($config_arr['delete_for_real'] == 1 ? 1 : 0);
$min_delete_view_class = htmlsafechars($config_arr['min_delete_view_class']);
$readpost_expiry = ((int) $config_arr['readpost_expiry'] * 86400);
$min_upload_class = htmlsafechars($config_arr['min_upload_class']);
$accepted_file_extension = [
    $config_arr['accepted_file_extension'],
];
$accepted_file_types = [
    $config_arr['accepted_file_types'],
];
$max_file_size = intval($config_arr['max_file_size']);
$upload_folder = htmlsafechars(trim($config_arr['upload_folder']));
$posted_action = strip_tags((isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '')));
if ($CURUSER['class'] >= UC_STAFF) {
    $valid_actions = [
        'forum',
        'view_forum',
        'section_view',
        'new_topic',
        'view_topic',
        'post_reply',
        'delete_post',
        'edit_post',
        'subscriptions',
        'delete_subscription',
        'add_subscription',
        'search',
        'new_replies',
        'view_unread_posts',
        'view_my_posts',
        'mark_all_as_read',
        'clear_unread_post',
        'download_attachment',
        'poll',
        'view_post_history',
        'staff_actions',
        'member_post_history',
        'staff_lock',
    ];
} else {
    $valid_actions = [
        'forum',
        'view_forum',
        'section_view',
        'new_topic',
        'view_topic',
        'post_reply',
        'delete_post',
        'edit_post',
        'subscriptions',
        'delete_subscription',
        'add_subscription',
        'search',
        'new_replies',
        'view_unread_posts',
        'view_my_posts',
        'mark_all_as_read',
        'clear_unread_post',
        'download_attachment',
        'poll',
    ];
}

$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'forum');
if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
    $HTMLOUT .= "
    <script>
        function confirm_delete(id) {
            if(confirm('" . $lang['fm_are_you_sure_you_want_to_delete_this_forum?'] . "')) {
                self.location.href='staffpanel.php?tool=forum_manage&action=delete&id='+id;
            }
        }
    </script>";
}
$mini_menu = "
    <div class='bottom20'>
        <ul class='level-center bg-06'>" . ($action !== 'forum' ? "
            <li class='margin20'>
                <a href='{$site_config['baseurl']}/forums.php'>{$lang['fe_forums_main']}</a>
            </li>" : '') . ($action !== 'subscriptions' ? "
            <li class='margin20'>
                <a href='{$site_config['baseurl']}/forums.php?action=subscriptions'>{$lang['fm_my_subscriptions']}</a>
            </li>" : '') . ($action !== 'search' ? "
            <li class='margin20'>
                <a href='{$site_config['baseurl']}/forums.php?action=search'>{$lang['fe_search']}</a>
            </li>" : '') . ($action !== 'view_unread_posts' ? "
            <li class='margin20'>
                <a href='{$site_config['baseurl']}/forums.php?action=view_unread_posts'>{$lang['fm_unread_posts']}</a>
            </li>" : '') . ($action !== 'new_replies' ? "
            <li class='margin20'>
                <a href='{$site_config['baseurl']}/forums.php?action=new_replies'>{$lang['fm_new_replies']}</a>
            </li>" : '') . ($action !== 'vew_my_posts' ? "
            <li class='margin20'>
                <a href='{$site_config['baseurl']}/forums.php?action=view_my_posts'>{$lang['fm_my_posts']}</a>
            </li>" : '') . "
            <li class='margin20'>
        	    <a href='{$site_config['baseurl']}/forums.php?action=mark_all_as_read'>{$lang['fm_mark_all_as_read']}</a>
        	</li>" . ($CURUSER['class'] >= UC_SYSOP && $action !== 'member_post_history' ? "
            <li class='margin20'>
        	    <a href='{$site_config['baseurl']}/forums.php?action=member_post_history'>{$lang['fm_member_post_history']}</a>
        	</li>" : '') . '
        </ul>
    </div>';

$legend = main_table("
    <tr>
        <td colspan='8'>{$lang['fm_legend']}</td>
    </tr>
    <tr>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/unlockednew.gif' alt='unlockednew' title='{$lang['fm_unlocked_new']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_unread_forum']}</td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/unlocked.gif' alt='unlocked' title='{$lang['fm_unlocked']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_read_forum']}</td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/topicnew.gif' alt='topicnew' title='{$lang['fe_new_topic']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_unread_post']}</td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/topic.gif' alt='topic' title='{$lang['fe_topic']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_read_post']}</td>
    </tr>
	<tr>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/hot_topic_new.gif' alt='hot_topic_new' title='{$lang['fm_hot_topic_new']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_hot_topic_unread']}</td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/hot_topic.gif' alt='hot_topic' title='{$lang['fm_hot_topic']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_hot_topic_more_than_30_replies']}<br></td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/lockednew.gif' alt='lockednew' title='{$lang['fm_locked_new']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_locked_un-read']}</td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/locked.gif' alt='locked' title='{$lang['fe_locked']}' class='tooltipper emoticon'></td>
        <td>{$lang['fe_locked']}<br></td>
	</tr>
	<tr>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/poll.gif' alt='poll' title='{$lang['fe_poll']}' class='tooltipper emoticon'></td>
        <td>{$lang['fe_poll']}</td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/pinned.gif' alt='pinned' title='{$lang['fe_pinned']}' class='tooltipper emoticon'></td>
        <td>{$lang['fe_pinned']}<br></td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/subscriptions.gif' alt='{$lang['fe_subscribed']} title='{$lang['fe_subscribed']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_subscribed_to_thread']}</td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/posted.gif' alt='posted' title='{$lang['fm_posted']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_you_have_posted_here']}<br></td>
	</tr>
	<tr>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/mg.gif' height='20' alt='{$lang['fm_1st_post_preview']} title='{$lang['fm_1st_post_preview']}' class='tooltipper emoticon'></td>
        <td>{$lang['fm_1st_post_preview']}<br></td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/last_post.gif' alt='last post' title='{$lang['fe_last_post']}' class='tooltipper emoticon'></td>
        <td>{$lang['fe_last_post']}</td>
        <td class='has-text-centered'><img src='{$site_config['pic_baseurl']}forums/topic_normal.gif' alt='{$lang['fe_thread_icon']}' title='{$lang['fe_thread_icon']}' class='tooltipper emoticon'></td>
        <td>{$lang['fe_thread_icon']}</td>
        <td></td>
        <td></td>
	</tr>");

$poll_starts = (isset($_POST['poll_starts']) ? intval($_POST['poll_starts']) : 0);
$poll_ends = (isset($_POST['poll_ends']) ? intval($_POST['poll_ends']) : 1356048000);
$change_vote = ((isset($_POST['change_vote']) && 'yes' === $_POST['change_vote']) ? 'yes' : 'no');
$multi_options = (isset($_POST['multi_options']) ? intval($_POST['multi_options']) : 1);
//$can_add_poll = (isset($_GET['action']) && $_GET['action'] == 'new_topic' ? 1 : 0);
//=== options for amount of options lol
$options = '';
for ($i = 2; $i < 21; ++$i) {
    $options .= '<option value="' . $i . '" ' . ($multi_options === $i ? 'selected="selected"' : '') . '>' . $i . ' options</option>';
}
$more_options = '
<div id="tools" ' . ((isset($_POST['poll_question']) && '' !== $_POST['poll_question']) ? '' : 'style="display:none"') . ' >
<table border="0" cellspacing="0" cellpadding="5" width="800">
<tr>
<td colspan="3">' . $lang['fm_additional_options'] . '}...</td>
</tr>' . ($CURUSER['class'] < $min_upload_class ? '' : '<tr>
<td><img src="' . $site_config['pic_baseurl'] . 'forums/attach.gif" alt="' . $lang['fm_attach'] . '" class="emoticon"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['fe_attachments'] . ':</span></td>
<td>
<input type="file" size="30" name="attachment[]" /> <a title="' . $lang['fm_add_more_attachments'] . '"  id="more" style="white-space:nowrap;font-weight:bold;cursor:pointer;">' . $lang['fm_add_more_attachments'] . '</a>
<img src="' . $site_config['pic_baseurl'] . 'forums/zip.gif" alt="' . $lang['fe_zip'] . '}" class="emoticon">
<img src="' . $site_config['pic_baseurl'] . 'forums/rar.gif" alt="' . $lang['fe_rar'] . '" class="emoticon"><br>
<div id="attach_more" style="display:none">
<input type="file" size="30" name="attachment[]" /><br>
<input type="file" size="30" name="attachment[]" /><br>
<input type="file" size="30" name="attachment[]" />
</div>
</td>
</tr>') . ((isset($_GET['action']) && 'new_topic' != $_GET['action']) ? '' : '<tr>
<td></td>
<td></td>
<td><span style="white-space:nowrap;font-weight: bold;"> <img src="' . $site_config['pic_baseurl'] . 'forums/poll.gif" alt="" class="emoticon">' . $lang['poll_add_poll_to_topic'] . '</span>
</td>
</tr>
<tr>
<td><img src="' . $site_config['pic_baseurl'] . 'forums/question.png" alt="Question" class="emoticon"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_question'] . ':</span></td>
<td><input type="text" name="poll_question" class="w-100" value="' . (isset($_POST['poll_question']) ? strip_tags($_POST['poll_question']) : '') . '" /></td>
</tr>
<tr>
<td><img src="' . $site_config['pic_baseurl'] . 'forums/options.gif" alt="' . $lang['poll_answers'] . '" class="emoticon"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_answers'] . ':</span></td>
<td><textarea cols="30" rows="4" name="poll_answers" class="text_area_small">' . (isset($_POST['poll_answers']) ? strip_tags($_POST['poll_answers']) : '') . '</textarea><br>' . $lang['poll_one_option_per_line_min_2_op_max_20_options_bbcode_is_enabled.'] . '</td>
</tr>
<tr>
<td><img src="' . $site_config['pic_baseurl'] . 'forums/clock.png" alt=' . $lang['poll_starts'] . ' class="emoticon"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_starts'] . ':</span></td>
<td><select name="poll_starts">
<option value="0" ' . (0 === $poll_starts ? 'selected="selected"' : '') . '>' . $lang['poll_start_now'] . '!</option>
<option value="1" ' . (1 === $poll_starts ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_day'], '1') . '</option>
<option value="2" ' . (2 === $poll_starts ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '2') . '</option>
<option value="3" ' . (3 === $poll_starts ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '3') . '</option>
<option value="4" ' . (4 === $poll_starts ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '4') . '</option>
<option value="5" ' . (5 === $poll_starts ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '5') . '</option>
<option value="6" ' . (6 === $poll_starts ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '6') . '</option>
<option value="7" ' . (7 === $poll_starts ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_week'], '1') . '</option>
</select>' . $lang['fm_when_to_start_the_poll'] . ' ' . $lang['poll_start_now'] . '!</td>
</tr>
<tr>
<td><img src="' . $site_config['pic_baseurl'] . 'forums/stop.png" alt=' . $lang['poll_ends'] . ' class="emoticon"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_ends'] . ':</span></td>
<td><select name="poll_ends">
<option value="1356048000" ' . (1356048000 === $poll_ends ? 'selected="selected"' : '') . '>' . $lang['poll_run_forever'] . '</option>
<option value="1" ' . (1 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_day'], '1') . '</option>
<option value="2" ' . (2 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '2') . '</option>
<option value="3" ' . (3 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '3') . '</option>
<option value="4" ' . (4 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '4') . '</option>
<option value="5" ' . (5 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '5') . '</option>
<option value="6" ' . (6 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '6') . '</option>
<option value="7" ' . (7 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_week'], '1') . '</option>
<option value="14" ' . (14 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_weeks'], '2') . '</option>
<option value="21" ' . (21 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_weeks'], '3') . '</option>
<option value="28" ' . (28 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_month'], '1') . '</option>
<option value="56" ' . (56 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_months'], '2') . '</option>
<option value="84" ' . (84 === $poll_ends ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_months'], '3') . '</option>
</select> How long should this poll run? Default is "run forever"</td>
</tr>
<tr>
<td><img src="' . $site_config['pic_baseurl'] . 'forums/multi.gif" alt=' . $lang['poll_multi_options'] . ' class="emoticon"/></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_multi_options'] . ':</span></td>
<td><select name="multi_options">
<option value="1" ' . (1 === $multi_options ? 'selected="selected"' : '') . '>' . $lang['poll_single_option'] . '!</option>
' . $options . '
</select>' . $lang['fm_allow_members_to_have_more_then_one_selection'] . ' ' . $lang['poll_single_option'] . '}!</td>
</tr>
<tr>
<td></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . $lang['poll_change_vote'] . ':</span></td>
<td><input name="change_vote" value="yes" type="radio"' . ('yes' === $change_vote ? ' checked="checked"' : '') . ' />' . $lang['fm_yes'] . '
<input name="change_vote" value="no" type="radio"' . ('no' === $change_vote ? ' checked="checked"' : '') . ' />' . $lang['fm_no'] . '<br> ' . $lang['fm_allow_members_to_change_their_vote'] . ' "no"
</td></tr>') . '
</table>
</div>';
$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));

$the_bottom_of_the_page = insert_quick_jump_menu($forum_id) . $legend;
switch ($action) {
    case 'view_forum':
        require_once FORUM_DIR . 'view_forum.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'view_topic':
        require_once FORUM_DIR . 'view_topic.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'section_view':
        require_once FORUM_DIR . 'section_view.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'poll':
        require_once FORUM_DIR . 'poll.php';
        break;

    case 'subscriptions':
        require_once FORUM_DIR . 'subscriptions.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'add_subscription':
        require_once FORUM_DIR . 'add_subscription.php';
        break;

    case 'delete_post':
        require_once FORUM_DIR . 'delete_post.php';
        break;

    case 'delete_subscription':
        require_once FORUM_DIR . 'delete_subscription.php';
        break;

    case 'new_topic':
        require_once FORUM_DIR . 'new_topic.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'post_reply':
        require_once FORUM_DIR . 'post_reply.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'search':
        require_once FORUM_DIR . 'search.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'view_unread_posts':
        require_once FORUM_DIR . 'view_unread_posts.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'new_replies':
        require_once FORUM_DIR . 'new_replies.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'view_my_posts':
        require_once FORUM_DIR . 'view_my_posts.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'member_post_history':
        require_once FORUM_DIR . 'member_post_history.php';
//dd($HTMLOUT);
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'mark_all_as_read':
        require_once FORUM_DIR . 'mark_all_as_read.php';
        break;

    case 'download_attachment':
        require_once FORUM_DIR . 'download_attachment.php';
        break;

    case 'clear_unread_post':
        require_once FORUM_DIR . 'clear_unread_post.php';
        break;

    case 'edit_post':
        require_once FORUM_DIR . 'edit_post.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'view_post_history':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr('Error', $lang['fm_no_access_for_you_mr_fancy']);
        }
        require_once FORUM_DIR . 'view_post_history.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'staff_actions':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr('Error', $lang['fm_no_access_for_you_mr_fancy']);
        }
        require_once FORUM_DIR . 'staff_actions.php';
        break;

    case 'staff_lock':
        if ($CURUSER['class'] < UC_MAX) {
            stderr('Error', $lang['fm_no_access_for_you_mr_fancy']);
        }
        require_once FORUM_DIR . 'stafflock_post.php';
        break;

    case 'forum':
        $query = $fluent->from('over_forums AS of')
            ->select(null)
            ->select('of.id AS over_forum_id')
            ->select('of.name AS over_forum_name')
            ->select('of.description AS over_forum_description')
            ->select('of.min_class_view AS over_forum_min_class_view')
            ->select('f.id AS real_forum_id')
            ->select('f.name')
            ->select('f.description')
            ->select('f.post_count')
            ->select('f.topic_count')
            ->select('f.forum_id')
            ->innerJoin('forums AS f ON f.forum_id = of.id')
            ->where('of.min_class_view <= ?', $CURUSER['class'])
            ->where('f.min_class_read <= ?', $CURUSER['class'])
            ->where('f.parent_forum = 0')
            ->orderBy('of.sort, f.sort');

        $HTMLOUT .= $mini_menu;

        foreach ($query as $arr_forums) {
            $body = '';
            $body .= ($arr_forums['over_forum_id'] !== $over_forum_id ? '
                <h2 class="margin20">
	                <a href="' . $site_config['baseurl'] . '/forums.php?action=section_view&amp;forum_id=' . (int) $arr_forums['over_forum_id'] . '" title="' . htmlsafechars($arr_forums['over_forum_description'], ENT_QUOTES) . '" class="tooltipper">
	                    <span>' . htmlsafechars($arr_forums['over_forum_name'], ENT_QUOTES) . '</span>
	                </a>
	            </h2>' : '');
            if ($arr_forums['forum_id'] === $arr_forums['over_forum_id']) {
                $forum_id = $arr_forums['real_forum_id'];
                $forum_name = htmlsafechars($arr_forums['name'], ENT_QUOTES);
                $forum_description = htmlsafechars($arr_forums['description'], ENT_QUOTES);
                $topic_count = number_format($arr_forums['topic_count']);
                $post_count = number_format($arr_forums['post_count']);

                $last_post_arr = $cache->get('last_post_' . $forum_id . '_' . $CURUSER['class']);
                if ($last_post_arr === false || is_null($last_post_arr)) {
                    $query = sql_query('SELECT t.id AS topic_id, t.topic_name, t.last_post, t.anonymous AS tan, p.added, p.anonymous AS pan, p.user_id, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.avatar_rights FROM topics AS t LEFT JOIN posts AS p ON p.topic_id = t.id RIGHT JOIN users AS u ON u.id = p.user_id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? ' t.status != \'deleted\' AND p.status != \'deleted\' AND' : '')) . ' t.forum_id = ' . sqlesc($forum_id) . ' ORDER BY p.id DESC LIMIT 1') or sqlerr(__FILE__, __LINE__);
                    $last_post_arr = mysqli_fetch_assoc($query);
                    $cache->set('last_post_' . $forum_id . '_' . $CURUSER['class'], $last_post_arr, $site_config['expires']['last_post']);
                }
                if ($last_post_arr['last_post'] > 0) {
                    $last_post_id = (int) $last_post_arr['last_post'];
                    if (($last_read_post_arr = $cache->get('last_read_post_' . $last_post_arr['topic_id'] . '_' . $CURUSER['id'])) === false) {
                        $query = sql_query('SELECT last_post_read FROM read_posts WHERE user_id = ' . sqlesc($CURUSER['id']) . ' AND topic_id = ' . sqlesc($last_post_arr['topic_id'])) or sqlerr(__FILE__, __LINE__);
                        $last_read_post_arr = mysqli_fetch_row($query);
                        $cache->set('last_read_post_' . $last_post_arr['topic_id'] . '_' . $CURUSER['id'], $last_read_post_arr, $site_config['expires']['last_read_post']);
                    }
                    $image_to_use = ($last_post_arr['added'] > (TIME_NOW - $readpost_expiry)) ? (!$last_read_post_arr or $last_post_id > $last_read_post_arr[0]) : 0;
                    $img = ($image_to_use ? 'unlockednew' : 'unlocked');
                    if ('yes' == $last_post_arr['tan']) {
                        if ($CURUSER['class'] < UC_STAFF && $last_post_arr['user_id'] != $CURUSER['id']) {
                            $last_post = '<span style="white-space:nowrap;">' . $lang['fe_last_post_by'] . ': <i>' . get_anonymous_name() . '</i> in &#9658; <a href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=' . $last_post_id . '#' . $last_post_id . '" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '"><span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . '</span></a><br>' . get_date($last_post_arr['added'], '') . '<br></span>';
                        } else {
                            $last_post = '<span style="white-space:nowrap;">' . $lang['fe_last_post_by'] . ': <i>' . get_anonymous_name() . '</i> [' . ('' !== $last_post_arr['username'] ? format_username($last_post_arr['id']) : '' . $lang['fe_lost'] . '') . '] <span style="font-size: x-small;"> [ ' . get_user_class_name($last_post_arr['class']) . ' ] </span><br>in &#9658; <a href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=' . $last_post_id . '#' . $last_post_id . '" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '"><span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . '</span></a><br>' . get_date($last_post_arr['added'], '') . '<br></span>';
                        }
                    } else {
                        $last_post = '<span style="white-space:nowrap;">' . $lang['fe_last_post_by'] . ': ' . ('' !== $last_post_arr['username'] ? format_username($last_post_arr['id']) : '' . $lang['fe_lost'] . '') . ' <span style="font-size: x-small;"> [ ' . get_user_class_name($last_post_arr['class']) . ' ] </span><br>in &#9658; <a href="' . $site_config['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=' . $last_post_id . '#' . $last_post_id . '" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '"><span style="font-weight: bold;">' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . '</span></a><br>' . get_date($last_post_arr['added'], '') . '<br></span>';
                    }
                    $keys['child_boards'] = 'child_boards_' . $last_post_id . '_' . $CURUSER['class'];
                    if (($child_boards_cache = $cache->get($keys['child_boards'])) === false) {
                        $child_boards = '';
                        $child_boards_cache = [];
                        $res = sql_query('SELECT name, id FROM forums WHERE parent_forum = ' . sqlesc($arr_forums['real_forum_id']) . ' AND min_class_read <= ' . sqlesc($CURUSER['class']) . ' ORDER BY sort ASC') or sqlerr(__FILE__, __LINE__);
                        while ($arr = mysqli_fetch_assoc($res)) {
                            if ($child_boards) {
                                $child_boards .= ', ';
                            }
                            $child_boards .= '<a href="' . $site_config['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . (int) $arr['id'] . '" title="' . $lang['fm_click_to_view'] . '!" class="altlink">' . htmlsafechars($arr['name'], ENT_QUOTES) . '</a>';
                        }
                        $child_boards_cache['child_boards'] = $child_boards;
                        $cache->set($keys['child_boards'], $child_boards_cache, $site_config['expires']['child_boards']);
                    }
                    $child_boards = $child_boards_cache['child_boards'];
                    if ('' != $child_boards) {
                        $child_boards = '<hr><span style="font-size: xx-small;">' . $lang['sv_child_boards'] . ':</span> ' . $child_boards;
                    }
                } else {
                    $img = 'unlocked';
                    $last_post = 'N/A';
                }
                $body .= '
                    <tr>
                        <td class="w-25">
                            <div class="level">
                                <span class="level-left">
                                    <img src="' . $site_config['pic_baseurl'] . 'forums/' . $img . '.gif" alt="' . $img . '" title=' . $lang['fm_unlocked'] . ' class="tooltipper emoticon right10" />
                                    ' . bubble('
                                    <span>
                                        <a href="?action=view_forum&amp;forum_id=' . $arr_forums['real_forum_id'] . '">' . $forum_name . '</a>
                                    </span>', '<span>' . $forum_name . '</span>	' . $forum_description) . ($CURUSER['class'] >= UC_ADMINISTRATOR ? '
                                </span>
                                <span class="level-right">
                                    <span class="left10">
                                        <a href="staffpanel.php?tool=forum_manage&amp;action=forum_manage&amp;action2=edit_forum_page&amp;id=' . $forum_id . '" >
                                            <i class="icon-edit icon"></i>
                                        </a>
                                    </span>
                                    <span>
                                        <a href="javascript:confirm_delete(\'' . $forum_id . '\');">
                                            <i class="icon-cancel icon"></i>
                                        </a>
                                    </span>
                                </span>
                            </div>' : '') . '
                            <span> ' . $forum_description . '</span>' . $child_boards . $now_viewing . '
                        </td>
                        <td class="w-25">
                            <span>' . $post_count . ' ' . $lang['fe_posts'] . '<br>' . $topic_count . ' ' . $lang['fe_topics'] . '</span>
                        </td>
                        <td class="w-25"><span>' . $last_post . '</span></td>
                    </tr>';
            }
            $over_forum_id = $arr_forums['over_forum_id'];
            $child_boards = '';

            $HTMLOUT .= wrapper(main_table($body));
        }
        $body = insert_quick_jump_menu();

        $list = [];
        $forum_users_cache = $cache->get('now_viewing');
        if ($forum_users_cache === false || is_null($forum_users_cache)) {
            $forumusers = '';
            $forum_users_cache = [];
            $query = $fluent->from('now_viewing')
                ->where('users.perms < ?', bt_options::PERMS_STEALTH)
                ->innerJoin('users ON now_viewing.user_id = users.id');

            foreach ($query as $row) {
                $list[] = format_username($row['user_id']);
            }

            $forumusers = implode(',&nbsp;&nbsp;', $list);

            $forum_users_cache['forum_users'] = $forumusers;
            $forum_users_cache['actcount'] = count($list);
            $cache->set('now_viewing', $forum_users_cache, $site_config['expires']['forum_users']);
        }
        if (!$forum_users_cache['forum_users']) {
            $forum_users_cache['forum_users'] = $lang['fm_there_have_been_no_active_users_in_the_last_15_minutes'];
        }

        $forum_users = $forum_users_cache['forum_users'];

        $body .= main_div("
            <h2>{$lang['fm_members_currently_active']}</h2>
	        <p>{$forum_users}</p>", 'bottom20 has-text-centered') . $legend;
        $HTMLOUT .= $body;
        break;
}

function highlightWords($text, $words)
{
    preg_match_all('~\w+~', $words, $m);
    if (!$m) {
        return $text;
    }
    $re = '~\\b(' . implode('|', $m[0]) . ')~i';
    $string = preg_replace($re, '<span style="color: black; background-color: yellow;font-weight: bold;">$0</span>', $text);

    return $string;
}

function ratingpic_forums($num)
{
    global $site_config;
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5) {
        return;
    }

    return '<img src="' . $site_config['pic_baseurl'] . 'forums/rating/' . $r . '.gif" alt="rating: ' . $num . ' / 5" class="emoticon">';
}

function insert_quick_jump_menu($current_forum = 0, $staff = false)
{
    global $CURUSER, $site_config, $cache, $lang, $fluent;

    $cachename = 'f_insertJumpTo_' . $CURUSER['id'] . ($staff ? '' : '_staff' === false);
    $qjcache = $cache->get($cachename);
    if ($qjcache === false || is_null($qjcache)) {
        $qjcache = $fluent->from('forums')
            ->select(null)
            ->select('forums.id')
            ->select('forums.name')
            ->select('forums.parent_forum')
            ->select('forums.min_class_read')
            ->select('over_forums.name AS overforums_name')
            ->select('over_forums.sort')
            ->innerJoin('over_forums ON forums.forum_id = over_forums.id')
            ->orderBy('over_forums.sort ASC')
            ->orderBy('forums.parent_forum ASC')
            ->orderBy('forums.sort ASC')
            ->fetchAll();
        $cache->set($cachename, $qjcache, $site_config['expires']['forum_insertJumpTo']);
    }

    $switch = '';
    $body = ($staff === false ? '
    <div class="has-text-centered bottom20">
        <form method="get" action="' . $site_config['baseurl'] . '/forums.php" name="jump">
            <span>
                <input type="hidden" name="action" value="view_forum" /> 
                <select name="forum_id" onchange="if(this.options[this.selectedIndex].value != -1){forms[\'jump\'].submit()}">
                    <option class="head" value="0">' . $lang['fm_select_a_forum_to_jump_to'] . '</option>' : '');

    foreach ($qjcache as $arr) {
        if ($CURUSER['class'] >= $arr['min_class_read']) {
            if ($switch !== $arr['overforums_name']) {
                $body .= '
                    <option class="head" value="-1">' . htmlsafechars($arr['overforums_name']) . '</option>';
            }
            $switch = htmlsafechars($arr['overforums_name']);
            $body .= '
                    <option value="' . (int) $arr['id'] . '">' . ($arr['parent_forum'] != 0 ? '&#176; ' . htmlsafechars($arr['name']) . ' [ child-board ]' : htmlsafechars($arr['name'])) . '</option>';
        }
    }

    $body .= (false === $staff ? '
                </select>
            </span>
        </form>
    </div>' : '');

    return $body;
}

echo stdhead($lang['fe_forums'], true, $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
