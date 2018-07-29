<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$dotenv = new Dotenv\Dotenv(dirname(__FILE__, 2) . DIRECTORY_SEPARATOR);
$dotenv->load();
define('SQL_DEBUG', false);
define('SOCKET', true);
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'database.php';

$fluent = new DarkAlchemy\Pu239\Database();
$query = $fluent->from('INFORMATION_SCHEMA.TABLE_CONSTRAINTS')
    ->select('null')
    ->select('TABLE_NAME')
    ->select('CONSTRAINT_NAME')
    ->where('CONSTRAINT_NAME LIKE "%_ibfk_%"')
    ->where('CONSTRAINT_TYPE = "FOREIGN KEY"')
    ->where('TABLE_SCHEMA = ?', $_ENV['DB_DATABASE'])
    ->execute();

foreach ($query as $row) {
    $fluent->getPdo()
        ->query("ALTER TABLE {$_ENV['DB_DATABASE']}.{$row['TABLE_NAME']} DROP FOREIGN KEY {$row['CONSTRAINT_NAME']}");
    echo "Dropped foreign key '{$row['CONSTRAINT_NAME']}'\n";
}
echo "All default foreign keys have been removed. To recreate them import foreign_keys.sql.\n\n";
