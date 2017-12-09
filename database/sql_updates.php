<?php

$sql_updates = [
    [
        'id' => 1,
        'info' => 'Drop Column<br>will fail if column already dropped',
        'date' => '06 Dec, 2017',
        'query' => 'ALTER TABLE `polls` DROP `starter_name`'
    ],
    [
        'id' => 2,
        'info' => 'Add missing FULLTEXT index for pm messages search',
        'date' => '07 Dec, 2017',
        'query' => 'ALTER TABLE `messages` ADD FULLTEXT INDEX `ft_subject` (`subject`);'
    ],
    [
        'id' => 3,
        'info' => 'Add missing FULLTEXT index for pm messages search',
        'date' => '07 Dec, 2017',
        'query' => 'ALTER TABLE `messages` ADD FULLTEXT INDEX `ft_msg` (`msg`);'
    ],
    [
        'id' => 4,
        'info' => 'Add missing FULLTEXT index for pm messages search',
        'date' => '07 Dec, 2017',
        'query' => 'ALTER TABLE `messages` ADD FULLTEXT INDEX `ft_subject_msg` (`subject`, `msg`);'
    ],
];
