<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_rating.php';
require_once CLASS_DIR . 'class_user_options.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('forums'), load_language('forums_global'));
$stdhead = [
    'css' => [
        get_file('forums_css')
    ],
];
$stdfoot = [
    'js' => [
        get_file('forums_js')
    ],
];
$over_forum_id = $count = $now_viewing = $child_boards = '';
if ($site_config['forums_online'] == 0 && $CURUSER['class'] < UC_STAFF) {
    stderr($lang['fm_information'], $lang['fm_the_forums_are_currently_offline']);
}
$HTMLOUT = '';
//=== update members last forums access
sql_query('UPDATE users SET forum_access=' . TIME_NOW . ' WHERE id=' . sqlesc($CURUSER['id']));
/*==============================
the following is 110% up to you...
you can set all the configuration stuff here in the forums.php main file,
or you can use the admin/forum_config.php.
hardcoding the vars here is a bit more secure, but some sites are administered
without a coder being handy all the time, so I've added this option to the code :)
using the forum_config SQL and following the instructions will get you started with the defaults and the forums as I have them set up
the default is to use the config method...
un-comment the next bit and set up the values below if you are gong to use hard coded method!
and comment out the following config DB method stuff
=====================================
IF you DON'T want to use the forum_config.php...

use the following and suit to your site:

    //=== Retros read post mod (sets all posts older then XX days to read, saves a huge bunch of DB space
    //=== I  just noticed that this is now a TBDEV global... I'll leave it here and in cleanup, as they are the only places it's used ***
    $readpost_expiry = 14 * 86400; //=== 14 days

    //=== stuff for file uploads
    $min_upload_class = UC_POWER_USER;

    //=== if you change the following 2 lines,  you will need to change code in new_topic.php & post_reply.php & edit_post.php
    $accepted_file_extension  = array('.zip', '.rar');
    $accepted_file_types  = array('application/zip', 'application/rar');
    $max_file_size = 1024*1024*2; //=== 2 MB
    //=== name of your uploads folder must be writable chmod 777 will do
    $upload_folder = 'uploads/'; //===  you should rename this for security. or even beter put it outside your root dir :D

============================================================ */
//=== get config info from the DB (comment out and use hard coded if you prefer)
$config_id = 1;
$config_res = sql_query('SELECT delete_for_real, min_delete_view_class, readpost_expiry, min_upload_class, accepted_file_extension, accepted_file_types, max_file_size, upload_folder FROM forum_config WHERE id = ' . sqlesc($config_id));
$config_arr = mysqli_fetch_assoc($config_res);
//=== all config stuff:
$delete_for_real = ($config_arr['delete_for_real'] == 1 ? 1 : 0);
$min_delete_view_class = htmlsafechars($config_arr['min_delete_view_class']);
$readpost_expiry = ((int)$config_arr['readpost_expiry'] * 86400);
$min_upload_class = htmlsafechars($config_arr['min_upload_class']);
$accepted_file_extension = [
    $config_arr['accepted_file_extension'],
];
$accepted_file_types = [
    $config_arr['accepted_file_types'],
];
$max_file_size = intval($config_arr['max_file_size']);
$upload_folder = ROOT_DIR . htmlsafechars(trim($config_arr['upload_folder']));
//=== post / get action posted so we know what to do :P
$posted_action = strip_tags((isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '')));
//=== add all possible actions here and check them to be sure they are ok
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
        'member_post_history',
    ];
}

//=== tool tip
function tool_tip($link, $text, $title = false)
{
    $bubble = '<a href="#" class="tt_f2"><span class="tooltip_forum_tip"><span class="top">' . $title . '</span><span class="middle">' . $text . '</span></span>' . $link . '</a>';

    return $bubble;
}

