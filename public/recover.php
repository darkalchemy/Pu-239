<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
dbconn();
global $CURUSER, $INSTALLER09;
if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('recover'));
$stdhead = [
    /* include js **/
    'js' => [
        '8f7d0f2dfbd5335a149bf9d7e6212b35.min'
    ],
];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!mkglobal('email' . ($INSTALLER09['captcha_on'] ? ':captchaSelection' : '') . '')) {
        stderr('Oops', 'Missing form data - You must fill all fields');
    }
    if ($INSTALLER09['captcha_on']) {
        if (empty($captchaSelection) || getSessionVar('simpleCaptchaAnswer') != $captchaSelection) {
            header('Location: recover.php');
            exit();
        }
    }
    $email = trim($_POST['email']);
    if (!validemail($email)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_invalidemail']}");
    }
    $res = sql_query('SELECT * FROM users WHERE email = ' . sqlesc($email) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
    $arr = mysqli_fetch_assoc($res) or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_notfound']}");
    if (!mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_dberror']}");
    }
    $hash = $arr['passhash'];
    $body = sprintf($lang['email_request'], $email, $_SERVER['REMOTE_ADDR'], $INSTALLER09['baseurl'], $arr['id'], $hash) . $INSTALLER09['site_name'];
    @mail($arr['email'], "{$INSTALLER09['site_name']} {$lang['email_subjreset']}", $body, "From: {$INSTALLER09['site_email']}") or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_nomail']}");
    stderr($lang['stderr_successhead'], $lang['stderr_confmailsent']);
    unsetSessionVar('simpleCaptchaAnswer');
    unsetSessionVar('simpleCaptchaTimestamp');
} elseif ($_GET) {
    $id = (int)$_GET['id'];
    if (!$id) {
        exit();
    }
    $res = sql_query('SELECT username, email, passhash FROM users WHERE id = ' . sqlesc($id));
    $arr = mysqli_fetch_assoc($res);
    $email = $arr['email'];
    $newpassword = make_password();
    $newpasshash = make_passhash($newpassword);
    sql_query('UPDATE users SET passhash = ' . sqlesc($newpasshash) . ' WHERE id = ' . sqlesc($id) ) or sqlerr(__FILE__, __LINE__);
    if (!mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_noupdate']}");
    }
    $body = sprintf($lang['email_newpass'], $arr['username'], $newpassword, $INSTALLER09['baseurl']) . $INSTALLER09['site_name'];
    @mail($email, "{$INSTALLER09['site_name']} {$lang['email_subject']}", $body, "From: {$INSTALLER09['site_email']}") or stderr($lang['stderr_errorhead'], $lang['stderr_nomail']);
    stderr($lang['stderr_successhead'], $lang['stderr_mailed']);
    unsetSessionVar('simpleCaptchaAnswer');
    unsetSessionVar('simpleCaptchaTimestamp');
} else {
    $HTMLOUT = '';
    $HTMLOUT .= "
    <div class='login-container center-block'>
        <form method='post' action='{$_SERVER['PHP_SELF']}'>
            <table border='1' cellspacing='0' cellpadding='10'>" . ($INSTALLER09['captcha_on'] ? "
                <tr>
                    <td colspan='2'>
                        <h2 class='text-center'>{$lang['recover_unamepass']}</h2>
                        <p>{$lang['recover_form']}</p>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead' colspan='2' id='captcha_show'></td>
                </tr>" : '') . "
                <tr>
                    <td class='rowhead'>{$lang['recover_regdemail']}</td>
                    <td><input type='text' size='40' name='email' /></td>
                </tr>
                <tr>
                    <td colspan='2' align='center'><input type='submit' value='{$lang['recover_btn']}' class='btn' /></td>
                </tr>
            </table>
        </form>
    </div>";
    echo stdhead($lang['head_recover'], true, $stdhead) . $HTMLOUT . stdfoot();
}
