<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class BotTriggers.
 */
class BotTriggers
{
    protected $fluent;
    protected $cache;

    /**
     * BotTriggers constructor.
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
        $result = $this->fluent->insertInto('bot_triggers')
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
     * @throws Exception
     *
     * @return bool
     */
    public function update(array $set, int $id)
    {
        $result = $this->fluent->update('bot_triggers')
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
     * @throws Exception
     *
     * @return array|bool
     */
    public function get_unapproved()
    {
        $result = $this->fluent->from('bot_triggers AS t')
                               ->leftJoin('bot_replies AS r ON t.id = r.phraseid')
                               ->whereOr('t.approved_by = 0')
                               ->whereOr('r.approved_by = 0')
                               ->groupBy('t.id')
                               ->groupBy('t.phrase')
                               ->orderBy('t.phrase')
                               ->fetchAll();

        return $result;
    }

    /**
     * @throws Exception
     *
     * @return array|bool
     */
    public function getall()
    {
        $result = $this->fluent->from('bot_triggers AS t')
                               ->orderBy('t.phrase')
                               ->fetchAll();

        return $result;
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
        $results = $this->fluent->deleteFrom('bot_triggers')
                                ->where('id = ?', $id)
                                ->execute();
        $this->cache->delete('bot_replies_');

        return $results;
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
        $trigger = $this->fluent->from('bot_triggers')
                                ->select(null)
                                ->select('phrase')
                                ->where('id = ?', $id)
                                ->fetch('phrase');

        return $trigger;
    }
}
