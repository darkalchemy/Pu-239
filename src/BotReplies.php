<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

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
     * @throws Exception
     *
     * @return bool
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

        return true;
    }

    /**
     * @param array $set
     * @param int   $id
     * @param int   $userid
     *
     * @throws Exception
     *
     * @return bool
     */
    public function update(array $set, int $id, int $userid)
    {
        $result = $this->fluent->update('bot_replies')
                               ->set($set)
                               ->where('id = ?', $id)
                               ->where('added_by != ?', $userid)
                               ->execute();

        if (!$result) {
            return false;
        }
        $this->cache->delete('bot_triggers_');
        $this->cache->delete('bot_replies_');

        return true;
    }

    /**
     * @param bool $approved
     *
     * @throws Exception
     *
     * @return array
     */
    public function get_replies(bool $approved)
    {
        $result = $this->fluent->from('bot_replies');
        if ($approved) {
            $result = $result->where('approved_by > 0');
        } else {
            $result = $result->where('approved_by = 0');
        }
        $result = $result->fetchAll();
        if (is_array($result)) {
            return $result;
        }

        return [];
    }

    /**
     * @param int $id
     *
     * @throws Exception
     *
     * @return bool
     */
    public function delete(int $id)
    {
        return $this->fluent->deleteFrom('bot_replies')
                     ->where('id = ?', $id)
                     ->execute();
    }
}
