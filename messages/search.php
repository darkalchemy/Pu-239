<?php

declare(strict_types = 1);

$user = check_user_status();
global $container, $site_config;

$num_result = $and_member = '';
$keywords = isset($_POST['keywords']) ? htmlsafechars($_POST['keywords']) : '';
$member = isset($_POST['member']) ? htmlsafechars($_POST['member']) : '';
$all_boxes = isset($_POST['all_boxes']) ? (int) $_POST['all_boxes'] : '';
$sender_reciever = $mailbox >= 1 ? 'sender' : 'receiver';
$what_in_out = $mailbox >= 1 ? 'AND receiver = ' . sqlesc($user['id']) : 'AND sender = ' . sqlesc($user['id']);
$location = isset($_POST['all_boxes']) ? 'AND location != 0' : 'AND location = ' . $mailbox;
$limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 25;
$as_list_post = isset($_POST['as_list_post']) ? (int) $_POST['as_list_post'] : 2;
$desc_asc = isset($_POST['ASC']) == 1 ? 'ASC' : 'DESC';
$subject = isset($_POST['subject']) ? htmlsafechars($_POST['subject']) : '';
$text = isset($_POST['text']) ? htmlsafechars($_POST['text']) : '';
$member_sys = isset($_POST['system']) ? 'system' : '';
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
    stderr(_('Error'), _('A ruffian that will swear, drink, dance, revel the night, rob, murder and commit the oldest of ins the newest kind of ways.'));
}

