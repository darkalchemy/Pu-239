<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

class Notify
{
    protected $fluent;
    protected $cache;

    /**
     * Sitelog constructor.
     *
     * @param Database $fluent
     * @param Cache    $cache
     */
    public function __construct(Database $fluent, Cache $cache)
    {
        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     * @param int $upcomingid
     * @param int $userid
     *
     * @throws Exception
     *
     * @return bool
     */
    public function delete(int $upcomingid, int $userid)
    {
        $result = $this->fluent->deleteFrom('notify')
                               ->where('id = ?', $upcomingid)
                               ->where('userid = ?', $userid)
                               ->execute();
        $this->delete_cache($upcomingid, $userid);

        return $result;
    }

    /**
     * @param int $upcomingid
     * @param int $userid
     *
     * @throws Exception
     *
     * @return bool|int
     */
    public function add(int $upcomingid, int $userid)
    {
        $values = [
            'recipeid' => $upcomingid, 'userid' => $userid,
        ];
        $id = $this->fluent->insertInto('upcoming')
            ->values($values)
            ->ignore()
            ->execute();
        $this->delete_cache($upcomingid, $userid);

        return $id;
    }

    public function get(int $upcomingid)
    {
        $count = $this->cache->get('notify_requests_' . $upcomingid);
        if ($count === false || is_null($count)) {
            $count = $this->fluent->from('notify')
                                  ->select(null)
                                  ->select('COUNT(id) AS count')
                                  ->where('upcomingid = ?', $upcomingid)
                                  ->fetch('count');

            $this->cache->set('notify_requests_' . $upcomingid, $count, 86400);
        }

        return $count;
    }

    public function delete_cache(int $upcomingid, int $userid)
    {
        $this->cache->delete('usernotify_' . $userid);
        $this->cache->delete('usernotifies_' . $userid);
        $this->cache->delete('notify_requests_' . $upcomingid);
    }
}
