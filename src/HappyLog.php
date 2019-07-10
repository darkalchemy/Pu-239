<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class HappyLog.
 */
class HappyLog
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * HappyLog constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param ContainerInterface $c
     */
    public function __construct(Cache $cache, Database $fluent, ContainerInterface $c)
    {
        $this->container = $c;
        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_count(int $userid)
    {
        $count = $this->fluent->from('happylog')
                              ->select(null)
                              ->select('COUNT(id) AS count')
                              ->where('userid = ?', $userid)
                              ->fetch('count');

        return $count;
    }

    /**
     * @param int   $userid
     * @param array $limit
     *
     * @throws Exception
     *
     * @return mixed
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
                              ->leftJoin('torrents AS t ON h.torrentid=t.id')
                              ->where('h.userid = ?', $userid)
                              ->orderBy('h.date DESC')
                              ->limit($limit['limit'])
                              ->offset($limit['offset'])
                              ->fetchAll();

        return $happy;
    }
}
