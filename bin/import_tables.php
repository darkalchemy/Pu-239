<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$db = $_ENV['DB_DATABASE'];

$tables = [
    DATABASE_DIR . 'images.sql.gz',
    DATABASE_DIR . 'imdb_info.sql.gz',
    DATABASE_DIR . 'person.sql.gz',
    DATABASE_DIR . 'trivia.sql.gz',
    DATABASE_DIR . 'tvmaze.sql.gz',
];

$i = 0;
foreach ($tables as $table) {
    if (file_exists($table)) {
        $i++;
        exec("gunzip < '$table' | mysql '$db' -u'{$user}' -p'{$pass}'");
    }
}

echo "$i tables imported\n";
