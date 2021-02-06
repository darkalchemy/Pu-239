<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;
use Psr\Container\ContainerInterface;

/**
 * Class Ban.
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
     *
     * @param string $ip
     *
     * @throws Exception
     *
     * @return array|PDOStatement
     *
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
     *
     * @param string $ip
     *
     * @throws Exception
     *
     * @return mixed
     *
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
     *
     * @param string $ip
     *
     * @throws Exception
     *
     * @return bool
     *
     */
    public function check_bans(string $ip)
    {
        if (empty($ip)) {
            return false;
        }
        $key = 'bans_' . $ip;
        $ban = $this->cache->get($key);
        if (($ban === false || is_null($ban))) {
            $ban = $this->fluent->from('bans')
                ->select(null)
                ->select('comment')
                ->where('? = INET6_NTOA(first)', $ip)
                ->where('? = INET6_NTOA(last)', $ip)
                ->limit(1)
                ->fetch('comment');

            if (!empty($ban)) {
                $this->cache->set($key, $ban, 60);

                return true;
            } else {
                $this->cache->set($key, 0, 60);
            }
        }

        return false;
    }

    /**
     * @param string $ip
     * @param int    $userid
     * @param string $comment
     *
     * @throws Exception
     *
     * @return false
     */
    public function add_ban(string $ip, int $userid, string $comment)
    {
        if (empty($ip)) {
            return false;
        }
        $this->cache->set('bans_' . $ip, 0, 60);
        $values = [
            'first' => inet_pton($ip),
            'last' => inet_pton($ip),
            'addedby' => $userid,
            'comment' => $comment,
            'added' => TIME_NOW,
        ];
        $this->fluent->insertInto('bans')
            ->values($values)
            ->execute();
    }
}
