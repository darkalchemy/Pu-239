<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn();

global $CURUSER;
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
    $fail = sql_query('SELECT SUM(attempts) FROM failedlogins WHERE ip=' . sqlesc($ip)) or sqlerr(__FILE__, __LINE__);
    list($total) = mysqli_fetch_row($fail);
    $left = $site_config['failedlogins'] - $total;
    if ($left <= 2) {
        $left = "
        <span style='color:red'>{$left}</span>";
    } else {
        $left = "
        <span style='color:green'>{$left}</span>";
    }

    return $left;
}

//== End Failed logins
$HTMLOUT = '';
if (!empty($_GET['returnto'])) {
    $returnto = htmlsafechars($_GET['returnto']);
}
if (!isset($_GET['nowarn'])) {
    $HTMLOUT .= "
        <div class='login-container center-block'>
            <h4>{$lang['login_error']}</h4>
            <h4>{$lang['login_cookies']}</h4>
            <h4>{$lang['login_cookies1']}</h4>
            <h4>
                <b>[{$site_config['failedlogins']}]</b> {$lang['login_failed']}<br>{$lang['login_failed_1']}<b> " . left() . " </b> {$lang['login_failed_2']}
            </h4>
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
        <div class='login-container center-block'>
            <form class='well form-inline' method='post' action='takelogin.php'>
                <table class='table table-bordered center-block'>
                    <tr>
                        <td>{$lang['login_username']}</td>
                        <td align='left'><input type='text' size='30' name='username' /></td>
                    </tr>
                    <tr>
                        <td>{$lang['login_password']}</td>
                        <td align='left'><input type='password' size='30' name='password' /></td>
                    </tr>
                    <tr>";
if ($got_ssl) {
    $HTMLOUT .= "
                        <td>{$lang['login_use_ssl']}</td>
                        <td>
                            <label class='label label-inverse' for='ssl'>{$lang['login_ssl1']}&#160;
                                <input type='checkbox' name='use_ssl' " . ($got_ssl ? "checked='checked'" : "disabled='disabled' title='SSL connection not available'") . " value='1' id='ssl'/>
                            </label><br>
                            <label class='label label-inverse' for='ssl2'>{$lang['login_ssl2']}&#160;
                                <input type='checkbox' name='perm_ssl' " . ($got_ssl ? '' : "disabled='disabled' title='SSL connection not available'") . " value='1' id='ssl2'/>
                            </label>
                        </td>";
}
$HTMLOUT .= "
                    </tr>" . ($site_config['captcha_on'] ? "
                    <tr>
                        <td align='center' class='rowhead' colspan='2' id='captcha_show'></td>
                    </tr>" : '') . "
                    <tr>
                        <td colspan='2'><em class='center-block'>{$lang['login_click']}<strong>{$lang['login_x']}</strong></em></td>
                    </tr>
                    <tr>
                        <td colspan='2' class='text-center'>
                            <span class='answers-container'>";
for ($i = 0; $i < count($value); ++$i) {
    $HTMLOUT .= "
                                <input name='submitme' type='submit' value='{$value[$i]}' class='btn' />";
}
$HTMLOUT .= "
                            </span>";

if (isset($returnto)) {
    $HTMLOUT .= "
                            <input type='hidden' name='returnto' value='" . htmlsafechars($returnto) . "' />";
}
$HTMLOUT .= "           </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <span class='answers-container'>
                                <em class='btn'>{$lang['login_signup']}</em>
                                <em class='btn'>{$lang['login_forgot']}</em>
                                <em class='btn'>{$lang['login_forgot_1']}</em>
                            </span>
                        </td>
                    </tr>
                </table>
            </form>
        </div>";

echo stdhead("{$lang['login_login_btn']}", true) . $HTMLOUT . stdfoot($stdfoot);
