<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

class Person
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * Casino constructor.
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
     * @param string $name
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_person_by_name(string $name)
    {
        $result = $this->fluent->from('person')
            ->where('name = ?', $name)
            ->fetch();

        return $result;
    }

    /**
     * @param array $update
     * @param int   $id
     *
     * @throws Exception
     */
    public function update(array $update, int $id)
    {
        $this->fluent->update('person')
            ->set($update)
            ->where('id = ?', $id)
            ->execute();
    }
}
