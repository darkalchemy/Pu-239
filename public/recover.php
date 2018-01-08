<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
dbconn();
global $CURUSER, $site_config;
if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('recover'));
$stdfoot = [
    'js' => [
        get_file_name('captcha1_js'),
    ],
];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!mkglobal('email' . ($site_config['captcha_on'] ? ':captchaSelection' : '') . '')) {
        stderr('Oops', 'Missing form data - You must fill all fields');
    }
    if ($site_config['captcha_on']) {
        if (empty($captchaSelection) || getSessionVar('simpleCaptchaAnswer') != $captchaSelection) {
            header('Location: recover.php');
            die();
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
    $body = sprintf($lang['email_request'], $email, $_SERVER['REMOTE_ADDR'], $site_config['baseurl'], $arr['id'], $hash) . $site_config['site_name'];
    @mail($arr['email'], "{$site_config['site_name']} {$lang['email_subjreset']}", $body, "From: {$site_config['site_email']}") or stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_nomail']}");
    stderr($lang['stderr_successhead'], $lang['stderr_confmailsent']);
    unsetSessionVar('simpleCaptchaAnswer');
    unsetSessionVar('simpleCaptchaTimestamp');
} elseif ($_GET) {
    $id = (int)$_GET['id'];
    if (!$id) {
        die();
    }
    $res = sql_query('SELECT username, email, passhash FROM users WHERE id = ' . sqlesc($id));
    $arr = mysqli_fetch_assoc($res);
    $email = $arr['email'];
    $newpassword = make_password();
    $newpasshash = make_passhash($newpassword);
    sql_query('UPDATE users SET passhash = ' . sqlesc($newpasshash) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
    if (!mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_noupdate']}");
    }
    $body = sprintf($lang['email_newpass'], $arr['username'], $newpassword, $site_config['baseurl']) . $site_config['site_name'];
    @mail($email, "{$site_config['site_name']} {$lang['email_subject']}", $body, "From: {$site_config['site_email']}") or stderr($lang['stderr_errorhead'], $lang['stderr_nomail']);
    stderr($lang['stderr_successhead'], $lang['stderr_mailed']);
    unsetSessionVar('simpleCaptchaAnswer');
    unsetSessionVar('simpleCaptchaTimestamp');
} else {
    $HTMLOUT .= "
    <div class='half-container has-text-centered portlet'>
        <form method='post' action='{$_SERVER['PHP_SELF']}'>
            <table class='table table-bordered top20 bottom20'>" . ($site_config['captcha_on'] ? "
                <tr class='no_hover'>
                    <td colspan='2'>
                        <h2 class='has-text-centered'>{$lang['recover_unamepass']}</h2>
                        <p>{$lang['recover_form']}</p>
                    </td>
                </tr>
                <tr class='no_hover'>
                    <td colspan='2' id='captcha_show'></td>
                </tr>" : '') . "
                <tr class='no_hover'>
                    <td class='rowhead'>{$lang['recover_regdemail']}</td>
                    <td><input type='text' class='w-100' name='email' /></td>
                </tr>
                <tr class='no_hover'>
                    <td colspan='2'>
                        <div class='has-text-centered'>
                            <input type='submit' value='{$lang['recover_btn']}' class='button is-small' />
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </div>";
    echo stdhead($lang['head_recover'], true) . $HTMLOUT . stdfoot($stdfoot);
}
