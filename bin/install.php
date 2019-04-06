<?php

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

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
$file = CONFIG_DIR . 'config.php.example';
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
    die(CONFIG_DIR . 'config.php file could not be saved');
}

require_once INCL_DIR . 'function_common.php';
require_once CONFIG_DIR . 'config.php';
require_once CONFIG_DIR . 'classes.php';
require_once VENDOR_DIR . 'autoload.php';
require_once INCL_DIR . 'function_password.php';

use Noodlehaus\Config;

$conf = new Config([
    CONFIG_DIR . DIRECTORY_SEPARATOR . 'config.php',
]);
$site_config = $conf->all();

$site_config['password']['memory_cost'] = 2048;
$site_config['password']['time_cost'] = 12;
$site_config['password']['threads'] = 4;
$host = $site_config['database']['host'];
$user = $site_config['database']['username'];
$pass = quotemeta($site_config['database']['password']);
$db = $site_config['database']['database'];

$mysql_test = new mysqli($host, $user, $pass, $db);
if ($mysql_test->connect_error) {
    die("There was an error while selecting the database\n" . $mysql_test->error . "\n");
}

$query = 'SELECT VERSION() AS ver';
$sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $host, $user, $pass, $db, $query);
$retval = shell_exec($sql);
if (!preg_match('/10\.\d+\.\d+\-MariaDB|8\.\d+\.\d+/i', $retval)) {
    $query = 'SHOW VARIABLES LIKE "innodb_large_prefix"';
    $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $host, $user, $pass, $db, $query);
    $retval = shell_exec($sql);
    if (!preg_match('/innodb_large_prefix\s+ON/', $retval)) {
        die("Please add/update my.cnf 'innodb_large_prefix = 1' and restart mysql.\n");
    }

    $query = 'SHOW VARIABLES LIKE "innodb_file_format"';
    $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $host, $user, $pass, $db, $query);
    $retval = shell_exec($sql);
    if (!preg_match('/innodb_file_format\s+Barracuda/', $retval)) {
        die("Please add/update my.cnf 'innodb_file_format = Barracuda' and restart mysql.\n");
    }
}

$query = 'SHOW VARIABLES LIKE "innodb_file_per_table"';
$sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $host, $user, $pass, $db, $query);
$retval = shell_exec($sql);
if (!preg_match('/innodb_file_per_table\s+ON/', $retval)) {
    die("Please add/update my.cnf 'innodb_file_per_table = 1' and restart mysql.\n");
}

$query = 'SHOW VARIABLES LIKE "innodb_autoinc_lock_mode"';
$sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $host, $user, $pass, $db, $query);
$retval = shell_exec($sql);
if (!preg_match('/innodb_autoinc_lock_mode\s+0/', $retval)) {
    die("Please add/update my.cnf 'innodb_autoinc_lock_mode = 0' and restart mysql.\n");
}
$admin = [
    'username' => $vars['admin']['username'],
    'email' => $vars['admin']['email'],
    'passhash' => make_passhash(trim($vars['admin']['pass'])),
    'status' => 'confirmed',
    'added' => TIME_NOW,
    'last_access' => TIME_NOW,
    'torrent_pass' => make_password(32),
    'auth' => make_password(32),
    'apikey' => make_password(32),
    'ip' => inet_pton('127.0.0.1'),
    'class' => UC_MAX,
];
$bot = [
    'username' => $vars['chatbot']['name'],
    'email' => '',
    'passhash' => make_passhash(make_password()),
    'status' => 'confirmed',
    'added' => TIME_NOW,
    'last_access' => TIME_NOW,
    'torrent_pass' => make_password(32),
    'auth' => make_password(32),
    'apikey' => make_password(32),
    'ip' => inet_pton('127.0.0.1'),
];

$timestamp = strtotime('today midnight');
$sources = [
    'schema' => DATABASE_DIR . 'schema.sql.gz',
    'data' => DATABASE_DIR . 'data.sql.gz',
    'trivia' => DATABASE_DIR . 'trivia.sql.gz',
    'tvmaze' => DATABASE_DIR . 'tvmaze.sql.gz',
    'timestamps' => "UPDATE cleanup SET clean_time = $timestamp WHERE clean_time > 0",
    'admin' => $admin,
    'bot' => $bot,
];

$tables = [
    'trivia',
    'tvmaze',
];
$data = [
    'schema',
    'data',
];
foreach ($sources as $name => $source) {
    if ($name === 'admin' || $name === 'bot') {
        add_user($source);
    } elseif (in_array($name, $tables)) {
        echo 'Importing database table: ' . $name . "\n";
        exec("gunzip < '$source' | /usr/bin/mysql -u'{$user}' -h '{$host}' -p'{$pass}' {$db}");
    } elseif (in_array($name, $data)) {
        echo 'Importing database ' . $name . "\n";
        exec("gunzip < '$source' | /usr/bin/mysql -u'{$user}' -h '{$host}' -p'{$pass}' {$db}");
    } else {
        $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $host, $user, $pass, $db, $source);
        exec($sql, $output, $retval);
    }
    if ($retval != 0) {
        die("There was an error while working with database, at step: {$name}\n");
    }
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

echo "Installation Completed!!\n\nGo to http://{$vars['announce_urls']['http']}/login.php and sign in.\n\n";

function regex($x)
{
    return '/\#' . str_replace([
            'https://',
            'http://',
        ], '', trim($x)) . '/';
}

function add_user(array $values)
{
    global $site_config;

    $fluent = new Pu239\Database();
    $user_id = $fluent->insertInto('users')
        ->values($values)
        ->execute();

    if ($user_id) {
        $values = [
            'userid' => $user_id,
        ];
        $fluent->insertInto('usersachiev')
            ->values($values)
            ->execute();
        $fluent->insertInto('user_blocks')
            ->values($values)
            ->execute();
    }
}

function update_config(array $set, string $parent, string $name)
{
    $fluent = new Pu239\Database();
    $fluent->update('site_config')
        ->set($set)
        ->where('parent = ?', $parent)
        ->where('name = ?', $name)
        ->execute();
}
