<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
global $CURUSER, $site_config, $session;

if (!$CURUSER) {
    get_template();
}
dbconn();

$lang = array_merge(load_language('global'), load_language('ok'));
$type = isset($_GET['type']) ? $_GET['type'] : '';
$HTMLOUT = '';
if ($type === 'signup' && isset($_GET['email'])) {
    stderr("{$lang['ok_success']}", sprintf((!$site_config['email_confirm'] ? $lang['ok_email'] : $lang['ok_email_confirm']), htmlsafechars($_GET['email'], ENT_QUOTES)));
} elseif ($type === 'invite' && isset($_GET['email'])) {
    stderr("{$lang['ok_invsuccess']}", sprintf($lang['ok_email2'], htmlsafechars($_GET['email'], ENT_QUOTES)));
} elseif ($type === 'sysop') {
    check_user_status();
    if (isset($CURUSER)) {
        $session->set('is-info', "[p]{$lang['ok_sysop_activated']}[/p][p]Create your System BOT, be sure to use the same username as used during the install[/p]");
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=adduser");
        die();
    }
    $HTMLOUT = stdhead("{$lang['ok_sysop_account']}");
    $text1 = $lang['ok_sysop_activated'];
    $text2 = main_div($lang['ok_account_login']);
    $HTMLOUT .= wrapper($text1 . $text2, 'has-text-centered');
    $HTMLOUT .= stdfoot();
    echo $HTMLOUT;
    die();
} elseif ($type === 'confirmed') {
    $HTMLOUT .= stdhead("{$lang['ok_confirmed']}");
    $HTMLOUT .= "<h1>{$lang['ok_confirmed']}</h1>\n";
    $HTMLOUT .= "{$lang['ok_user_confirmed']}";
    $HTMLOUT .= stdfoot();
    echo $HTMLOUT;
    die();
} elseif ($type === 'confirm') {
    check_user_status();
    if (isset($CURUSER)) {
        $session->set('is-info', $lang['ok_signup_confirm']);
        header("Location: {$site_config['baseurl']}/index.php");
        die();
    } else {
        $HTMLOUT .= stdhead("{$lang['ok_signup_confirm']}");
        $HTMLOUT .= "<h1>{$lang['ok_success_confirmed']}</h1>\n";
        $HTMLOUT .= "{$lang['ok_account_cookies']}";
        $HTMLOUT .= stdfoot();
        echo $HTMLOUT;
        die();
    }
} else {
    stderr("{$lang['ok_user_error']}", "{$lang['ok_no_action']}");
    die();
}
