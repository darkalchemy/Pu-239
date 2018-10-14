<?php

namespace DarkAlchemy\Pu239;

class HappyLog
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
     * @param int $userid
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_count(int $userid)
    {
        $count = $this->fluent->from('happylog')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->where('userid = ?', $userid)
            ->fetch('count');

        return $count;
    }

    /**
     * @param int   $userid
     * @param array $limit
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_by_userid(int $userid, array $limit)
    {
        $happy = $this->fluent->from('happylog AS h')
            ->select(null)
            ->select('h.userid')
            ->select('h.torrentid')
            ->select('h.date')
            ->select('h.multi')
            ->select('t.name')
            ->leftJoin('torrents AS t ON h.torrentid = t.id')
            ->where('h.userid = ?', $userid)
            ->orderBy('h.date DESC')
            ->limit('?, ?', $limit[0], $limit[1])
            ->fetchAll();

        return $happy;
    }
}
