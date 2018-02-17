<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER, $site_config;
$session = new Session();

if (empty($_GET['hash_please'])) {
    die('No Hash? Are you up to no good?');
}
if (!password_verify($_GET['hash_please'], $session->get('salt'))) {
    die("Hash mis-match(stale hash), press the browser's back button and try again.");
}
$session->destroy();
header("Location: {$site_config['baseurl']}/login.php");
