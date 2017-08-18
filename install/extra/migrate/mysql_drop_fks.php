<?php

$user = "username";
$pass= "secret";
$db = "database";
$host = "localhost";

if ($user === 'username' || $pass === 'secret') {
    exit("please edit this file\n");
}
$link = mysqli_connect("$host","$user","$pass","$db") or die("Error " . mysqli_error($link));

$sql = "SELECT TABLE_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' AND TABLE_SCHEMA = '$db'";
$rs = $link->query($sql);
while($row = mysqli_fetch_assoc($rs)) {
    $tbl = $row['TABLE_NAME'];
    $fk = $row['CONSTRAINT_NAME'];
    $sql = "ALTER TABLE `$db`.`$tbl` DROP FOREIGN KEY `$fk`";
    echo $sql . "\n";
    $link->query($sql);
}
