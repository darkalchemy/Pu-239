<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_rating.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_user_options.php';
$user = check_user_status();
global $container, $site_config;

$image = placeholder_image();
$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('forums_js'),
        get_file_name('sceditor_js'),
    ],
];
$over_forum_id = $count = $now_viewing = $child_boards = '';
if (!$site_config['forum_config']['online'] && !has_access($user['class'], UC_STAFF, 'coder')) {
    stderr(_('Information'), _('The forums are currently offline for maintainance work'));
}
$HTMLOUT = '';
$fluent = $container->get(Database::class);
$fluent->update('users')
       ->set(['forum_access' => TIME_NOW])
       ->where('id = ?', $user['id'])
       ->execute();

$posted_action = isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : '');
if (has_access($user['class'], UC_STAFF, 'coder') || has_access($user['class'], UC_STAFF, 'forum_mod')) {
    $valid_actions = [
        'forum',
        'view_forum',
        'section_view',
        'new_topic',
        'view_topic',
        'post_reply',
        'delete_post',
        'undelete_post',
        'edit_post',
        'subscriptions',
        'delete_subscription',
        'add_subscription',
        'search',
        'new_replies',
        'view_unread_posts',
        'view_my_posts',
        'mark_all_as_read',
        'mark_all_as_unread',
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
        'mark_all_as_unread',
        'clear_unread_post',
        'download_attachment',
        'poll',
    ];
}

$action = in_array($posted_action, $valid_actions) ? $posted_action : 'forum';
if (has_access($user['class'], UC_ADMINISTRATOR, 'coder')) {
    $HTMLOUT .= "
    <script>
        function confirm_delete(id) {
            if(confirm('" . _('Are you sure you want to delete this forum?') . "')) {
                self.location.href='staffpanel.php?tool=forum_manage&action=delete&id='+id;
            }
        }
    </script>";
}
$mini_menu = "
    <div class='bottom20'>
        <ul class='level-center bg-06'>" . ($action !== 'forum' ? "
            <li class='margin10'>
                <a href='{$site_config['paths']['baseurl']}/forums.php'>" . _('Main Forums') . '</a>
            </li>' : '') . ($action !== 'subscriptions' ? "
            <li class='margin10'>
                <a href='{$site_config['paths']['baseurl']}/forums.php?action=subscriptions'>" . _('My Subscriptions') . '</a>
            </li>' : '') . ($action !== 'search' ? "
            <li class='margin10'>
                <a href='{$site_config['paths']['baseurl']}/forums.php?action=search'>" . _('Search') . '</a>
            </li>' : '') . ($action !== 'view_unread_posts' ? "
            <li class='margin10'>
                <a href='{$site_config['paths']['baseurl']}/forums.php?action=view_unread_posts'>" . _('Unread Posts') . '</a>
            </li>' : '') . ($action !== 'new_replies' ? "
            <li class='margin10'>
                <a href='{$site_config['paths']['baseurl']}/forums.php?action=new_replies'>" . _('New Replies') . '</a>
            </li>' : '') . ($action !== 'vew_my_posts' ? "
            <li class='margin10'>
                <a href='{$site_config['paths']['baseurl']}/forums.php?action=view_my_posts'>" . _('My Posts') . '</a>
            </li>' : '') . "
            <li class='margin10'>
        	    <a href='{$site_config['paths']['baseurl']}/forums.php?action=mark_all_as_read'>" . _('Mark All As Read') . "</a>
        	</li>
            <li class='margin10'>
        	    <a href='{$site_config['paths']['baseurl']}/forums.php?action=mark_all_as_unread'>" . _('Mark All As Unread') . '</a>
        	</li>' . (has_access($user['class'], UC_SYSOP, 'coder') && $action !== 'member_post_history' ? "
            <li class='margin10'>
        	    <a href='{$site_config['paths']['baseurl']}/forums.php?action=member_post_history'>" . _('Member Post History') . '</a>
        	</li>' : '') . '
        </ul>
    </div>';

