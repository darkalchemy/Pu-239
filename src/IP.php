<?php

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;

/**
 * Class IP.
 */
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
     * @param int $userid
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function get(int $userid)
    {
        $ips = $this->fluent->from('ips')
                            ->select('INET6_NTOA(ip) AS ip')
                            ->where('userid=?', $userid)
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
    public function insert_update(array $values, array $update, int $userid)
    {
        $type = $values['type'];
        $ttl = $type === 'announce' ? 60 : 300;
        $ip = $values['ip'];
        $cached_ip = $this->cache->get($type . '_ip_' . $userid . '_' . md5(inet_pton($ip)));
        if ($cached_ip === false || is_null($cached_ip)) {
            $id = $this->fluent->from('ips')
                               ->select(null)
                               ->select('id')
                               ->where('INET6_NTOA(ip) = ?', $ip)
                               ->where('userid=?', $userid)
                               ->where('type = ?', $type)
                               ->fetch('id');

            if (empty($id)) {
                $values['ip'] = inet_pton($ip);
                $this->insert($values, $userid);
            } else {
                $this->set($update, $id);
            }
            $this->cache->set($type . '_ip_' . $userid . '_' . md5(inet_pton($ip)), inet_pton($ip), $ttl);
        }
    }

    /**
     * @param array $values
     * @param int   $userid
     *
     * @throws Exception
     */
    public function insert(array $values, int $userid)
    {
        $this->fluent->insertInto('ips')
                     ->values($values)
                     ->ignore()
                     ->execute();

        $this->cache->delete('ip_history_' . $userid);
    }

    /**
     * @param array $set
     * @param int   $id
     *
     * @return bool|int|PDOStatement
     *
     * @throws Exception
     */
    public function set(array $set, int $id)
    {
        $result = $this->fluent->update('ips')
                               ->set($set)
                               ->where('id=?', $id)
                               ->execute();

        return $result;
    }

    /**
     * @param int $id
     *
     * @throws Exception
     */
    public function delete(int $id)
    {
        $this->fluent->delete('ips')
                     ->where('id=?', $id)
                     ->execute();
    }
}
