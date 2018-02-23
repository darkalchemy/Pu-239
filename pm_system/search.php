<?php

global $CURUSER, $lang, $site_config;

$num_result = $and_member = '';
$keywords = (isset($_POST['keywords']) ? htmlsafechars($_POST['keywords']) : '');
$member = (isset($_POST['member']) ? htmlsafechars($_POST['member']) : '');
$all_boxes = (isset($_POST['all_boxes']) ? intval($_POST['all_boxes']) : '');
$sender_reciever = ($mailbox >= 1 ? 'sender' : 'receiver');
$what_in_out = ($mailbox >= 1 ? 'AND receiver = '.sqlesc($CURUSER['id']) : 'AND sender = '.sqlesc($CURUSER['id']));
$location = (isset($_POST['all_boxes']) ? 'AND location != 0' : 'AND location = '.$mailbox);
$limit = (isset($_POST['limit']) ? intval($_POST['limit']) : 25);
$as_list_post = (isset($_POST['as_list_post']) ? intval($_POST['as_list_post']) : 2);
$desc_asc = (1 == isset($_POST['ASC']) ? 'ASC' : 'DESC');
$subject = (isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : '');
$text = (isset($_POST['text']) ? htmlsafechars($_POST['text']) : '');
$member_sys = (isset($_POST['system']) ? 'system' : '');
$possible_sort = [
    'added',
    'subject',
    'sender',
    'receiver',
    'relevance',
];
$box = isset($_POST['box']) ? (int) $_POST['box'] : 1;
$sort = (isset($_GET['sort']) ? htmlsafechars($_GET['sort']) : (isset($_POST['sort']) ? htmlsafechars($_POST['sort']) : 'relevance'));
if (!in_array($sort, $possible_sort)) {
    stderr($lang['pm_error'], $lang['pm_error_ruffian']);
} else {
    $sort = htmlsafechars($_POST['sort']);
}

