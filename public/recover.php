<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'password_functions.php';
dbconn();
global $CURUSER, $site_config, $fluent, $session;

if (!$CURUSER) {
    get_template();
}
$lang = array_merge(load_language('global'), load_language('recover'), load_language('confirm'));

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

$stdfoot = [
    'js' => [
    ],
];
$HTMLOUT = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!mkglobal('email')) {
        stderr('Oops', 'Missing form data - You must fill all fields');
    }
    if (!empty($_ENV['RECAPTCHA_SITE_KEY'])) {
        $response = !empty($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        if ($response === '') {
            stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_error2']}");
            die();
        }
        $ip = getip();
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $params = [
            'secret' => $_ENV['RECAPTCHA_SECRET_KEY'],
            'response' => $response,
            'remoteip' => $ip,
        ];
        $query = http_build_query($params);
        $contextData = [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                "Connection: close\r\n" .
                'Content-Length: ' . strlen($query) . "\r\n",
            'content' => $query,
        ];
        $context = stream_context_create(['http' => $contextData]);
        $result = file_get_contents(
            $url,
            false,
            $context
        );
        $responseKeys = json_decode($result, true);
        if (intval($responseKeys['success']) !== 1) {
            stderr('Error', 'reCAPTCHA Failed');
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

    $body = sprintf($lang['email_request'], $email, getip(), $site_config['baseurl'], $alt_id, $secret, $site_config['site_name']);
    $mail = new Message();
    $mail->setFrom("{$site_config['site_email']}", "{$site_config['chatBotName']}")
        ->addTo($user['email'])
        ->setReturnPath($site_config['site_email'])
        ->setSubject("{$site_config['site_name']} {$lang['email_subjreset']}")
        ->setHtmlBody($body);

    $mailer = new SendmailMailer();
    $mailer->commandArgs = "-f{$site_config['site_email']}";
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
        'passhash' => $newpasshash,
    ];
    $update = $fluent->update('users')
        ->set($set)
        ->where('id = ?', $row['user_id'])
        ->execute();
    if (!$update || empty($update)) {
        stderr("{$lang['stderr_errorhead']}", "{$lang['stderr_noupdate']}");
    }
    $body = sprintf($lang['email_newpass'], $row['username'], $newpassword, $site_config['baseurl'], $site_config['site_name']);
    $mail = new Message();
    $mail->setFrom("{$site_config['site_email']}", "{$site_config['chatBotName']}")
        ->addTo($email)
        ->setReturnPath($site_config['site_email'])
        ->setSubject("{$site_config['site_name']} {$lang['email_subjdetails']}")
        ->setHtmlBody($body);

    $mailer = new SendmailMailer();
    $mailer->commandArgs = "-f{$site_config['site_email']}";
    $mailer->send($mail);

    stderr($lang['stderr_successhead'], $lang['stderr_mailed']);
} else {
    $HTMLOUT .= "
    <div class='half-container has-text-centered portlet'>
        <form method='post' action='{$_SERVER['PHP_SELF']}'>
            <table class='table table-bordered top20 bottom20'>
                <tr class='no_hover'>
                    <td colspan='2'>
                        <h2 class='has-text-centered'>{$lang['recover_unamepass']}</h2>
                        <p>{$lang['recover_form']}</p>
                    </td>
                </tr>
                <tr class='no_hover'>
                    <td class='rowhead'>{$lang['recover_regdemail']}</td>
                    <td><input type='text' class='w-100' name='email' /></td>
                </tr>";
    if (!empty($_ENV['RECAPTCHA_SITE_KEY'])) {
        $HTMLOUT .= "
                    <tr>
                        <td colspan='2'>
                            <div class='g-recaptcha level-center' data-theme='dark' data-sitekey='{$_ENV['RECAPTCHA_SITE_KEY']}'></div>
                        </td>
                    </tr>";
    }
    $HTMLOUT .= "
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
    echo stdhead($lang['head_recover']) . $HTMLOUT . stdfoot($stdfoot);
}
