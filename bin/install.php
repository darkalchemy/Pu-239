<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
require_once INCL_DIR . 'function_password.php';

if (empty($argv[1])) {
    die("To install please run\n\nphp {$argv[0]} install\n");
}

if (count($argv) === 13) {
    $vars = [
        'site_name' => $argv[2],
        'announce_http' => $argv[3],
        'announce_ssl' => $argv[4],
        'mysql_db' => $argv[5],
        'mysql_user' => $argv[6],
        'mysql_pass' => $argv[7],
        'bot_username' => $argv[8],
        'site_email' => $argv[9],
        'admin_username' => $argv[10],
        'admin_pass' => $argv[11],
        'admin_email' => $argv[12],
    ];
} else {
    $vars = [
        'site_name' => readline('Site Name: '),
        'announce_http' => readline('Site HTTP URL: '),
        'announce_ssl' => readline('Site HTTPS URL: '),
        'mysql_db' => readline('Database Name: '),
        'mysql_user' => readline('Database Username: '),
        'mysql_pass' => readline('Database Password: '),
        'bot_username' => readline('BOT Username: '),
        'site_email' => readline('Site Email: '),
        'admin_username' => readline('Admin Username: '),
        'admin_pass' => readline('Admin Password: '),
        'admin_email' => readline('Admin Email: '),
    ];
}

$vars['sessionName'] = str_replace(' ', '_', $vars['site_name']);
$vars['cookie_prefix'] = $vars['sessionName'];
$vars['cookie_domain'] = $vars['announce_http'];
$vars['domain'] = $vars['announce_http'];

$file = CONFIG_DIR . 'site.php.example';
$config = file_get_contents($file);
$keys = array_map('regex', array_keys($vars));
$values = array_values($vars);
$config = preg_replace($keys, $values, $config);
for ($i = 1; $i <= 4; ++$i) {
    $config = preg_replace("/#pass{$i}/", bin2hex(random_bytes(16)), $config);
}

if (!file_put_contents(CONFIG_DIR . 'site.php', $config)) {
    die(CONFIG_DIR . 'site.php file could not be saved');
}
$file = ROOT_DIR . '.env.example';
$config = file_get_contents($file);
$config = preg_replace($keys, $values, $config);
if (!file_put_contents(ROOT_DIR . '.env', $config)) {
    die(ROOT_DIR . '.env file could not be saved');
}

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'define.php';
require_once CONFIG_DIR . 'site.php';
require_once INCL_DIR . 'function_common.php';
require_once CONFIG_DIR . 'main.php';
require_once VENDOR_DIR . 'autoload.php';
require_once INCL_DIR . 'function_password.php';

$dotenv = new Dotenv\Dotenv(ROOT_DIR);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USERNAME'];
$pass = quotemeta($_ENV['DB_PASSWORD']);
$db = $db;

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
        'username' => $vars['admin_username'],
        'email' => $vars['admin_email'],
        'passhash' => make_passhash(trim($vars['admin_pass'])),
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
        'username' => $vars['bot_username'],
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
    'schema' => 'source ' . DATABASE_DIR . 'schema.sql',
    'data' => 'source ' . DATABASE_DIR . 'data.sql',
    'trivia' => DATABASE_DIR . 'trivia.sql.bz2',
    'tvmaze' => DATABASE_DIR . 'tvmaze.sql.bz2',
    'timestamps' => "UPDATE cleanup SET clean_time = $timestamp",
    'admin' => $admin,
    'bot' => $bot,
];

foreach ($sources as $name => $source) {
    if ($name === 'admin' || $name === 'bot') {
        add_user($source);
    } elseif (preg_match('/bz2/', $source)) {
        echo 'Importing database table: ' . $name . "\n";
        exec("bunzip2 < '$source' | /usr/bin/mysql -u'{$user}' -h '{$host}' -p'{$pass}' '{$db}'", $output, $retval);
    } else {
        if (preg_match('/source/', $source)) {
            echo 'Importing database ' . $name . "\n";
        }
        $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $host, $user, $pass, $db, $source);
        exec($sql, $output, $retval);
    }
    if ($retval != 0) {
        die("There was an error while working with database, at step: {$name}\n");
    }
}

echo "Installation Completed!!\n\nGo to http://{$vars['announce_http']}/login.php and sign in.\n\n";

function regex($x)
{
    return '/\#' . str_replace(['https://', 'http://'], '', trim($x)) . '/';
}

function add_user($values)
{
    global $site_config;

    $fluent = new DarkAlchemy\Pu239\Database();
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
    }
}
