<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$user = check_user_status();
global $site_config;
$HTMLOUT = '';
if (strtoupper(substr(PHP_OS, 0, 3) === 'WIN')) {
    $windows = 1;
    $unix = 0;
} else {
    $windows = 0;
    $unix = 1;
}
$register_globals = (bool) ini_get('register_gobals');
$system = ini_get('system');
$unix = (bool) $unix;
$win = (bool) $windows;
if ($register_globals) {
    $ip = getenv($_SERVER['REMOTE_ADDR']);
    $self = $_SERVER['PHP_SELF'];
} else {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $host = isset($_POST['host']) ? $_POST['host'] : '';
    $ip = getip($user['id']);
    $self = $_SERVER['SCRIPT_NAME'];
}
if ($action === 'do') {
    $host = preg_replace('/[^A-Za-z0-9.]/', '', $host);
    $HTMLOUT .= '<div class="error">';
    $HTMLOUT .= '' . _('Trace Output:') . '<br>';
    $HTMLOUT .= '<pre>';
    if ($unix) {
        system('' . 'traceroute ' . $host);
        system('killall -q traceroute');
    } else {
        system('' . 'tracert ' . $host);
    }
    $HTMLOUT .= '</pre>';
    $HTMLOUT .= '' . _('done...') . '</div>';
} else {
    $HTMLOUT .= '
    <p><span class="size_3">' . _fe('Your IP is: {0}', $ip) . '</span></p>
    <form method="post" action="' . $_SERVER['PHP_SELF'] . '" accept-charset="utf-8">' . _('Enter IP or Host ') . '<input type="text" id=specialboxn name="host" value="' . $ip . '">
    <input type="hidden" name="action" value="do"><input type="submit" value="' . _('Traceroute!') . '" class="button is-small">
   </form>';
    $HTMLOUT .= '<br><b>' . $system . '</b>';
    $HTMLOUT .= '</body></html>';
}
$title = _('Traceroute');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
