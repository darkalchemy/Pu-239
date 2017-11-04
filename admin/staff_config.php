<?php
require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);

global $mc1, $site_config;
$mc1->delete_value('staff_settings_');

setSessionVar('is-success', 'Staff List Updated');
header("Location: {$site_config['baseurl']}/staffpanel.php");

