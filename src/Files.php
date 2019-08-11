<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

class Files
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
     * @param int $id
     *
     * @throws Exception
     */
    public function delete(int $id)
    {
        $this->fluent->deleteFrom('files')
                     ->where('torrent = ?', $id)
                     ->execute();
    }

    /**
     * @param array $values
     *
     * @throws Exception
     *
     * @return bool|int
     */
    public function insert(array $values)
    {
        $id = $this->fluent->insertInto('files')
            ->values($values)
            ->execute();

        return $id;
    }
}