if ($member) {
    $res_username = sql_query('SELECT id FROM users WHERE LOWER(username) = LOWER('.sqlesc($member).') LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $arr_userid = mysqli_fetch_assoc($res_username);
    if (0 === mysqli_num_rows($res_username)) {
        stderr($lang['pm_error'], $lang['pm_forwardpm_nomember']);
    }
    //=== if searching by member...
    $and_member = ($mailbox >= 1 ? ' AND sender = '.sqlesc($arr_userid['id']) : ' AND receiver = '.sqlesc($arr_userid['id']));
    $the_username = format_username($arr_userid['id']);
}
if ($member_sys) {
    $and_member = ' AND sender = 0 ';
    $the_username = '<span>System</span>';
}

$res = sql_query('SELECT boxnumber, name FROM pmboxes WHERE userid = '.sqlesc($CURUSER['id']).' ORDER BY boxnumber') or sqlerr(__FILE__, __LINE__);

$HTMLOUT .= $top_links.'
        <h1>'.$lang['pm_search_title'].'</h1>
        <form action="pm_system.php?action=search" method="post">
            <input type="hidden" name="action"  value="'.$lang['pm_search_btn'].'" />';
$header = '
                <tr>
                    <th colspan="2">'.$lang['pm_search_s'].'</th>
                </tr>';
$body = '
                <tr>
                    <td><span>'.$lang['pm_search_terms'].'</span></td>
                    <td><input type="text" class="search" name="keywords" value="'.$keywords.'" />'.$lang['pm_search_common'].'</td>
                </tr>
                <tr>
                    <td><span>'.$lang['pm_search_box'].'</span></td>
                    <td>'.get_all_boxes($box).'</td>
                </tr>
                <tr>
                    <td><span>'.$lang['pm_search_allbox'].'</span></td>
                    <td><input name="all_boxes" type="checkbox" value="1" '.(1 == $all_boxes ? ' checked' : '').' />'.$lang['pm_search_ignored'].'</td>
                </tr>
                <tr>
                    <td><span>'.$lang['pm_search_member_by'].'</span></td>
                    <td><input type="text" class="member" name="member" value="'.$member.'" />'.$lang['pm_search_member_only'].'</td>
                </tr>
                <tr>
                    <td><span>'.$lang['pm_search_system'].'</span></td>
                    <td><input name="system" type="checkbox" value="system" '.('system' == $member_sys ? ' checked' : '').' />'.$lang['pm_search_system_only'].'</td>
                </tr>
                <tr>
                    <td><span>'.$lang['pm_search_in'].'</span></td>
                    <td><input name="subject" type="checkbox" value="1" '.(1 == $subject ? ' checked' : '').' />'.$lang['pm_search_subject'].'
                    <input name="text" type="checkbox" value="1" '.(1 === $text ? ' checked' : '').' />'.$lang['pm_search_msgtext'].'</td>
                </tr>
                <tr>
                    <td><span>'.$lang['pm_search_sortby'].'</span></td>
                    <td>
                    <select name="sort">
                        <option value="relevance" '.('relevance' === $sort ? ' selected' : '').'>'.$lang['pm_search_relevance'].'</option>
                        <option value="subject" '.('subject' === $sort ? ' selected' : '').'>'.$lang['pm_search_subject'].'</option>
                        <option value="added" '.('added' === $sort ? ' selected' : '').'>'.$lang['pm_search_added'].'</option>
                        <option value="'.$sender_reciever.'" '.($sort === $sender_reciever ? ' selected="selected' : '').'>'.$lang['pm_search_member'].'</option>
                    </select>
                        <input name="ASC" type="radio" value="1" '.((isset($_POST['ASC']) && 1 == $_POST['ASC']) ? ' checked' : '').' />'.$lang['pm_search_asc'].'
                        <input name="ASC" type="radio" value="2" '.((isset($_POST['ASC']) && 2 == $_POST['ASC'] || !isset($_POST['ASC'])) ? ' checked' : '').' />'.$lang['pm_search_desc'].'</td>
                </tr>
                <tr>
                    <td><span>'.$lang['pm_search_show'].'</span></td>
                    <td>
                    <select name="limit">
                        <option value="25"'.((25 == $limit || !$limit) ? ' selected' : '').'>'.$lang['pm_search_25'].'</option>
                        <option value="50"'.(50 == $limit ? ' selected' : '').'>'.$lang['pm_search_50'].'</option>
                        <option value="75"'.(75 == $limit ? ' selected' : '').'>'.$lang['pm_search_75'].'</option>
                        <option value="100"'.(100 == $limit ? ' selected' : '').'>'.$lang['pm_search_100'].'</option>
                        <option value="150"'.(150 == $limit ? ' selected' : '').'>'.$lang['pm_search_150'].'</option>
                        <option value="200"'.(200 == $limit ? ' selected' : '').'>'.$lang['pm_search_200'].'</option>
                        <option value="1000"'.(1000 == $limit ? ' selected' : '').'>'.$lang['pm_search_allres'].'</option>
                    </select></td>
                </tr>'.($limit < 100 ? '
                <tr>
                    <td><span>'.$lang['pm_search_display'].'</span></td>
                    <td><input name="as_list_post" type="radio" value="1" '.(1 == $as_list_post ? ' checked' : '').' /> <span>'.$lang['pm_search_list'].'</span>
                    <input name="as_list_post" type="radio" value="2" '.(2 == $as_list_post ? ' checked' : '').' /> <span> '.$lang['pm_search_message'].'</span></td>
                </tr>' : '').'
                <tr class="no_hover">
                    <td colspan="2" class="has-text-centered margin20">
                    <input type="submit" class="button is-small" name="change" value="'.$lang['pm_search_btn'].'" /></td>
                </tr>';
$HTMLOUT .= main_table($body, $header);
$HTMLOUT .= '
            </form>';

if ('POST' === $_SERVER['REQUEST_METHOD']) {
    $remove_me = [
        'a',
        'the',
        'and',
        'to',
        'for',
        'by',
    ];
    $search = preg_replace('/\b('.implode('|', $remove_me).')\b/', '', $keywords);
    switch (true) {
        case !$keywords && $member:
            $res_search = sql_query('SELECT * FROM messages WHERE sender = '.sqlesc($arr_userid['id'])." $location AND receiver = ".sqlesc($CURUSER['id']).' ORDER BY '.sqlesc($sort)." $desc_asc LIMIT ".$limit) or sqlerr(__FILE__, __LINE__);
            break;

        case !$keywords && $member_sys:
            $res_search = sql_query("SELECT * FROM messages WHERE sender = 0 $location AND receiver = ".sqlesc($CURUSER['id']).' ORDER BY '.sqlesc($sort)." $desc_asc LIMIT ".$limit) or sqlerr(__FILE__, __LINE__);
            break;

        case $subject && !$text:
            $res_search = sql_query(
                'SELECT *, MATCH(subject)
                    AGAINST('.sqlesc($search).' IN NATURAL LANGUAGE MODE) AS relevance
                    FROM messages WHERE MATCH(subject) AGAINST ('.sqlesc($search)." IN NATURAL LANGUAGE MODE)
                    $and_member $location $what_in_out
                    ORDER BY ".sqlesc($sort)." $desc_asc LIMIT $limit"
            ) or sqlerr(__FILE__, __LINE__);
            break;

        case !$subject && $text:
            $res_search = sql_query(
                'SELECT *, MATCH(msg)
                    AGAINST('.sqlesc($search).' IN NATURAL LANGUAGE MODE) AS relevance
                    FROM messages WHERE MATCH(msg) AGAINST ('.sqlesc($search)." IN NATURAL LANGUAGE MODE)
                    $and_member $location $what_in_out
                    ORDER BY ".sqlesc($sort)." $desc_asc LIMIT $limit"
            ) or sqlerr(__FILE__, __LINE__);
            break;

        case $subject && $text || !$subject && !$text:
            $res_search = sql_query(
                'SELECT *, MATCH(subject, msg)
                    AGAINST ('.sqlesc($search).' IN NATURAL LANGUAGE MODE) AS relevance
                    FROM messages WHERE MATCH(subject,msg) AGAINST ('.sqlesc($search)." IN NATURAL LANGUAGE MODE)
                    $and_member $location $what_in_out
                    ORDER BY ".sqlesc($sort)." $desc_asc LIMIT $limit"
            ) or sqlerr(__FILE__, __LINE__);
            break;
    }
    $num_result = mysqli_num_rows($res_search);
    $table = $table_header = $table_body = '';
    if (1 === $as_list_post) {
        $table_header = "
            <tr>
                <th class='w-10 has-text-centered'>Mailbox</th>
                <th class='w-50'>{$lang['pm_search_subject']}</th>
                <th class='w-10 has-text-centered'>Sender</th>
                <th class='w-10 has-text-centered'>{$lang['pm_search_date']}</th>
                <th class='w-1 has-text-centered'><input type='checkbox' id='checkThemAll' class='tooltipper' title='Select All' /></th>
            </tr>";

        while ($row = mysqli_fetch_assoc($res_search)) {
            $read = 'yes' === $row['unread'] ? "<img src='{$site_config['pic_baseurl']}pn_inboxnew.gif' title='{$lang['pm_mailbox_unreadmsg']}' alt='{$lang['pm_mailbox_unread']}' class='tooltipper' />" : "<img src='{$site_config['pic_baseurl']}pn_inbox.gif title='{$lang['pm_mailbox_readmsg']}' alt='{$lang['pm_mailbox_read']}' class='tooltipper' />";
            $sender = $row['sender'] > 0 ? format_username($row['sender']) : 'System';
            $date = str_replace(', ', '<br>', get_date($row['added'], 'LONG'));
            $subject = str_ireplace($keywords, "<span style='background-color:yellow;font-weight:bold;color:black;'>{$keywords}</span>", htmlsafechars($row['subject']));
            $table_body .= "
            <tr>
                <td class='w-10 has-text-centered'>$read</td>
                <td><a href='{$site_config['baseurl']}/pm_system.php?id={$row['id']}'>{$subject}</a></td>
                <td class='w-10 has-text-centered'>$sender</td>
                <td class='has-text-centered'>$date</td>
                <td class='w-1 has-text-centered'><input type='checkbox' name='pm[]' value='".(int) $row['id']."' /></td>
            </tr>";
        }
        $table = main_table($table_body, $table_header);
    } else {
        while ($row = mysqli_fetch_assoc($res_search)) {
            $sender = $row['sender'] > 0 ? format_username($row['sender']) : 'System';
            $date = get_date($row['added'], 'LONG');
            $body = str_ireplace($keywords, "<span style='background-color:yellow;font-weight:bold;color:black;'>{$keywords}</span>", format_comment($row['msg']));
            $subject = str_ireplace($keywords, "<span style='background-color:yellow;font-weight:bold;color:black;'>{$keywords}</span>", htmlsafechars($row['subject']));
            $table .= main_table("
            <tr>
                <td class='w-10'>{$lang['pm_search_subject']}</td>
                <td><a href='{$site_config['baseurl']}/pm_system.php?id={$row['id']}'>$subject</a></td>
                <td class='w-1'><input type='checkbox' name='pm[]' value='".(int) $row['id']."' /></td>
            </tr>
            <tr>
                <td class='w-10'></td>
                <td colspan='2'>$body</td>
            </tr>
            <tr>
                <td class='w-10'>".(PM_SENTBOX === $mailbox ? $lang['pm_search_send_to'] : $lang['pm_search_sender'])."</td>
                <td colspan='2'>$sender</td>
            </tr>
            <tr>
                <td class='w-10'>{$lang['pm_search_date']}</td>
                <td colspan='2'>$date</td>
            </tr>", null, null, 'bottom20');
        }
    }

    $results = "
        <h1>{$lang['pm_search_your_for']}".($keywords ? '"'.$keywords.'"' : ($member ? $lang['pm_search_member'].format_username($arr_userid['id']).$lang['pm_search_pms'] : ($member_sys ? $lang['pm_search_sysmsg'] : ''))).'</h1>
        <h3>'.($num_result < $limit ? $lang['pm_search_returned'] : $lang['pm_search_show_first']).' <span>'.$num_result.'</span>
        '.$lang['pm_search_match'].''.(1 === $num_result ? '' : $lang['pm_search_matches']).$lang['pm_search_excl'].(0 === $num_result ? $lang['pm_search_better'] : '').'
        </h3>';
    if ($num_result > 0) {
        $results .= "
    <form action='pm_system.php' method='post' name='messages'>
        <input type='hidden' name='action' value='move_or_delete_multi' />
        <input type='hidden' name='returnto' value='search' />
        $table
        <div class='has-text-centered top20'>";
        if (2 === $as_list_post) {
            $results .= "
            <input type='checkbox' id='checkThemAll' class='tooltipper' title='Select All' /><span class='left10 right10'>Select All</span>";
        }
        $results .= "
            <input type='submit' class='button is-small right10' name='move' value='{$lang['pm_search_move_to']}' />".get_all_boxes($box)." or
            <input type='submit' class='button is-small left10 right10' name='delete' value='{$lang['pm_search_delete']}' />{$lang['pm_search_selected']}
        </div>
    </form>";
    }

    $HTMLOUT .= main_div($results, 'top20');
}
