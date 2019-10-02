<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config;

$cache = $container->get(Cache::class);
$cache->delete('is_staff_');
$session = $container->get(Session::class);
$session->set('is-success', 'Staff List Updated');
header("Location: {$_SERVER['PHP_SELF']}");
