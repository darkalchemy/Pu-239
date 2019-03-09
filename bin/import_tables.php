<?php

require_once __DIR__ . '/../include/bittorrent.php';

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USERNAME'];
$pass = quotemeta($_ENV['DB_PASSWORD']);
$db = $_ENV['DB_DATABASE'];

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