//=== check posted action, and if no action was posted, show the default main forums page
$action = (in_array($posted_action, $valid_actions) ? $posted_action : 'forum');
//=== some default global type stuff
//=== let admin and above delete shite
$jsbottom = '';
if ($CURUSER['class'] >= UC_ADMINISTRATOR) {
    $jsbottom .= "
    <script>
        function confirm_delete(id) {
            if(confirm('" . $lang['fm_are_you_sure_you_want_to_delete_this_forum?'] . "')) {
                self.location.href='staffpanel.php?tool=forum_manage&action=delete&id='+id;
            }
        }
    </script>";
}
//=== mini menu
$mini_menu = '
    <div class="answers-container">
        <a class="altlink bordered" href="./forums.php?action=subscriptions">' . $lang['fm_my_subscriptions'] . '</a>
        <a class="altlink bordered" href="./forums.php?action=search">' . $lang['fe_search'] . '</a>
        <a class="altlink bordered" href="./forums.php?action=view_unread_posts">' . $lang['fm_unread_posts'] . '</a>
        <a class="altlink bordered" href="./forums.php?action=new_replies">' . $lang['fm_new_replies'] . '</a>
        <a class="altlink bordered" href="./forums.php?action=view_my_posts">' . $lang['fm_my_posts'] . '</a>
        <a class="altlink bordered" href="./forums.php?action=mark_all_as_read">' . $lang['fm_mark_all_as_read'] . '</a>' . ($CURUSER['class'] === UC_MAX ? '
        <a class="altlink bordered" href="./forums.php?action=member_post_history">' . $lang['fm_member_post_history'] . '</a>' : '
    </div>');
