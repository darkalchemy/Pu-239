<?php

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;

/**
 * Class Poll.
 */
class Poll
{
    protected $fluent;
    protected $cache;

    public function __construct()
    {
        global $fluent, $cache;

        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     * @param int $poll_id
     *
     * @throws Exception
     */
    public function delete(int $poll_id)
    {
        $this->fluent->deleteFrom('polls')
                     ->where('pid=?', $poll_id)
                     ->execute();

        $this->cache->delete('poll_' . $poll_id);
        $this->cache->delete('polls_');
    }

    /**
     * @param array $set
     * @param int   $poll_id
     *
     * @return bool|int|PDOStatement
     *
     * @throws Exception
     */
    public function update(array $set, int $poll_id)
    {
        $result = $this->fluent->update('polls')
                               ->set($set)
                               ->where('pid=?', $poll_id)
                               ->execute();
        $this->cache->delete('poll_' . $poll_id);
        $this->cache->delete('polls_');

        return $result;
    }

    /**
     * @param array $values
     *
     * @return bool|int
     *
     * @throws Exception
     */
    public function insert(array $values)
    {
        $poll_id = $this->fluent->insertInto('polls')
                                ->values($values)
                                ->execute();

        $this->cache->delete('polls_');

        return $poll_id;
    }

    /**
     * @param int $poll_id
     *
     * @return bool|mixed
     *
     * @throws Exception
     */
    public function get(int $poll_id)
    {
        $poll = $this->cache->get('poll_' . $poll_id);
        if ($poll === false || is_null($poll)) {
            $poll = $this->fluent->from('polls')
                                 ->where('pid=?', $poll_id)
                                 ->fetch();
            $this->cache->set('polls_' . $poll_id, $poll, 86400);
        }

        return $poll;
    }

    /**
     * @param int $limit
     *
     * @return array|bool|mixed
     *
     * @throws Exception
     */
    public function get_all(int $limit = 0)
    {
        $polls = $this->cache->get('polls_');
        if ($polls === false || is_null($polls)) {
            $polls = $this->fluent->from('polls')
                                  ->orderBy('start_date DESC')
                                  ->fetchAll();

            if (!empty($polls)) {
                $this->cache->set('polls_', $polls, 86400);
            } else {
                $this->cache->set('polls_', [], 86400);
            }
        }

        if (!empty($polls) && $limit > 0) {
            return $polls[0];
        }

        return $polls;
    }
}
