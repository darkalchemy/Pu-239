<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER;
$hash_please = (isset($_GET['hash_please']) && htmlsafechars($_GET['hash_please']));
$salty = salty($CURUSER['username']);
if (empty($hash_please)) {
    die('No Hash your up to no good MOFO');
}
if ($hash_please != $salty) {
    die('Unsecure Logout - Hash mis-match please contact site admin');
}
destroySession();
header("Location: {$site_config['baseurl']}/");
