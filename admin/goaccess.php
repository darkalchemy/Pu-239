<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
if (file_exists(CACHE_DIR . 'goaccess.html')) {
    require_once CACHE_DIR . 'goaccess.html';
    die();
} else {
    stderr('Error', 'Is goaccess installed?');
}

echo stdhead('GoAccess') . $HTMLOUT . stdfoot();
