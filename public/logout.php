<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER, $site_config, $session;

$session->destroy();
header("Location: {$site_config['baseurl']}/login.php");
