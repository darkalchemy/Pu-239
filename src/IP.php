<?php

declare(strict_types = 1);

namespace Pu239;

use Psr\Container\ContainerInterface;

/**
 * Class IP.
 */
class IP
{
    protected $cache;
    protected $user;
    protected $container;

    /**
     * IP constructor.
     *
     * @param PeerCache          $cache
     * @param User               $user
     * @param ContainerInterface $c
     */
    public function __construct(PeerCache $cache, User $user, ContainerInterface $c)
    {
        $this->container = $c;
        $this->cache = $cache;
        $this->user = $user;
    }

    /**
     * @param int $userid
     *
     * @return array
     */
    public function get(int $userid): array
    {
        return $this->cache->get('ips_by_userid_' . $userid) ?: [];
    }

    /**
     * @param int    $userid
     * @param string $ip
     */
    protected function set(int $userid, string $ip): void
    {
        $ips = $this->get($userid);
        if (!in_array($ip, $ips)) {
            $ips[] = $ip;
            $this->cache->set('ips_by_userid_' . $userid, $ips);
        }
    }

    /**
     * @param int $userid
     *
     * @return string
     */
    public function get_current(int $userid): string
    {
        return $this->cache->get('current_ip_by_userid_' . $userid) ?: '';
    }

    /**
     * @param int    $userid
     * @param string $ip
     */
    protected function set_current(int $userid, string $ip): void
    {
        $this->cache->set('current_ip_by_userid_' . $userid, $ip);
    }

    /**
     * @param int $userid
     *
     * @return array
     */
    public function get_data_set(int $userid): array
    {
        return $this->cache->get('ip_dataset_by_userid_' . $userid) ?: [];
    }

    /**
     * @param int   $userid
     * @param array $dataset
     */
    public function set_data_set(int $userid, array $dataset): void
    {
        $this->cache->set('ip_dataset_by_userid_' . $userid, $dataset, 0);
    }

    /**
     * @param int    $userid
     * @param string $ip
     * @param string $type
     */
    protected function add_ip_to_user(int $userid, string $ip, string $type): void
    {
        $this->set($userid, $ip);
        $data = $this->get_data_set($userid);
        foreach ($data as $key => $value) {
            if ($type === $value['type'] && $ip === $value['ip']) {
                unset($data[$key]);
            }
        }
        $data[] = [
            'ip' => $ip,
            'type' => $type,
            'last_access' => TIME_NOW,
        ];
        $this->set_data_set($userid, array_values($data));
    }

    /**
     * @param string $ip
     *
     * @return array
     */
    protected function get_users_by_ip(string $ip): array
    {
        return $this->cache->get('users_by_ip_' . $ip) ?: [];
    }

    /**
     * @param int    $userid
     * @param string $ip
     */
    protected function add_user_to_ip(int $userid, string $ip): void
    {
        $user_ids = $this->get_users_by_ip($ip);
        if (!in_array($userid, $user_ids)) {
            $user_ids[] = $userid;
            $this->cache->set('users_by_ip_' . $ip, $user_ids, 0);
        }
    }

    /**
     * @return array
     */
    protected function get_all_ips(): array
    {
        return $this->cache->get('all_ips_') ?: [];
    }

    /**
     * @param string $ip
     */
    protected function add_ip_to_ips(string $ip): void
    {
        $ips = $this->get_all_ips();
        if (!in_array($ip, $ips)) {
            $ips[] = $ip;
            $this->cache->set('all_ips_', $ips, 0);
        }
    }

    /**
     * @param int    $userid
     * @param string $type
     * @param string $ip
     */
    public function insert(int $userid, string $type, string $ip): void
    {
        $this->add_ip_to_ips($ip);
        $this->add_ip_to_user($userid, $ip, $type);
        $this->add_user_to_ip($userid, $ip);
        $this->set_current($userid, $ip);
    }

    /**
     * @param int    $userid
     * @param string $ip
     * @param string $type
     */
    public function delete(int $userid, string $ip, string $type)
    {
        $data = $this->get_data_set($userid);
        foreach ($data as $key => $value) {
            if ($ip === $value['ip'] && $type === $value['type']) {
                unset($data[$key]);
            }
        }
        $this->set_data_set($userid, $data);
    }

    /**
     * @param string $ip
     *
     * @throws \Envms\FluentPDO\Exception
     *
     * @return array
     */
    public function getUsersFromIP(string $ip)
    {
        $data = $this->get_users_by_ip($ip);
        $users = [];
        foreach ($data as $userid) {
            $user = $this->user->getUserFromId($userid);
            if (!empty($user)) {
                $user['ip'] = $ip;
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * @param int $timestamp
     */
    public function delete_by_age(int $timestamp)
    {
        $all = $this->get_all_ips();
        $users = [];
        foreach ($all as $ip) {
            $users = array_merge($users, $this->get_users_by_ip($ip));
        }
        foreach ($users as $userid) {
            $data = $this->get_data_set($userid);
            foreach ($data as $key => $value) {
                if ($value['last_access'] <= $timestamp) {
                    unset($data[$key]);
                }
            }
            if (empty($data)) {
                $this->cache->deleteMulti([
                    'ip_dataset_by_userid_' . $userid,
                    'ips_by_userid_' . $userid,
                ]);
            } else {
                $this->set_data_set($userid, $data);
            }
        }
    }

    /**
     * @param string $ip
     *
     * @return int
     */
    public function get_user_count(string $ip): int
    {
        return count($this->get_users_by_ip($ip));
    }

    /**
     * @param int    $userid
     * @param int    $days
     * @param string $type
     *
     * @return int
     */
    public function get_ip_count(int $userid, int $days, string $type): int
    {
        $array = $this->get_data_set($userid);
        if ($days === 0 && $type === 'all') {
            return count($array);
        }
        foreach ($array as $key => $value) {
            if (($days > 0 && $value['last_access'] <= (TIME_NOW - (86400 * $days))) || ($type !== 'all' && $type != $value['type'])) {
                unset($array[$key]);
            }
        }
        return count($array);
    }

    /**
     * @return array
     */
    public function get_duplicates()
    {
        $ips = $this->get_all_ips();
        $data = $users = [];
        foreach ($ips as $ip) {
            $users[$ip] = $this->get_users_by_ip($ip);
        }
        array_multisort(array_map('count', $users), SORT_DESC, $users);
        foreach ($users as $key => $value) {
            $data[] = [
                'ip' => $key,
                'count' => count($value),
                'users' => $value,
            ];
        }
        return $data;
    }
}
