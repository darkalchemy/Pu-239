<?php

namespace DarkAlchemy\Pu239;

class IP
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
     * @param array $values
     * @param array $update
     * @param       $userid
     */
    public function insert(array $values, array $update, $userid)
    {
        $this->fluent->insertInto('ips', $values)
            ->onDuplicateKeyUpdate($update)
            ->execute();

        $this->cache->delete('ip_history_' . $userid);
    }
}
