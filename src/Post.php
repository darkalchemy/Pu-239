<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Post.
 * @package Pu239
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
}
