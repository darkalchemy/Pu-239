<?php

namespace Pu239;

use Envms\FluentPDO\Exception;

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

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function insert(array $values)
    {
        $this->fluent->insertInto('bonuslog')
            ->values($values)
            ->execute();
    }
}
