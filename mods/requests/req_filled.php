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
$torrentid = (isset($_POST['torrentid']) ? (int)$_POST['torrentid'] : 0);
if ($torrentid < 1) stderr("{$lang['error_error']}", "{$lang['error_funky']}");
$res = sql_query("SELECT id FROM torrents WHERE id = $torrentid") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if (!$arr) stderr("{$lang['error_error']}", "{$lang['error_no_torrent2']}$torrentid");
$res = sql_query("SELECT users.username, requests.userid, requests.torrentid, requests.request FROM requests inner join users on requests.userid = users.id where requests.id = $id") or sqlerr(__FILE__, __LINE__);
$arr = mysqli_fetch_assoc($res);
if ($CURUSER['id'] == $arr['userid']) stderr("{$lang['error_error']}", "{$lang['error_own_id']}");
$msg = "{$lang['filled_your']}[b]" . htmlspecialchars($arr['request']) . "[/b]{$lang['filled_by']}[b]" . $CURUSER['username'] . "[/b]{$lang['filled_dl']}[b][url=details.php?id=" . $torrentid . "]" . $INSTALLER09['baseurl'] . "/details.php?id=" . $torrentid . "[/url][/b]{$lang['filled_thx']}{$lang['filled_wrong']}[b][url=" . $INSTALLER09['baseurl'] . "/viewrequests.php?id=$id&req_reset]{$lang['filled_this']}[/url][/b]{$lang['filled_link']}";
sql_query("UPDATE requests SET torrentid = " . $torrentid . ", filledby = $CURUSER[id] WHERE id = $id") or sqlerr(__FILE__, __LINE__);
sql_query("INSERT INTO messages (poster, sender, receiver, added, msg, subject, location) VALUES(0, 0, $arr[userid], " . TIME_NOW . ", " . sqlesc($msg) . ", 'Request Filled', 1)") or sqlerr(__FILE__, __LINE__);
//$Cache->delete_value('inbox_new_'.$arr['userid'].'');
if ($INSTALLER09['karma'] && isset($CURUSER['seedbonus'])) sql_query("UPDATE users SET seedbonus = seedbonus+" . $INSTALLER09['req_comment_bonus'] . " WHERE id = $CURUSER[id]") or sqlerr(__FILE__, __LINE__);
$res = sql_query("SELECT `userid` FROM `voted_requests` WHERE `requestid` = $id AND userid != $arr[userid]") or sqlerr(__FILE__, __LINE__);
$msgs_buffer = array();
if (mysqli_num_rows($res) > 0) {
    $pn_subject = sqlesc("{$lang['add_request']} " . $arr['request'] . "{$lang['filled_upl']}");
    $pn_msg = sqlesc("{$lang['filled_voted']}[b]" . $arr['request'] . "[/b]{$lang['filled_by']}[b]" . $CURUSER['username'] . "[/b]{$lang['filled_dl']}
    [b][url=details.php?id=" . $torrentid . "]" . $INSTALLER09['baseurl'] . "/details.php?id=" . $torrentid . "[/url][/b].
      {$lang['filled_thx']}");
    while ($row = mysqli_fetch_assoc($res)) $msgs_buffer[] = '(0, ' . $row['userid'] . ', ' . TIME_NOW . ', ' . $pn_msg . ', ' . $pn_subject . ')';
    $pn_count = count($msgs_buffer);
    if ($pn_count > 0) {
        sql_query("INSERT INTO messages (sender,receiver,added,msg,subject) VALUES " . implode(', ', $msgs_buffer)) or sqlerr(__FILE__, __LINE__);
        //write_log('[Request Filled Messaged '.$pn_count.' members');
        
    }
    unset($msgs_buffer);
}
((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
$HTMLOUT.= "<table class='main' width='750px' border='0' cellspacing='0' cellpadding='0'>" . "<tr><td class='embedded'>\n";
$HTMLOUT.= "<h1 align='center'>{$lang['reset_success']}</h1>
<table cellspacing='10' cellpadding='10'>
<tr><td align='left'>{$lang['filled_your']}$id (" . htmlspecialchars($arr['request']) . "){$lang['filled_success']}<a class='altlink' href='details.php?id=" . $torrentid . "'>" . $INSTALLER09['baseurl'] . "/details.php?id=" . $torrentid . "</a>.  
<br /><br />{$lang['filled_user']}<a class='altlink' href='userdetails.php?id=$arr[userid]'><b>$arr[username]</b></a>{$lang['filled_pm']}<br /><br />
{$lang['filled_mistake']}<br />{$lang['filled_reset']}<a class='altlink' href='viewrequests.php?id=$id&amp;req_reset'>{$lang['filled_here']}</a> 
<br /><br />{$lang['filled_unless']}<br /><br />
<a class='altlink' href='viewrequests.php'>{$lang['req_view_all']}</a>
</td></tr></table>";
$HTMLOUT.= "</td></tr></table>\n";
/////////////////////// HTML OUTPUT //////////////////////////////
echo stdhead('Request Filled') . $HTMLOUT . stdfoot();
?>
