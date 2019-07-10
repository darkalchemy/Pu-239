<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Referrer.
 */
class Referrer
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * Referrer constructor.
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
        $id = $this->fluent->insertInto('referrers')
                           ->values($values)
                           ->execute();

        return $id;
    }
}
