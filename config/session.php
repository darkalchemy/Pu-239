<?php

declare(strict_types = 1);

use Delight\Cookie\Session;
use Pu239\Settings;

global $container;

$settings = $container->get(Settings::class);
$site_config = $settings->get_settings();
if (ini_get('session.save_handler') != 'files') {
    ini_set('session.sid_length', '256');
} else {
    ini_set('session.sid_length', '128');
}
ini_set('default_charset', 'utf-8');
ini_set('session.name', $site_config['session']['name']);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_cookies', '1');
ini_set('session.cookie_secure', (get_scheme() === 'https' ? '1' : '0'));
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.lazy_write', '0');
ini_set('session.cookie_httponly', '1');
ini_set('max_execution_time', '300');
ini_set('session.cookie_domain', '');
Session::start('Strict');
