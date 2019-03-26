<?php

namespace Pu239;

/**
 * Class Bonuslog.
 */
class Bonuslog
{
    protected $fluent;

    public function __construct()
    {
        global $fluent;

        $this->fluent = $fluent;
    }

    public function insert(array $values)
    {
        $this->fluent->insertInto('bonuslog')
            ->values($values)
            ->execute();
    }
}
