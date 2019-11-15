<?php

declare(strict_types = 1);

namespace Pu239;

use PDOStatement;
use Psr\Container\ContainerInterface;

/**
 * Class Person.
 */
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
     * @return mixed|string
     */
    public function get_person_by_name(string $name)
    {
        try {
            return $this->fluent->from('person')
                                ->where('name = ?', $name)
                                ->fetch();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array  $update
     * @param string $imdb_id
     *
     * @return bool|int|PDOStatement|string
     */
    public function update_by_imdb(array $update, string $imdb_id)
    {
        try {
            return $this->fluent->update('person')
                                ->set($update)
                                ->where('imdb_id = ?', $imdb_id)
                                ->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array  $update
     * @param string $url
     *
     * @return bool|int|PDOStatement|string
     */
    public function update_by_url(array $update, string $url)
    {
        try {
            return $this->fluent->update('person')
                                ->set($update)
                                ->where('photo = ?', $url)
                                ->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
