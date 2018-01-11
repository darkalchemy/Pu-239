<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER, $site_config;

if (empty($_GET['hash_please'])) {
    die('No Hash your up to no good MOFO');
}
if (!password_verify($_GET['hash_please'], getSessionVar('salt'))) {
    die('Unsecure Logout - Hash mis-match please contact site admin');
}
destroySession();
header("Location: {$site_config['baseurl']}/login.php");
