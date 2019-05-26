<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;

/**
 * Class Snatched.
 */
class Snatched
{
    protected $cache;
    protected $fluent;

    /**
     * Snatched constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     */
    public function __construct(Cache $cache, Database $fluent)
    {
        $this->cache = $cache;
        $this->fluent = $fluent;
    }

    /**
     * @param int $userid
     * @param int $tid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_snatched(int $userid, int $tid)
    {
        $snatches = $this->cache->get("snatches_{$userid}_{$tid}");
        if ($snatches === false || is_null($snatches)) {
            $snatches = $this->fluent->from('snatched')
                                     ->select(null)
                                     ->select('id')
                                     ->select('seedtime')
                                     ->select('leechtime')
                                     ->select('uploaded')
                                     ->select('downloaded')
                                     ->select('real_uploaded')
                                     ->select('real_downloaded')
                                     ->select('finished')
                                     ->select('timesann')
                                     ->select('start_date AS start_snatch')
                                     ->where('torrentid = ?', $tid)
                                     ->where('userid = ?', $userid)
                                     ->fetch();
            if (!empty($snatches)) {
                $this->cache->set("snatches_{$userid}_{$tid}", $snatches, 3600);
            }
        }

        return $snatches;
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws Exception
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('snatched', $values)
                     ->onDuplicateKeyUpdate($update)
                     ->execute();
    }

    /**
     * @param array $set
     * @param int   $tid
     * @param int   $userid
     *
     * @throws Exception
     * @throws UnbegunTransaction
     */
    public function update(array $set, int $tid, int $userid)
    {
        $this->fluent->update('snatched')
                     ->set($set)
                     ->where('torrentid = ?', $tid)
                     ->where('userid = ?', $userid)
                     ->execute();

        $this->cache->update_row("snatches_{$userid}_{$tid}", $set);
    }

    /**
     * @param int $dt
     *
     * @throws Exception
     */
    public function delete_stale(int $dt)
    {
        $this->fluent->delete('snatched')
                     ->where('last_action < ?', $dt)
                     ->execute();
    }
}
