<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;
use Psr\Container\ContainerInterface;

/**
 * Class Ban.
 * @package Pu239
 */
class Ban
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * Ban constructor.
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
     * @param string $ip
     *
     * @throws Exception
     *
     * @return array|PDOStatement
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
     * @throws Exception
     *
     * @return mixed
     */
    public function get_count(string $ip)
    {
        $count = $this->fluent->from('bans')
                              ->select(null)
                              ->select('COUNT(id) AS count')
                              ->where('? >= first', inet_pton($ip))
                              ->where('? <= last', inet_pton($ip))
                              ->fetch('count');

        return $count;
    }

    /**
     * @param string $ip
     *
     * @throws Exception
     *
     * @return bool
     */
    public function check_bans(string $ip)
    {
        if (empty($ip)) {
            return false;
        }
        $key = 'bans_' . $ip;
        $this->cache->delete($key);
        $ban = $this->cache->get($key);
        if (($ban === false || is_null($ban)) && $ban != 0) {
            $ban = $this->fluent->from('bans')
                                ->select(null)
                                ->select('comment')
                                ->where('? >= first', inet_pton($ip))
                                ->where('? <= last', inet_pton($ip))
                                ->limit(1)
                                ->fetch('comment');

            if (!empty($ban)) {
                $this->cache->set($key, $ban, 86400);

                return true;
            } else {
                $this->cache->set($key, 0, 86400);
            }
        }

        return false;
    }
}
