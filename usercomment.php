<?php
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
//== usercomments.php - by pdq - based on comments.php, duh :P
require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php');
require_once (INCL_DIR . 'user_functions.php');
require_once (INCL_DIR . 'bbcode_functions.php');
require_once (INCL_DIR . 'pager_functions.php');
require_once (INCL_DIR . 'html_functions.php');
dbconn(false);
loggedinorreturn();
$lang = array_merge(load_language('global'));
$HTMLOUT = $user = '';
$action = isset($_GET["action"]) ? htmlsafechars(trim($_GET["action"])) : '';
$stdhead = array(
    /** include css **/
    'css' => array(
        'style',
        'style2',
        'bbcode'
    )
);
function usercommenttable($rows)
{
    $htmlout = '';
    global $CURUSER, $INSTALLER09, $userid, $lang;
    $htmlout.= begin_main_frame();
    $htmlout.= begin_frame();
    $count = 0;
    foreach ($rows as $row) {
        $htmlout.= "<p class='sub'>#".(int)$row['id']." by ";
        if (isset($row["username"])) {
            $title = $row["title"];
            if ($title == "") $title = get_user_class_name($row["class"]);
            else $title = htmlsafechars($title);
            $htmlout.= "<a name='comm" . (int)$row['id'] . "' href='userdetails.php?id=" . (int)$row['user'] . "'><b>" . htmlsafechars($row["username"]) . "</b></a>" . ($row["donor"] == "yes" ? "<img src=\"{$INSTALLER09['pic_base_url']}star.gif\" alt='Donor' />" : "") . ($row["warned"] >= 1 ? "<img src=" . "\"{$INSTALLER09['pic_base_url']}warned.gif\" alt=\"Warned\" />" : "") . " ($title)\n";
        } else $htmlout.= "<a name=\"comm" . (int)$row["id"] . "\"><i>(orphaned)</i></a>\n";
        $htmlout.= " " . get_date($row["added"], 'DATE', 0, 1) . "" . ($userid == $CURUSER["id"] || $row["user"] == $CURUSER["id"] || $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=edit&amp;cid=".(int)$row['id']."'>Edit</a>]" : "") . ($userid == $CURUSER["id"] || $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=delete&amp;cid=" . (int)$row['id'] . "'>Delete</a>]" : "") . ($row["editedby"] && $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=vieworiginal&amp;cid=" . (int)$row['id'] . "'>View original</a>]" : "") . "</p>\n";
        $avatar = ($CURUSER["avatars"] == "yes" ? htmlsafechars($row["avatar"]) : "");
        if (!$avatar) $avatar = "{$INSTALLER09['pic_base_url']}default_avatar.gif";
        $text = format_comment($row["text"]);
        if ($row["editedby"]) $text.= "<font size='1' class='small'><br /><br />Last edited by <a href='userdetails.php?id=" . (int)$row['editedby'] . "'><b>" . htmlsafechars($row['edit_name']) . "</b></a> " . get_date($row['editedat'], 'DATE', 0, 1) . "</font>\n";
        $htmlout.= begin_table(true);
        $htmlout.= "<tr valign='top'>\n";
        $htmlout.= "<td align='center' width='150' style='padding:0px'><img width='150' src=\"{$avatar}\" alt=\"Avatar\" /></td>\n";
        $htmlout.= "<td class='text'>{$text}</td>\n";
        $htmlout.= "</tr>\n";
        $htmlout.= end_table();
    }
    $htmlout.= end_frame();
    $htmlout.= end_main_frame();
    return $htmlout;
}
if ($action == "add") {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $userid = 0 + $_POST["userid"];
        if (!is_valid_id($userid)) stderr("Error", "Invalid ID.");
        $res = sql_query("SELECT username FROM users WHERE id =" . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_array($res, MYSQLI_NUM);
        if (!$arr) stderr("Error", "No user with that ID.");
        $body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
        if (!$body) stderr("Error", "Comment body cannot be empty!");
        sql_query("INSERT INTO usercomments (user, userid, added, text, ori_text) VALUES (" . sqlesc($CURUSER['id']) . ", " . sqlesc($userid) . ", '" . TIME_NOW . "', " . sqlesc($body) . "," . sqlesc($body) . ")");
        $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
        sql_query("UPDATE users SET comments = comments + 1 WHERE id =" . sqlesc($userid));
        header("Refresh: 0; url=userdetails.php?id=$userid&viewcomm=$newid#comm$newid");
        die;
    }
    $userid = 0 + $_GET["userid"];
    if (!is_valid_id($userid)) stderr("Error", "Invalid ID.");
    $res = sql_query("SELECT username FROM users WHERE id = " . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) stderr("Error", "No user with that ID.");
    $HTMLOUT.= "<h1>Add a comment for '" . htmlsafechars($arr["username"]) . "'</h1>
    <form method='post' action='usercomment.php?action=add'>
    <input type='hidden' name='userid' value='$userid' />
    <div>". BBcode(false)."</div>
    <br /><br />
    <input type='submit' class='btn' value='Do it!' /></form>\n";
    $res = sql_query("SELECT usercomments.id, usercomments.text, usercomments.editedby, usercomments.editedat, usercomments.added, usercomments.edit_name, username, users.id as user, users.avatar, users.title, users.anonymous, users.class, users.donor, users.warned, users.leechwarn, users.chatpost FROM usercomments LEFT JOIN users ON usercomments.user = users.id WHERE user = " . sqlesc($userid) . " ORDER BY usercomments.id DESC LIMIT 5");
    $allrows = array();
    while ($row = mysqli_fetch_assoc($res)) $allrows[] = $row;
    if (count($allrows)) {
        $HTMLOUT.= "<h2>Most recent comments, in reverse order</h2>\n";
        $HTMLOUT.= usercommenttable($allrows);
    }
    echo stdhead("Add a comment for \"" . htmlsafechars($arr["username"]) . "\"", true, $stdhead) . $HTMLOUT . stdfoot();
    die;
} elseif ($action == "edit") {
    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid)) stderr("Error", "Invalid ID.");
    $res = sql_query("SELECT c.*, u.username, u.id FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) stderr("Error", "Invalid ID.");
    if ($arr["user"] != $CURUSER["id"] && $CURUSER['class'] < UC_STAFF) stderr("Error", "Permission denied.");
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $body = htmlsafechars($_POST["body"]);
        $returnto = htmlsafechars($_POST["returnto"]);
        if ($body == "") stderr("Error", "Comment body cannot be empty!");
        $editedat = sqlesc(TIME_NOW);
        sql_query("UPDATE usercomments SET text=" . sqlesc($body) . ", editedat={$editedat}, edit_name=".sqlesc($CURUSER['username']).", editedby=" . sqlesc($CURUSER['id']) . " WHERE id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
        if ($returnto) header("Location: $returnto");
        else header("Location: {$INSTALLER09['baseurl']}/userdetails.php?id={$userid}");
        die;
    }
    $HTMLOUT.= "<h1>Edit comment for \"" . htmlsafechars($arr["username"]) . "\"</h1>
    <form method='post' action='usercomment.php?action=edit&amp;cid={$commentid}'>
    <input type='hidden' name='returnto' value='{$_SERVER["HTTP_REFERER"]}' />
    <input type=\"hidden\" name=\"cid\" value='" . (int)$commentid . "' />
    <textarea name='body' rows='10' cols='60'>" . htmlsafechars($arr["text"]) . "</textarea>
    <input type='submit' class='btn' value='Do it!' /></form>";
    echo stdhead("Edit comment for \"" . htmlsafechars($arr["username"]) . "\"", true, $stdhead) . $HTMLOUT . stdfoot();
    stdfoot();
    die;
} elseif ($action == "delete") {
    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid)) stderr("Error", "Invalid ID.");
    $sure = isset($_GET["sure"]) ? (int)$_GET["sure"] : false;
    if (!$sure) {
        $referer = $_SERVER["HTTP_REFERER"];
        stderr("Delete comment", "You are about to delete a comment. Click\n" . "<a href='usercomment.php?action=delete&amp;cid=$commentid&amp;sure=1" . ($referer ? "&amp;returnto=" . urlencode($referer) : "") . "'>here</a> if you are sure.");
        //stderr("Delete comment", "You are about to delete a comment. Click\n" . "<a href='usercomment.php?action=delete&amp;cid={$commentid}&amp;sure=1&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'>here</a> if you are sure.");
        
    }
    $res = sql_query("SELECT id, userid FROM usercomments WHERE id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if ($arr['id'] != $CURUSER['id']) {
        if ($CURUSER['class'] < UC_STAFF) stderr("Error", "Permission denied.");
    }
    if ($arr) $userid = (int)$arr["userid"];
    sql_query("DELETE FROM usercomments WHERE id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    if ($userid && mysqli_affected_rows($GLOBALS["___mysqli_ston"]) > 0) sql_query("UPDATE users SET comments = comments - 1 WHERE id = " . sqlesc($userid));
    $returnto = htmlsafechars($_GET["returnto"]);
    if ($returnto) header("Location: $returnto");
    else header("Location: {$INSTALLER09['baseurl']}/userdetails.php?id={$userid}");
    die;
} elseif ($action == "vieworiginal") {
    if ($CURUSER['class'] < UC_STAFF) stderr("Error", "Permission denied.");
    $commentid = 0 + $_GET["cid"];
    if (!is_valid_id($commentid)) stderr("Error", "Invalid ID.");
    $res = sql_query("SELECT c.*, u.username FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id=" . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) stderr("Error", "Invalid ID");
    $HTMLOUT.= "<h1>Original contents of comment #{$commentid}</h1>
    <table width='500' border='1' cellspacing='0' cellpadding='5'>
    <tr><td class='comment'>\n";
    $HTMLOUT.= " " . htmlsafechars($arr["ori_text"]);
    $HTMLOUT.= "</td></tr></table>\n";
    $returnto = htmlsafechars($_SERVER["HTTP_REFERER"]);
    if ($returnto) $HTMLOUT.= "<font size='small'>(<a href='{$returnto}'>back</a>)</font>\n";
    echo stdhead("User Comments") . $HTMLOUT . stdfoot();
    die;
} else stderr("Error", "Unknown action");
die;
?>
