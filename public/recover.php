<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Delight\Auth\InvalidSelectorTokenPairException;
use Delight\Auth\ResetDisabledException;
use Delight\Auth\TokenExpiredException;
use Delight\Auth\TooManyRequestsException;
use Pu239\User;
use Rakit\Validation\Validator;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_password.php';
global $container, $site_config;

get_template();
$auth = $container->get(Auth::class);
if ($auth->isLoggedIn()) {
    header("Location: {$site_config['paths']['baseurl']}");
    die();
}
if (!$site_config['mail']['smtp_enable'] || $site_config['mail']['smtp_password'] === 'gmail password' || $site_config['mail']['smtp_username'] === 'gmail username') {
    stderr(_('Error'), _('Mail functions have not been enabled.'));
}
$stdfoot = [];
$HTMLOUT = '';
$auth = $container->get(Auth::class);
$user = $container->get(User::class);
$validator = $container->get(Validator::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['selector'])) {
    $post = $_POST;
    unset($_POST, $_GET, $_FILES);
    $validation = $validator->validate($post, [
        'email' => 'required|email',
    ]);
    if ($validation->fails()) {
        write_log(_fe('{0} has tried to reset password using invalid data. ', getip(0)) . json_encode($post, JSON_PRETTY_PRINT));
        header("Location: {$_SERVER['PHP_SELF']}");
        die();
    }
    $email = trim($post['email']);
    $user->create_reset($email);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selector'])) {
    $post = $_POST;
    unset($_POST, $_GET, $_FILES);
    $validation = $validator->validate($post, [
        'selector' => 'required|alpha_dash',
        'token' => 'required|alpha_dash',
        'password' => 'required|min:8',
        'confirm_password' => 'required|same:password',
    ]);
    if ($validation->fails()) {
        write_log(_fe('{0} has tried to reset password using invalid data. ', getip(0)) . json_encode($post, JSON_PRETTY_PRINT));
        header("Location: {$_SERVER['PHP_SELF']}");
        die();
    }
    $user->reset_password($post, false);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $get = $_GET;
    unset($_POST, $_GET, $_FILES);
    $validation = $validator->validate($get, [
        'selector' => 'required|alpha_dash',
        'token' => 'required|alpha_dash',
    ]);
    if ($validation->fails()) {
        write_log(_fe('{0} has tried to reset password using invalid data. ', getip(0)) . json_encode($get, JSON_PRETTY_PRINT));
        header("Location: {$_SERVER['PHP_SELF']}");
        die();
    }
    try {
        $auth->canResetPasswordOrThrow($get['selector'], $get['token']);
        $stdfoot = array_merge_recursive($stdfoot, [
            'js' => [
                get_file_name('check_password_js'),
            ],
        ]);
        $HTMLOUT = "
    <form method='post' action='{$site_config['paths']['baseurl']}/recover.php' enctype='multipart/form-data' accept-charset='utf-8'>
        <div class='has-text-centered'>
            <h2 class='has-text-centered'>" . _('Set New Password') . '</h2>';

        $body = "
            <div class='bottom20'>
                <input type='password' id='password' name='password' class='w-100' autocomplete='on' placeholder='" . _('Password') . "' required minlength='8'>
            </div>
            <div>
                <input type='password' id='confirm_password' name='confirm_password' class='w-100' autocomplete='on' placeholder='" . _('Password') . "' required minlength='8'>
                <input type='hidden' name='selector' value='{$get['selector']}'>
                <input type='hidden' name='token' value='{$get['token']}'>
            </div>
            <div class='has-text-centered padding10'>
                <input id='signup' type='submit' value='" . _('Reset') . "' class='button is-small top20'>
            </div>";
        $HTMLOUT .= main_div($body, '', 'padding20') . '
        </div>
    </form>';

        $title = _('Reset');
        $breadcrumbs = [
            "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
        ];
        echo stdhead($title, [], 'w-50 min-350 has-text-centered', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
    } catch (InvalidSelectorTokenPairException $e) {
        stderr(_('Error'), _('Token is Invalid'));
    } catch (TokenExpiredException $e) {
        stderr(_('Error'), _('Token is Expired'));
    } catch (ResetDisabledException $e) {
        stderr(_('Error'), _('Password Reset is Disabled'));
    } catch (TooManyRequestsException $e) {
        stderr(_('Error'), _('Too many requests from your IP'));
    }
} else {
    $HTMLOUT .= "
        <form method='post' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>
            <h2 class='has-text-centered'>" . _('Enter your email address') . '</h2>';
    $HTMLOUT .= main_div("
            <div class='bottom20'>
                <input type='email' class='w-100' name='email' autocomplete='on' placeholder='" . _('Email Address') . "' required>
            </div>
            <div class='has-text-centered'>
                <input type='submit' class='button is-small'>
            </div>", '', 'padding20') . '
        </form>';

    $title = _('Reset Password');
    $breadcrumbs = [
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'w-50 min-350 has-text-centered', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
}
