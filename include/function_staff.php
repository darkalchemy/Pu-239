<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;

/**
 * @param $text
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return bool|int
 */
function write_info($text)
{
    $values = [
        'added' => TIME_NOW,
        'txt' => $text,
    ];
    global $container;
    $fluent = $container->get(Database::class);
    $id = $fluent->insertInto('infolog')
                 ->values($values)
                 ->execute();

    return $id;
}
