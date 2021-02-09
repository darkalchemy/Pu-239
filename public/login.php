<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Ban;
use Pu239\IP;
use Pu239\Session;
use Pu239\User;
use Rakit\Validation\Validator;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_returnto.php';
global $container, $site_config;

$auth = $container->get(Auth::class);
if ($auth->isLoggedIn()) {
    header("Location: {$site_config['paths']['baseurl']}");
    die();
}
get_template();
$bans_class = $container->get(Ban::class);
if ($bans_class->get_count($ip = getip(0)) > 0) {
    stderr(_('Error'), _fe('This IP ({0}) address has been banned.', $ip));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        write_log(_fe('{0} has tried to login using invalid data. ', getip(0)) . json_encode($post, JSON_PRETTY_PRINT));
        header("Location: {$_SERVER['PHP_SELF']}");
        die();
    }
    $user_class = $container->get(User::class);
    if ($user_class->login($post['email'], $post['password'], (int) isset($post['remember']) ? 1 : 0)) {
        $userid = $auth->getUserId();
        $user = $user_class->getUserFromId($userid);
        if ($site_config['site']['ip_logging'] || !($user['perms'] & PERMS_NO_IP)) {
            insert_update_ip('login', $userid);
        }
        if ($site_config['site']['limit_ips']) {
            $ips_class = $container->get(IP::class);
            $count = $ips_class->get_ip_count($userid, 3, 'login');
            if ($count > $site_config['site']['limit_ips_count']) {
                $user_class->logout($userid, false);
                $session->set('is-danger', _('You have exceeded the maximum number of IPs allowed'));
                stderr(_('Error'), _fe('You are allowed {0} in the previous 3 days. You have used {1} different IPs', $site_config['site']['limit_ips_count'], $count));
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
                <div class='columns level'>
                    <div class='column is-one-quarter'>" . _('Email Address') . "</div>
                    <div class='column'>
                        <input type='email' class='w-100' name='email' autocomplete='on' placeholder='" . _('Email Address') . "' required>
                    </div>
                </div>
                <div class='columns level'>
                    <div class='column is-one-quarter'>" . _('Password') . "</div>
                    <div class='column'>
                        <input type='password' class='w-100' name='password' autocomplete='on' placeholder='" . _('Password') . "' required>
                    </div>
                </div>$return_to
                <div class='level-center-center bottom10'>
                    <input type='checkbox' name='remember' value='1' id='remember' class='right10' checked>
                    <label for='remember' class='level-item tooltipper' title='" . _('Keep me logged in.') . "'>" . _('Remember Me?') . "</label>
                </div>
                <div class='has-text-centered'>
                    <input id='login' type='submit' value='" . _('Login') . "' class='button is-small'>
                </div>
                <div class='level-center top20'>
                    <a href='{$site_config['paths']['baseurl']}/signup.php'>" . _('Signup') . '</a>' . ($site_config['mail']['smtp_enable'] ? "
                    <a href='{$site_config['paths']['baseurl']}/recover.php'>" . _('Forgot Password') . '</a>' : '') . '
                </div>';

$HTMLOUT .= main_div($body, '', 'padding20') . '
            </form>';

$title = _('Login');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'w-50 min-350 has-text-centered', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
