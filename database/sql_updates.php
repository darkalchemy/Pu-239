<?php

/*
    [
        'id' => 1,
        'info' => 'Drop unnecessary column',
        'date' => '06 Dec, 2017',
        'query' => 'ALTER TABLE `polls` DROP `starter_name`',
    ],
*/
$sql_updates = [
    [
        'id' => 1,
        'info' => 'Add 12 Hour Time Format to site_config',
        'date' => '07 Jul, 2018',
        'query' => "INSERT INTO `site_config` (name, value, description) VALUES ('12_hour', 1, '12 hour time format(true), 24 hour time format (false)')",
    ],
];
