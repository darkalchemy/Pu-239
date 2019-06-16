<?php

use Jobby\Jobby;

require_once __DIR__ . '/../include/bittorrent.php';
global $container, $site_config;

$jobby = $container->get(Jobby::class);
$jobby->add('Cron Controller', [
    'runAs' => $site_config['webserver']['username'],
    'command' => '/usr/bin/php ' . INCL_DIR . 'cron_controller.php',
    'schedule' => '* * * * *',
    'output' => LOGS_DIR . 'cleanup/cron_' . date('Y.m.d', TIME_NOW) . '.log',
    'enabled' => true,
]);

$jobby->add('Cron Controller', [
    'runAs' => $site_config['webserver']['username'],
    'command' => '/usr/bin/php ' . INCL_DIR . 'images_update.php',
    'schedule' => '*/10 * * * *',
    'output' => LOGS_DIR . 'images/images_' . date('Y.m.d', TIME_NOW) . '.log',
    'enabled' => true,
]);

$jobby->add('Fix log permissions', [
    'command' => 'sudo chown -R ' . $site_config['webserver']['username'] . ':' . $site_config['webserver']['username'] . ' ' . LOGS_DIR,
    'schedule' => '* * * * *',
    'enabled' => true,
]);

$jobby->run();
