<?php

declare(strict_types = 1);

use Pu239\User;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$lang = array_merge(load_language('global'), load_language('login'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $container, $site_config;
    $user = $container->get(User::class);

    if ($user->login(htmlsafechars($_POST['email']), htmlsafechars($_POST['password']), (int) isset($_POST['remember']) ? 1 : 0, $lang)) {
        if (!empty($returnto)) {
            header("Location: {$site_config['paths']['baseurl']}" . urldecode($returnto));
        } else {
            header("Location: {$site_config['paths']['baseurl']}/index.php");
        }
        die();
    } else {
        unset($_POST);
    }
}
global $site_config;

get_template();
$stdfoot = [];

if (!empty($_GET['returnto'])) {
    $returnto = urlencode($_GET['returnto']);
}

$got_ssl = isset($_SERVER['HTTPS']) && (bool) $_SERVER['HTTPS'] == true ? true : false;

$HTMLOUT = "
            <form id='site_login' class='form-inline table-wrapper' method='post' action='{$site_config['paths']['baseurl']}/login.php' accept-charset='utf-8'>
                <div class='level-center'>";

$body = "
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_email']}</td>
                        <td>
                            <input type='email' class='w-100' name='email' autocomplete='on' required>" . ($got_ssl ? "
                            <input type='hidden' name='use_ssl' value='" . ($got_ssl ? 1 : 0) . "' id='ssl'>" : '') . "
                            <input type='hidden' id='token' name='token' value=''>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_password']}</td>
                        <td>
                            <input type='password' class='w-100' name='password' autocomplete='on' required>";
if (isset($returnto)) {
    $body .= "
                            <input type='hidden' name='returnto' value='" . htmlsafechars($returnto) . "'>";
}
$body .= "
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2' class='has-text-centered'>
                            <span class='has-text-centered margin5'>
                                <input id='login' type='submit' value='Login' class='button is-small'>
                            </span>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2' class='has-text-centered'>
                            <span class='has-text-centered margin5'>
                                <label for='remember' class='level-item tooltipper' title='{$lang['login_remember_title']}'>{$lang['login_remember']}
                                    <input type='checkbox' name='remember' value='1' id='remember' class='left10'>
                                </label>
                            </span>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <span class='level-center is-wrapped margin5'>
                                <span class='tab'>{$lang['login_signup']}</span>" . ($site_config['mail']['smtp_enable'] ? "
                                <span class='tab'>{$lang['login_forgot_1']}</span>" : '') . '
                            </span>
                        </td>
                    </tr>';
$HTMLOUT .= main_table($body, '', '', 'w-50', '') . '
            </div>
        </form>';

echo stdhead("{$lang['login_login_btn']}") . wrapper($HTMLOUT) . stdfoot($stdfoot);
