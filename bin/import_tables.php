<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/app.php';
global $container;

$pdo = $container->get(PDO::class);

$tables = [
    DATABASE_DIR . 'trivia.sql.gz',
    DATABASE_DIR . 'tvmaze.sql.gz',
];

$i = 0;
if (empty($argv[1])) {
    foreach ($tables as $table) {
        if (file_exists($table)) {
            ++$i;
            $ext = pathinfo($table, PATHINFO_EXTENSION);
            if ($ext === 'gz') {
                $source = file_get_contents('compress.zlib://' . $table);
            } else {
                $source = file_get_contents($table);
            }
            $pdo->exec($source);
        }
    }
} else {
    $table = $argv[1];
    if (file_exists($table)) {
        ++$i;
        $ext = pathinfo($table, PATHINFO_EXTENSION);
        if ($ext === 'gz') {
            $source = file_get_contents('compress.zlib://' . $table);
        } else {
            $source = file_get_contents($table);
        }
        $pdo->exec($source);
    }
}

echo "$i tables imported\n";
