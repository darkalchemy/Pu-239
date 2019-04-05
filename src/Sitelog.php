<?php

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Sitelog.
 */
class Sitelog
{
    protected $fluent;

    public function __construct()
    {
        global $fluent;

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
