<?php

namespace DarkAlchemy\Pu239;

class Image
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
     * @throws \Exception
     */
    public function insert(array $values)
    {
        $this->fluent->insertInto('images')
            ->values($values)
            ->ignore()
            ->execute();
    }
}
