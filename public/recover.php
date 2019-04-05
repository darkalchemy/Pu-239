<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_password.php';
require_once INCL_DIR . 'function_recaptcha.php';

dbconn();
global $CURUSER, $site_config, $fluent, $session;

if (!$CURUSER) {
    get_template();
}
$stdfoot = '';
if (!empty($site_config['recaptcha']['secret'])) {
    $stdfoot = [
        'js' => [
            get_file_name('recaptcha_js'),
        ],
    ];
}
$lang = array_merge(load_language('global'), load_language('recover'), load_language('confirm'));

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$HTMLOUT = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mkglobal('email')) {
        stderr('Oops', 'Missing form data - You must fill all fields');
    }
    if (!empty($site_config['recaptcha']['site'])) {
        $response = !empty($_POST['token']) ? $_POST['token'] : '';
        $result = verify_recaptcha($response);
        if ($result !== 'valid') {
            $session->set('is-warning', "[h2]reCAPTCHA failed. {$result}[/h2]");
            header("Location: {$site_config['paths']['baseurl']}/recover.php");
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
        'id' => $alt_id,
    ];
    $fluent->insertInto('tokens')
        ->values($values)
        ->execute();

    $body = sprintf($lang['email_request'], $email, getip(), $site_config['paths']['baseurl'], $alt_id, $secret, $site_config['site']['name']);
    $mail = new Message();
    $mail->setFrom("{$site_config['site']['email']}", "{$site_config['chatBotName']}")
        ->addTo($user['email'])
        ->setReturnPath($site_config['site']['email'])
        ->setSubject("{$site_config['site']['name']} {$lang['email_subjreset']}")
        ->setHtmlBody($body);

    $mailer = new SendmailMailer();
    $mailer->commandArgs = "-f{$site_config['site']['email']}";
    $mailer->send($mail);

    stderr($lang['stderr_successhead'], $lang['stderr_confmailsent']);
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
        ->where('tokens.id=?', $id)
        ->where('created_at>DATE_SUB(NOW(), INTERVAL 120 MINUTE)')
        ->fetch();

    if (!password_verify($token, $row['token'])) {
        stderr("{$lang['confirm_user_error']}", "{$lang['confirm_invalid_id']}");
        die();
    }

    $email = $row['email'];
    $newpassword = make_password(16);
    $newpasshash = make_passhash($newpassword);
    $set = [
        'passhash' => $newpasshash,
    ];
    $update = $fluent->update('users')
        ->set($set)
        ->where('id=?', $row['user_id'])
        ->execute();
    if (!$update || empty($update)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_noupdate']}");
    }
    $body = sprintf($lang['email_newpass'], $row['username'], $newpassword, $site_config['paths']['baseurl'], $site_config['site']['name']);
    $mail = new Message();
    $mail->setFrom("{$site_config['site']['email']}", "{$site_config['chatBotName']}")
        ->addTo($email)
        ->setReturnPath($site_config['site']['email'])
        ->setSubject("{$site_config['site']['name']} {$lang['email_subjdetails']}")
        ->setHtmlBody($body);

    $mailer = new SendmailMailer();
    $mailer->commandArgs = "-f{$site_config['site']['email']}";
    $mailer->send($mail);

    stderr($lang['stderr_successhead'], $lang['stderr_mailed']);
} else {
    $HTMLOUT .= "
        <form method='post' action='{$_SERVER['PHP_SELF']}' accept-charset='utf-8'>
            <div class='level-center'>";
    $HTMLOUT .= main_table("
                <tr class='no_hover'>
                    <td class='has-text-centered' colspan='2'>
                        <h2>{$lang['recover_unamepass']}</h2>
                        <p>{$lang['recover_form']}</p>
                    </td>
                </tr>
                <tr class='no_hover'>
                    <td class='rowhead'>{$lang['recover_regdemail']}</td>
                    <td>
                        <input type='text' class='w-100' name='email'>
                        <input type='hidden' id='token' name='token' value=''>
                    </td>
                </tr>
                <tr class='no_hover'>
                    <td colspan='2'>
                        <div class='has-text-centered'>
                            <input id='recover_captcha_check' type='submit' value='" . (!empty($site_config['recaptcha']['site']) ? 'Verifying reCAPTCHA' : 'Recover') . "' class='button is-small'" . (!empty($site_config['recaptcha']['site']) ? ' disabled' : '') . '/>
                        </div>
                    </td>
                </tr>', '', '', 'w-50', '') . '
        </form>';
    echo stdhead($lang['head_recover']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
}
