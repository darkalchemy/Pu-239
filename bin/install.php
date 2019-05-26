<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

if (empty($argv[1])) {
    die("To install please run\n\nphp {$argv[0]} install\n");
}

if (count($argv) === 13) {
    $vars = [
        'site' => [
            'name' => $argv[2],
            'email' => $argv[9],
            'salt' => bin2hex(random_bytes(16)),
            'salty' => bin2hex(random_bytes(16)),
            'skey' => bin2hex(random_bytes(16)),
        ],
        'announce_urls' => [
            'http' => $argv[3],
            'https' => $argv[4],
        ],
        'chatbot' => [
            'name' => $argv[8],
        ],
        'admin' => [
            'username' => $argv[10],
            'pass' => $argv[11],
            'email' => $argv[12],
        ],
        'mysql' => [
            'db' => $argv[5],
            'user' => $argv[6],
            'pass' => $argv[7],
        ],
    ];
} else {
    $vars = [
        'site' => [
            'name' => readline('Site Name: '),
            'email' => readline('Site Email: '),
            'salt' => bin2hex(random_bytes(16)),
            'salty' => bin2hex(random_bytes(16)),
            'skey' => bin2hex(random_bytes(16)),
        ],
        'announce_urls' => [
            'http' => readline('Site HTTP URL: '),
            'https' => readline('Site HTTPS URL: '),
        ],
        'chatbot' => [
            'name' => readline('BOT Username: '),
        ],
        'admin' => [
            'username' => readline('Admin Username: '),
            'pass' => readline('Admin Password: '),
            'email' => readline('Admin Email: '),
        ],
        'mysql' => [
            'db' => readline('Database Name: '),
            'user' => readline('Database Username: '),
            'pass' => readline('Database Password: '),
        ],
    ];
}

$vars['mysql']['pass'] = quotemeta($vars['mysql']['pass']);
$vars['admin']['pass'] = quotemeta($vars['admin']['pass']);
$vars['baseurl'] = str_replace('http://', '', $vars['announce_urls']['http']);
$vars['session']['name'] = str_replace(' ', '_', $vars['site']['name']);
$vars['session']['domain'] = $vars['baseurl'];
$vars['session']['prefix'] = $vars['session']['name'] . '_';
$vars['cookies']['prefix'] = $vars['session']['prefix'];
$vars['cookies']['domain'] = $vars['baseurl'];

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
$file = CONFIG_DIR . 'config_example.php';
$config = file_get_contents($file);
$config = str_replace([
    '#mysql_db',
    '#mysql_user',
    '#mysql_pass',
    '#cookie_prefix',
    '#baseurl',
], [
    $vars['mysql']['db'],
    $vars['mysql']['user'],
    $vars['mysql']['pass'],
    $vars['cookies']['prefix'],
    $vars['baseurl'],
], $config);

if (!file_put_contents(CONFIG_DIR . 'config.php', $config)) {
    die(CONFIG_DIR . 'config.php file could not be saved, check your permissions.');
}

$production = false;
require_once CONFIG_DIR . 'classes.php';
require_once VENDOR_DIR . 'autoload.php';
require_once INCL_DIR . 'function_common.php';

$builder = new ContainerBuilder();
if ($production) {
    $builder->enableCompilation(DI_CACHE_DIR);
}
$builder->addDefinitions(CONFIG_DIR . '/config.php');
$builder->addDefinitions(CONFIG_DIR . '/definitions.php');
$builder->useAutowiring(true);
$builder->useAnnotations(false);
try {
    $container = $builder->build();
} catch (Exception $e) {
    //TODO Logger;
}

$site_config = $container->get('env');
$site_config['files']['path'] = CACHE_DIR . 'install';
$cache = $container->get(Cache::class);
$auth = $container->get(Auth::class);
$pdo = $container->get(PDO::class);
$cache->flushDB();
$sources = [
    'schema' => file_get_contents('compress.zlib://' . DATABASE_DIR . 'schema.sql.gz'),
    'data' => file_get_contents('compress.zlib://' . DATABASE_DIR . 'data.sql.gz'),
    'trivia' => file_get_contents('compress.zlib://' . DATABASE_DIR . 'trivia.sql.gz'),
    'tvmaze' => file_get_contents('compress.zlib://' . DATABASE_DIR . 'tvmaze.sql.gz'),
];

foreach ($sources as $name => $source) {
    echo 'Importing: ' . $name . "\n";
    $result = $pdo->exec($source);
    if ($result != 0) {
        die("There was an error while working with database, at step: {$name}\n");
    }
}

$timestamp = strtotime('today midnight');
$query = "UPDATE cleanup SET clean_time = $timestamp WHERE clean_time > 0";
$stmt = $pdo->query($query);
if (!$stmt->execute()) {
    die("There was an error while working with database, at step: {$name}\n");
}

foreach ($vars['site'] as $key => $value) {
    $set = [
        'value' => $value,
    ];
    update_config($set, 'site', $key);
}

foreach ($vars['session'] as $key => $value) {
    $set = [
        'value' => $value,
    ];
    update_config($set, 'session', $key);
}

foreach ($vars['cookies'] as $key => $value) {
    $set = [
        'value' => $value,
    ];
    update_config($set, 'cookies', $key);
}

foreach ($vars['chatbot'] as $key => $value) {
    $set = [
        'value' => $value,
    ];
    update_config($set, 'chatbot', $key);
}

$userId = $auth->registerWithUniqueUsername(strip_tags($vars['admin']['email']), strip_tags($vars['admin']['pass']), strip_tags($vars['admin']['username']));
if (!empty($userId)) {
    update_user($userId, UC_MAX);
}
$userId = $auth->registerWithUniqueUsername('donkey.kong@nintendo.com', bin2hex(random_bytes(16)), strip_tags($vars['chatbot']['name']));

echo "Installation Completed!!\n\nGo to http://{$vars['announce_urls']['http']}/login.php and sign in.\n\n";
$cache->flushDB();

/**
 * @param $x
 *
 * @return string
 */
function regex($x)
{
    return '/\#' . str_replace([
        'https://',
        'http://',
    ], '', trim($x)) . '/';
}

/**
 * @param array  $set
 * @param string $parent
 * @param string $name
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function update_config(array $set, string $parent, string $name)
{
    global $container;

    $fluent = $container->get(Database::class);
    $fluent->update('site_config')
           ->set($set)
           ->where('parent = ?', $parent)
           ->where('name = ?', $name)
           ->execute();
}

/**
 * @param int $userid
 * @param int $class
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws Exception
 */
function update_user(int $userid, int $class)
{
    global $container;

    $fluent = $container->get(Database::class);
    $dt = TIME_NOW;
    $set = [
        'free_switch' => $dt + 14 * 86400,
        'torrent_pass' => bin2hex(random_bytes(32)),
        'auth' => bin2hex(random_bytes(32)),
        'apikey' => bin2hex(random_bytes(32)),
        'stylesheet' => 1,
        'last_access' => $dt,
        'class' => $class,
    ];
    $fluent->update('users')
           ->set($set)
           ->where('id = ?', $userid)
           ->execute();
    $fluent->insertInto('usersachiev')
           ->values(['userid' => $userid])
           ->execute();
    $fluent->insertInto('user_blocks')
           ->values(['userid' => $userid])
           ->execute();
}
