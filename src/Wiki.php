<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Wiki
 * @package Pu239
 */
class Wiki
{
    protected $fluent;

    /**
     * Sitelog constructor.
     *
     * @param Database $fluent
     */
    public function __construct(Database $fluent)
    {
        $this->fluent = $fluent;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function get_last()
    {
        $name = $this->fluent->from('wiki')
                             ->select(null)
                             ->select('name')
                             ->orderBy('id DESC')
                             ->limit(1)
                             ->fetch('name');

        return $name;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function add(array $values)
    {
        $this->fluent->insertInto('wiki')
                     ->values($values)
                     ->execute();
    }

    /**
     * @param array $update
     * @param int   $id
     *
     * @throws Exception
     */
    public function update(array $update, int $id)
    {
        $this->fluent->update('wiki')
                     ->set($update)
                     ->where('id = ?', $id)
                     ->execute();
    }

    /**
     * @param string $name
     *
     * @return array|bool
     * @throws Exception
     */
    public function get_by_name(string $name)
    {
        $results = $this->fluent->from('wiki')
                                ->where('name LIKE ?', "%{$name}%")
                                ->orderBy('GREATEST(time, lastedit) DESC')
                                ->fetchAll();

        return $results;
    }

    /**
     * @param int $id
     *
     * @return mixed
     * @throws Exception
     */
    public function get_by_id(int $id)
    {
        $result = $this->fluent->from('wiki')
                               ->where('id = ?', $id)
                               ->fetch();

        return $result;
    }

    /**
     * @return array|bool
     * @throws Exception
     */
    public function get_latest()
    {
        $results = $this->fluent->from('wiki')
                                ->orderBy('GREATEST(time, lastedit) DESC')
                                ->limit(25)
                                ->fetchAll();

        return $results;
    }
}