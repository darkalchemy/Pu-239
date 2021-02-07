<?php

declare(strict_types = 1);
require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
require_once CONFIG_DIR . 'classes.php';
require_once VENDOR_DIR . 'autoload.php';

use DI\ContainerBuilder;

date_default_timezone_set('UTC');

$builder = new ContainerBuilder();
if (PRODUCTION) {
    $builder->enableCompilation(DI_CACHE_DIR);
}
$builder->addDefinitions(CONFIG_DIR . 'config.php');
$builder->addDefinitions(CONFIG_DIR . 'emoticons.php');
$builder->addDefinitions(CONFIG_DIR . 'subtitles.php');
$builder->addDefinitions(CONFIG_DIR . 'whereis.php');
$builder->addDefinitions(CONFIG_DIR . 'definitions.php');
$builder->useAutowiring(true);
$builder->useAnnotations(false);
try {
    $container = $builder->build();
} catch (Exception $e) {
    die("try 'composer install', then check that definitions.php matches src directory");
}
require_once CONFIG_DIR . 'session.php';
