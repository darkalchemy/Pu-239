<?php

declare(strict_types = 1);

use Pu239\Session;

require_once __DIR__ . '/../../include/bittorrent.php';
$user = check_user_status();
if (empty($user) || $user['class'] < UC_MAX) {
    global $container;

    $session = $container->get(Session::class);
    $session->set('is-warning', 'You do not have access to view that page');
    header("Location: {$site_config['paths']['baseurl']}");
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
            parent::__construct($this->plugins);
        }

        /**
         * @return mixed
         */
        public function name()
        {
            global $site_config;

            return $site_config['site']['name'];
        }

        /**
         * @return mixed|string
         */
        public function database()
        {
            global $site_config;

            return "{$site_config['db']['database']}";
        }

        /**
         * @return array|mixed
         */
        public function credentials()
        {
            global $site_config, $user;

            if (in_array($user['id'], $site_config['adminer']['allowed_ids'])) {
                return [
                    'localhost',
                    $site_config['db']['username'],
                    $site_config['db']['password'],
                ];
            }
        }
    }

    return new AdminerCustomization($plugins);
}

include ADMIN_DIR . 'adminer.php';
