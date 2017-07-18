<?php
if (!defined('IN_OFFERS')) exit('No direct script access allowed');
/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                			    |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL			                                    |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4					    |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.					    |
 |--------------------------------------------------------------------------|
  _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
$stdfoot = array(
    /** include js **/
    'js' => array(
        'popup'
    )
);
$res = sql_query('SELECT o.*, o.added as utadded, u.username 
                  FROM offers AS o LEFT JOIN users AS u ON (u.id=o.userid) 
                  WHERE o.id = ' . $id) or sqlerr(__FILE__, __LINE__);
if (!mysqli_num_rows($res)) stderr('Error', 'Invalid Offer ID');
$num = mysqli_fetch_assoc($res);
$added = get_date($num['utadded'], '');
$s = htmlspecialchars($num['offer']);
$HTMLOUT.= '<h3>Details Of Offer: ' . $s . '</h3>';
$HTMLOUT.= "<table border='1' width='750px' cellspacing='0' cellpadding='5'><tr><td align='center' colspan='2'><h1>$s</h1></td></tr>";
if ($num['descr']) {
    require_once 'include/bbcode_functions.php';
    $HTMLOUT.= "<tr><td align='right' valign='top'><b>Description</b></td>
    <td align='left' colspan='2' valign='top'>" . format_comment($num['descr']) . "</td></tr>";
}
$HTMLOUT.= "<tr><td align='right'><b>Added</b></td>
<td align='left'>$added</td></tr>";
if ($CURUSER['id'] == $num['userid'] || $CURUSER['class'] >= UC_MODERATOR) {
    $edit = " | <a class='altlink' href='viewoffers.php?id=" . $id . "&amp;edit_offer'>Edit Offer</a> |";
    $delete = " <a class='altlink' href='viewoffers.php?id=" . $id . "&amp;del_offer'>Delete offer</a> ";
    if ($num['torrentid'] != 0) $reset = "| <a class='altlink' href='viewoffers.php?id=" . $id . "&amp;offer_reset'>Re-set Offer</a>";
}
$HTMLOUT.= "<tr>
<td align='right'><b>offered&nbsp;By</b></td><td align='left'>
<a class='altlink' href='userdetails.php?id=$num[userid]'>{$num['username']}</a>  $edit  $delete $reset  |
<a class='altlink' href='viewoffers.php'><b>All offers</b></a> </td></tr><tr><td align='right'>
<b>Vote for this offer</b></td><td align='left'><a href='viewoffers.php?id=" . $id . "&amp;offer_vote'><b>Vote</b></a>
</td></tr>
" . ($INSTALLER09['reports'] ? "<tr><td align='right'><b>Report Offer</b></td><td align='left'>
for breaking the rules 
<form action='report.php?type=Offer&amp;id=$id' method='post'><input class='btn' type='submit' name='submit' value='Report Offer' /></form></td>
</tr>" : '');
if ($num['torrentid'] == 0) $HTMLOUT.= "<tr><td align='right' valign='top'><b>Accept This Offer</b></td>
    <td>
    <form method='post' action='viewoffers.php?id=" . $id . "&amp;offer_filled'>
    <strong>" . $INSTALLER09['baseurl'] . "/details.php?id=</strong><input type='text' size='10' name='torrentid' value='' /> <input type='submit' value='Fill Offer' class='btn' /><br />
    Enter the <b>ID</b>  of the torrent. (copy/paste the <strong>ID</strong> from another window/tab the correct ID number)<br /></form></td>
    </tr>\n";
else $HTMLOUT.= "<tr><td align='right' valign='top'><b>This Offer was accepted:</b></td><td><a class='altlink' href='details.php?id=" . $num['torrentid'] . "'><b>" . $INSTALLER09['baseurl'] . "/details.php?id=" . $num['torrentid'] . "</b></a></td></tr>";
$HTMLOUT.= "<tr><td class='embedded' colspan='2'><p><a name='startcomments'></a></p>\n";
$commentbar = "<p align='center'><a class='index' href='comment.php?action=add&amp;tid=$id&amp;type=offer'>Add Comment</a></p>\n";
$subres = sql_query("SELECT COUNT(*) FROM comments WHERE offer = $id");
$subrow = mysqli_fetch_array($subres);
$count = $subrow[0];
$HTMLOUT.= '</td></tr></table>';
if (!$count) $HTMLOUT.= '<h2>No comments</h2>';
else {
    $pager = pager(25, $count, "viewoffers.php?id=$id&amp;offer_details&amp;", array(
        'lastpagedefault' => 1
    ));
    $subres = sql_query("SELECT comments.id, comments.text, comments.user, comments.editedat, 
                      comments.editedby, comments.ori_text, comments.offer AS offer, 
                      comments.added, comments.anonymous, users.avatar, users.av_w ,users.av_h,
                      users.warned, users.username, users.title, users.class, users.last_access, 
                      users.enabled, users.reputation, users.donor, users.downloaded, users.uploaded 
                      FROM comments LEFT JOIN users ON comments.user = users.id 
                      WHERE offer = $id ORDER BY comments.id") or sqlerr(__FILE__, __LINE__);
    $allrows = array();
    while ($subrow = mysqli_fetch_assoc($subres)) $allrows[] = $subrow;
    $HTMLOUT.= $commentbar;
    $HTMLOUT.= $pager['pagertop'];
    require_once (INCL_DIR . 'html_functions.php');
    $HTMLOUT.= commenttable($allrows, 'offer');
    $HTMLOUT.= $pager['pagerbottom'];
}
$HTMLOUT.= $commentbar;
/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Offer Details') . $HTMLOUT . stdfoot($stdfoot);
?>
