<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$db = $_ENV['DB_DATABASE'];

$tables = [
    DATABASE_DIR . 'trivia.bz2',
    DATABASE_DIR . 'tvmaze.bz2',
];

$i = 0;
if (empty($argv[1])) {
    foreach ($tables as $table) {
        if (file_exists($table)) {
            ++$i;
            exec("bunzip2 < '$table' | mysql -u'{$user}' -p'{$pass}' '$db'");
        }
    }
} else {
    $table = DATABASE_DIR . $argv[1];
    if (file_exists($table)) {
        ++$i;
        exec("bunzip2 < '$table' | mysql -u'{$user}' -p'{$pass}' '$db'");
    }
}

echo "$i tables imported\n";
