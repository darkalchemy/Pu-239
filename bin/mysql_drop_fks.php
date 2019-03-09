<?php

require_once __DIR__ . '/../include/bittorrent.php';
global $pdo, $fluent;

$fluent = new Pu239\Database();
$query = $pdo->prepare('SELECT TABLE_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE (CONSTRAINT_NAME LIKE "%_ibfk_%" OR CONSTRAINT_TYPE = "FOREIGN KEY") AND TABLE_SCHEMA = ?');
$query->execute([$_ENV['DB_DATABASE']]);
$tables = $query->fetchAll();

foreach ($tables as $row) {
    $sql = "ALTER TABLE {$_ENV['DB_DATABASE']}.{$row['TABLE_NAME']} DROP FOREIGN KEY {$row['CONSTRAINT_NAME']}";
    $query = $fluent->getPdo()
        ->prepare($sql);
    $query->execute();
    echo "Dropped foreign key '{$row['CONSTRAINT_NAME']}'\n";
}
echo "All default foreign keys have been removed. To recreate them import foreign_keys.sql.\n\n";
