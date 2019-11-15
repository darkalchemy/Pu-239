<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Searchcloud.
 */
class Searchcloud
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * Searchcloud constructor.
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
     *
     * @param array $limit
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get(array $limit)
    {
        $search = $this->fluent->from('searchcloud')
                               ->orderBy('howmuch DESC')
                               ->limit($limit['limit'])
                               ->offset($limit['offset'])
                               ->fetchAll();

        return $search;
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function get_count()
    {
        $search = $this->fluent->from('searchcloud')
                               ->select(null)
                               ->select('COUNT(id) AS count')
                               ->fetch('count');

        return $search;
    }

    /**
     * @param array $terms
     *
     * @throws Exception
     */
    public function delete(array $terms)
    {
        foreach ($terms as $term) {
            $this->fluent->deleteFrom('searchcloud')
                         ->where('id = ?', $term)
                         ->execute();
        }
        $this->cache->delete('searchcloud_');
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws Exception
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('searchcloud', $values)
                     ->onDuplicateKeyUpdate($update)
                     ->execute();
    }
}
