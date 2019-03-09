<?php

require_once __DIR__ . '/../include/bittorrent.php';
check_user_status();
global $CURUSER, $site_config, $session;

$session->destroy();
