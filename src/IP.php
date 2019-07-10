<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class IP.
 */
class IP
{
    protected $cache;
    protected $fluent;
    protected $user;
    protected $container;

    /**
     * IP constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param User               $user
     * @param ContainerInterface $c
     */
    public function __construct(Cache $cache, Database $fluent, User $user, ContainerInterface $c)
    {
        $this->container = $c;
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->user = $user;
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get(int $userid)
    {
        $ips = $this->fluent->from('ips')
                            ->select('INET6_NTOA(ip) AS ip')
                            ->where('userid = ?', $userid)
                            ->groupBy('ip')
                            ->groupBy('id')
                            ->fetchAll();

        return $ips;
    }

    /**
     * @param array $values
     * @param array $update
     * @param int   $userid
     *
     * @throws Exception
     */
    public function insert(array $values, array $update, int $userid)
    {
        $type = $values['type'];
        $ttl = $type === 'announce' ? 60 : 300;
        $ip = $values['ip'];
        $cached_ip = $this->cache->get($type . '_ip_' . $userid . '_' . $ip);
        if ($cached_ip === false || is_null($cached_ip)) {
            $values['ip'] = inet_pton($ip);
            $this->fluent->insertInto('ips', $values)
                         ->onDuplicateKeyUpdate($update)
                         ->execute();
            $this->cache->set($type . '_ip_' . $userid . '_' . $ip, $ip, $ttl);
        }
    }

    /**
     * @param int $id
     *
     * @throws Exception
     */
    public function delete(int $id)
    {
        $this->fluent->delete('ips')
                     ->where('id = ?', $id)
                     ->execute();
    }

    /**
     * @param string $ip
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function getUsersFromIP(string $ip)
    {
        $ips = $this->fluent->from('ips AS i')
                            ->select(null)
                            ->select('i.type')
                            ->select('u.id AS userid')
                            ->select('INET6_NTOA(i.ip) AS ip')
                            ->innerJoin('users AS u ON i.userid = u.id')
                            ->where('i.ip = ?', inet_pton($ip))
                            ->orderBy('u.id')
                            ->fetchAll();

        $users = [];
        foreach ($ips as $select) {
            $user = $this->user->getUserFromId($select['userid']);
            if (!empty($user)) {
                $user['ip'] = $ip;
                $user['type'] = $select['type'];
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * @param int $timestamp
     *
     * @throws Exception
     */
    public function delete_by_age(int $timestamp)
    {
        $this->fluent->deleteFrom('ips')
                     ->where('last_access < ?', $timestamp)
                     ->execute();
    }

    /**
     * @param int    $userid
     * @param int    $days
     * @param string $type
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_ip_count(int $userid, int $days, string $type)
    {
        $count = $this->fluent->from('ips')
                              ->select(null)
                              ->select('COUNT(ip) AS count')
                              ->where('type = ?', $type)
                              ->where('userid = ?', $userid)
                              ->where('last_access >= NOW() - INTERVAL ? DAY', $days)
                              ->fetch('count');

        return $count;
    }
}