$location_bar = $mini_menu . (isset($_GET['m']) ? '
    <h1 class="text-center">' . $lang['fm_all_forums_up_to_date'] . '.</h1>' : '');
$legend = '
    <table class="table table-bordered table-striped top20 bottom20 first">
        <thead>
            <tr>
                <th class="forum_head_dark" colspan="8">' . $lang['fm_legend'] . '</th>
            </tr>
        </thead>
    <tr>
    <td><img src="./images/forums/unlockednew.gif" alt="unlockednew" title="' . $lang['fm_unlocked_new'] . '"/></td>
    <td>' . $lang['fm_unread_forum'] . '</td>
    <td><img src="./images/forums/unlocked.gif" alt="unlocked" title="' . $lang['fm_unlocked'] . '" /></td>
    <td>' . $lang['fm_read_forum'] . '</td>
    <td><img src="./images/forums/topicnew.gif" alt="topicnew" title="' . $lang['fe_new_topic'] . '" /></td>
    <td>' . $lang['fm_unread_post'] . '</td>
    <td><img src="./images/forums/topic.gif" alt="topic" title="' . $lang['fe_topic'] . '" /></td>
    <td>' . $lang['fm_read_post'] . '</td>
    </tr>
    <tr>
    <td><img src="./images/forums/hot_topic_new.gif" alt="hot_topic_new" title="' . $lang['fm_hot_topic_new'] . '" /></td>
    <td>' . $lang['fm_hot_topic_unread'] . '</td>
    <td><img src="./images/forums/hot_topic.gif" alt="hot_topic" title="' . $lang['fm_hot_topic'] . '" /></td>
    <td>' . $lang['fm_hot_topic_more_than_30_replies'] . '</td>
    <td><img src="./images/forums/lockednew.gif" alt="lockednew" title="' . $lang['fm_locked_new'] . '"/></td>
    <td>' . $lang['fm_locked_un-read'] . '</td>
    <td><img src="./images/forums/locked.gif" alt="locked" title="' . $lang['fe_locked'] . '" /></td>
    <td>' . $lang['fe_locked'] . '</td>
    </tr>
    <tr>
    <td><img src="./images/forums/poll.gif" alt="poll" title="' . $lang['fe_poll'] . '" /></td>
    <td>' . $lang['fe_poll'] . '</td>
    <td><img src="./images/forums/pinned.gif" alt="pinned" title="' . $lang['fe_pinned'] . '" /></td>
    <td>' . $lang['fe_pinned'] . '</td>
    <td><img src="./images/forums/subscriptions.gif" alt="' . $lang['fe_subscribed'] . '" title="' . $lang['fe_subscribed'] . '" /></td>
    <td>' . $lang['fm_subscribed_to_thread'] . '</td>
    <td><img src="./images/forums/posted.gif" alt="posted" title="' . $lang['fm_posted'] . '" /></td>
    <td>' . $lang['fm_you_have_posted_here'] . '</td>
    </tr>
    <tr>
    <td><img src="./images/forums/mg.gif" height="20" alt="' . $lang['fm_1st_post_preview'] . '" title="' . $lang['fm_1st_post_preview'] . '" /></td>
    <td>' . $lang['fm_1st_post_preview'] . '</td>
    <td><img src="./images/forums/last_post.gif" alt="last post" title="' . $lang['fe_last_post'] . '" /></td>
    <td>' . $lang['fe_last_post'] . '</td>
    <td><img src="./images/forums/topic_normal.gif" alt="' . $lang['fe_thread_icon'] . '" title="' . $lang['fe_thread_icon'] . '" /></td>
    <td>' . $lang['fe_thread_icon'] . '</td>
    <td></td>
    <td></td>
    </tr>
    </table>';
//=== more options poll & atachments
$poll_starts = (isset($_POST['poll_starts']) ? intval($_POST['poll_starts']) : 0);
$poll_ends = (isset($_POST['poll_ends']) ? intval($_POST['poll_ends']) : 1356048000);
$change_vote = ((isset($_POST['change_vote']) && $_POST['change_vote'] === 'yes') ? 'yes' : 'no');
$multi_options = (isset($_POST['multi_options']) ? intval($_POST['multi_options']) : 1);
//$can_add_poll = (isset($_GET['action']) && $_GET['action'] == 'new_topic' ? 1 : 0);
//=== options for amount of options lol
$options = '';
for ($i = 2; $i < 21; ++$i) {
    $options .= '<option class="body" value="' . $i . '" ' . ($multi_options === $i ? 'selected="selected"' : '') . '>' . $i . ' options</option>';
}
$more_options = '<div id="tools" ' . ((isset($_POST['poll_question']) && $_POST['poll_question'] !== '') ? '' : '') . ' >
   
    <table class="table table-bordered table-striped top20 bottom20 second">
        <thead>
            <tr>
                <th class="forum_head_dark" colspan="3">' . $lang['fm_additional_options'] . '...</th>
            </tr>
        </thead>' . ($CURUSER['class'] < $min_upload_class ? '' : '<tr>
    <td><img src="' . $site_config['pic_base_url'] . 'forums/attach.gif" alt="' . $lang['fm_attach'] . '" /></td>
    <td><span>' . $lang['fe_attachments'] . ':</span></td>
    <td>
    <input type="file" name="attachment[]" /> <a title="' . $lang['fm_add_more_attachments'] . '"  id="more">' . $lang['fm_add_more_attachments'] . '</a>
    <img src="' . $site_config['pic_base_url'] . 'forums/zip.gif" alt="' . $lang['fe_zip'] . '" width="18" />
    <img src="' . $site_config['pic_base_url'] . 'forums/rar.gif" alt="' . $lang['fe_rar'] . '" width="18" />
    <div id="attach_more">
    <input type="file" name="attachment[]" />
    <input type="file" name="attachment[]" />
    <input type="file" name="attachment[]" />
    </div>
    </td>
    </tr>') . ((isset($_GET['action']) && $_GET['action'] != 'new_topic') ? '' : '<tr>
    <td></td>
    <td></td>
    <td><span> <img src="' . $site_config['pic_base_url'] . 'forums/poll.gif" alt="" /> ' . $lang['poll_add_poll_to_topic'] . '</span>
    </td>
    </tr>
    <tr>
    <td><img src="' . $site_config['pic_base_url'] . 'forums/question.png" alt="Question" width="24" /></td>
    <td><span>' . $lang['poll_question'] . ':</span></td>
    <td><input type="text" name="poll_question" class="text_default" value="' . (isset($_POST['poll_question']) ? strip_tags($_POST['poll_question']) : '') . '" /></td>
    </tr>
    <tr>
    <td><img src="' . $site_config['pic_base_url'] . 'forums/options.gif" alt="' . $lang['poll_answers'] . '" width="24" /></td>
    <td><span>' . $lang['poll_answers'] . ':</span></td>
    <td><textarea cols="30" rows="4" name="poll_answers" class="text_area_small">' . (isset($_POST['poll_answers']) ? strip_tags($_POST['poll_answers']) : '') . '</textarea> ' . $lang['poll_one_option_per_line_min_2_op_max_20_options_bbcode_is_enabled.'] . '</td>
    </tr>
    <tr>
    <td><img src="' . $site_config['pic_base_url'] . 'forums/clock.png" alt="' . $lang['poll_starts'] . '" width="30" /></td>
    <td><span>' . $lang['poll_starts'] . ':</span></td>
    <td><select name="poll_starts">
   <option class="body" value="0" ' . ($poll_starts === 0 ? 'selected="selected"' : '') . '>' . $lang['poll_start_now'] . '!</option>
    <option class="body" value="1" ' . ($poll_starts === 1 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_day'], '1') . '</option>
    <option class="body" value="2" ' . ($poll_starts === 2 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '2') . '</option>
    <option class="body" value="3" ' . ($poll_starts === 3 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '3') . '</option>
    <option class="body" value="4" ' . ($poll_starts === 4 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '4') . '</option>
    <option class="body" value="5" ' . ($poll_starts === 5 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '5') . '</option>
    <option class="body" value="6" ' . ($poll_starts === 6 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '6') . '</option>
    <option class="body" value="7" ' . ($poll_starts === 7 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_week'], '1') . '</option>
    </select> ' . $lang['fm_when_to_start_the_poll'] . ' "' . $lang['poll_start_now'] . '!"</td>
    </tr>
    <tr>
    <td><img src="' . $site_config['pic_base_url'] . 'forums/stop.png" alt="' . $lang['poll_ends'] . '" width="20" /></td>
    <td><span>' . $lang['poll_ends'] . ':</span></td>
    <td><select name="poll_ends">
    <option class="body" value="1356048000" ' . ($poll_ends === 1356048000 ? 'selected="selected"' : '') . '>' . $lang['poll_run_forever'] . '</option>
    <option class="body" value="1" ' . ($poll_ends === 1 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_day'], '1') . '</option>
    <option class="body" value="2" ' . ($poll_ends === 2 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '2') . '</option>
    <option class="body" value="3" ' . ($poll_ends === 3 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '3') . '</option>
    <option class="body" value="4" ' . ($poll_ends === 4 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '4') . '</option>
    <option class="body" value="5" ' . ($poll_ends === 5 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '5') . '</option>
    <option class="body" value="6" ' . ($poll_ends === 6 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_days'], '6') . '</option>
   <option class="body" value="7" ' . ($poll_ends === 7 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_week'], '1') . '</option>
   <option class="body" value="14" ' . ($poll_ends === 14 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_weeks'], '2') . '</option>
    <option class="body" value="21" ' . ($poll_ends === 21 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_weeks'], '3') . '</option>
    <option class="body" value="28" ' . ($poll_ends === 28 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_month'], '1') . '</option>
    <option class="body" value="56" ' . ($poll_ends === 56 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_months'], '2') . '</option>
    <option class="body" value="84" ' . ($poll_ends === 84 ? 'selected="selected"' : '') . '>' . sprintf($lang['poll_in_x_months'], '3') . '</option>
    </select> How long should this poll run? Default is "run forever"</td>
    </tr>
    <tr>
    <td><img src="' . $site_config['pic_base_url'] . 'forums/multi.gif" alt="' . $lang['poll_multi_options'] . '" width="20" /></td>
    <td><span>' . $lang['poll_multi_options'] . ':</span></td>
    <td><select name="multi_options">
    <option class="body" value="1" ' . ($multi_options === 1 ? 'selected="selected"' : '') . '>' . $lang['poll_single_option'] . '!</option>
    ' . $options . '
    </select> ' . $lang['fm_allow_members_to_have_more_then_one_selection'] . ' "' . $lang['poll_single_option'] . '!"</td>
    </tr>
    <tr>
    <td></td>
    <td><span>' . $lang['poll_change_vote'] . ':</span></td>
    <td><input name="change_vote" value="yes" type="radio"' . ($change_vote === 'yes' ? ' checked="checked"' : '') . ' />' . $lang['fm_yes'] . '
    <input name="change_vote" value="no" type="radio"' . ($change_vote === 'no' ? ' checked="checked"' : '') . ' />' . $lang['fm_no'] . ' ' . $lang['fm_allow_members_to_change_their_vote'] . ' "no"
    </td></tr>') . '
    </table>
    </div>';
