<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
dbconn();

global $CURUSER, $site_config;

if (!$CURUSER) {
    get_template();
} else {
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
$stdfoot = '';
if (!empty($_ENV['RECAPTCHA_SECRET_KEY'])) {
    $stdfoot = [
        'js' => [
            get_file_name('recaptcha_js'),
        ],
    ];
}

$lang = array_merge(load_language('global'), load_language('login'));
$left = $total = '';

/**
 * @return mixed|string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function left()
{
    global $site_config, $failed_logins;

    $ip = getip(true);
    $count = $failed_logins->get($ip);
    $left = $site_config['failedlogins'] - $count;
    if ($left <= 2) {
        $left = "
        <span class='has-text-danger'>{$left}</span>";
    } else {
        $left = "
        <span class='has-text-success'>{$left}</span>";
    }

    return $left;
}

$HTMLOUT = '';
if (!empty($_GET['returnto'])) {
    $returnto = htmlsafechars($_GET['returnto']);
}

$got_ssl = isset($_SERVER['HTTPS']) && (bool) $_SERVER['HTTPS'] == true ? true : false;
$HTMLOUT .= "
            <form id='site_login' class='form-inline table-wrapper' method='post' action='{$site_config['baseurl']}/takelogin.php'>
                <div class='level-center'>";

$body = "
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_username']}</td>
                        <td>
                            <input type='text' class='w-100' name='username' autocomplete='on'>" . ($got_ssl ? "
                            <input type='hidden' name='use_ssl' value='" . ($got_ssl ? 1 : 0) . "' id='ssl'>" : '') . "
                            <input type='hidden' id='token' name='token' value=''>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_password']}</td>
                        <td><input type='password' class='w-100' name='password' autocomplete='on'></td>
                    </tr>";

$body .= "
                    <tr class='no_hover'>
                        <td colspan='2' class='has-text-centered'>
                            <span class='has-text-centered margin5'>
                                <input id='login_captcha_check' type='submit' value='" . (!empty($_ENV['RECAPTCHA_SITE_KEY']) ? 'Verifying reCAPTCHA' : 'Login') . "' class='button is-small'>
                            </span>";

if (isset($returnto)) {
    $body .= "
                            <input type='hidden' name='returnto' value='" . htmlsafechars($returnto) . "'>";
}
$body .= "           </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2' class='has-text-centered'>
                            <span class='has-text-centered margin5'>
                                <label for='remember' class='level-item tooltipper' title='Keep me logged in'>Remember Me?
                                    <input type='checkbox' name='remember' value='1' id='remember' class='left10'>
                                </label>
                            </span>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <span class='level is-flex is-wrapped margin5'>
                                <span class='tab'>{$lang['login_signup']}</span>
                                <span class='tab'>{$lang['login_forgot']}</span>
                                <span class='tab'>{$lang['login_forgot_1']}</span>
                            </span>
                        </td>
                    </tr>
                </table>
            </form>";

$HTMLOUT .= main_table($body, '', '', 'w-50', '');
echo stdhead("{$lang['login_login_btn']}") . wrapper($HTMLOUT) . stdfoot($stdfoot);
