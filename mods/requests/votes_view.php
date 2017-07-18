<?php
if (!defined('IN_REQUESTS')) exit('No direct script access allowed');
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
$res2 = sql_query('select count(voted_requests.id) AS c from voted_requests inner join users on voted_requests.userid = users.id inner join requests on voted_requests.requestid = requests.id WHERE voted_requests.requestid =' . $id) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res2);
$count = (int)$row['c'];
if ($count > 0) {
    $pager = pager(25, $count, 'viewrequests.php?');
    $res = sql_query('select users.id as userid,users.username, users.downloaded, users.title, users.class, users.donor, users.warned, users.leechwarn, users.chatpost, users.pirate, users.king, users.enabled, users.uploaded, requests.id as requestid, requests.request, requests.added from voted_requests inner join users on voted_requests.userid = users.id inner join requests on voted_requests.requestid = requests.id WHERE voted_requests.requestid =' . $id . ' ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
    $res2 = sql_query("select request from requests where id=$id");
    $arr2 = mysqli_fetch_assoc($res2);
    $HTMLOUT.= "<h1>{$lang['view_voters']}<a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>" . htmlspecialchars($arr2['request']) . "</b></a></h1>";
    $HTMLOUT.= "<p>{$lang['view_vote_this']}<a class='altlink' href='viewrequests.php?id=$id&amp;req_vote'><b>{$lang['view_req']}</b></a></p>";
    $HTMLOUT.= $pager['pagertop'];
    if (mysqli_num_rows($res) == 0) $HTMLOUT.= "<p align='center'><b>{$lang['view_nothing']}</b></p>\n";
    else {
        $HTMLOUT.= "<table border='1' cellspacing='0' cellpadding='5'>
<tr><td class='colhead'>{$lang['view_name']}</td><td class='colhead' align='left'>{$lang['view_upl']}</td><td class='colhead' align='left'>{$lang['view_dl']}</td>
<td class='colhead' align='left'>{$lang['view_ratio']}</td></tr>\n";
        while ($arr = mysqli_fetch_assoc($res)) {
            $ratio = member_ratio($arr['uploaded'], $arr['downloaded']);
            $uploaded = mksize($arr['uploaded']);
            $joindate = get_date($arr['added'], '');
            $downloaded = mksize($arr["downloaded"]);
            $enabled = ($arr['enabled'] == 'no' ? '<span style="color:red;">No</span>' : '<span style="color:green;">Yes</span>');
            $arr['id'] = $arr['userid'];
            $username = format_username($arr);
            $HTMLOUT.= "<tr><td><b>$username</b></td>
             <td align='left'>$uploaded</td>
             <td align='left'>$downloaded</td>
             <td align='left'>$ratio</td></tr>\n";
        }
        $HTMLOUT.= "</table>\n";
    }
    $HTMLOUT.= $pager['pagerbottom'];
} else $HTMLOUT.= "{$lang['req_nothing']}";
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Voters') . $HTMLOUT . stdfoot();
?>
