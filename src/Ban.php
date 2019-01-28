<?php

namespace Pu239;

/**
 * Class Ban.
 */
class Ban
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
     * @param string $ip
     *
     * @return array|\PDOStatement
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_range(string $ip)
    {
        $bans = $this->fluent->from('bans')
            ->select('INET6_NTOA(first) AS first')
            ->select('INET6_NTOA(last) AS last')
            ->where('? >= first', inet_pton($ip))
            ->where('? <= last', inet_pton($ip))
            ->fetchAll();

        return $bans;
    }

    /**
     * @param string $ip
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_count(string $ip)
    {
        $count = $this->fluent->from('bans')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('? >= first', inet_pton($ip))
            ->where('? <= last', inet_pton($ip))
            ->fetch('count');

        return $count;
    }
}
