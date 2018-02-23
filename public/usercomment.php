<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $CURUSER, $site_config, $userid;

$lang    = load_language('global');
$HTMLOUT = $user = '';
$action  = isset($_GET['action']) ? htmlsafechars(trim($_GET['action'])) : '';
$stdhead = [
    'css' => [
        'style',
        'style2',
        'bbcode',
    ],
];
/**
 * @param $rows
 *
 * @return string
 */
function usercommenttable($rows)
{
    $htmlout = '';
    global $CURUSER, $site_config, $userid;
    $htmlout .= begin_main_frame();
    $htmlout .= begin_frame();
    $count = 0;
    foreach ($rows as $row) {
        $htmlout .= "<p class='sub'>#" . (int) $row['id'] . ' by ';
        if (isset($row['username'])) {
            $title = $row['title'];
            if ('' == $title) {
                $title = get_user_class_name($row['class']);
            } else {
                $title = htmlsafechars($title);
            }
            $htmlout .= format_username($row['id']);
        } else {
            $htmlout .= '<a name="comm' . (int) $row['id'] . "\"><i>(orphaned)</i></a>\n";
        }
        $htmlout .= ' ' . get_date($row['added'], 'DATE', 0, 1) . '' . ($userid == $CURUSER['id'] || $row['user'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=edit&amp;cid=" . (int) $row['id'] . "'>Edit</a>]" : '') . ($userid == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=delete&amp;cid=" . (int) $row['id'] . "'>Delete</a>]" : '') . ($row['editedby'] && $CURUSER['class'] >= UC_STAFF ? " - [<a href='usercomment.php?action=vieworiginal&amp;cid=" . (int) $row['id'] . "'>View original</a>]" : '') . "</p>\n";
        $avatar = ('yes' == $CURUSER['avatars'] ? htmlsafechars($row['avatar']) : '');
        if (!$avatar) {
            $avatar = "{$site_config['pic_baseurl']}forumicons/default_avatar.gif";
        }
        $text = format_comment($row['text']);
        if ($row['editedby']) {
            $text .= "<font size='1' class='small'><br><br>Last edited by " . format_username($row['editedby']) . ' ' . get_date($row['editedat'], 'DATE', 0, 1) . "</font>\n";
        }
        $htmlout .= begin_table(false);
        $htmlout .= "
                    <tr>
                        <td class='has-text-centered' width='150'>
                            <img src='" . image_proxy($avatar) . "' alt='Avatar' class='avatar' />
                        </td>
                        <td class='text'>{$text}</td>
                    </tr>";
        $htmlout .= end_table();
    }
    $htmlout .= end_frame();
    $htmlout .= end_main_frame();

    return $htmlout;
}

if ('add' == $action) {
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        $userid = (int) $_POST['userid'];
        if (!is_valid_id($userid)) {
            stderr('Error', 'Invalid ID.');
        }
        $res = sql_query('SELECT username FROM users WHERE id =' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_array($res, MYSQLI_NUM);
        if (!$arr) {
            stderr('Error', 'No user with that ID.');
        }
        $body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
        if (!$body) {
            stderr('Error', 'Comment body cannot be empty!');
        }
        sql_query('INSERT INTO usercomments (user, userid, added, text, ori_text) VALUES (' . sqlesc($CURUSER['id']) . ', ' . sqlesc($userid) . ", '" . TIME_NOW . "', " . sqlesc($body) . ',' . sqlesc($body) . ')');
        $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
        sql_query('UPDATE users SET comments = comments + 1 WHERE id =' . sqlesc($userid));
        header("Refresh: 0; url=userdetails.php?id=$userid&viewcomm=$newid#comm$newid");
        die();
    }
    $userid = (int) $_GET['userid'];
    if (!is_valid_id($userid)) {
        stderr('Error', 'Invalid ID.');
    }
    $res = sql_query('SELECT username FROM users WHERE id = ' . sqlesc($userid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) {
        stderr('Error', 'No user with that ID.');
    }
    $HTMLOUT .= "<h1>Add a comment for '" . htmlsafechars($arr['username']) . "'</h1>
    <form method='post' action='usercomment.php?action=add'>
    <input type='hidden' name='userid' value='$userid' />
    <div>" . BBcode() . "</div>
    <div class='has-text-centered margin20'>
    <input type='submit' class='button is-small' value='Do it!' />
    </div></form>";
    $res = sql_query('SELECT c.id, c.text, c.editedby, c.editedat, c.added, c.username, users.id AS user, u.avatar, u.title, u.anonymous, u.class, u.donor, u.warned, u.leechwarn, u.chatpost
                        FROM usercomments AS c
                        LEFT JOIN users AS u ON c.user = u.id
                        WHERE user = ' . sqlesc($userid) . '
                        ORDER BY c.id DESC
                        LIMIT 5');
    $allrows = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $allrows[] = $row;
    }
    if (!empty($allrows) && count($allrows)) {
        $HTMLOUT .= "<h2>Most recent comments, in reverse order</h2>\n";
        $HTMLOUT .= usercommenttable($allrows);
    }
    echo stdhead('Add a comment for "' . htmlsafechars($arr['username']) . '"', true, $stdhead) . wrapper($HTMLOUT) . stdfoot();
} elseif ('edit' == $action) {
    $commentid = (int) $_GET['cid'];
    if (!is_valid_id($commentid)) {
        stderr('Error', 'Invalid ID.');
    }
    $res = sql_query('SELECT c.*, u.username, u.id FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id=' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) {
        stderr('Error', 'Invalid ID.');
    }
    if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
        stderr('Error', 'Permission denied.');
    }
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
        $body     = htmlsafechars($_POST['body']);
        $returnto = htmlsafechars($_POST['returnto']);
        if ('' == $body) {
            stderr('Error', 'Comment body cannot be empty!');
        }
        $editedat = sqlesc(TIME_NOW);
        sql_query('UPDATE usercomments SET text = ' . sqlesc($body) . ', editedat = {$editedat}, editedby = ' . sqlesc($CURUSER['id']) . ' WHERE id = ' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
        if ($returnto) {
            header("Location: $returnto");
        } else {
            header("Location: {$site_config['baseurl']}/userdetails.php?id={$userid}");
        }
        die();
    }
    $HTMLOUT .= '<h1>Edit comment for "' . htmlsafechars($arr['username']) . "\"</h1>
    <form method='post' action='usercomment.php?action=edit&amp;cid={$commentid}'>
    <input type='hidden' name='returnto' value='{$_SERVER['HTTP_REFERER']}' />
    <input type=\"hidden\" name=\"cid\" value='" . (int) $commentid . "' />
    <textarea name='body' rows='10' cols='60'>" . htmlsafechars($arr['text']) . "</textarea>
    <div class='has-text-centered margin20'>
        <input type='submit' class='button is-small' value='Do it!' />
    </div></form>";
    echo stdhead('Edit comment for "' . htmlsafechars($arr['username']) . '"', true, $stdhead) . wrapper($HTMLOUT) . stdfoot();
    stdfoot();
    die();
} elseif ('delete' == $action) {
    $commentid = (int) $_GET['cid'];
    if (!is_valid_id($commentid)) {
        stderr('Error', 'Invalid ID.');
    }
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : false;
    if (!$sure) {
        $referer = $_SERVER['HTTP_REFERER'];
        stderr('Delete comment', "You are about to delete a comment. Click\n" . "<a href='usercomment.php?action=delete&amp;cid=$commentid&amp;sure=1" . ($referer ? '&amp;returnto=' . urlencode($referer) : '') . "'>here</a> if you are sure.");
        //stderr("Delete comment", "You are about to delete a comment. Click\n" . "<a href='usercomment.php?action=delete&amp;cid={$commentid}&amp;sure=1&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'>here</a> if you are sure.");
    }
    $res = sql_query('SELECT id, userid FROM usercomments WHERE id=' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if ($arr['id'] != $CURUSER['id']) {
        if ($CURUSER['class'] < UC_STAFF) {
            stderr('Error', 'Permission denied.');
        }
    }
    if ($arr) {
        $userid = (int) $arr['userid'];
    }
    sql_query('DELETE FROM usercomments WHERE id=' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    if ($userid && mysqli_affected_rows($GLOBALS['___mysqli_ston']) > 0) {
        sql_query('UPDATE users SET comments = comments - 1 WHERE id = ' . sqlesc($userid));
    }
    $returnto = htmlsafechars($_GET['returnto']);
    if ($returnto) {
        header("Location: $returnto");
    } else {
        header("Location: {$site_config['baseurl']}/userdetails.php?id={$userid}");
    }
    die();
} elseif ('vieworiginal' == $action) {
    if ($CURUSER['class'] < UC_STAFF) {
        stderr('Error', 'Permission denied.');
    }
    $commentid = (int) $_GET['cid'];
    if (!is_valid_id($commentid)) {
        stderr('Error', 'Invalid ID.');
    }
    $res = sql_query('SELECT c.*, u.username FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id=' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) {
        stderr('Error', 'Invalid ID');
    }
    $HTMLOUT .= "<h1>Original contents of comment #{$commentid}</h1>
    <table>
    <tr><td class='comment'>\n";
    $HTMLOUT .= ' ' . htmlsafechars($arr['ori_text']);
    $HTMLOUT .= "</td></tr></table>\n";
    $returnto = htmlsafechars($_SERVER['HTTP_REFERER']);
    if ($returnto) {
        $HTMLOUT .= "<font size='small'>(<a href='{$returnto}'>back</a>)</font>\n";
    }
    echo stdhead('User Comments') . wrapper($HTMLOUT) . stdfoot();
} else {
    stderr('Error', 'Unknown action');
}
die();
