<?php

declare(strict_types = 1);

namespace Pu239;

use PDOStatement;

/**
 * Class Wiki.
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
     * @return string
     */
    public function get_last()
    {
        try {
            return $this->fluent->from('wiki')
                                ->select(null)
                                ->select('name')
                                ->orderBy('id DESC')
                                ->limit(1)
                                ->fetch('name');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array $values
     *
     * @return string
     */
    public function add(array $values)
    {
        try {
            return $this->fluent->insertInto('wiki')
                                ->values($values)
                                ->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array $update
     * @param int   $id
     *
     * @return bool|int|PDOStatement|string
     */
    public function update(array $update, int $id)
    {
        try {
            return $this->fluent->update('wiki')
                                ->set($update)
                                ->where('id = ?', $id)
                                ->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function get_by_name(string $name)
    {
        try {
            return $this->fluent->from('wiki')
                                ->where('name LIKE ?', "%{$name}%")
                                ->orderBy('GREATEST(time, lastedit) DESC')
                                ->fetchAll();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param int $id
     *
     * @return mixed|string
     */
    public function get_by_id(int $id)
    {
        try {
            return $this->fluent->from('wiki')
                                ->where('id = ?', $id)
                                ->fetch();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return string
     */
    public function get_latest()
    {
        try {
            return $this->fluent->from('wiki')
                                ->orderBy('GREATEST(time, lastedit) DESC')
                                ->limit(25)
                                ->fetchAll();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param int $id
     *
     * @return bool|string
     */
    public function delete(int $id)
    {
        try {
            return $this->fluent->deleteFrom('wiki')
                                ->where('id = ?', $id)
                                ->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