$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) : (isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0));
//=== print the bottom of the page
$the_bottom_of_the_page = '';
$the_bottom_of_the_page .= insert_quick_jump_menu($forum_id) . $legend;
$the_bottom_of_the_page .= $jsbottom . stdfoot($stdfoot);
//=== here we go with all the possibilities \\o\o/o//
//=== will be sure to put these in order of most hit to make it a bit faster...
switch ($action) {
    //=== view forum section

    case 'view_forum':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        require_once FORUM_DIR . 'view_forum.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;
    //=== view topic

    case 'view_topic':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        require_once FORUM_DIR . 'view_topic.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;
    //=== view  section

    case 'section_view':
        require_once FORUM_DIR . 'section_view.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;
    //===   poll stuff

    case 'poll':
        //require_once 'include/bbcode_functions.php';
        require_once FORUM_DIR . 'poll.php';
        break;
    //=== subscriptions add_subscription

    case 'subscriptions':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        require_once FORUM_DIR . 'subscriptions.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;
    //===  add subscription

    case 'add_subscription':
        require_once FORUM_DIR . 'add_subscription.php';
        break;
    //===  add delete post

    case 'delete_post':
        require_once FORUM_DIR . 'delete_post.php';
        break;
    //===  delete subscription

    case 'delete_subscription':
        require_once FORUM_DIR . 'delete_subscription.php';
        break;
    //=== new topic

    case 'new_topic':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once FORUM_DIR . 'new_topic.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;
    //=== post reply

    case 'post_reply':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once FORUM_DIR . 'post_reply.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'search':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        require_once FORUM_DIR . 'search.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'view_unread_posts':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        require_once FORUM_DIR . 'view_unread_posts.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'new_replies':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        require_once FORUM_DIR . 'new_replies.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'view_my_posts':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        require_once FORUM_DIR . 'view_my_posts.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'member_post_history':
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once INCL_DIR . 'pager_new.php';
        require_once FORUM_DIR . 'member_post_history.php';
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
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once FORUM_DIR . 'edit_post.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'view_post_history':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr('Error', $lang['fm_no_access_for_you_mr_fancy']);
        }
        require_once INCL_DIR . 'bbcode_functions.php';
        require_once FORUM_DIR . 'view_post_history.php';
        $HTMLOUT .= $the_bottom_of_the_page;
        break;

    case 'staff_actions':
        if ($CURUSER['class'] < UC_STAFF) {
            stderr('Error', $lang['fm_no_access_for_you_mr_fancy']);
        }
        require_once FORUM_DIR . 'staff_actions.php';
        break;
    //===  staff lock

    case 'staff_lock':
        if ($CURUSER['class'] < UC_MAX) {
            stderr('Error', $lang['fm_no_access_for_you_mr_fancy']);
        }
        require_once FORUM_DIR . 'stafflock_post.php';
        break;
    //=== default action / forums

    case 'forum':
        //=== some default stuff
        //=== main huge query:
        $res_forums = sql_query('SELECT o_f.id AS over_forum_id, o_f.name AS over_forum_name, o_f.description AS over_forum_description, o_f.min_class_view AS over_forum_min_class_view, f.id AS real_forum_id, f.name, f.description, f.post_count, f.topic_count,  f.forum_id FROM over_forums AS o_f JOIN forums AS f WHERE o_f.min_class_view <= ' . $CURUSER['class'] . ' AND f.min_class_read <= ' . $CURUSER['class'] . ' AND parent_forum = 0 ORDER BY o_f.sort, f.sort ASC');
        $HTMLOUT .= $location_bar . '<table class="table table-bordered table-striped top20 bottom20 third">';
        //=== well... let's do the loop and make the damned page!
        $i = 0;
        while ($arr_forums = mysqli_fetch_assoc($res_forums)) {
            $space = $i++ >= 1 ? '<tr><td colspan="3"></td></tr>' : '';
            //=== if it's a forums section print it, if not, list the fourm sections in it \o/
            $HTMLOUT .= ($arr_forums['over_forum_id'] != $over_forum_id ? '
        ' . $space . '
        <thead>
            <tr>
                <th class="forum_head_dark" colspan="3">
                    <a class="altlink bordered" href="./forums.php?action=section_view&amp;forum_id=' . (int)$arr_forums['over_forum_id'] . '" title="' . htmlsafechars($arr_forums['over_forum_description'], ENT_QUOTES) . '">
                        <span>' . htmlsafechars($arr_forums['over_forum_name'], ENT_QUOTES) . '</span>
                    </a>
                </th>
            </tr>
        </thead>' : '');
            if ($arr_forums['forum_id'] == $arr_forums['over_forum_id']) {
                $forum_id = (int)$arr_forums['real_forum_id'];
                $forum_name = htmlsafechars($arr_forums['name'], ENT_QUOTES);
                $forum_description = htmlsafechars($arr_forums['description'], ENT_QUOTES);
                $topic_count = number_format($arr_forums['topic_count']);
                $post_count = number_format($arr_forums['post_count']);
                //=== Find last post ID - cached \0/
                if (($last_post_arr = $mc1->get_value('last_post_' . $forum_id . '_' . $CURUSER['class'])) === false) {
                    $last_post_arr = mysqli_fetch_assoc(sql_query('SELECT t.id AS topic_id, t.topic_name, t.last_post, t.anonymous AS tan, p.added, p.anonymous AS pan, p.user_id, u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.avatar_rights FROM topics AS t LEFT JOIN posts AS p ON p.topic_id = t.id RIGHT JOIN users AS u ON u.id = p.user_id WHERE ' . ($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? ' t.status != \'deleted\' AND p.status != \'deleted\' AND' : '')) . ' t.forum_id = ' . sqlesc($forum_id) . ' ORDER BY p.id DESC LIMIT 1'));
                    //==
                    $mc1->cache_value('last_post_' . $forum_id . '_' . $CURUSER['class'], $last_post_arr, $site_config['expires']['last_post']);
                }
                //=== only do more if there is a post there...
                if ($last_post_arr['last_post'] > 0) {
                    $last_post_id = (int)$last_post_arr['last_post'];
                    //=== get the last post read by CURUSER (with Retro's $readpost_expiry thingie) - cached \0/
                    if (($last_read_post_arr = $mc1->get_value('last_read_post_' . $last_post_arr['topic_id'] . '_' . $CURUSER['id'])) === false) {
                        $last_read_post_arr = mysqli_fetch_row(sql_query('SELECT last_post_read FROM read_posts WHERE user_id=' . sqlesc($CURUSER['id']) . ' AND topic_id=' . sqlesc($last_post_arr['topic_id'])));
                        $mc1->cache_value('last_read_post_' . $last_post_arr['topic_id'] . '_' . $CURUSER['id'], $last_read_post_arr, $site_config['expires']['last_read_post']);
                    }
                    $image_to_use = ($last_post_arr['added'] > (TIME_NOW - $readpost_expiry)) ? (!$last_read_post_arr or $last_post_id > $last_read_post_arr[0]) : 0;
                    $img = ($image_to_use ? 'unlockednew' : 'unlocked');
                    //== Anonymous  ->
                    if ($last_post_arr['tan'] == 'yes') {
                        if ($CURUSER['class'] < UC_STAFF && $last_post_arr['user_id'] != $CURUSER['id']) {
                            $last_post = '<span>' . $lang['fe_last_post_by'] . ': <i>' . $lang['fe_anonymous'] . '</i> in &#9658; <a class="altlink bordered" href="./forums.php?action=view_topic&amp;topic_id=' . (int)$last_post_arr['topic_id'] . '&amp;page=' . $last_post_id . '#' . $last_post_id . '" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '"><span>' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . '</span></a>' . get_date($last_post_arr['added'], '') . '</span>';
                        } else {
                            $last_post = '<span>' . $lang['fe_last_post_by'] . ': <i>' . $lang['fe_anonymous'] . '</i> [' . ($last_post_arr['username'] !== '' ? format_username($last_post_arr['user_id']) : '' . $lang['fe_lost'] . '') . '] <span> [ ' . get_user_class_name($last_post_arr['class']) . ' ] </span>in &#9658; <a class="altlink bordered" href="./forums.php?action=view_topic&amp;topic_id=' . (int)$last_post_arr['topic_id'] . '&amp;page=' . $last_post_id . '#' . $last_post_id . '" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '"><span>' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . '</span></a>' . get_date($last_post_arr['added'], '') . '</span>';
                        }
                    } else {
                        $last_post = '<div>' . $lang['fe_last_post_by'] . ': ' . ($last_post_arr['username'] !== '' ? format_username($last_post_arr['user_id']) : '' . $lang['fe_lost'] . '') . '</div><div>In: &#9658; <a href="./forums.php?action=view_topic&amp;topic_id=' . (int)$last_post_arr['topic_id'] . '&amp;page=' . $last_post_id . '#' . $last_post_id . '" class="tooltipper" title="' . htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES) . '">' . CutName(htmlsafechars($last_post_arr['topic_name'], ENT_QUOTES), 30) . ' </a></div><div>On: ' . get_date($last_post_arr['added'], '') . '</div>';
                    }
                    //==
                    //=== get child boards if any - cached \0/
                    $keys['child_boards'] = 'child_boards_' . $last_post_id . '_' . $CURUSER['class'];
                    if (($child_boards_cache = $mc1->get_value($keys['child_boards'])) === false) {
                        $child_boards = '';
                        $child_boards_cache = [];
                        $res = sql_query('SELECT name, id FROM forums WHERE parent_forum = ' . sqlesc($arr_forums['real_forum_id']) . ' AND min_class_read <= ' . sqlesc($CURUSER['class']) . ' ORDER BY sort ASC') or sqlerr(__FILE__, __LINE__);
                        while ($arr = mysqli_fetch_assoc($res)) {
                            if ($child_boards) {
                                $child_boards .= ', ';
                            }
                            $child_boards .= '<a href="./forums.php?action=view_forum&amp;forum_id=' . (int)$arr['id'] . '" title="' . $lang['fm_click_to_view'] . '!" class="altlink bordered">' . htmlsafechars($arr['name'], ENT_QUOTES) . '</a>';
                        }
                        $child_boards_cache['child_boards'] = $child_boards;
                        $mc1->cache_value($keys['child_boards'], $child_boards_cache, $site_config['expires']['child_boards']);
                    }
                    $child_boards = $child_boards_cache['child_boards'];
                    if ($child_boards != '') {
                        $child_boards = '<hr><span>' . $lang['sv_child_boards'] . ':</span> ' . $child_boards;
                    }
                }
                else {
                    $img = 'unlocked';
                    $last_post = 'N/A';
                }
                $HTMLOUT .= '<tr>
    <td>
    <table class="table table-bordered table-striped top20 bottom20 fourth">
    <tr>
    <td width="30"><img src="' . $site_config['pic_base_url'] . 'forums/' . $img . '.gif" alt="' . $img . '" title="' . $lang['fm_unlocked'] . '" /></td>
    <td width="100%">
    ' . bubble('<span><a class="altlink" href="?action=view_forum&amp;forum_id=' . (int)$arr_forums['real_forum_id'] . '">
    ' . $forum_name . '</a></span>', '<span>' . $forum_name . '</span>
    ' . $forum_description) . ($CURUSER['class'] >= UC_ADMINISTRATOR ? '<span>
    [<a class="altlink bordered" href="./staffpanel.php?tool=forum_manage&amp;action=forum_manage&amp;action2=edit_forum_page&amp;id=' . $forum_id . '">' . $lang['fe_edit'] . '</a>]
    [<a class="altlink bordered" href="javascript:confirm_delete(\'' . $forum_id . '\');">' . $lang['fe_delete'] . '</a>]</span>' : '') . '
    <span> ' . $forum_description . '</span>' . $child_boards . $now_viewing . '</td>
    </tr>
    </table>
    </td>
    <td class="w-10">
        <div>' . $post_count . ' ' . $lang['fe_posts'] . '</div>
        <div>' . $topic_count . ' ' . $lang['fe_topics'] . '</div>
    </td>
    <td class="w-25">
        <span>' . $last_post . '</span>
    </td>
    </tr>';
            } //== end of section
            $over_forum_id = (int)$arr_forums['over_forum_id'];
            $child_boards = '';
            //$now_viewing = '';
        } //=== end while loop!
        $HTMLOUT .= '</table>' . $location_bar . '' . insert_quick_jump_menu() . '';
        //== whos looking - cached \0/
        $keys['now_viewing'] = 'now_viewing';
        if (($forum_users_cache = $mc1->get_value($keys['now_viewing'])) === false) {
            $forumusers = '';
            $forum_users_cache = [];
            $res = sql_query('SELECT n_v.user_id, u.id, u.username, u.class, u.donor, u.suspended, u.perms, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king, u.avatar_rights, u.perms FROM now_viewing AS n_v LEFT JOIN users AS u ON n_v.user_id = u.id') or sqlerr(__FILE__, __LINE__);
            $actcount = mysqli_num_rows($res);
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($forumusers) {
                    $forumusers .= ",\n";
                }
                $forumusers .= ($arr['perms'] & bt_options::PERMS_STEALTH ? '<i>UnKn0wn</i>' : format_username($arr['id']));
            }
            $forum_users_cache['forum_users'] = $forumusers;
            $forum_users_cache['actcount'] = $actcount;
            $mc1->cache_value($keys['now_viewing'], $forum_users_cache, $site_config['expires']['forum_users']);
        }
        if (!$forum_users_cache['forum_users']) {
            $forum_users_cache['forum_users'] = '' . $lang['fm_there_have_been_no_active_users_in_the_last_15_minutes'] . '.';
        }
        $forum_users = $forum_users_cache['forum_users'];
        $HTMLOUT .= '<table class="table table-bordered table-striped top20 bottom20 fifth">
   <tr>
   <th class="forum_head_dark">' . $lang['fm_members_currently_active'] . '</th>
   </tr>
    <tr>
    <td>' . $forum_users . '</td>
    </tr>
    </table>' . $legend . stdfoot($stdfoot);
        break;
} //=== end switch
//=== all functions
//=== search string highlighting by fusion found at stackoverflow.com :D
function highlightWords($text, $words)
{
    preg_match_all('~\w+~', $words, $m);
    if (!$m) {
        return $text;
    }
    $re = '~\\b(' . implode('|', $m[0]) . ')~i';
    $string = preg_replace($re, '<span>$0</span>', $text);

    return $string;
}

