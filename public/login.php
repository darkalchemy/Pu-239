<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn();

global $CURUSER, $site_config;
if (!$CURUSER) {
    get_template();
} else {
    header("Location: {$site_config['baseurl']}/index.php");
    exit();
}
$stdfoot = [
    'js' => [
        get_file('captcha1_js')
    ],
];
$lang = array_merge(load_language('global'), load_language('login'));
$left = $total = '';

function left()
{
    global $site_config;
    $total = 0;
    $ip = getip();
    $fail = sql_query('SELECT SUM(attempts) FROM failedlogins WHERE ip = ' . sqlesc($ip)) or sqlerr(__FILE__, __LINE__);
    list($total) = mysqli_fetch_row($fail);
    $left = $site_config['failedlogins'] - $total;
    if ($left <= 2) {
        $left = "
        <span>{$left}</span>";
    } else {
        $left = "
        <span>{$left}</span>";
    }

    return $left;
}

$HTMLOUT = '';
if (!empty($_GET['returnto'])) {
    $returnto = htmlsafechars($_GET['returnto']);
}
if (!isset($_GET['nowarn'])) {
    $HTMLOUT .= "
        <div class='half-container has-text-centered portlet'>
            <div class='margin20'>
                <h3>{$lang['login_error']}</h3>
                <h3>{$lang['login_cookies']}</h3>
                <h3>{$lang['login_cookies1']}</h3>
                <h3>
                    <b>[{$site_config['failedlogins']}]</b> {$lang['login_failed']}<br>{$lang['login_failed_1']}<b> " . left() . " </b> {$lang['login_failed_2']}
                </h3>
            </div>";
}
$got_ssl = isset($_SERVER['HTTPS']) && (bool)$_SERVER['HTTPS'] == true ? true : false;
$value = [
    '...',
    '...',
    '...',
    '...',
    '...',
    '...',
];
$value[random_int(1, count($value) - 1)] = 'X';
$HTMLOUT.= "
            <form class='form-inline table-wrapper' method='post' action='takelogin.php'>
                <table class='table table-bordered'>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_username']}</td>
                        <td><input type='text' class='w-100' name='username' /></td>
                    </tr>
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_password']}</td>
                        <td><input type='password' class='w-100' name='password' /></td>
                    </tr>";
if ($got_ssl) {
    $HTMLOUT .= "
                    <tr class='no_hover'>
                        <td class='rowhead'>{$lang['login_use_ssl']}</td>
                        <td>
                            <label class='label label-inverse' for='ssl'>{$lang['login_ssl1']}
                                <input type='checkbox' name='use_ssl' " . ($got_ssl ? "checked='checked'" : "disabled='disabled' title='SSL connection not available'") . " value='1' id='ssl'/>
                            </label><br>
                            <label class='label label-inverse' for='ssl2'>{$lang['login_ssl2']}
                                <input type='checkbox' name='perm_ssl' " . ($got_ssl ? '' : "disabled='disabled' title='SSL connection not available'") . " value='1' id='ssl2'/>
                            </label>
                        </td>
                    </tr>";
}
$HTMLOUT .=
                    ($site_config['captcha_on'] ? "
                    <tr class='no_hover'>
                        <td colspan='2' id='captcha_show'></td>
                    </tr>" : '') . "
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <span class='has-text-centered'>
                                {$lang['login_click']}<span class='has-text-danger is-bold'>{$lang['login_x']}</span>
                            </span>
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2' class='has-text-centered'>
                            <span class='tabs is-marginless'>";
for ($i = 0; $i < count($value); ++$i) {
    $HTMLOUT .= "
                                <input name='submitme' type='submit' value='{$value[$i]}' class='button' />";
}
$HTMLOUT .= "
                            </span>";

if (isset($returnto)) {
    $HTMLOUT .= "
                            <input type='hidden' name='returnto' value='" . htmlsafechars($returnto) . "' />";
}
$HTMLOUT .= "           </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <span class='level is-flex is-wrapped top5 bottom5'>
                                <span class='tab'>{$lang['login_signup']}</span>
                                <span class='tab'>{$lang['login_forgot']}</span>
                                <span class='tab'>{$lang['login_forgot_1']}</span>
                            </span>
                        </td>
                    </tr>
                </table>
            </form>
        </div>";

echo stdhead("{$lang['login_login_btn']}", true) . $HTMLOUT . stdfoot($stdfoot);
