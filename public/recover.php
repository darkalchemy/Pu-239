<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
dbconn();
global $CURUSER, $site_config, $sluent;

if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('recover'), load_language('confirm'));

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$stdfoot = [
    'js' => [
        get_file_name('captcha1_js'),
    ],
];
$HTMLOUT = '';
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
    $user = $fluent->from('users')
        ->where('email = ?', $email)
        ->fetch();

    if (!$user || empty($user)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_notfound']}");
    }
    $secret = make_password(30);
    $token = make_passhash($secret);
    $alt_id = make_password(16);
    $values = [
        'email' => $email,
        'token' => $token,
        'id'    => $alt_id,
    ];
    $fluent->insertInto('tokens')
        ->values($values)
        ->execute();

    $body = sprintf($lang['email_request'], $email, $_SERVER['REMOTE_ADDR'], $site_config['baseurl'], $alt_id, $secret, $site_config['site_name']);
    $mail = new Message;
    $mail->setFrom( "{$site_config['site_email']}", "{$site_config['chatBotName']}")
        ->addTo($user['email'])
        ->setReturnPath($site_config['site_email'])
        ->setSubject("{$site_config['site_name']} {$lang['email_subjreset']}")
        ->setHtmlBody($body);

    $mailer = new SendmailMailer;
    $mailer->commandArgs = "-f{$site_config['site_email']}";
    $mailer->send($mail);

    stderr($lang['stderr_successhead'], $lang['stderr_confmailsent']);
    unsetSessionVar('simpleCaptchaAnswer');
    unsetSessionVar('simpleCaptchaTimestamp');
} elseif ($_GET) {
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    $token = isset($_GET['token']) ? $_GET['token'] : '';
    if (empty($id)) {
        stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");
    }
    if (!preg_match("/^(?:[\d\w]){60}$/", $token)) {
        stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_key']}");
    }

    $row = $fluent->from('tokens')
        ->select('users.username')
        ->select('users.email')
        ->select('users.id AS user_id')
        ->innerJoin('users ON users.email = tokens.email')
        ->where('tokens.id = ?', $id)
        ->where('created_at > DATE_SUB(NOW(), INTERVAL 120 MINUTE)')
        ->fetch();

    if (!password_verify($token, $row['token'])) {
        stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");
        die();
    }

    $email = $row['email'];
    $newpassword = make_password(16);
    $newpasshash = make_passhash($newpassword);
    $set = [
        'passhash' => $newpasshash
    ];
    $update = $fluent->update('users')
        ->set($set)
        ->where('id = ?', $row['user_id'])
        ->execute();
    if (!$update || empty($update)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_noupdate']}");
    }
    $body = sprintf($lang['email_newpass'], $row['username'], $newpassword, $site_config['baseurl'], $site_config['site_name']);
    $mail = new Message;
    $mail->setFrom( "{$site_config['site_email']}", "{$site_config['chatBotName']}")
        ->addTo($user['email'])
        ->setReturnPath($site_config['site_email'])
        ->setSubject("{$site_config['site_name']} {$lang['email_subjdetails']}")
        ->setHtmlBody($body);

    $mailer = new SendmailMailer;
    $mailer->commandArgs = "-f{$site_config['site_email']}";
    $mailer->send($mail);

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
                    <td class='rowhead'>{$lang['recover_regdemail']}</td>
                    <td><input type='text' class='w-100' name='email' /></td>
                </tr>
                <tr class='no_hover'>
                    <td colspan='2' id='captcha_show'></td>
                </tr>" : '') . "
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
