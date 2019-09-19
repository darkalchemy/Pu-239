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
if (!$site_config['mail']['smtp_enable']) {
    stderr('Error', 'Mail functions have not been enabled.');
}
$stdfoot = [];
$lang = array_merge(load_language('global'), load_language('recover'), load_language('signup'));
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
        write_log(getip() . ' has tried to recover using invalid data. ' . json_encode($post, JSON_PRETTY_PRINT));
        header("Location: {$_SERVER['PHP_SELF']}");
        die();
    }
    $email = trim($post['email']);
    $user->create_reset($email, $lang);
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
        write_log(getip() . ' has tried to recover using invalid data. ' . json_encode($post, JSON_PRETTY_PRINT));
        header("Location: {$_SERVER['PHP_SELF']}");
        die();
    }
    $user->reset_password($lang, $post, false);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET)) {
    $get = $_GET;
    unset($_POST, $_GET, $_FILES);
    $validation = $validator->validate($get, [
        'selector' => 'required|alpha_dash',
        'token' => 'required|alpha_dash',
    ]);
    if ($validation->fails()) {
        write_log(getip() . ' has tried to recover using invalid data. ' . json_encode($get, JSON_PRETTY_PRINT));
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
            <h2 class='has-text-centered'>{$lang['set_new_password']}</h2>";

        $body = "
            <div class='bottom20'>
                <input type='password' id='password' name='password' class='w-100' autocomplete='on' placeholder='{$lang['signup_pass']}' required minlength='8'>
            </div>
            <div>
                <input type='password' id='confirm_password' name='confirm_password' class='w-100' autocomplete='on' placeholder='{$lang['signup_passa']}' required minlength='8'>
                <input type='hidden' name='selector' value='{$get['selector']}'>
                <input type='hidden' name='token' value='{$get['token']}'>
            </div>
            <div class='has-text-centered padding10'>
                <input id='signup' type='submit' value='Reset' class='button is-small top20'>
            </div>";
        $HTMLOUT .= main_div($body, '', 'padding20') . '
        </div>
    </form>';

        echo stdhead($lang['head_recover'], [], 'w-50 min-350 has-text-centered') . wrapper($HTMLOUT) . stdfoot($stdfoot);
        die();
    } catch (InvalidSelectorTokenPairException $e) {
        stderr($lang['stderr_errorhead'], 'Invalid token');
    } catch (TokenExpiredException $e) {
        stderr($lang['stderr_errorhead'], 'Token expired');
    } catch (ResetDisabledException $e) {
        stderr($lang['stderr_errorhead'], 'Password reset is disabled');
    } catch (TooManyRequestsException $e) {
        stderr($lang['stderr_errorhead'], 'Too many requests');
    }
} else {
    $HTMLOUT .= "
        <form method='post' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>
            <h2 class='has-text-centered'>{$lang['recover_unamepass']}</h2>";
    $HTMLOUT .= main_div("
            <div class='bottom20'>
                <input type='email' class='w-100' name='email' autocomplete='on' placeholder='{$lang['recover_regdemail']}' required>
            </div>
            <div class='has-text-centered'>
                <input type='submit' class='button is-small'>
            </div>", '', 'padding20') . '
        </form>';
    echo stdhead($lang['head_recover'], [], 'w-50 min-350 has-text-centered') . wrapper($HTMLOUT) . stdfoot($stdfoot);
}