if ($member) {
    $res_username = sql_query('SELECT id FROM users WHERE LOWER(username) = LOWER(' . sqlesc($member) . ') LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $arr_userid = mysqli_fetch_assoc($res_username);
    if (mysqli_num_rows($res_username) === 0) {
        stderr(_('Error'), _('Sorry, there is no member with that username.'));
    }
    //=== if searching by member...
    $and_member = ($mailbox >= 1 ? ' AND sender = ' . sqlesc($arr_userid['id']) : ' AND receiver = ' . sqlesc($arr_userid['id']));
    $the_username = format_username((int) $arr_userid['id']);
}
if ($member_sys) {
    $and_member = ' AND sender = 0 ';
    $the_username = '<span>System</span>';
}

$res = sql_query('SELECT boxnumber, name FROM pmboxes WHERE userid = ' . sqlesc($user['id']) . ' ORDER BY boxnumber') or sqlerr(__FILE__, __LINE__);

$HTMLOUT .= $top_links . '
        <h1>' . _('Search Messages') . '</h1>
        <form action="messages.php?action=search" method="post" accept-charset="utf-8">
            <input type="hidden" name="action"  value="' . _('search') . '">';
$header = '
                <tr>
                    <th colspan="2">' . _('Search') . '</th>
                </tr>';
$body = '
                <tr>
                    <td><span>' . _('Search terms:') . '</span></td>
                    <td><input type="text" class="search" name="keywords" value="' . $keywords . '"> [ ' . _('words to search for. common words are ignored') . ' ] </td>
                </tr>
                <tr>
                    <td><span>' . _('Search box:') . '</span></td>
                    <td>' . get_all_boxes($box, $user['id']) . '</td>
                </tr>
                <tr>
                    <td><span>' . _('Search all boxes:') . '</span></td>
                    <td><input name="all_boxes" type="checkbox" value="1" ' . ($all_boxes == 1 ? 'checked' : '') . '> [ ' . _('if checked the above box selection will be ignored') . ' ] </td>
                </tr>
                <tr>
                    <td><span>' . _('By member:') . '</span></td>
                    <td><input type="text" class="member" name="member" value="' . $member . '"> [ ' . _('search messages by this member only') . ' ] </td>
                </tr>
                <tr>
                    <td><span>' . _('System messages:') . '</span></td>
                    <td><input name="system" type="checkbox" value="system" ' . ($member_sys === 'system' ? 'checked' : '') . '>' . _('System (search system messages only)') . '</td>
                </tr>
                <tr>
                    <td><span>' . _('Search in:') . '</span></td>
                    <td><input name="subject" type="checkbox" value="1" ' . ($subject == 1 ? 'checked' : '') . '>' . _('Subject') . '
                    <input name="text" type="checkbox" value="1" ' . ($text === 1 ? 'checked' : '') . '>' . _('message text (select one or both. if none selected, both are assumed)') . '</td>
                </tr>
                <tr>
                    <td><span>' . _('Sort by:') . '</span></td>
                    <td>
                    <select name="sort">
                        <option value="relevance" ' . ($sort === 'relevance' ? 'selected' : '') . '>' . _('Relevance') . '</option>
                        <option value="subject" ' . ($sort === 'subject' ? 'selected' : '') . '>' . _('Subject') . '</option>
                        <option value="added" ' . ($sort === 'added' ? 'selected' : '') . '>' . _('Added') . '</option>
                        <option value="' . $sender_reciever . '" ' . ($sort === $sender_reciever ? 'selected' : '') . '>' . _('Member') . ' </option>
                    </select>
                        <input name="ASC" type="radio" value="1" ' . ((isset($_POST['ASC']) && $_POST['ASC'] == 1) ? 'checked' : '') . '>' . _(' Ascending ') . ' < input name="ASC" type="radio" value="2" ' . ((isset($_POST['ASC']) && $_POST['ASC'] == 2 || !isset($_POST['ASC'])) ? 'checked' : '') . '>' . _('  Descending') . ' </td>
                </tr>
                <tr>
                    <td><span> ' . _('Show:') . ' </span></td>
                    <td>
                    <select name="limit">
                        <option value="25" ' . (($limit == 25 || !$limit) ? 'selected' : '') . '>' . _fe('first {0} results, 25') . ' </option>
                        <option value="50" ' . ($limit == 50 ? 'selected' : '') . '>' . _fe('first {0} results', 50) . ' </option>
                        <option value="75" ' . ($limit == 75 ? 'selected' : '') . '>' . _fe('first {0} results', 75) . ' </option>
                        <option value="100" ' . ($limit == 100 ? 'selected' : '') . '>' . _fe('first {0} results', 100) . ' </option>
                        <option value="150" ' . ($limit == 150 ? 'selected' : '') . '>' . _fe('first {0} results', 200) . ' </option>
                        <option value="200" ' . ($limit == 200 ? 'selected' : '') . '>' . _fe('first {0} results', 200) . ' </option>
                        <option value="1000" ' . ($limit == 1000 ? 'selected' : '') . '>' . _('all results') . ' </option>
                    </select></td>
                </tr> ' . ($limit < 100 ? '
                <tr>
                    <td><span> ' . _('Display as:') . ' </span></td>
                    <td><input name="as_list_post" type="radio" value="1" ' . ($as_list_post == 1 ? 'checked' : '') . '><span> ' . _('List') . ' </span>
                    <input name="as_list_post" type="radio" value="2" ' . ($as_list_post == 2 ? 'checked' : '') . '><span> ' . _('Message') . ' </span></td>
                </tr> ' : '') . ' < tr class="no_hover">
                    <td colspan="2" class="has-text-centered margin20">
                    <input type="submit" class="button is-small" name="change" value="' . _('search') . '"></td>
                </tr> ';
$HTMLOUT .= main_table($body, $header);
$HTMLOUT .= '
            </form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $remove_me = [
        'a',
        'the',
        ' and ',
        'to',
        'for',
        'by',
    ];
    $search = preg_replace(' / \b(' . implode(' | ', $remove_me) . ')\b / ', '', $keywords);
    switch (true) {
        case !$keywords && $member:
            $res_search = sql_query('SELECT * FROM messages WHERE sender = ' . sqlesc($arr_userid['id']) . " $location AND receiver = " . sqlesc($user['id']) . ' ORDER BY ' . sqlesc($sort) . " $desc_asc LIMIT " . $limit) or sqlerr(__FILE__, __LINE__);
            break;

        case !$keywords && $member_sys:
            $res_search = sql_query("SELECT * FROM messages WHERE sender = 0 $location AND receiver = " . sqlesc($user['id']) . ' ORDER BY ' . sqlesc($sort) . " $desc_asc LIMIT " . $limit) or sqlerr(__FILE__, __LINE__);
            break;

        case $subject && !$text:
            $res_search = sql_query('SELECT *, MATCH(subject)
                    AGAINST(' . sqlesc($search) . ' IN NATURAL LANGUAGE MODE) AS relevance
                    FROM messages WHERE MATCH(subject) AGAINST(' . sqlesc($search) . " IN NATURAL LANGUAGE MODE)
                    $and_member $location $what_in_out
                    ORDER BY " . sqlesc($sort) . " $desc_asc LIMIT $limit") or sqlerr(__FILE__, __LINE__);
            break;

        case !$subject && $text:
            $res_search = sql_query('SELECT *, MATCH(msg)
                    AGAINST(' . sqlesc($search) . ' IN NATURAL LANGUAGE MODE) AS relevance
                    FROM messages WHERE MATCH(msg) AGAINST(' . sqlesc($search) . " IN NATURAL LANGUAGE MODE)
                    $and_member $location $what_in_out
                    ORDER BY " . sqlesc($sort) . " $desc_asc LIMIT $limit") or sqlerr(__FILE__, __LINE__);
            break;

        case $subject && $text || !$subject && !$text:
            $res_search = sql_query('SELECT *, MATCH(subject, msg)
                    AGAINST(' . sqlesc($search) . ' IN NATURAL LANGUAGE MODE) AS relevance
                    FROM messages WHERE MATCH(subject, msg) AGAINST(' . sqlesc($search) . " IN NATURAL LANGUAGE MODE)
                    $and_member $location $what_in_out
                    ORDER BY " . sqlesc($sort) . " $desc_asc LIMIT $limit") or sqlerr(__FILE__, __LINE__);
            break;
    }
    $num_result = mysqli_num_rows($res_search);
    $table = $table_header = $table_body = '';
    if ($as_list_post === 1) {
        $table_header = "
            <tr>
                <th class='w-10 has-text-centered'>Mailbox</th>
                <th class='w-50'>" . _('Subject') . "</th>
                <th class='w-10 has-text-centered'>" . _('Sender') . "</th>
                <th class='w-10 has-text-centered'>" . _(' Date') . "</th>
                <th class='w-1 has-text-centered'><input type='checkbox' id='checkThemAll' class='tooltipper' title='" . _('Select All') . "'></th>
            </tr>";

        while ($row = mysqli_fetch_assoc($res_search)) {
            $read = $row['unread'] === 'yes' ? "<img src='{$site_config['paths']['images_baseurl']}pn_inboxnew.gif' title='" . _('Unread Message') . "' alt='" . _('Unread') . "' class='tooltipper'>" : "<img src='{$site_config['paths']['images_baseurl']}pn_inbox.gif title='" . _('Read Message') . "' alt='" . _('Read') . "' class='tooltipper'>";
            $sender = $row['sender'] > 0 ? format_username((int) $row['sender']) : 'System';
            $date = str_replace(', ', '<br>', get_date((int) $row['added'], 'LONG'));
            $subject = str_ireplace($keywords, " <span style='background-color:yellow;font-weight:bold;color:black;'>{$keywords}</span> ", htmlsafechars((string) $row['subject']));
            $table_body .= "<tr>
                <td class='w-10 has-text-centered'>$read</td>
                <td><a href='{$site_config['paths']['baseurl']}/messages.php?id={$row['id']}'>{$subject}</a></td>
                <td class='w-10 has-text-centered'> $sender</td>
                <td class='has-text-centered'> $date</td>
                <td class='w-1 has-text-centered'><input type='checkbox' name='pm[]' value='" . (int) $row['id'] . "'></td>
            </tr> ";
        }
        $table = main_table($table_body, $table_header);
    } else {
        while ($row = mysqli_fetch_assoc($res_search)) {
            $sender = $row['sender'] > 0 ? format_username((int) $row['sender']) : 'System';
            $date = get_date((int) $row['added'], 'LONG');
            $body = str_ireplace($keywords, "<span style='background-color:yellow;font-weight:bold;color:black;'>{$keywords}</span>", format_comment($row['msg']));
            $subject = str_ireplace($keywords, "<span style='background-color:yellow;font-weight:bold;color:black;'>{$keywords}</span>", htmlsafechars((string) $row['subject']));
            $table .= main_table("
            <tr>
                <td class='w-10'>" . _('Subject') . "</td>
                <td><a href='{$site_config['paths']['baseurl']}/messages.php?id={$row['id']}'>$subject</a></td>
                <td class='w-1'><input type='checkbox' name='pm[]' value='" . (int) $row['id'] . "'></td>
            </tr>
            <tr>
                <td class='w-10'></td>
                <td colspan='2'>$body</td>
            </tr>
            <tr>
                <td class='w-10'>" . ($mailbox === $site_config['pm']['sent'] ? _('Search') : _('Sender')) . "</td>
                <td colspan='2'>$sender</td>
            </tr>
            <tr>
                <td class='w-10'>" . _(' Date') . "</td>
                <td colspan='2'>$date</td>
            </tr>", null, null, 'bottom20');
        }
    }
    $search_str = '';
    if (isset($keywords)) {
        $search_str = $keywords;
    } elseif (isset($member)) {
        $search_str = _fe("Member {0}'s PM's", format_username((int) $arr_userid['id']));
    } elseif (isset($member_sys)) {
        $search_str = _('system messages');
    }
    $results = '
        <h1>' . _('Your search for %s', $search_str) . '</h1>
        <h3>' . ($num_result < $limit ? _('returned') : _('showing first')) . ' <span>' . $num_result . '</span>
        ' . _('match') . ($num_result === 1 ? '' : _('es')) . _('!') . ($num_result === 0 ? _(' better luck next time...') : '') . '
        </h3>';
    if ($num_result > 0) {
        $results .= " <form action='messages.php' method='post' name='messages' enctype='multipart/form-data' accept-charset='utf-8'>
        <input type='hidden' name='action' value='move_or_delete_multi'>
        <input type='hidden' name='returnto' value='search'>$table
        <div class='has-text-centered top20'>";
        if ($as_list_post === 2) {
            $results .= "
            <input type='checkbox' id='checkThemAll' class='tooltipper' title='" . _('Select All') . "'><span class='left10 right10'>" . _('Select All') . '</span> ';
        }
        $results .= "
            <input type='submit' class='button is-small right10' name='move' value='" . _('Move to') . "'>" . get_all_boxes($box, $user['id']) . " or
            <input type='submit' class='button is-small left10 right10' name='delete' value='" . _('Delete') . "'>" . _(' selected messages.') . '
        </div>
    </form>';
    }

    $HTMLOUT .= main_div($results, 'top20');
}
