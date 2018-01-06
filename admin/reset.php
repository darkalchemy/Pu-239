<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $lang;

$lang = array_merge($lang, load_language('ad_reset'));
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim(htmlsafechars($_POST['username']));
    $uid = (int)$_POST['uid'];
    $newpassword = make_password();
    $passhash = make_passhash($newpassword);
    $postkey = PostKey([
        $uid,
        $CURUSER['id'],
    ]);
    $res = sql_query('UPDATE users SET passhash = ' . sqlesc($passhash) . ' WHERE username = ' . sqlesc($username) . ' AND id = ' . sqlesc($uid) . ' AND class < ' . $CURUSER['class']) or sqlerr(__FILE__, __LINE__);
    if (mysqli_affected_rows($GLOBALS['___mysqli_ston']) != 1) {
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
$HTMLOUT = '';
$HTMLOUT .= "<h1>{$lang['reset_title']}</h1>
<form method='post' action='staffpanel.php?tool=reset&amp;action=reset'>
<table >
<tr>
<td class='rowhead'>{$lang['reset_id']}</td><td>
<input type='text' name='uid' size='10' /></td></tr>
<tr>
<td class='rowhead'>{$lang['reset_username']}</td><td>
<input size='40' name='username' /></td></tr>
<tr>
<td colspan='2'>
<input type='submit' class='button is-small' value='reset' />
</td>
</tr>
</table></form>";
echo stdhead($lang['reset_stdhead']) . $HTMLOUT . stdfoot();
