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
        'id' => 1530957741,
        'info' => 'Truncate database_updates table',
        'date' => '07 Jul, 2018',
        'query' => 'TRUNCATE TABLE `database_updates`',
        'flush' => false,
    ],
    [
        'id' => 1530957742,
        'info' => 'Add 12 Hour Time Format to site_config',
        'date' => '07 Jul, 2018',
        'query' => "INSERT INTO `site_config` (name, value, description) VALUES ('12_hour', 1, '12 hour time format(true), 24 hour time format (false)')",
        'flush' => 'site_settings_',
    ],
    [
        'id' => 1531044142,
        'info' => 'Add 12 Hour Time Format to users table',
        'date' => '08 Jul, 2018',
        'query' => "ALTER TABLE users ADD COLUMN `12_hour` enum('yes','no') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'yes'",
        'flush' => true,
    ],
    [
        'id' => 1531336627,
        'info' => 'Add Images Table',
        'date' => '11 Jul, 2018',
        'query' => "CREATE TABLE IF NOT EXISTS `images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tmdb_id` int(10) unsigned NOT NULL DEFAULT '0',
  `tvmaze_id` int(10) unsigned NOT NULL DEFAULT '0',
  `imdb_id` char(9) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url_type` (`url`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC",
        'flush' => false,
    ],
];
