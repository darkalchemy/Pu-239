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

    public function insert(array $values)
    {
        $query = $this->fluent->insertInto('images')
            ->values($values)
            ->ignore()
            ->execute();
    }
}
