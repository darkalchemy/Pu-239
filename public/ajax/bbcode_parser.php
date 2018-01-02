<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();
global $site_config;

if (empty($_POST)) {
    setSessionVar('is-danger', 'Access Not Allowed');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
$output = format_comment($_POST['data']);
echo $output;
die();
