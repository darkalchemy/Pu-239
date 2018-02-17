<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../../../../');
$dotenv->load();

$user = $_ENV['DB_USERNAME'];
$pass= $_ENV['DB_PASSWORD'];
$db = $_ENV['DB_DATABASE'];
$host = $_ENV['DB_HOST'];

$link = mysqli_connect("$host", "$user", "$pass", "$db") or die("Error " . mysqli_error($link));

$sql = "SELECT TABLE_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_NAME LIKE '%_ibfk_%' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND TABLE_SCHEMA = '$db'";
$rs = $link->query($sql);
while ($row = mysqli_fetch_assoc($rs)) {
    $tbl = $row['TABLE_NAME'];
    $fk = $row['CONSTRAINT_NAME'];
    $sql = "ALTER TABLE `$db`.`$tbl` DROP FOREIGN KEY `$fk`";
    echo $sql . "\n";
    $link->query($sql);
}
