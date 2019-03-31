<?php

namespace Pu239;

/**
 * Class Usersachiev.
 */
class Usersachiev
{
    protected $cache;
    protected $fluent;
    protected $site_config;
    protected $limit;

    public function __construct()
    {
        global $fluent, $cache, $site_config;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
        $this->limit = $this->site_config['query_limit'];
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function insert(array $values, array $update)
    {
        $count = floor($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $this->fluent->insertInto('usersachiev', $t)
                         ->onDuplicateKeyUpdate($update)
                         ->execute();
        }
    }

    /**
     * @param array $values
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function add(array $values)
    {
        $this->fluent->insertInto('usersachiev')
                     ->values($values)
                     ->execute();
    }
}
