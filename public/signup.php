<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Envms\FluentPDO\Literal;
use Pu239\Database;
use Pu239\Message;
use Pu239\Session;
use Pu239\User;
use Rakit\Validation\Validator;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
global $container, $site_config;

$title = 'Join ' . $site_config['site']['name'];
$session = $container->get(Session::class);
$fluent = $container->get(Database::class);
$auth = $container->get(Auth::class);
if ($auth->isLoggedIn()) {
    $auth->logOutEverywhere();
    $auth->destroySession();
    header('Location: ' . $site_config['paths']['baseurl'] . $_SERVER['REQUEST_URI']);
    die();
}
get_template();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $container->get(User::class);
    $validator = $container->get(Validator::class);
    $ses_vars = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
    ];
    $session->set('signup_variables', json_encode($ses_vars));
    $post = $_POST;
    unset($_POST, $_GET, $_FILES);
    $validation = $validator->validate($post, [
        'username' => 'required|between:3,64',
        'email' => 'required|email',
        'password' => 'required|min:8',
        'confirm_password' => 'required|same:password',
        'promo' => 'alpha_num:between:64,64',
        'invite_id' => 'integer',
        'invite_code' => 'alpha_num:between:64,64',
    ]);
    if ($validation->fails() || !valid_username($post['username'], false, true)) {
        $session->set('is-warning', _('Invalid information provided, please try again.'));
        write_log(getip(0) . ' has used invalid data to signup. ' . json_encode($post, JSON_PRETTY_PRINT));
        header("Location: {$_SERVER['PHP_SELF']}");
        die();
    } else {
        $data = [
            'email' => $post['email'],
            'password' => $post['password'],
            'username' => $post['username'],
        ];
        $user = $container->get(User::class);
        $userid = $user->add($data);
    }

    if (!empty($userid)) {
        if ($site_config['site']['ip_logging']) {
            insert_update_ip('register', $userid);
        }
        $invite_id = !empty($post['invite_id']) ? (int) $post['invite_id'] : 0;
        $invite_code = !empty($post['invite_code']) ? $post['invite_code'] : '';
        $promo = !empty($post['promo']) ? $post['promo'] : '';
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
                $msg = "Hey there [you]! :wave:\nYour invitation to " . htmlsafechars($post['username']) . " has been accepted! :clap2:\n\ncheers\n";
                $subject = 'Someone you invited has arrived!';
                $msgs_buffer[] = [
                    'receiver' => $inviter,
                    'added' => TIME_NOW,
                    'msg' => $msg,
                    'subject' => $subject,
                ];
                $messages_class = $container->get(Message::class);
                $messages_class->insert($msgs_buffer);
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
$invite = $email = '';
$promo = false;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['promo'])) {
        $valid = validate_promo($_GET['promo'], false);
        if (!empty($valid)) {
            $title .= ' using a Site Promotion';
            $promo = "
                <input type='hidden' name='promo' value='{$valid}'>";
        } else {
            stderr(_('Error'), _('The Promotion you have attempted to use does not exist or has expired!'));
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
    stderr(_('Error'), _('Signups are presently closed'));
}
if ((!$site_config['openreg']['open'] || $site_config['openreg']['invites_only']) && empty($email) && empty($promo)) {
    stderr(_('Error'), _('Signups are Invite Only'));
}

$stdfoot = [
    'js' => [
        get_file_name('check_password_js'),
        get_file_name('check_username_js'),
    ],
];

$signup_vars = $session->get('signup_variables');
if (!empty($signup_vars)) {
    $signup_vars = json_decode($signup_vars, true);
} else {
    $signup_vars = [
        'username' => '',
        'email' => '',
    ];
}

$HTMLOUT = "
    <form method='post' action='{$site_config['paths']['baseurl']}/signup.php' enctype='multipart/form-data' accept-charset='utf-8'>";

$disabled = !empty($email) ? 'disabled' : 'required';

if (!empty($email)) {
    $email_form = "<input type='hidden' name='email' class='w-100' value='{$email}'>{$email}";
} else {
    $email_form = "<input type='email' name='email' id='email' class='w-100' onblur='check_email();' value='{$signup_vars['email']}' autocomplete='on' required>
                   <div id='emailcheck'></div>" . ($site_config['signup']['email_confirm'] ? "
                   <div class='alt_bordered top10 padding10'>" . _('Username') . '</div>' : '');
    $email_form = "<input type='email' name='email' id='email' class='w-100' onblur='check_email();' value='{$signup_vars['email']}' autocomplete='on' required>";
}
$email = !empty($email) ? $email : (!empty($signup_vars['email']) ? $signup_vars['email'] : '');
$body = "          
            <h1 class='has-text-centered'>$title</h1>
            <div class='columns level'>                    
                <div class='column is-one-quarter has-text-left'>" . _('Desired Username') . "</div>
                <div class='column'>
                    <input type='text' name='username' id='username' class='w-100' onblur='check_name();' value='{$signup_vars['username']}' autocomplete='on' required pattern='[\p{L}\p{N}_-]{3,64}'>
                </div>
            </div>
            <div id='namecheck'></div>
            <div class='columns level'>                    
                <div class='column is-one-quarter has-text-left'>" . _('Pick a Password') . "</div>
                <div class='column'>
                    <input type='password' id='password' name='password' class='w-100' autocomplete='on' required minlength='8' aria-autocomplete='list'>
                </div>
            </div>
            <div class='columns level'>                    
                <div class='column is-one-quarter has-text-left'>" . _('Enter password again') . "</div>
                <div class='column'>
                    <input type='password' id='confirm_password' name='confirm_password' class='w-100' autocomplete='on' required minlength='8' aria-autocomplete='list'>
                </div>
            </div>
            <div class='columns level'>                    
                <div class='column is-one-quarter has-text-left'>" . _('Email Address') . "</div>
                <div class='column'>
                    $email_form
                </div>
            </div>" . (empty($email) ? "
            <div id='emailcheck'></div>" : '') . "
            <div class='has-text-centered top20'>{$invite}{$promo}
                <input id='submit' type='submit' value='" . _('Signup') . "' class='button is-small' disabled>
            </div>";
$HTMLOUT .= main_div($body, '', 'padding20') . '
    </form>';

$title = _('Signup');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'w-50 min-350 has-text-centered', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
