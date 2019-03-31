<?php

require_once __DIR__ . '/../../include/bittorrent.php';
check_user_status();
global $CURUSER, $site_config, $session;

if (empty($CURUSER) || $CURUSER['class'] < UC_MAX) {
    $session->set('is-warning', 'You do not have access to view that page');
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}

/**
 * @return AdminerCustomization
 */
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
            'information_schema',
        ]),
        new AdminerFrames(),
        new AdminerDumpBz2(),
        new AdminerDumpZip(),
        new AdminerEnumTypes(),
        new AdminerVersionNoverify(),
        new AdminerTablesFilter(),
        new AdminerReadableDates(),
        new AdminerDumpDate(),
    ];

    /**
     * Class AdminerCustomization.
     */
    class AdminerCustomization extends AdminerPlugin
    {
        /**
         * AdminerCustomization constructor.
         *
         * @param $plugins
         */
        public function __construct($plugins)
        {
            $this->plugins = $plugins;
        }

        /**
         * @return mixed
         */
        public function name()
        {
            global $site_config;

            return $site_config['site_name'];
        }

        /**
         * @return mixed|string
         */
        public function database()
        {
            return "{$_ENV['DB_DATABASE']}";
        }

        /**
         * @return array|mixed
         */
        public function credentials()
        {
            global $CURUSER, $site_config;

            if (in_array($CURUSER['id'], $site_config['adminer_allowed_ids'])) {
                return [
                    'localhost',
                    $_ENV['DB_USERNAME'],
                    $_ENV['DB_PASSWORD'],
                ];
            }
        }
    }

    return new AdminerCustomization($plugins);
}

include ADMIN_DIR . 'adminer.php';
