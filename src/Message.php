<?php

namespace DarkAlchemy\Pu239;

class Message
{
    protected $cache;
    protected $fluent;
    protected $site_config;
    protected $limit;

    public function __construct()
    {
        global $fluent, $cache, $site_config;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
        $this->limit = $this->site_config['query_limit'];
    }

    /**
     * @param array $values
     *
     * @return bool|int
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function insert(array $values)
    {
        $count = floor($this->limit / max(array_map('count', $values)));
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
     * @return bool
     *
     * @throws \Envms\FluentPDO\Exception
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
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
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
     * @return bool|int|\PDOStatement
     *
     * @throws \Envms\FluentPDO\Exception
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
     * @throws \Envms\FluentPDO\Exception
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
     * @return bool|mixed
     *
     * @throws \Envms\FluentPDO\Exception
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
                ->select('COUNT(*) AS count')
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
     * @return int
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function delete_old_messages(int $dt)
    {
        $messages = $this->fluent->from('messages')
            ->select(null)
            ->select('receiver')
            ->where("saved != 'yes'")
            ->where('added <= ?', $dt);

        $this->fluent->delete('messages')
            ->where("saved != 'yes'")
            ->where('added <= ?', $dt)
            ->execute();

        $i = 0;
        foreach ($messages as $message) {
            ++$i;
            $this->cache->decrement('inbox_' . $message['receiver']);
        }

        return $i;
    }
}
