<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Literal;
use Envms\FluentPDO\Queries\Delete;
use PDOStatement;

/**
 * Class Upcoming.
 */
class Upcoming
{
    protected $fluent;
    protected $cache;
    protected $site_config;
    protected $settings;

    /**
     * Upcoming constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     * @param Settings $settings
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, Settings $settings)
    {
        $this->settings = $settings;
        $this->site_config = $this->settings->get_settings();
        $this->fluent = $fluent;
        $this->cache = $cache;
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
     *
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
     *
     * @param bool $all
     * @param bool $show_hidden
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_count(bool $all, bool $show_hidden)
    {
        $count = $this->fluent->from('upcoming AS u')
                              ->select(null)
                              ->select('COUNT(u.id) AS count');
        if (!$show_hidden) {
            $count->leftJoin('categories AS c ON u.category = c.id')
                  ->where('c.hidden = 0');
        }
        if (!$all) {
            $count->where('u.status != ?', 'uploaded');
        }
        $count = $count->fetch('count');

        return $count;
    }

    /**
     *
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
     *
     * @param int  $id
     * @param bool $staff
     * @param int  $userid
     *
     * @throws Exception
     *
     * @return bool|Delete
     */
    public function delete(int $id, bool $staff, int $userid)
    {
        $result = $this->fluent->deleteFrom('upcoming')
                               ->where('id = ?', $id);
        if (!$staff) {
            $result = $result->where('userid = ?', $userid);
        }
        $result = $result->execute();

        return $result;
    }

    /**
     *
     * @param array $set
     * @param int   $upcomingid
     *
     * @throws Exception
     *
     * @return bool|int|PDOStatement
     */
    public function update(array $set, int $upcomingid)
    {
        $result = $this->fluent->update('upcoming')
                               ->set($set)
                               ->where('id = ?', $upcomingid)
                               ->execute();

        return $result;
    }

    /**
     *
     * @param int    $limit
     * @param int    $offset
     * @param string $orderby
     * @param bool   $desc
     * @param bool   $all
     * @param bool   $index
     * @param bool   $show_hidden
     *
     * @throws Exception
     *
     * @return array|bool
     */
    public function get_all(int $limit, int $offset, string $orderby, bool $desc, bool $all, bool $index, bool $show_hidden)
    {
        $results = $this->fluent->from('upcoming AS r')
                                ->select('u.username')
                                ->select('u.class')
                                ->select('c.name as cat')
                                ->select('c.image')
                                ->select('p.name AS parent_name')
                                ->select('(n.id IS NOT NULL) AS notify')
                                ->leftJoin('users AS u ON r.userid = u.id')
                                ->leftJoin('categories AS c ON r.category = c.id')
                                ->leftJoin('categories AS p ON c.parent_id = p.id')
                                ->leftJoin('upcoming_notify AS n ON r.userid = n.userid AND r.id = n.upcomingid')
                                ->limit($limit)
                                ->offset($offset);
        if (!$show_hidden) {
            $results = $results->where('c.hidden = 0');
        }
        if (!$all) {
            $results = $results->where('r.status != ?', 'uploaded');
        }
        if ($index) {
            $results = $results->where('show_index = 1');
        }
        if (!$desc && $index) {
            $results = $results->where('expected >= NOW()');
        }
        if (!empty($orderby)) {
            $order = $orderby . ($desc ? ' DESC' : '');
            $results = $results->orderBy($order);
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
