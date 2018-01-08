<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
check_user_status();
global $CURUSER, $site_config;

if (empty($CURUSER) || $CURUSER['class'] !== UC_MAX) {
    setSessionVar('is-warning', 'You do not have access to view that page');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}

function adminer_object()
{
    include_once PLUGINS_DIR . 'plugin.php';

    foreach (glob(PLUGINS_DIR . '*.php') as $filename) {
        include_once "$filename";
    }

    $plugins = [
        new AdminerDatabaseHide([
            'mysql',
            'sys',
            'performance_schema',
            'information_schema'
        ])
    ];


    class AdminerCustomization extends AdminerPlugin
    {
        function __construct($plugins) {
            $this->plugins = $plugins;
        }

        function name()
        {
            global $site_config;

            return $site_config['site_name'];
        }

        function database()
        {
            return "{$_ENV['DB_DATABASE']}";
        }

        function credentials()
        {
            global $CURUSER;

            $allowed_ids = [
                1
            ];
            if (in_array($CURUSER['id'], $allowed_ids)) {
                return [
                    'localhost',
                    $_ENV['DB_USERNAME'],
                    $_ENV['DB_PASSWORD']
                ];
            }
        }
    }

    return new AdminerCustomization($plugins);
}

include ROOT_DIR . 'adminer.php';
