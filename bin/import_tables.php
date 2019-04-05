<?php

require_once __DIR__ . '/../include/bittorrent.php';

$host = $site_config['database']['host'];
$user = $site_config['database']['username'];
$pass = quotemeta($site_config['database']['password']);
$db = $site_config['database']['database'];

$tables = [
    DATABASE_DIR . 'trivia.sql.gz',
    DATABASE_DIR . 'tvmaze.sql.gz',
];

$i = 0;
if (empty($argv[1])) {
    foreach ($tables as $table) {
        if (file_exists($table)) {
            ++$i;
            exec("gunzip < '$table' | mysql -h $host -u'{$user}' -p'{$pass}' $db");
        }
    }
} else {
    $table = $argv[1];
    if (file_exists($table)) {
        ++$i;
        exec("gunzip < '$table' | mysql -h $host -u'{$user}' -p'{$pass}' $db");
    }
}

echo "$i tables imported\n";
