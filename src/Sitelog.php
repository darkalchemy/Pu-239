<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Sitelog.
 * @package Pu239
 */
class Sitelog
{
    protected $fluent;

    /**
     * Sitelog constructor.
     *
     * @param Database $fluent
     */
    public function __construct(Database $fluent)
    {
        $this->fluent = $fluent;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function insert(array $values)
    {
        $this->fluent->insertInto('sitelog')
                     ->values($values)
                     ->execute();
    }
}
