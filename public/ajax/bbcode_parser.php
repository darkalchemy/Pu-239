<?php
require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();
global $site_config;

if (empty($_POST)) {
    $session = new Session();
    $session->set('is-danger', 'Access Not Allowed');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
$output = format_comment($_POST['data']);
echo $output;
die();
