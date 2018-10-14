<?php

namespace DarkAlchemy\Pu239;

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
     * @param array $set
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function insert(array $set)
    {
        $id = $this->fluent->update('posts')
            ->set($set)
            ->execute();

        return $id;
    }

    /**
     * @param int $id
     * @param int $topic_id
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function delete(int $id, int $topic_id)
    {
        $this->fluent->delete('posts')
            ->where('id = ?', $id)
            ->where('topic_id = ?', $topic_id)
            ->execute();
    }
}
