<?php

global $CURUSER, $site_config, $lang;

$stdfoot = [
    'js' => [
//        'popup',
    ],
];
$res = sql_query('SELECT r.*, r.added AS utadded, u.username 
                  FROM requests AS r LEFT JOIN users AS u ON (u.id=r.userid) 
                  WHERE r.id = ' . $id) or sqlerr(__FILE__, __LINE__);
if (!mysqli_num_rows($res)) {
    stderr("{$lang['error_error']}", "{$lang['error_invalid']}");
}
$num = mysqli_fetch_assoc($res);
$added = get_date($num['utadded'], '');
$s = htmlspecialchars($num['request']);
$HTMLOUT .= "<h3>{$lang['details_details']}" . $s . '</h3>';
$HTMLOUT .= "<table width='750px'><tr><td colspan='2'><h1>$s</h1></td></tr>";
if ($num['descr']) {
    require_once INCL_DIR . 'bbcode_functions.php';
    $HTMLOUT .= "<tr><td><b>{$lang['add_description']}</b></td>
    <td colspan='2'>" . format_comment($num['descr']) . '</td></tr>';
}
$HTMLOUT .= "<tr><td><b>{$lang['req_added']}</b></td>
<td>$added</td></tr>";
if ($CURUSER['id'] == $num['userid'] || $CURUSER['class'] >= UC_STAFF) {
    $edit = " | <a class='altlink' href='viewrequests.php?id=" . $id . "&amp;edit_request'>{$lang['details_edit']}</a> |";
    $delete = " <a class='altlink' href='viewrequests.php?id=" . $id . "&amp;del_req'>{$lang['details_delete']}</a> ";
    if ($num['torrentid'] != 0) {
        $reset = "| <a class='altlink' href='viewrequests.php?id=" . $id . "&amp;req_reset'>{$lang['details_reset']}</a>";
    }
}
$HTMLOUT .= "<tr>
<td><b>{$lang['req_req_by']}</b></td><td>
<a class='altlink' href='userdetails.php?id=$num[userid]'>{$num['username']}</a>  $edit  $delete $reset  |
<a class='altlink' href='viewrequests.php'><b>{$lang['details_all_req']}</b></a> </td></tr><tr><td>
<b>{$lang['details_vote_req']}</b></td><td><a href='viewrequests.php?id=" . $id . "&amp;req_vote'><b>{$lang['details_vote']}</b></a>
</td></tr>
" . ($site_config['reports'] ? "<tr><td><b>{$lang['details_report']}</b></td><td>
{$lang['details_break']} 
<form action='report.php?type=Request&amp;id=$id' method='post'><input class='button is-small' type='submit' name='submit' value='{$lang['details_report']}' /></form></td>
</tr>" : '');
if ($num['torrentid'] == 0) {
    $HTMLOUT .= "<tr><td><b>{$lang['details_fill_this']}</b></td>
<td>" . ($CURUSER['id'] != $num['userid'] ? "
<form method='post' action='viewrequests.php?id=" . $id . "&amp;req_filled'>
    <strong>" . $site_config['baseurl'] . "/details.php?id=</strong><input type='text' size='10' name='torrentid' value='' /> <input type='submit' value='{$lang['details_fill']}' class='button is-small' /><br>
{$lang['details_enter_id']}<br></form>" : "{$lang['details_yours']}") . "</td>
</tr>\n";
} else {
    $HTMLOUT .= "<tr><td><b>{$lang['details_filled']}</b></td><td><a class='altlink' href='details.php?id=" . $num['torrentid'] . "'><b>" . $site_config['baseurl'] . '/details.php?id=' . $num['torrentid'] . '</b></a></td></tr>';
}
$HTMLOUT .= "<tr><td class='embedded' colspan='2'><p><a name='startcomments'></a></p>\n";
$commentbar = "<p><a class='index' href='comment.php?action=add&amp;tid=$id&amp;type=request'>{$lang['details_add_comment']}</a></p>\n";
$subres = sql_query("SELECT COUNT(*) FROM comments WHERE request = $id");
$subrow = mysqli_fetch_array($subres);
$count = $subrow[0];
$HTMLOUT .= '</td></tr></table>';
if (!$count) {
    $HTMLOUT .= "<h2>{$lang['details_no_comment']}</h2>";
} else {
    $pager = pager(25, $count, "viewrequests.php?id=$id&amp;req_details&amp;", [
        'lastpagedefault' => 1,
    ]);
    $subres = sql_query("SELECT comments.id, comments.text, comments.user, comments.editedat, 
                      comments.editedby, comments.ori_text, comments.request AS request, 
                      comments.added, comments.anonymous, users.avatar, users.av_w ,users.av_h,
                      users.warned, users.username, users.title, users.class, users.last_access, 
                      users.enabled, users.reputation, users.donor, users.downloaded, users.uploaded 
                      FROM comments LEFT JOIN users ON comments.user = users.id 
                      WHERE request = $id ORDER BY comments.id") or sqlerr(__FILE__, __LINE__);
    $allrows = [];
    while ($subrow = mysqli_fetch_assoc($subres)) {
        $allrows[] = $subrow;
    }
    $HTMLOUT .= $commentbar;
    $HTMLOUT .= $pager['pagertop'];
    require_once INCL_DIR . 'html_functions.php';
    $HTMLOUT .= commenttable($allrows, 'request');
    $HTMLOUT .= $pager['pagerbottom'];
}
$HTMLOUT .= $commentbar;
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Request Details') . $HTMLOUT . stdfoot($stdfoot);
