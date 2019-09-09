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
     * @param int    $limit
     * @param int    $offset
     * @param string $orderby
     * @param bool   $desc
     * @param bool   $all
     * @param bool   $index
     *
     * @throws Exception
     *
     * @return array|bool
     */
    public function get_all(int $limit, int $offset, string $orderby, bool $desc, bool $all, bool $index)
    {
        $hash = hash('sha256', "{$limit}_{$offset}_{$orderby}_{$desc}_{$all}");
        $this->cache->delete('recipes_showindex_' . $hash);
        $cooker = $this->cache->get('recipes_showindex_' . $hash);
        if ($cooker === false || is_null($cooker)) {
            $results = $this->fluent->from('upcoming AS r')
                                    ->select('u.username')
                                    ->select('u.class')
                                    ->select('c.name as cat')
                                    ->select('c.image')
                                    ->select('p.name AS parent_name')
                                    ->leftJoin('users AS u ON r.userid = u.id')
                                    ->leftJoin('categories AS c ON r.category = c.id')
                                    ->leftJoin('categories AS p ON c.parent_id = p.id')
                                    ->limit($limit)
                                    ->offset($offset);
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
            if (!empty($cooker)) {
                $this->cache->set('recipes_showindex_' . $hash, $cooker, $this->site_config['expires']['recipes_index']);
            }
        }

        return $cooker;
    }
}
