<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
dbconn(true);
loggedinorreturn();

setSessionVar('error', 'Access Not Allowed');
header("Location: {$INSTALLER09['baseurl']}/index.php");
die();

