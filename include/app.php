<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
require_once CONFIG_DIR . 'classes.php';
require_once VENDOR_DIR . 'autoload.php';
use SlashTrace\SlashTrace;
use SlashTrace\Sentry\SentryHandler;
use SlashTrace\EventHandler\DebugHandler;

require_once INCL_DIR . 'function_common.php';

date_default_timezone_set('UTC');

use Noodlehaus\Config;

$conf = new Config([
    CONFIG_DIR . 'config.php',
]);
$site_config = $conf->all();

require_once INCL_DIR . 'function_password.php';
$cache = new Pu239\Cache();
$fluent = new Pu239\Database();
require_once INCL_DIR . 'site_settings.php';

if (!$site_config['site']['production']) {
    $slashtrace = new SlashTrace();
    $slashtrace->addHandler(new DebugHandler());
    $slashtrace->register();
} else {
    if (!empty($site_config['api']['sentry'])) {
        $handler = new SentryHandler("{$site_config['api']['sentry']}");
        $slashtrace = new SlashTrace();
        $slashtrace->addHandler($handler);
        $slashtrace->register();
    }
}
