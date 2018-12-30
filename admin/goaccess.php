<?php

require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $session;

if (file_exists(CACHE_DIR . 'goaccess.html')) {
    require_once CACHE_DIR . 'goaccess.html';
    die();
} else {
    stderr('Error', 'Is goaccess installed?');
}

echo stdhead('GoAccess') . $HTMLOUT . stdfoot();

