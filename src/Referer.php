<?php

namespace DarkAlchemy\Pu239;

class Referer
{
    protected $cache;
    protected $fluent;
    protected $site_config;

    public function __construct()
    {
        global $fluent, $cache, $site_config;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
    }

    /**
     * @param array $set
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function insert(array $values)
    {
        $id = $this->fluent->insertInto('referer')
            ->values($values)
            ->execute();

        return $id;
    }
}
