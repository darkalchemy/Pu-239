<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;
use Psr\Container\ContainerInterface;

/**
 * Class Message.
 */
class Message
{
    protected $cache;
    protected $fluent;
    protected $env;
    protected $limit;
    protected $container;
    protected $site_config;

    /**
     * Message constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param Settings           $settings
     * @param ContainerInterface $c
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, Settings $settings, ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        $this->site_config = $settings->get_settings();
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->limit = $this->env['db']['query_limit'];
    }

    /**
     * @param array $values
     *
     * @throws Exception
     *
     * @return bool|int
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return false;
        }
        $count = (int) ($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $result = $this->fluent->insertInto('messages')
                                   ->values($t)
                                   ->execute();
        }

        if (count($values) > $count) {
            foreach ($values as $user) {
                $ids[] = 'inbox_' . $user['receiver'];
            }

            if (!empty($ids)) {
                $this->cache->deleteMulti($ids);
            }
        } else {
            foreach ($values as $user) {
                $this->cache->increment('inbox_' . $user['receiver']);
            }
        }

        if (!empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * @param int $id
     * @param int $userid
     *
     * @throws Exception
     *
     * @return bool
     */
    public function delete(int $id, int $userid)
    {
        $result = $this->fluent->delete('messages')
                               ->where('id = ?', $id)
                               ->execute();

        $this->cache->decrement('inbox_' . $userid);

        return $result;
    }

    /**
     * @param int $id
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_by_id(int $id)
    {
        $message = $this->fluent->from('messages')
                                ->where('id = ?', $id)
                                ->fetch();

        return $message;
    }

    /**
     * @param array $set
     * @param int   $id
     *
     * @throws Exception
     *
     * @return bool|int|PDOStatement
     */
    public function update(array $set, int $id)
    {
        $result = $this->fluent->update('messages')
                               ->set($set)
                               ->where('id = ?', $id)
                               ->execute();

        return $result;
    }

    /**
     * @param array $set
     * @param int   $location
     * @param int   $userid
     *
     * @throws Exception
     */
    public function update_location(array $set, int $location, int $userid)
    {
        $this->fluent->update('messages')
                     ->set($set)
                     ->where('location = ?', $location)
                     ->where('receiver = ?', $userid)
                     ->execute();
    }

    /**
     * @param int $userid
     * @param int $location
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get_count(int $userid, int $location = 1)
    {
        $pmCount = false;
        if ($location === 1) {
            $pmCount = $this->cache->get('inbox_' . $userid);
        }
        if ($pmCount === false || is_null($pmCount)) {
            $pmCount = $this->fluent->from('messages')
                                    ->select(null)
                                    ->select('COUNT(id) AS count')
                                    ->where('receiver = ?', $userid)
                                    ->where('unread = ?', 'yes')
                                    ->where('location = ?', $location)
                                    ->fetch('count');
        }
        if ($location === 1) {
            $this->cache->set('inbox_' . $userid, $pmCount, $this->site_config['expires']['unread']);
        }

        return $pmCount;
    }

    /**
     * @param int $dt
     *
     * @throws Exception
     *
     * @return int
     */
    public function delete_old_messages(int $dt)
    {
        $messages_1 = $this->fluent->from('messages')
                                   ->select(null)
                                   ->select('receiver')
                                   ->where('location = 0')
                                   ->where('added <= ?', $dt);

        $this->fluent->delete('messages')
                     ->where('location = 0')
                     ->where('added <= ?', $dt)
                     ->execute();

        $messages_2 = $this->fluent->from('messages')
                                   ->select(null)
                                   ->select('receiver')
                                   ->where('location = 1')
                                   ->where('added <= ?', $dt);

        $this->fluent->delete('messages')
                     ->where('location = 1')
                     ->where('added <= ?', $dt)
                     ->execute();

        $i = 0;
        foreach ($messages_1 as $message) {
            ++$i;
            $this->cache->delete('inbox_' . $message['receiver']);
        }
        foreach ($messages_2 as $message) {
            ++$i;
            $this->cache->delete('inbox_' . $message['receiver']);
        }

        return $i;
    }
}
