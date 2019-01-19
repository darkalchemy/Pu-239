<?php

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_password.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $lang, $mysqli;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $username = !empty($_GET['username']) ? $_GET['username'] : '';
    $userid = !empty($_GET['userid']) ? $_GET['userid'] : '';
}
$lang = array_merge($lang, load_language('ad_reset'));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(htmlsafechars($_POST['username']));
    $uid = (int) $_POST['uid'];
    $newpassword = make_password();
    $passhash = make_passhash($newpassword);
    $postkey = PostKey([
        $uid,
        $CURUSER['id'],
    ]);
    $res = sql_query('UPDATE users SET passhash = ' . sqlesc($passhash) . ' WHERE username = ' . sqlesc($username) . ' AND id = ' . sqlesc($uid) . ' AND class < ' . $CURUSER['class']) or sqlerr(__FILE__, __LINE__);
    if (mysqli_affected_rows($mysqli) != 1) {
        stderr($lang['reset_stderr'], $lang['reset_stderr1']);
    }
    if (CheckPostKey([
            $uid,
            $CURUSER['id'],
        ], $postkey) == false) {
        stderr($lang['reset_stderr2'], $lang['reset_stderr3']);
    }
    write_log($lang['reset_pw_log1'] . htmlsafechars($username) . $lang['reset_pw_log2'] . htmlsafechars($CURUSER['username']));
    stderr($lang['reset_pw_success'], '' . $lang['reset_pw_success1'] . ' <b>' . htmlsafechars($username) . '</b>' . $lang['reset_pw_success2'] . '<b>' . htmlsafechars($newpassword) . '</b>.');
}
$body = "
    <tr>
        <td>{$lang['reset_id']}</td>
        <td><input type='text' name='uid' size='10' value='$userid'></td>
    </tr>
    <tr>
        <td>{$lang['reset_username']}</td>
        <td><input size='40' name='username' value='$username'></td>
    </tr>
    <tr>
        <td colspan='2' class='has-text-centered'>
            <input type='submit' class='button is-small' value='reset'>
        </td>
    </tr>";
$HTMLOUT .= "
<h1 class='has-text-centered'>{$lang['reset_title']}</h1>
<form method='post' action='staffpanel.php?tool=reset&amp;action=reset'>" . main_table($body) . '
</form>';
echo stdhead($lang['reset_stdhead']) . wrapper($HTMLOUT) . stdfoot();
