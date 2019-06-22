<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\IP;
use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$lang = array_merge(load_language('global'), load_language('login'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $container, $site_config;

    $user = $container->get(User::class);
    if ($user->login(htmlsafechars($_POST['email']), htmlsafechars($_POST['password']), (int) isset($_POST['remember']) ? 1 : 0, $lang)) {
        $auth = $container->get(Auth::class);
        $userid = $auth->getUserId();
        insert_update_ip('login', $userid);
        if ($site_config['site']['limit_ips']) {
            $ips_class = $container->get(IP::class);
            $count = $ips_class->get_ip_count($userid, 3, 'login');
            if ($count > $site_config['site']['limit_ips_count']) {
                $user->logout($userid, false);
                die('You have exceeded the maximum number of IPs allowed');
            }
        }
        if (!empty($_POST['returnto'])) {
            header("Location: {$site_config['paths']['baseurl']}" . urldecode($_POST['returnto']));
        } else {
            header("Location: {$site_config['paths']['baseurl']}");
        }
        die();
    } else {
        unset($_POST);
    }
}
global $site_config;

get_template();
$stdfoot = [];
$return_to = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['returnto'])) {
    $returnto = urlencode(urldecode($_GET['returnto']));
    $return_to = "
                        <input type='hidden' name='returnto' value='$returnto'>";
}

$got_ssl = isset($_SERVER['HTTPS']) && (bool) $_SERVER['HTTPS'] == true ? true : false;

$HTMLOUT = "
            <form id='site_login' class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/login.php' accept-charset='utf-8'>";

$body = "
                <div class='columns'>                    
                    <div class='column is-one-quarter'>{$lang['login_email']}</div>
                    <div class='column'>
                        <input type='email' class='w-100' name='email' autocomplete='on' placeholder='{$lang['login_email']}' required>" . ($got_ssl ? "
                        <input type='hidden' name='use_ssl' value='" . ($got_ssl ? 1 : 0) . "' id='ssl'>" : '') . "
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

echo stdhead("{$lang['login_login_btn']}") . wrapper($HTMLOUT) . stdfoot($stdfoot);