function ratingpic_forums($num)
{
    global $site_config;
    $r = round($num * 2) / 2;
    if ($r < 1 || $r > 5) {
        return;
    }

    return '<img src="' . $site_config['pic_base_url'] . 'forums/rating/' . $r . '.gif" alt="rating: ' . $num . ' / 5" />';
}

//=== Inserts a quick jump menu ......UPDATED!  now used for staff stuff too \o/ - cached
function insert_quick_jump_menu($current_forum = 0, $staff = false)
{
    global $CURUSER, $site_config, $mc1, $lang;
    $cachename = 'f_insertJumpTo' . $CURUSER['id'] . ($staff === false ? '' : '_staff');
    if (($quick_jump_menu = $mc1->get_value($cachename)) === false) {
        $res = sql_query('SELECT f.id, f.name, f.parent_forum, f.min_class_read, of.name AS over_forum_name FROM forums AS f LEFT JOIN over_forums AS of ON f.forum_id = of.id ORDER BY of.sort, f.parent_forum, f.sort ASC');
        $switch = '';
        $quick_jump_menu = ($staff === false ? '
                <table class="table table-bordered table-striped top20 bottom20 sixth"><tr><td>
                <form method="get" action="./forums.php" name="jump">
                <span>
                <input type="hidden" name="action" value="view_forum" />' . $lang['fm_quick_jump'] . ':
                <select name="forum_id" onchange="if(this.options[this.selectedIndex].value != -1){ forms[\'jump\'].submit() }">
                <option class="head" value="0"> ' . $lang['fm_select_a_forum_to_jump_to'] . '</option>' : '');
        if (mysqli_num_rows($res) > 0) {
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($CURUSER['class'] >= $arr['min_class_read']) {
                    if ($switch !== $arr['over_forum_name']) {
                        $quick_jump_menu .= '<option class="head" value="-1">' . htmlsafechars($arr['over_forum_name']) . ' </option>';
                    }
                    $switch = htmlsafechars($arr['over_forum_name']);
                    $quick_jump_menu .= '<option class="body" value="' . (int)$arr['id'] . '">' . ($arr['parent_forum'] != 0 ? '&#176; ' . htmlsafechars($arr['name']) . ' [ child-board ]' : htmlsafechars($arr['name'])) . '</option>';
                }
            }
        }
        $quick_jump_menu .= ($staff === false ? '</select></span></form></td></tr></table>' : '');
        $mc1->cache_value($cachename, $quick_jump_menu, $site_config['expires']['forum_insertJumpTo']);
    }

    return $quick_jump_menu;
}

echo stdhead($site_config['site_name'] . ' ' . $lang['fe_forums'] . '', true, $stdhead) . $HTMLOUT;
