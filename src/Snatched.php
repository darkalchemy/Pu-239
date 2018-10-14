<?php

namespace DarkAlchemy\Pu239;

class Snatched
{
    protected $cache;
    protected $fluent;
    protected $config;

    public function __construct()
    {
        global $cache, $fluent, $site_config;

        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->config = $site_config;
    }

    /**
     * @param int $userid
     * @param int $tid
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_snatched(int $userid, int $tid)
    {
        $snatches = $this->fluent->from('snatched')
            ->select(null)
            ->select('seedtime')
            ->select('leechtime')
            ->select('uploaded')
            ->select('downloaded')
            ->select('finished')
            ->select('timesann')
            ->select('start_date AS start_snatch')
            ->where('torrentid = ?', $tid)
            ->where('userid = ?', $userid)
            ->fetch();

        return $snatches;
    }

    /**
     * @param array $values
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function insert(array $values)
    {
        $this->fluent->insertInto('snatched')
            ->values($values)
            ->execute();
    }

    /**
     * @param array $set
     * @param int   $tid
     * @param int   $userid
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function update(array $set, int $tid, int $userid)
    {
        $this->fluent->update('snatched')
            ->set($set)
            ->where('torrentid = ?', $tid)
            ->where('userid = ?', $userid)
            ->execute();
    }

    /**
     * @param int $dt
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function delete_stale(int $dt)
    {
        $this->fluent->delete('snatched')
            ->where('last_action < ?', $dt)
            ->execute();
    }
}
