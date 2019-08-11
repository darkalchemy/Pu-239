<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\IP;
use Pu239\Session;
use Pu239\User;
use Rakit\Validation\Validator;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_returnto.php';
global $container, $site_config;

$lang = array_merge(load_language('global'), load_language('login'));
get_template();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = $container->get(Auth::class);
    if ($auth->isLoggedIn()) {
        $auth->logOutEverywhere();
        $auth->destroySession();
        stderr('Error', 'You were already logged in, you have now been logged out, everywhere.');
    }
    $session = $container->get(Session::class);
    $validator = $container->get(Validator::class);
    $post = $_POST;
    unset($_POST, $_GET, $_FILES);
    $validation = $validator->validate($post, [
        'email' => 'required|email',
        'password' => 'required',
        'remember' => 'in:1',
    ]);
    if ($validation->fails()) {
        write_log(getip() . ' has tried to login using invalid data. ' . json_encode($post, JSON_PRETTY_PRINT));
        header("Location: {$_SERVER['PHP_SELF']}");
        die();
    }
    $user = $container->get(User::class);
    if ($user->login($post['email'], $post['password'], (int) isset($post['remember']) ? 1 : 0, $lang)) {
        $userid = $auth->getUserId();
        insert_update_ip('login', $userid);
        if ($site_config['site']['limit_ips']) {
            $ips_class = $container->get(IP::class);
            $count = $ips_class->get_ip_count($userid, 3, 'login');
            if ($count > $site_config['site']['limit_ips_count']) {
                $user->logout($userid, false);
                $session->set('is-danger', 'You have exceeded the maximum number of IPs allowed');
                stderr('Error', "You are allowed {$site_config['site']['limit_ips_count']} in the previous 3 days. You have used $count different IPs");
            }
        }
        if (!empty($post['returnto'])) {
            $returnto = get_return_to($post['returnto']);
            if (!empty($returnto)) {
                header("Location: {$returnto}");
                die();
            }
        }
        header("Location: {$site_config['paths']['baseurl']}");
        die();
    } else {
        unset($_POST, $_GET, $_FILES);
    }
}

$stdfoot = [];
$return_to = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['returnto']) && !is_array($_GET['returnto'])) {
    $return = explode('?', urldecode($_GET['returnto']));
    if (file_exists(ROOT_DIR . trim('/', $return[0]))) {
        $returnto = urlencode(urldecode($_GET['returnto']));
        $return_to = "
                        <input type='hidden' name='returnto' value='$returnto'>";
    }
}

$HTMLOUT = "
            <form id='site_login' class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/login.php' enctype='multipart/form-data' accept-charset='utf-8'>";
$body = "
                <div class='columns'>
                    <div class='column is-one-quarter'>{$lang['login_email']}</div>
                    <div class='column'>
                        <input type='email' class='w-100' name='email' autocomplete='on' placeholder='{$lang['login_email']}' required>
                    </div>
                </div>
                <div class='columns'>
                    <div class='column is-one-quarter'>{$lang['login_password']}</div>
                    <div class='column'>
                        <input type='password' class='w-100' name='password' autocomplete='on' placeholder='{$lang['login_password']}' required>
                    </div>
                </div>$return_to
                <div class='level-center-center bottom10'>
                    <input type='checkbox' name='remember' value='1' id='remember' class='right10' checked>
                    <label for='remember' class='level-item tooltipper' title='{$lang['login_remember_title']}'>{$lang['login_remember']}</label>
                </div>
                <div class='has-text-centered'>
                    <input id='login' type='submit' value='Login' class='button is-small'>
                </div>
                <div class='level-center top20'>
                    <span class='tab'>{$lang['login_signup']}</span>" . ($site_config['mail']['smtp_enable'] ? "
                    <span class='tab'>{$lang['login_forgot_1']}</span>" : '') . '
                </div>';

$HTMLOUT .= main_div($body, '', 'padding20') . '
            </div>
        </form>';

echo stdhead($lang['login_login_btn'], [], 'w-50 min-350 has-text-centered') . wrapper($HTMLOUT) . stdfoot($stdfoot);
