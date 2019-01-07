<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'define.php';
require_once INCL_DIR . 'password_functions.php';

if (empty($argv[1])) {
    die("To install please run\n\nphp {$argv[0]} install\n");
}

$vars = [
    'site_name'      => readline('Site Name: '),
    'announce_http'  => readline('Site HTTP URL: '),
    'announce_ssl'   => readline('Site HTTPS URL: '),
    'mysql_db'       => readline('Database Name: '),
    'mysql_user'     => readline('Database Username: '),
    'mysql_pass'     => readline('Database Password: '),
    'bot_username'   => readline('BOT Username: '),
    'site_email'     => readline('Site Email: '),
    'admin_username' => readline('Admin Username: '),
    'admin_pass'     => readline('Admin Password: '),
    'admin_email'     => readline('Admin Email: '),
];

$vars['sessionName']    = str_replace(' ', '_', $vars['site_name']);
$vars['cookie_prefix']  = $vars['sessionName'];
$vars['cookie_domain']  = $vars['announce_http'];
$vars['domain']         = $vars['announce_http'];

$file = INCL_DIR . 'config.php.example';
$config = file_get_contents($file);
$keys = array_map('regex', array_keys($vars));
$values = array_values($vars);
$config = preg_replace($keys, $values, $config);
for($i = 1; $i<=6; $i++) {
    $config = preg_replace("/#pass{$i}/", bin2hex(random_bytes(16)), $config);
}

if (!file_put_contents(INCL_DIR . 'config.php', $config)) {
    die(INCL_DIR . 'config.php file could not be saved');
}
$file = ROOT_DIR . '.env.example';
$config = file_get_contents($file);
$config = preg_replace($keys, $values, $config);
if (!file_put_contents(ROOT_DIR . '.env', $config)) {
    die(ROOT_DIR . '.env file could not be saved');
}

require_once INCL_DIR . 'bittorrent.php';
require_once INCL_DIR . 'password_functions.php';

$dotenv = new Dotenv\Dotenv(ROOT_DIR);
$dotenv->load();

$mysql_test = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
if ($mysql_test->connect_error) {
    die("There was an error while selecting the database\n" . $mysql_test->error . "\n");
}

$query = 'SELECT VERSION() AS ver';
$sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $query);
$retval = shell_exec($sql);
if (!preg_match('/10\.3\.\d+\-MariaDB/i', $retval)) {
    $query = 'SHOW VARIABLES LIKE "innodb_large_prefix"';
    $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $query);
    $retval = shell_exec($sql);
    if (!preg_match('/innodb_large_prefix\s+ON/', $retval)) {
        die("Please add/update my.cnf 'innodb_large_prefix = 1' and restart mysql.\n");
    }

    $query = 'SHOW VARIABLES LIKE "innodb_file_format"';
    $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $query);
    $retval = shell_exec($sql);
    if (!preg_match('/innodb_file_format\s+Barracuda/', $retval)) {
        die("Please add/update my.cnf 'innodb_file_format = Barracuda' and restart mysql.\n");
    }
}

$query = 'SHOW VARIABLES LIKE "innodb_file_per_table"';
$sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $query);
$retval = shell_exec($sql);
if (!preg_match('/innodb_file_per_table\s+ON/', $retval)) {
    die("Please add/update my.cnf 'innodb_file_per_table = 1' and restart mysql.\n");
}

$query = 'SHOW VARIABLES LIKE "innodb_autoinc_lock_mode"';
$sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $query);
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
    'schema'       => 'source ' . DATABASE_DIR . 'schema.sql',
    'data'         => 'source ' . DATABASE_DIR . 'data.sql',
    'trivia'       => DATABASE_DIR . 'trivia.sql.bz2',
    'tvmaze'       => DATABASE_DIR . 'tvmaze.sql.bz2',
    'timestamps'   => "UPDATE cleanup SET clean_time = $timestamp",
    'admin'        => $admin,
    'bot'          => $bot,
];

foreach ($sources as $name => $source) {
    if ($name === 'admin' || $name === 'bot') {
        add_user($source);
    } elseif (preg_match('/bz2/', $source)) {
        echo 'Importing database table: ' . $name . "\n";
        exec("bunzip2 < '$source' | /usr/bin/mysql -u'{$_ENV['DB_USERNAME']}' -h '{$_ENV['DB_HOST']}' -p'{$_ENV['DB_PASSWORD']}' '{$_ENV['DB_DATABASE']}'", $output, $retval);
    } else {
        if (preg_match('/source/', $source)) {
            echo 'Importing database ' . $name . "\n";
        }
        $sql = sprintf("/usr/bin/mysql -h %s -u%s -p'%s' %s -e '%s'", $_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE'], $source);
        exec($sql, $output, $retval);
    }
    if ($retval != 0) {
        die("There was an error while working with database, at step: {$name}\n");
    }
}

echo "Installation Completed!!\n\nGo to http://{$vars['announce_http']}/login.php and sign in.";

function regex($x)
{
    return '/\#' . str_replace(['https://', 'http://'], '', trim($x)) . '/';
}

function add_user($values)
{
    global $user_stuffs, $site_config;

    $user_id = $user_stuffs->add($values);
    if ($user_id) {
        sql_query('INSERT INTO usersachiev (userid) VALUES (' . sqlesc($user_id) . ')') or sqlerr(__FILE__, __LINE__);
    }
}