$legend = main_table("
    <tr>
        <td colspan='8'>" . _('Legend') . "</td>
    </tr>
    <tr>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/unlockednew.gif' alt='unlockednew' title='" . _('Unlocked new') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Unread forum') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/unlocked.gif' alt='unlocked' title='" . _('Unlocked') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Read forum') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/topicnew.gif' alt='topicnew' title='" . _('New Topic') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Unread post') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/topic.gif' alt='topic' title='" . _('Topic') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Read post') . "</td>
    </tr>
	<tr>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/hot_topic_new.gif' alt='hot_topic_new' title='" . _('Hot Topic New') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Hot topic un-read') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/hot_topic.gif' alt='hot_topic' title='" . _('Hot Topic') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Hot topic [more than 30 replies]') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/lockednew.gif' alt='lockednew' title='" . _('Locked new') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Locked un-read') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/locked.gif' alt='locked' title='" . _('Locked') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Locked') . "</td>
	</tr>
	<tr>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/poll.gif' alt='poll' title='" . _('Poll') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Poll') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/pinned.gif' alt='pinned' title='" . _('Pinned') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Pinned') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/subscriptions.gif' alt='" . _('Subscribed') . "' title='" . _('Subscribed') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Subscribed to thread') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/posted.gif' alt='posted' title='" . _('Posted') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('You have posted here') . "</td>
	</tr>
	<tr>
        <td class='has-text-centered'>
            <i class='icon-search icon tooltipper' aria-hidden='true' title='" . _('1st Post Preview') . "'></i>
        </td>
        <td>" . _('1st Post Preview') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/last_post.gif' alt='last post' title='" . _('Last Post') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Last Post') . "</td>
        <td class='has-text-centered'><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}forums/topic_normal.gif' alt='" . _('Thread Icon') . "' title='" . _('Thread Icon') . "' class='tooltipper emoticon lazy'></td>
        <td>" . _('Thread Icon') . '</td>
        <td></td>
        <td></td>
	</tr>');

$poll_starts = isset($_POST['poll_starts']) ? (int) $_POST['poll_starts'] : 0;
$poll_ends = isset($_POST['poll_ends']) ? (int) $_POST['poll_ends'] : 1356048000;
$change_vote = (isset($_POST['change_vote']) && $_POST['change_vote'] === 'yes') ? 'yes' : 'no';
$multi_options = isset($_POST['multi_options']) ? (int) $_POST['multi_options'] : 1;
$can_add_poll = isset($_GET['action']) && $_GET['action'] === 'new_topic' ? 1 : 0;

