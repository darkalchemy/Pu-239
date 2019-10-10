<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Post.
 */
class Post
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * Post constructor.
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
     * @param array $values
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function insert(array $values)
    {
        $id = $this->fluent->insertInto('posts')
                           ->values($values)
                           ->execute();

        return (int) $id;
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
                     ->where('id = ?', $id)
                     ->where('topic_id = ?', $topic_id)
                     ->execute();
    }

    /**
     * @param int $userid
     *
     * @return mixed|string
     */
    public function get_user_count(int $userid)
    {
        try {
            return $this->fluent->from('posts')
                                ->select(null)
                                ->select('COUNT(id) AS count')
                                ->where('user_id = ?', $userid)
                                ->fetch('count');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
