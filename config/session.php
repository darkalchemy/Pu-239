<?php

declare(strict_types = 1);
require_once INCL_DIR . 'function_common.php';
require_once CONFIG_DIR . 'functions.php';

use Delight\Cookie\Session;
use Pu239\Settings;

global $container;

// Override the settings in php.ini
ini_set('memory_limit', '1024M');
ini_set('zlib.output_compression', 'Off');
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('ignore_repeated_errors', 'On');
//ini_set('error_reporting', 'E_ALL');
ini_set('error_log', PHPERROR_LOGS_DIR . 'error.log');

// Set seession and cookies values
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
