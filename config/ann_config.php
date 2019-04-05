<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'app.php';

$session = new Pu239\Session();
$ip_stuffs = new Pu239\IP();
$peer_stuffs = new Pu239\Peer();
$snatched_stuffs = new Pu239\Snatched();
$torrent_stuffs = new Pu239\Torrent();
$user_stuffs = new Pu239\User();

require_once CLASS_DIR . 'class_bt_options.php';
require_once CONFIG_DIR . 'classes.php';
require_once INCL_DIR . 'database.php';

$agent = $_SERVER['HTTP_USER_AGENT'];
