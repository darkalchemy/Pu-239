<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Envms\FluentPDO\Literal;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
$lang = array_merge(load_language('global'), load_language('signup'), load_language('takesignup'));
global $container, $site_config;

$title = 'Join ' . $site_config['site']['name'];
get_template();
$session = $container->get(Session::class);
$fluent = $container->get(Database::class);
$auth = $container->get(Auth::class);
if ($auth->isLoggedIn()) {
    $auth->logOutEverywhere();
    $auth->destroySession();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session->set('signup_variables', serialize($_POST));
    $post = $_POST;
    unset($_POST);
    $promo = !empty($post['promo']) ? htmlsafechars($post['promo']) : null;
    $invite_id = !empty($post['invite_id']) ? (int) $post['invite_id'] : null;
    $invite_code = !empty($post['invite_code']) ? htmlsafechars($post['invite_code']) : null;
    $user = $container->get(User::class);
    $userid = $user->add($post, $lang);
    if (!empty($userid)) {
        if (!empty($invite_id) && !empty($invite_code)) {
            $email = validate_invite($invite_id, $invite_code);
            if (!empty($email)) {
                $inviter = $fluent->from('invite_codes')
                                  ->select(null)
                                  ->select('sender')
                                  ->where('id = ?', $invite_id)
                                  ->where('code = ?', $invite_code)
                                  ->where('status = "Pending"')
                                  ->fetch('sender');
                $msg = "Hey there [you]! :wave:\nIt seems that someone you invited to {$site_config['site']['name']} has arrived! :clap2:\n\ncheers\n";
                $subject = 'Someone you invited has arrived!';
                $msgs_buffer[] = [
                    'receiver' => $inviter,
                    'added' => TIME_NOW,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $message_stuffs = $container->get(Message::class);
                $message_stuffs->insert($msgs_buffer);
                $set = [
                    'join_type' => 'invite',
                    'invitedby' => $inviter,
                ];
                $user->update($set, $userid);
            }
        } elseif (!empty($promo)) {
            $valid = validate_promo($promo, true);
            if ($valid) {
                $set = [
                    'accounts_made' => new Literal('accounts_made + 1'),
                    'users' => empty($valid['users']) ? $userid : $valid['users'] . '|' . $userid,
                ];
                $fluent->update('promo')
                       ->set($set)
                       ->where('link = ?', $valid['link'])
                       ->execute();

                $set = [
                    'join_type' => 'promo',
                    'invitedby' => $valid['id'],
                    'seedbonus' => $valid['bonus_karma'],
                    'invites' => $valid['bonus_invites'],
                    'uploaded' => $valid['bonus_upload'] * 1073741824,
                ];
                $user->update($set, $userid);
            }
        }

        if (!empty($inviter)) {
            $set = [
                'receiver' => $userid,
                'status' => 'Confirmed',
            ];
            $fluent->update('invite_codes')
                   ->set($set)
                   ->where('sender = ?', $inviter)
                   ->where('id = ?', $invite_id)
                   ->execute();
        }
        $session->unset('signup_variables');
        header("Location: {$site_config['paths']['baseurl']}/login.php");
        die();
    }
}
$invite = $email = '';
$promo = false;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['promo'])) {
        $valid = validate_promo($_GET['promo'], false);
        if (!empty($valid)) {
            $title .= ' using a Site Promotion';
            $promo = "
                <input type='hidden' name='promo' value='{$valid}'>";
        }
    }
    $invite_id = !empty($_GET['id']) ? (int) $_GET['id'] : null;
    $invite_code = !empty($_GET['code']) ? htmlsafechars($_GET['code']) : null;
    if (!empty($invite_id) && !empty($invite_code)) {
        $title .= ' using an Invite';
        $email = validate_invite($invite_id, $invite_code);
        $invite = "
            <input type='hidden' name='invite_id' value='$invite_id'>
            <input type='hidden' name='invite_code' value='$invite_code'>";
    }
}
if (!$site_config['openreg']['open'] && !$site_config['openreg']['invites_only']) {
    stderr($lang['stderr_errorhead'], $lang['signup_closed']);
}
if ((!$site_config['openreg']['open'] || $site_config['openreg']['invites_only']) && empty($email) && empty($promo)) {
    stderr($lang['stderr_errorhead'], $lang['signup_invite']);
}

$stdfoot = [
    'js' => [
        get_file_name('check_password_js'),
        get_file_name('check_username_js'),
    ],
];
$signup_vars = [
    'wantusername' => '',
    'email' => '',
];

$signup_vars = $session->get('signup_variables');
if (!empty($signup_vars)) {
    $signup_vars = unserialize($signup_vars);
}

$HTMLOUT = "
    <form method='post' action='{$site_config['paths']['baseurl']}/signup.php' accept-charset='utf-8'>
        <div class='level-center'>";

$disabled = !empty($email) ? 'disabled' : 'required';
if (!empty($email)) {
    $email_form = "<input type='hidden' name='email' class='w-100' value='{$email}'>{$email}";
} else {
    $email_form = "<input type='email' name='email' class='w-100' autocomplete='on'>" . ($site_config['signup']['email_confirm'] ? "
                    <div class='alt_bordered top10 padding10'>{$lang['signup_valemail']}</div>" : '');
}
$email = !empty($email) ? $email : (!empty($signup_vars['email']) ? $signup_vars['email'] : '');
$body = "
            <tr>
                <td colspan='2'>
                    <h1 class='has-text-centered'>$title</h1>
                    <p class='has-text-centered padding10 '>{$lang['signup_cookies']}</p>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_uname']}</td>
                <td>
                    <input type='text' name='username' id='username' class='w-100' onblur='checkit();' value='{$signup_vars['username']}' autocomplete='on' required pattern='[\p{L}\p{N}_-]{3,64}'>
                    <div id='namecheck'></div>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_pass']}</td>
                <td>
                    <input type='password' id='password' name='password' class='w-100' autocomplete='on' required minlength='8'>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_passa']}</td>
                <td>
                    <input type='password' id='confirm_password' name='confirm_password' class='w-100' autocomplete='on' required minlength='8'>
                </td>
            </tr>
            <tr class='no_hover'>
                <td class='rowhead'>{$lang['signup_email']}</td>
                <td>
                    $email_form
                </td>
            </tr>
            <tr class='no_hover'>
                <td colspan='2' class='has-text-centered margin20'>{$invite}{$promo}
                    <input id='submit' type='submit' value='Signup' class='button is-small margin10' disabled>
                </td>
            </tr>";
$HTMLOUT .= main_table($body, '', '', 'w-50', '') . '
        </div>
    </form>';
echo stdhead($lang['head_signup']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
