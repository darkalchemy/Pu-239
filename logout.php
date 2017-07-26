<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
dbconn();
/*if(!($CURUSER)) {
die('Error You already logged out you muppet');
exit();
}*/
global $CURUSER;
$hash_please = (isset($_GET['hash_please']) && htmlsafechars($_GET['hash_please']));
$salty = md5('Th15T3xtis5add3dto66uddy6he@water...' . $CURUSER['username'] . '');
if (empty($hash_please)) {
    die('No Hash your up to no good MOFO');
}
if ($hash_please != $salty) {
    die('Unsecure Logout - Hash mis-match please contact site admin');
}
logoutcookie();
header("Location: {$INSTALLER09['baseurl']}/");
