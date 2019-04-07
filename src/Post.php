<?php

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Post.
 */
class Post
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
     * @param array $values
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function insert(array $values)
    {
        $id = $this->fluent->insertInto('posts')
                           ->values($values)
                           ->execute();

        return $id;
    }

    /**
     * @param int $id
     * @param int $topic_id
     *
     * @throws Exception
     */
    public function delete(int $id, int $topic_id)
    {
        $this->fluent->delete('posts')
                     ->where('id=?', $id)
                     ->where('topic_id=?', $topic_id)
                     ->execute();
    }
}
