<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
dbconn();
global $CURUSER;
$hash_please = (isset($_GET['hash_please']) && htmlsafechars($_GET['hash_please']));
$salty = salty($CURUSER['username']);
if (empty($hash_please)) {
    die('No Hash your up to no good MOFO');
}
if ($hash_please != $salty) {
    die('Unsecure Logout - Hash mis-match please contact site admin');
}
logoutcookie();
header("Location: {$INSTALLER09['baseurl']}/");
