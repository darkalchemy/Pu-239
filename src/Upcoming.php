<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Literal;

/**
 * Class Upcoming.
 */
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
     * @return array|bool
     */
    public function get_all(int $limit, int $offset, string $orderby, bool $desc, bool $all)
    {
        $results = $this->fluent->from('upcoming AS r')
                               ->select('u.username')
                               ->select('c.name as cat')
                               ->select('c.image')
                               ->select('p.name AS parent_name')
                               ->leftJoin('users AS u ON r.userid = u.id')
                               ->leftJoin('categories AS c ON r.category = c.id')
                               ->leftJoin('categories AS p ON c.parent_id = p.id')
                               ->limit($limit)
                               ->offset($offset);
        if (!$all) {
            $results->where('r.status != ?', 'uploaded');
        }
        if (!empty($orderby)) {
            $order = $orderby . ($desc ? ' DESC' : '');
            $results->orderBy($order);
        }
        $results = $results->orderBy('r.userid');
        $cooker = [];
        foreach ($results as $result) {
            if (!empty($result['parent_name'])) {
                $result['cat'] = $result['parent_name'] . '::' . $result['cat'];
            }
            $cooker[] = $result;
        }

        return $cooker;
    }
}
