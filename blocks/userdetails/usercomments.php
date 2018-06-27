<?php
/**
 * @param $rows
 *
 * @return string
 */
function usercommenttable($rows)
{
    $htmlout = '';
    global $CURUSER, $site_config, $userid, $lang;

    $htmlout .= "<table class='main' width='750' >" . "<tr><td class='embedded'>";
    $htmlout .= begin_frame();
    $count = 0;
    foreach ($rows as $row) {
        $htmlout .= "<p class='sub'>#" . (int) $row['id'] . ' by ';
        if (isset($row['username'])) {
            $title = $row['title'];
            if ($title == '') {
                $title = get_user_class_name($row['class']);
            } else {
                $title = htmlsafechars($title);
            }
            $htmlout .= format_username($row['user']) . "<br> ($title)\n";
        } else {
            $htmlout .= '<a id="comm' . (int) $row['id'] . "\"><i>{$lang['userdetails_orphaned']}</i></a>\n";
        }
        $htmlout .= ' ' . get_date($row['added'], 'DATE', 0, 1) . '' . ($userid == $CURUSER['id'] || $row['user'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=edit&amp;cid=" . (int) $row['id'] . "'>{$lang['userdetails_comm_edit']}</a>]" : '') . ($userid == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=delete&amp;cid=" . (int) $row['id'] . "'>{$lang['userdetails_comm_delete']}</a>]" : '') . ($row['editedby'] && $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=vieworiginal&amp;cid=" . (int) $row['id'] . "'>{$lang['userdetails_comm_voriginal']}</a>]" : '') . "</p>\n";
        $avatar = get_avatar($row);
        $text = format_comment($row['text']);
        if ($row['editedby']) {
            $text .= "<span class='size_2'>" . format_username($row['editedby']) . ' ' . get_date($row['editedat'], 'DATE', 0, 1) . "</span>\n";
        }
        $htmlout .= "
            <table width='100%' >
                <tr>
                    <td class='has-text-centered w-25 mw-150'>{$avatar}</td>
                    <td class='text'>$text</td>
                </tr>
            </table>";
    }
    $htmlout .= end_frame();
    $htmlout .= '</td></tr></table>';

    return $htmlout;
}

$text = "
    <a id='startcomments'></a>
    <div class='has-text-centered'>
        <h1>{$lang['userdetails_comm_left']}" . format_username($id) . '</a></h1>';
$commentbar = "
        <a href='{$site_config['baseurl']}/usercomment.php?action=add&amp;userid={$id}'>Add a comment</a>";
$subres = sql_query('SELECT COUNT(id) FROM usercomments WHERE userid = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$subrow = mysqli_fetch_array($subres, MYSQLI_NUM);
$count = $subrow[0];
if (!$count) {
    $text .= "
        <h2>{$lang['userdetails_comm_yet']}</h2>\n";
} else {
    require_once INCL_DIR . 'pager_functions.php';
    $pager = pager(5, $count, "userdetails.php?id=$id&amp;", [
        'lastpagedefault' => 1,
    ]);
    $subres = sql_query("SELECT usercomments.id, text, user, usercomments.added, editedby, editedat, avatar, offensive_avatar, anonymous, warned, username, title, class, leechwarn, chatpost, pirate, king, donor FROM usercomments LEFT JOIN users ON usercomments.user = users.id WHERE userid = {$id} ORDER BY usercomments.id {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
    $allrows = [];
    while ($subrow = mysqli_fetch_assoc($subres)) {
        $allrows[] = $subrow;
    }
    $text .= ($commentbar);
    $text .= ($pager['pagertop']);
    $text .= usercommenttable($allrows);
    $text .= ($pager['pagerbottom']);
}
$text .= ($commentbar);
$text .= '</div>';

$HTMLOUT .= main_div($text);
