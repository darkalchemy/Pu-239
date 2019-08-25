<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/app.php';
require_once CONFIG_DIR . 'classes.php';
require_once INCL_DIR . 'database.php';
$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Not Provided by Client';
