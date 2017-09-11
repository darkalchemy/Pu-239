<?php
define('IN_site_config_CRON', true);
if (!isset($argv) or !is_array($argv) or (count($argv) != 2) or !preg_match('/^[0-9a-fA-F]{32}$/i', $argv[1])) {
    exit('Go away!');
}
require_once 'include/cronclean.php';
