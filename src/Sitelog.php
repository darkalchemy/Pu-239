<?php

namespace Pu239;

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

    public function insert(array $values)
    {
        $this->fluent->insertInto('sitelog')
                     ->values($values)
                     ->execute();
    }
}
