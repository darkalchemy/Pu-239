<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Topics.
 */
class Topic
{
    protected $cache;
    protected $fluent;

    /**
     * FailedLogin constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     */
    public function __construct(Cache $cache, Database $fluent)
    {
        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     *
     * @param int $topic_id
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get_forum_id_from_topic_id(int $topic_id)
    {
        $forum_id = $this->cache->get('forum_id_from_topic_id_' . $topic_id);
        if ($forum_id === false || is_null($forum_id)) {
            $forum_id = $this->fluent->from('topics')
                                     ->select(null)
                                     ->select('forum_id')
                                     ->where('id = ?', $topic_id)
                                     ->fetch('forum_id');

            $this->cache->set('forum_id_from_topic_id_' . $topic_id, $forum_id, 86400);
        }

        return $forum_id;
    }

    /**
     * @param int $userid
     *
     * @return mixed|string
     */
    public function get_user_count(int $userid)
    {
        try {
            return $this->fluent->from('topics')
                                ->select(null)
                                ->select('COUNT(id) AS count')
                                ->where('user_id = ?', $userid)
                                ->fetch('count');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
