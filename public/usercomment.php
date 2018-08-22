<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'comment_functions.php';
check_user_status();
global $CURUSER, $site_config, $userid, $fluent, $user_stuffs, $session;

$lang = load_language('global');
$HTMLOUT = $user = '';
$action = isset($_GET['action']) ? htmlsafechars(trim($_GET['action'])) : '';

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userid = (int) $_POST['userid'];
        if (!is_valid_id($userid)) {
            stderr('Error', 'Invalid ID.');
        }
        $arr = $user_stuffs->getUserFromId($userid);
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
    } else {
        $userid = (int) $_GET['userid'];
        if (!is_valid_id($userid)) {
            stderr('Error', 'Invalid ID.');
        }
        $arr = $user_stuffs->getUserFromId($userid);
        if (!$arr) {
            stderr('Error', 'No user with that ID.');
        }
    }
    $HTMLOUT .= "
    <h1 class='has-text-centered'>Add a comment for " . format_username($userid) . "</h1>
    <form method='post' action='usercomment.php?action=add'>
        <input type='hidden' name='userid' value='$userid' />
        <div>" . BBcode() . "</div>
        <div class='has-text-centered margin20'>
        <input type='submit' class='button is-small' value='Do it!' />
        </div>
    </form>";

    $allrows = $fluent->from('usercomments')
        ->where('user = ?', $userid)
        ->orderBy('id DESC')
        ->limit(5)
        ->fetchAll();

    if ($allrows) {
        $HTMLOUT .= '
            <h2>Most recent comments, in reverse order</h2>' . commenttable($allrows, 'userdetails');
    }
    echo stdhead('Add a comment for ' . htmlsafechars($arr['username'])) . wrapper($HTMLOUT) . stdfoot();
    die();
} elseif ($action === 'edit') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userid = (int) $_POST['userid'];
        $commentid = (int) $_POST['cid'];
    } else {
        $userid = (int) $_GET['userid'];
        $commentid = (int) $_GET['cid'];
    }
    if (!is_valid_id($commentid)) {
        stderr('Error', 'Invalid ID.');
    }
    $res = sql_query('SELECT c.*, u.username, u.id FROM usercomments AS c LEFT JOIN users AS u ON c.userid = u.id WHERE c.id = ' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res);
    if (!$arr) {
        stderr('Error', 'Invalid ID.');
    }
    if ($arr['user'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
        stderr('Error', 'Permission denied.');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = htmlsafechars($_POST['body']);
        $returnto = htmlsafechars($_POST['returnto']);
        if ($body == '') {
            stderr('Error', 'Comment body cannot be empty!');
        }
        sql_query('UPDATE usercomments SET text = ' . sqlesc($body) . ', editedat = ' . sqlesc(TIME_NOW) . ', editedby = ' . sqlesc($CURUSER['id']) . ' WHERE id = ' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
        if ($returnto) {
            header("Location: $returnto");
        } else {
            header("Location: {$site_config['baseurl']}/userdetails.php?id={$userid}#comments");
        }
        die();
    }
    $referer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $HTMLOUT .= '<h1 class="has-text-centered">Edit comment for ' . format_username($arr['userid']) . "</h1>
    <form method='post' action='usercomment.php?action=edit&amp;cid={$commentid}'>
    <input type='hidden' name='returnto' value='{$referer}' />
    <input type=\"hidden\" name=\"cid\" value='" . (int) $commentid . "' />
    <textarea name='body' rows='10' class='w-100'>" . htmlsafechars($arr['text']) . "</textarea>
    <div class='has-text-centered margin20'>
        <input type='submit' class='button is-small' value='Do it!' />
    </div></form>";
    echo stdhead('Edit comment for ' . htmlsafechars($arr['username'])) . wrapper($HTMLOUT) . stdfoot();
    die();
} elseif ($action === 'delete') {
    $commentid = (int) $_GET['cid'];
    if (!is_valid_id($commentid)) {
        stderr('Error', 'Invalid ID.');
    }
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : false;
    if (!$sure) {
        $referer = $_SERVER['HTTP_REFERER'];
        stderr('Delete comment', "You are about to delete a comment. Click\n" . "<a href='usercomment.php?action=delete&amp;cid=$commentid&amp;sure=1" . ($referer ? '&amp;returnto=' . urlencode($referer) : '') . "'><span class='has-text-lime'>here</span></a> if you are sure.");
    }
    $arr = $fluent->from('usercomments')
        ->where('id = ?', $commentid)
        ->fetch();

    if ($arr) {
        $userid = (int) $arr['userid'];
    }
    if ($arr['id'] != $CURUSER['id'] && $CURUSER['class'] < UC_STAFF) {
        stderr('Error', 'Permission denied.');
    }
    sql_query('DELETE FROM usercomments WHERE id = ' . sqlesc($commentid)) or sqlerr(__FILE__, __LINE__);
    if ($userid && mysqli_affected_rows($GLOBALS['___mysqli_ston']) > 0) {
        sql_query('UPDATE users SET comments = comments - 1 WHERE id = ' . sqlesc($userid));
    }
    $session->set('is-success', 'User Comment has been deleted.');
    if ($_GET['returnto']) {
        header('Location: ' . htmlsafechars($_GET['returnto']));
    } else {
        header("Location: {$site_config['baseurl']}/userdetails.php?id={$userid}#comments");
    }
    die();
} elseif ($action === 'vieworiginal') {
    if ($CURUSER['class'] < UC_STAFF) {
        stderr('Error', 'Permission denied.');
    }
    $commentid = (int) $_GET['cid'];
    if (!is_valid_id($commentid)) {
        stderr('Error', 'Invalid ID.');
    }
    $arr = $fluent->from('usercomments')
        ->where('id = ?', $commentid)
        ->fetch();

    if (!$arr) {
        stderr('Error', 'Invalid ID');
    }
    $HTMLOUT = "
        <h1 class='has-text-centered'>{$lang['comment_original_content']}#$commentid</h1>" . main_div("<div class='margin10 bg-02 round10 column'>" . format_comment(htmlsafechars($arr['ori_text'])) . '</div>');

    $returnto = (isset($_SERVER['HTTP_REFERER']) ? htmlsafechars($_SERVER['HTTP_REFERER']) : '');
    if ($returnto) {
        $HTMLOUT .= "
            <div class='has-text-centered margin20'>
                <a href='$returnto#comments' class='button is-small has-text-black'>back</a>
            </div>  ";
    }
    echo stdhead("{$lang['comment_original']}") . wrapper($HTMLOUT) . stdfoot();
    die();
} else {
    stderr('Error', 'Unknown action');
}
die();
