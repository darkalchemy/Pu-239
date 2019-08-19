<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;

/**
 * Class BotReplies.
 */
class BotReplies
{
    protected $fluent;
    protected $cache;

    /**
     * BotReplies constructor.
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
     * @param array $values
     *
     * @return bool
     * @throws Exception
     *
     */
    public function insert(array $values)
    {
        $result = $this->fluent->insertInto('bot_replies')
                               ->ignore()
                               ->values($values)
                               ->execute();

        if (!$result) {
            return false;
        }
        $this->cache->delete('bot_replies_');

        return true;
    }

    /**
     * @param array $set
     * @param int   $id
     *
     * @return bool
     * @throws Exception
     *
     */
    public function update(array $set, int $id)
    {
        $result = $this->fluent->update('bot_replies')
                               ->set($set)
                               ->where('id = ?', $id)
                               ->execute();

        if (!$result) {
            return false;
        }
        $this->cache->delete('bot_replies_');

        return true;
    }

    /**
     * @return array|bool
     * @throws Exception
     */
    public function get_replies()
    {
        $result = $this->fluent->from('bot_replies')
                               ->fetchAll();
        if (is_array($result)) {
            return $result;
        }

        return [];
    }

    /**
     * @param int $id
     *
     * @return bool
     * @throws Exception
     *
     */
    public function delete(int $id)
    {
        $result = $this->fluent->deleteFrom('bot_replies')
                               ->where('id = ?', $id)
                               ->execute();
        $this->cache->delete('bot_replies_');

        return $result;
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws Exception
     *
     */
    public function get_by_id(int $id)
    {
        $reply = $this->fluent->from('bot_replies')
                              ->select(null)
                              ->select('reply')
                              ->where('id = ?', $id)
                              ->fetch('reply');

        return $reply;
    }

    /**
     * @return array|PDOStatement
     * @throws Exception
     *
     */
    public function get_approved_replies()
    {
        $results = $this->fluent->from('bot_replies AS r')
                                ->select(null)
                                ->select('r.reply')
                                ->select('t.phrase')
                                ->innerJoin('bot_triggers AS t ON r.phraseid = t.id')
                                ->where('r.approved_by > 0')
                                ->where('t.approved_by > 0')
                                ->fetchPairs('t.phrase', 'r.reply');

        return $results;
    }
}
