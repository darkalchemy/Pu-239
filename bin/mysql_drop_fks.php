<?php

require_once __DIR__ . '/../include/bittorrent.php';
global $site_config;
$sql = "SELECT TABLE_NAME, CONSTRAINT_NAME 
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
    WHERE CONSTRAINT_NAME LIKE '%_ibfk_%' AND CONSTRAINT_TYPE = 'FOREIGN KEY' AND TABLE_SCHEMA = " . sqlesc($site_config['db']['database']);
$rs = sql_query($sql) or sqlerr(__FILE__, __LINE__);
while ($row = mysqli_fetch_assoc($rs)) {
    $tbl = $row['TABLE_NAME'];
    $fk = $row['CONSTRAINT_NAME'];
    $sql = "ALTER TABLE `" . $site_config['db']['database'] . "`.`$tbl` DROP FOREIGN KEY `$fk`";
    echo $sql . "\n";
    sql_query($sql);
}
