<?php

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);

global $site_config, $cache, $session;

$cache->delete('is_staff_');
$session->set('is-success', 'Staff List Updated');
header("Location: {$site_config['paths']['baseurl']}/staffpanel.php");
