<?php

declare(strict_types = 1);

namespace Pu239;

use Psr\Container\ContainerInterface;

/**
 * Class Achievementlist.
 */
class Achievementlist
{
    protected $cache;
    protected $fluent;
    protected $env;
    protected $limit;
    protected $container;

    /**
     * Achievement constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param ContainerInterface $c
     */
    public function __construct(Cache $cache, Database $fluent, ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->limit = $this->env['db']['query_limit'];
    }

    /**
     * @param array $values
     *
     * @return bool|int|string
     */
    public function add(array $values)
    {
        try {
            return $this->fluent->insertInto('achievementlist')
                                ->values($values)
                                ->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