$options = '';
for ($i = 2; $i < 21; ++$i) {
    $options .= '<option value="' . $i . '" ' . ($multi_options === $i ? 'selected' : '') . '>' . $i . ' options</option>';
}
$accept_types = str_replace('|', ', ', $site_config['forum_config']['accepted_file_types']);
$more_options = '
<div id="staff_tools" ' . ((isset($_POST['poll_question']) && $_POST['poll_question'] !== '') ? '' : 'style="display:none"') . '>' . main_table(($user['class'] < $site_config['forum_config']['min_upload_class'] ? '' : '<tr>
<td><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/attach.gif" alt="' . _('Attach') . '" class="emoticon lazy"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . _('Attachments') . ':</span></td>
<td>
<input type="file" size="30" name="attachment[]" accept="' . $accept_types . '"> <a title="' . _('Add more attachments') . '"  id="more" style="white-space:nowrap;font-weight:bold;cursor:pointer;">' . _('Add more attachments') . '</a>
<img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/zip.gif" alt="' . _('Zip') . '}" class="emoticon lazy tooltipper" title="Zip Files">
<img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/rar.gif" alt="' . _('Rar') . '" class="emoticon lazy tooltipper" title="Rar Files"><br>
<div id="attach_more" style="display:none">
<input type="file" size="30" name="attachment[]"><br>
<input type="file" size="30" name="attachment[]"><br>
<input type="file" size="30" name="attachment[]">
</div>
</td>
</tr>') . ((isset($_GET['action']) && $_GET['action'] != 'new_topic') ? '' : '<tr>
<td></td>
<td></td>
<td><span style="white-space:nowrap;font-weight: bold;"> <img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/poll.gif" alt="" class="emoticon lazy">' . _('Add poll to topic') . '</span>
</td>
</tr>
<tr>
<td><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/question.png" alt="Question" class="emoticon lazy"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . _('Poll question') . ':</span></td>
<td><input type="text" name="poll_question" class="w-100" value="' . (isset($_POST['poll_question']) ? strip_tags($_POST['poll_question']) : '') . '"></td>
</tr>
<tr>
<td><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/options.gif" alt="' . _('Poll answers') . '" class="emoticon lazy"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . _('Poll answers') . ':</span></td>
<td><textarea cols="30" rows="4" name="poll_answers" class="text_area_small">' . (isset($_POST['poll_answers']) ? strip_tags($_POST['poll_answers']) : '') . '</textarea><br>' . _('One option per line. There is a minimum of 2 options, and a maximun of 20 options. BBcode is enabled.') . '</td>
</tr>
<tr>
<td><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/clock.png" alt=' . _('Poll starts') . ' class="emoticon lazy"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . _('Poll starts') . ':</span></td>
<td><select name="poll_starts">
<option value="0" ' . ($poll_starts === 0 ? 'selected' : '') . '>' . _('Start Now') . '!</option>
<option value="1" ' . ($poll_starts === 1 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 1) . '</option>
<option value="2" ' . ($poll_starts === 2 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 2) . '</option>
<option value="3" ' . ($poll_starts === 3 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 3) . '</option>
<option value="4" ' . ($poll_starts === 4 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 4) . '</option>
<option value="5" ' . ($poll_starts === 5 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 5) . '</option>
<option value="6" ' . ($poll_starts === 6 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 6) . '</option>
<option value="7" ' . ($poll_starts === 7 ? 'selected' : '') . '>' . _pfe('in {0} week', 'in {0} weeks', 16) . '</option>
</select>' . _('When to start the poll. Default is: Start Now!') . '</td>
</tr>
<tr>
<td><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/stop.png" alt=' . _('Poll ends') . ' class="emoticon lazy"></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . _('Poll ends') . ':</span></td>
<td><select name="poll_ends">
<option value="1356048000" ' . ($poll_ends === 1356048000 ? 'selected' : '') . '>' . _('Run Forever') . '</option>
<option value="1" ' . ($poll_ends === 1 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 1) . '</option>
<option value="2" ' . ($poll_ends === 2 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 2) . '</option>
<option value="3" ' . ($poll_ends === 3 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 3) . '</option>
<option value="4" ' . ($poll_ends === 4 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 4) . '</option>
<option value="5" ' . ($poll_ends === 5 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 5) . '</option>
<option value="6" ' . ($poll_ends === 6 ? 'selected' : '') . '>' . _pfe('in {0} day', 'in {0} days', 6) . '</option>
<option value="7" ' . ($poll_ends === 7 ? 'selected' : '') . '>' . _pfe('in {0} week', 'in {0} weeks', 1) . '</option>
<option value="14" ' . ($poll_ends === 14 ? 'selected' : '') . '>' . _pfe('in {0} week', 'in {0} weeks', 2) . '</option>
<option value="21" ' . ($poll_ends === 21 ? 'selected' : '') . '>' . _pfe('in {0} week', 'in {0} weeks', 3) . '</option>
<option value="28" ' . ($poll_ends === 28 ? 'selected' : '') . '>' . _pfe('in {0} month', 'in {0} months', 1) . '</option>
<option value="56" ' . ($poll_ends === 56 ? 'selected' : '') . '>' . _pfe('in {0} month', 'in {0} months', 2) . '</option>
<option value="84" ' . ($poll_ends === 84 ? 'selected' : '') . '>' . _pfe('in {0} month', 'in {0} months', 3) . '</option>
</select>' . _("How long should this poll run? Default is 'run forever'") . '</td>
</tr>
<tr>
<td><img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/multi.gif" alt=' . _('Multi options') . ' class="emoticon lazy"/></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . _('Multi options') . ':</span></td>
<td><select name="multi_options">
<option value="1" ' . ($multi_options === 1 ? 'selected' : '') . '>' . _('Single option') . '!</option>
' . $options . '
</select>' . _("Allow members to have more then one selection? Default is 'Single option'!") . '</td>
</tr>
<tr>
<td></td>
<td><span style="white-space:nowrap;font-weight: bold;">' . _('Change vote') . ':</span></td>
<td><input name="change_vote" value="yes" type="radio" ' . ($change_vote === 'yes' ? 'checked' : '') . '>' . _('Yes') . '
<input name="change_vote" value="no" type="radio" ' . ($change_vote === 'no' ? 'checked' : '') . '>' . _('No') . '<br> ' . _('Allow members to change their vote? Default is "no"') . '
</td></tr>'), '', '', 'padding20') . '
</div>';
$forum_id = isset($_GET['forum_id']) ? (int) $_GET['forum_id'] : (isset($_POST['forum_id']) ? (int) $_POST['forum_id'] : 0);

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

    case 'undelete_post':
        require_once FORUM_DIR . 'undelete_post.php';
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
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'mark_all_as_read':
    case 'mark_all_as_unread':
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
        if (!has_access($user['class'], UC_STAFF, 'coder') && !has_access($user['class'], UC_STAFF, 'forum_mod')) {
            stderr(_('Error'), _('No access for you Mr. Fancy-Pants.'));
        }
        require_once FORUM_DIR . 'view_post_history.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'staff_actions':
        if (!has_access($user['class'], UC_STAFF, 'coder') && !has_access($user['class'], UC_STAFF, 'forum_mod')) {
            stderr(_('Error'), _('No access for you Mr. Fancy-Pants.'));
        }
        require_once FORUM_DIR . 'staff_actions.php';
        break;

    case 'staff_lock':
        if (!has_access($user['class'], UC_MAX, 'coder')) {
            stderr(_('Error'), _('No access for you Mr. Fancy-Pants.'));
        }
        require_once FORUM_DIR . 'stafflock_post.php';
        break;

    case 'forum':
        $query = $fluent->from('over_forums AS ovf')
                        ->select(null)
                        ->select('ovf.id AS over_forum_id')
                        ->select('ovf.name AS over_forum_name')
                        ->select('ovf.description AS over_forum_description')
                        ->select('ovf.min_class_view AS over_forum_min_class_view')
                        ->select('f.id AS real_forum_id')
                        ->select('f.name')
                        ->select('f.description')
                        ->select('f.post_count')
                        ->select('f.topic_count')
                        ->select('f.forum_id')
                        ->select('f.parent_forum')
                        ->innerJoin('forums AS f ON f.forum_id = ovf.id')
                        ->where('ovf.min_class_view <= ?', $user['class'])
                        ->where('f.min_class_read <= ?', $user['class'])
                        ->orderBy('ovf.sort, f.sort')
                        ->fetchAll();
        $children = [];
        foreach ($query as $forum) {
            if ($forum['parent_forum'] === 0) {
                $parents[] = $forum;
            } else {
                $children[] = $forum;
            }
        }

        unset($query);
        $i = 0;
        $updated = [];
        foreach ($parents as $parent) {
            $parent['children_ids'] = [];
            foreach ($children as $child) {
                $parent['children_ids'][] = $parent['real_forum_id'];
                if ($parent['real_forum_id'] === $child['parent_forum']) {
                    $parent['post_count'] += $child['post_count'];
                    $parent['topic_count'] += $child['topic_count'];
                    $parent['children_ids'][] = $child['real_forum_id'];
                }
            }
            $updated[] = $parent;
        }

        $HTMLOUT .= $mini_menu;
        foreach ($updated as $arr_forums) {
            $HTMLOUT .= ($arr_forums['over_forum_id'] !== $over_forum_id ? "
                <h2 class='margin20'>
	                <a href='{$_SERVER['PHP_SELF']}?action=section_view&amp;forum_id={$arr_forums['over_forum_id']}' title='" . format_comment($arr_forums['over_forum_description']) . "' class='tooltipper'>
	                    <span>" . format_comment($arr_forums['over_forum_name']) . '</span>
	                </a>
	            </h2>' : '');
            $body = '';
            if ($arr_forums['forum_id'] === $arr_forums['over_forum_id']) {
                $forum_id = $arr_forums['real_forum_id'];
                $forum_name = _(htmlsafechars($arr_forums['name']));
                $forum_description = _(htmlsafechars($arr_forums['description']));
                $topic_count = number_format($arr_forums['topic_count']);
                $post_count = number_format($arr_forums['post_count']);
                $last_post_arr = $cache->get('forum_last_post_' . $forum_id . '_' . $user['class']);
                if ($last_post_arr === false || is_null($last_post_arr)) {
                    $query = $fluent->from('topics AS t')
                                    ->select(null)
                                    ->select('t.id AS topic_id')
                                    ->select('t.topic_name')
                                    ->select('t.last_post')
                                    ->select('t.anonymous AS tan')
                                    ->select('p.added')
                                    ->select('p.anonymous AS pan')
                                    ->select('p.user_id')
                                    ->leftJoin('posts AS p ON t.id = p.topic_id');
                    if (!has_access($user['class'], UC_STAFF, 'coder')) {
                        $query = $query->where('p.status = "ok"')
                                       ->where('t.status = "ok"');
                    } elseif ($user['class'] < $site_config['forum_config']['min_delete_view_class']) {
                        $query = $query->where('t.status != "deleted"')
                                       ->where('p.status != "deleted"');
                    }
                    $last_post_arr = $query->where('t.forum_id', $arr_forums['children_ids'])
                                           ->orderBy('p.id DESC')
                                           ->limit(1)
                                           ->fetch();

                    $cache->set('forum_last_post_' . $forum_id . '_' . $user['class'], $last_post_arr, $site_config['expires']['last_post']);
                }
                $last_post = '';
                $topic_name = !empty($last_post_arr['topic_name']) ? format_comment(_($last_post_arr['topic_name'])) : '';
                if (!empty($last_post_arr) && $last_post_arr['last_post'] > 0) {
                    $last_post_id = $last_post_arr['last_post'];
                    if (($last_read_post_arr = $cache->get('last_read_post_' . $last_post_arr['topic_id'] . '_' . $user['id'])) === false) {
                        $query = sql_query('SELECT last_post_read FROM read_posts WHERE user_id = ' . sqlesc($user['id']) . ' AND topic_id = ' . sqlesc($last_post_arr['topic_id'])) or sqlerr(__FILE__, __LINE__);
                        $last_read_post_arr = mysqli_fetch_row($query);
                        $cache->set('last_read_post_' . $last_post_arr['topic_id'] . '_' . $user['id'], $last_read_post_arr, $site_config['expires']['last_read_post']);
                    }
                    $image_to_use = ($last_post_arr['added'] > (TIME_NOW - $site_config['forum_config']['readpost_expiry'])) ? (!$last_read_post_arr or $last_post_id > $last_read_post_arr[0]) : 0;
                    $img = ($image_to_use ? 'unlockednew' : 'unlocked');

                    if ($last_post_arr['tan'] === '1') {
                        if (!has_access($user['class'], UC_STAFF, 'coder') && $last_post_arr['user_id'] != $user['id']) {
                            $last_post = '<span style="white-space:nowrap;">' . _('Last Post by') . ': <i>' . get_anonymous_name() . '</i> in &#9658; <a href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=last#' . $last_post_id . '" title="' . $topic_name . '" class="tooltipper"><span style="font-weight: bold;">' . CutName($topic_name, 30) . '</span></a><br>' . get_date((int) $last_post_arr['added'], '') . '<br></span>';
                        } else {
                            $last_post = '<span style="white-space:nowrap;">' . _('Last Post by') . ': <i>' . get_anonymous_name() . '</i> [' . (!empty($last_post_arr['user_id']) ? format_username((int) $last_post_arr['user_id']) : _('Lost')) . ']<br>in &#9658; <a href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=last#' . $last_post_id . '" title="' . $topic_name . '"><span style="font-weight: bold;">' . CutName($topic_name, 30) . '</span></a><br>' . get_date((int) $last_post_arr['added'], '') . '<br></span>';
                        }
                    } else {
                        $last_post = '<span style="white-space:nowrap;">' . _('Last Post by') . ': ' . (!empty($last_post_arr['user_id']) ? format_username((int) $last_post_arr['user_id']) : _('Lost')) . '</span><br>in &#9658; <a href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_topic&amp;topic_id=' . (int) $last_post_arr['topic_id'] . '&amp;page=last#' . $last_post_id . '" title="' . $topic_name . '" class="tooltipper"><span style="font-weight: bold;">' . CutName($topic_name, 30) . '</span></a><br>' . get_date((int) $last_post_arr['added'], '') . '<br></span>';
                    }
                } else {
                    $img = 'unlocked';
                    $last_post = 'N/A';
                }
                $keys['child_boards'] = 'child_boards_' . $arr_forums['real_forum_id'] . '_' . $user['class'];
                $child_boards_cache = $cache->get($keys['child_boards']);
                if ($child_boards_cache === false || is_null($child_boards_cache)) {
                    $child_boards_cache = [];
                    $query = $fluent->from('forums')
                                    ->select(null)
                                    ->select('id')
                                    ->select('name')
                                    ->where('parent_forum = ?', $arr_forums['real_forum_id'])
                                    ->where('min_class_read <= ?', $user['class'])
                                    ->orderBy('sort');

                    foreach ($query as $arr) {
                        $child_boards_cache[] = '<a href="' . $site_config['paths']['baseurl'] . '/forums.php?action=view_forum&amp;forum_id=' . (int) $arr['id'] . '" title="' . _('click to view') . '!" class="is-link tooltipper">' . format_comment($arr['name']) . '</a>';
                    }
                    $cache->set($keys['child_boards'], $child_boards_cache, $site_config['expires']['child_boards']);
                }
                $child_boards = '';
                if (!empty($child_boards_cache)) {
                    $child_boards = '<hr class="is-marginless"><div class="top10"><span class="size_3">' . _('child boards') . ': </span>' . implode(', ', $child_boards_cache) . '</div>';
                }
                $body .= '
                    <tr class="min-600">
                        <td class="min-350 w-40">
                            <div class="level">
                                <span class="level-left">
                                    <img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/' . $img . '.gif" alt="' . $img . '" title="' . _('Unlocked') . '" class="tooltipper emoticon lazy right10">
                                    ' . bubble('<a href="?action=view_forum&amp;forum_id=' . $arr_forums['real_forum_id'] . '">' . $forum_name . '</a>', $forum_description) . (has_access($user['class'], UC_ADMINISTRATOR, 'coder') ? '
                                </span>
                                <span class="level-right">
                                    <span class="left10">
                                        <a href="staffpanel.php?tool=forum_manage&amp;action=forum_manage&amp;action2=edit_forum_page&amp;id=' . $forum_id . '">
                                            <i class="icon-edit icon has-text-info tooltipper" title="Edit Forum"></i>
                                        </a>
                                    </span>
                                    <span>
                                        <a href="javascript:confirm_delete(\'' . $forum_id . '\');">
                                            <i class="icon-trash-empty icon has-text-danger tooltipper" aria-hidden="true" title="Delete Forum"></i>
                                        </a>
                                    </span>
                                </span>
                            </div>' : '
                                </span>
                            </div>') . '
                            <div>' . $forum_description . '</div>' . $child_boards . $now_viewing . '
                        </td>
                        <td class="min-150 w-25">
                            <span>' . $post_count . ' ' . _('Posts') . '<br>' . $topic_count . ' ' . _('Topics') . '</span>
                        </td>
                        <td class="min-150 w-25"><span>' . $last_post . '</span></td>
                    </tr>';
            }
            $over_forum_id = $arr_forums['over_forum_id'];
            $child_boards = '';

            $HTMLOUT .= wrapper(main_table($body));
        }
        $body = insert_quick_jump_menu();

        $list = [];
        $forum_users_cache = $cache->get('now_viewing_');
        if ($forum_users_cache === false || is_null($forum_users_cache)) {
            $forumusers = '';
            $forum_users_cache = [];
            $query = $fluent->from('now_viewing')
                            ->where('users.perms < ?', PERMS_STEALTH)
                            ->where('users.anonymous_until < ?', TIME_NOW)
                            ->where('users.paranoia < 2')
                            ->innerJoin('users ON now_viewing.user_id = users.id');

            foreach ($query as $row) {
                $list[] = format_username((int) $row['user_id']);
            }

            $forumusers = implode(',&nbsp;&nbsp;', $list);

            $forum_users_cache['forum_users'] = $forumusers;
            $forum_users_cache['actcount'] = count($list);
            $cache->set('now_viewing_', $forum_users_cache, $site_config['expires']['forum_users']);
        }
        if (!$forum_users_cache['forum_users']) {
            $forum_users_cache['forum_users'] = _('There have been no active users in the last 15 minutes');
        }

        $forum_users = $forum_users_cache['forum_users'];

        $body .= main_div('
            <h2>' . _('Members currently active') . "</h2>
	        <div class='padding10'>{$forum_users}</div>", 'bottom20 has-text-centered') . $legend;
        $HTMLOUT .= $body;
        break;
}

/**
 * @param $text
 * @param $words
 *
 * @return string|string[]|null
 */
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

/**
 * @param $num
 *
 * @throws NotFoundException
 * @throws DependencyException
 *
 * @return string|void
 */
function ratingpic_forums($num)
{
    global $site_config;

    $image = placeholder_image();
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5) {
        return;
    }

    return '<img src="' . $image . '" data-src="' . $site_config['paths']['images_baseurl'] . 'forums/rating/' . $r . '.gif" alt="rating: ' . $num . ' / 5" class="emoticon lazy">';
}

