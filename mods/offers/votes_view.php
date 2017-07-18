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
$res2 = sql_query('select count(voted_offers.id) AS c from voted_offers inner join users on voted_offers.userid = users.id inner join offers on voted_offers.offerid = offers.id WHERE voted_offers.offerid =' . $id) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res2);
$count = (int)$row['c'];
if ($count > 0) {
    $pager = pager(25, $count, 'viewoffers.php?');
    $res = sql_query('select users.id as userid,users.username, users.downloaded, users.title, users.class, users.donor, users.warned, users.leechwarn, users.chatpost, users.pirate, users.king, users.enabled, users.uploaded, offers.id as offerid, offers.offer, offers.added from voted_offers inner join users on voted_offers.userid = users.id inner join offers on voted_offers.offerid = offers.id WHERE voted_offers.offerid =' . $id . ' ' . $pager['limit']) or sqlerr(__FILE__, __LINE__);
    $res2 = sql_query("select offer from offers where id=$id");
    $arr2 = mysqli_fetch_assoc($res2);
    $HTMLOUT.= "<h1>Voters for <a class='altlink' href='viewoffers.php?id=$id&amp;offer_details'><b>" . htmlspecialchars($arr2['offer']) . "</b></a></h1>";
    $HTMLOUT.= "<p>Vote for this <a class='altlink' href='viewoffers.php?id=$id&amp;offer_vote'><b>Offer</b></a></p>";
    $HTMLOUT.= $pager['pagertop'];
    if (mysqli_num_rows($res) == 0) $HTMLOUT.= "<p align='center'><b>Nothing found</b></p>\n";
    else {
        $HTMLOUT.= "<table border='1' cellspacing='0' cellpadding='5'>
<tr><td class='colhead'>Username</td><td class='colhead' align='left'>Uploaded</td><td class='colhead' align='left'>Downloaded</td>
<td class='colhead' align='left'>Share Ratio</td></tr>\n";
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
} else $HTMLOUT.= 'Nothing here!';
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Voters') . $HTMLOUT . stdfoot();
?>
