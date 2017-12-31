<?php

$sql_updates = [
    [
        'id'    => 1,
        'info'  => 'Drop unnecessary column',
        'date'  => '06 Dec, 2017',
        'query' => 'ALTER TABLE `polls` DROP `starter_name`',
    ],
    [
        'id'    => 2,
        'info'  => 'Add missing FULLTEXT index for pm messages search',
        'date'  => '07 Dec, 2017',
        'query' => 'ALTER TABLE `messages` ADD FULLTEXT INDEX `ft_subject` (`subject`);',
    ],
    [
        'id'    => 3,
        'info'  => 'Add missing FULLTEXT index for pm messages search',
        'date'  => '07 Dec, 2017',
        'query' => 'ALTER TABLE `messages` ADD FULLTEXT INDEX `ft_msg` (`msg`);',
    ],
    [
        'id'    => 4,
        'info'  => 'Add missing FULLTEXT index for pm messages search',
        'date'  => '07 Dec, 2017',
        'query' => 'ALTER TABLE `messages` ADD FULLTEXT INDEX `ft_subject_msg` (`subject`, `msg`);',
    ],
    [
        'id'    => 5,
        'info'  => 'Update last_action default value',
        'date'  => '10 Dec, 2017',
        'query' => 'ALTER TABLE `peers` MODIFY `last_action` int(10) unsigned NOT NULL DEFAULT 0;',
    ],
    [
        'id'    => 6,
        'info'  => 'Update prev_action default value',
        'date'  => '10 Dec, 2017',
        'query' => 'ALTER TABLE `peers` MODIFY `prev_action` int(10) unsigned NOT NULL DEFAULT 0;',
    ],
    [
        'id'    => 7,
        'info'  => 'Add unique index on ips table',
        'date'  => '11 Dec, 2017',
        'query' => 'ALTER TABLE `ips` ADD UNIQUE INDEX `ip_userid`(`ip`, `userid`);',
    ],
    [
        'id'    => 8,
        'info'  => 'Remove unnecessary column',
        'date'  => '31 Dec, 2017',
        'query' => 'ALTER TABLE `database_updates` DROP COLUMN `info`;',
    ],
    [
        'id'    => 9,
        'info'  => 'Increase seedbonus limits',
        'date'  => '31 Dec, 2017',
        'query' => 'ALTER TABLE `users` MODIFY `seedbonus` decimal(20,1) NOT NULL DEFAULT 200;',
    ],
    [
        'id'    => 10,
        'info'  => 'Set initial invites for new users to 0',
        'date'  => '31 Dec, 2017',
        'query' => 'ALTER TABLE `users` MODIFY `invites` int(10) unsigned NOT NULL DEFAULT 0',
    ],
];
