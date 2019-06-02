<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Forum.
 */
class Forum
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * FailedLogin constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param ContainerInterface $c
     */
    public function __construct(Cache $cache, Database $fluent, ContainerInterface $c)
    {
        $this->container = $c;
        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     * @param int $forum_id
     *
     * @throws Exception
     *
     * @return bool
     */
    public function delete(int $forum_id)
    {
        $result = $this->fluent->deleteFrom('forums')
                               ->where('id = ?', $forum_id)
                               ->execute();

        return $result;
    }

    /**
     * @param array $set
     * @param int   $forum_id
     *
     * @throws Exception
     *
     * @return bool|int|\PDOStatement
     */
    public function update(array $set, int $forum_id)
    {
        $result = $this->fluent->update('forums')
                               ->set($set)
                               ->where('id = ?', $forum_id)
                               ->execute();

        return $result;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     *
     * @return bool|int
     */
    public function add(array $values)
    {
        $id = $this->fluent->insertInto('forums')
                           ->values($values)
                           ->execute();

        return $id;
    }

    /**
     * @param int $forum_id
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_forum(int $forum_id)
    {
        $forum = $this->fluent->from('forums')
                              ->where('id = ?', $forum_id)
                              ->fetch();

        return $forum;
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function get_count()
    {
        $count = $this->fluent->from('forums')
                              ->select(null)
                              ->select('COUNT(id) AS count')
                              ->fetch('count');

        return $count;
    }
}
