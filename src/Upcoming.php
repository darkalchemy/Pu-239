<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Literal;
use Envms\FluentPDO\Queries\Select;

class Upcoming
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
     * @param int $id
     *
     * @throws Exception
     */
    public function increment_hits(int $id)
    {
        $set = [
            'views' => new Literal('views + 1'),
        ];
        $this->fluent->update('upcoming')
                     ->set($set)
                     ->where('id = ?', $id)
                     ->execute();
    }

    /**
     * @param int $upcomingid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get(int $upcomingid)
    {
        $result = $this->fluent->from('upcoming AS r')
                               ->select('u.username')
                               ->select('c.name as cat')
                               ->select('c.image')
                               ->leftJoin('users AS u ON r.userid = u.id')
                               ->leftJoin('categories AS c ON r.category = c.id')
                               ->where('r.id = ?', $upcomingid)
                               ->fetch();

        return $result;
    }

    /**
     * @param bool $all
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_count(bool $all)
    {
        $count = $this->fluent->from('upcoming')
                              ->select(null)
                              ->select('COUNT(id) AS count');
        if (!$all) {
            $count->where('status != ?', 'uploaded');
        }
        $count = $count->fetch('count');

        return $count;
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
        $id = $this->fluent->insertInto('upcoming')
                           ->values($values)
                           ->execute();

        return $id;
    }

    /**
     * @param int $id
     *
     * @throws Exception
     */
    public function delete(int $id)
    {
        $this->fluent->deleteFrom('upcoming')
                     ->where('id = ?', $id)
                     ->execute();
    }

    /**
     * @param array $set
     * @param int   $upcomingid
     *
     * @throws Exception
     */
    public function update(array $set, int $upcomingid)
    {
        $this->fluent->update('upcoming')
                     ->set($set)
                     ->where('id = ?', $upcomingid)
                     ->execute();
    }

    /**
     * @param int    $limit
     * @param int    $offset
     * @param string $orderby
     * @param bool   $desc
     * @param bool   $all
     *
     * @throws Exception
     *
     * @return Select
     */
    public function get_all(int $limit, int $offset, string $orderby, bool $desc, bool $all)
    {
        $result = $this->fluent->from('upcoming AS r')
                               ->select('u.username')
                               ->select('c.name as cat')
                               ->select('c.image');
        if (!$all) {
            $result->where('r.status != ?', 'uploaded');
        }
        $result->leftJoin('users AS u ON r.userid = u.id')
               ->leftJoin('categories AS c ON r.category = c.id')
               ->limit($limit)
               ->offset($offset);
        if (!empty($orderby)) {
            $order = $orderby . ($desc ? ' DESC' : '');
            $result->orderBy($order);
        }
        $result->orderBy('r.userid')
               ->fetchAll();

        return $result;
    }
}