/**
 * @param int  $current_forum
 * @param bool $staff
 *
 * @throws InvalidManipulation
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function insert_quick_jump_menu($current_forum = 0, $staff = false)
{
    global $container, $site_config, $user;

    $cache = $container->get(Cache::class);
    $cachename = 'f_insertJumpTo_' . $user['id'] . ($staff ? '' : '_staff' === false);
    $qjcache = $cache->get($cachename);
    if ($qjcache === false || is_null($qjcache)) {
        $fluent = $container->get(Database::class);
        $qjcache = $fluent->from('forums')
                          ->select(null)
                          ->select('forums.id')
                          ->select('forums.name')
                          ->select('forums.parent_forum')
                          ->select('forums.min_class_read')
                          ->select('over_forums.name AS overforums_name')
                          ->select('over_forums.sort')
                          ->innerJoin('over_forums ON forums.forum_id=over_forums.id')
                          ->orderBy('over_forums.sort ASC')
                          ->orderBy('forums.parent_forum ASC')
                          ->orderBy('forums.sort ASC')
                          ->fetchAll();
        $cache->set($cachename, $qjcache, $site_config['expires']['forum_insertJumpTo']);
    }

    $switch = '';
    $body = ($staff === false ? '
    <div class="has-text-centered margin20">
        <form method="get" action="' . $site_config['paths']['baseurl'] . '/forums.php" name="jump" accept-charset="utf-8">
            <span>
                <input type="hidden" name="action" value="view_forum">
                <select name="forum_id" onchange="if (this.options[this.selectedIndex].value != -1) {forms[\'jump\'].submit()}">
                    <option class="head" value="0">' . _('Select a forum to jump to') . '</option>' : '');

    foreach ($qjcache as $arr) {
        if ($user['class'] >= $arr['min_class_read']) {
            if ($switch !== $arr['overforums_name']) {
                $body .= '
                    <option class="head" value="-1">' . format_comment($arr['overforums_name']) . '</option>';
            }
            $switch = format_comment($arr['overforums_name']);
            $body .= '
                    <option value="' . (int) $arr['id'] . '">' . ($arr['parent_forum'] != 0 ? '&#176; ' . format_comment($arr['name']) . ' [ child-board ]' : format_comment($arr['name'])) . '</option>';
        }
    }

    $body .= ($staff === false ? '
                </select>
            </span>
        </form>
    </div>' : '');

    return $body;
}

$title = _('Forums');
if (empty($breadcrumbs)) {
    $breadcrumbs = [
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
}
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
