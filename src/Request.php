<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Envms\FluentPDO\Queries\Select;

/**
 * Class Request
 */
class Request
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
     * @param bool $all
     *
     * @return Select|mixed
     * @throws Exception
     */
    public function get_count(bool $all)
    {
        $count = $this->fluent->from('requests')
                              ->select(null)
                              ->select('COUNT(id) AS count');
        if (!$all) {
            $count->where('filled_torrent_id != 0');
        }
        $count = $count->fetch('count');

        return $count;
    }

    public function get_all(int $limit, int $offset, string $orderby, bool $desc, bool $all)
    {
        $results = $this->fluent->from('upcoming AS r')
                                ->select('u.username')
                                ->select('u.class')
                                ->select('c.name as cat')
                                ->select('c.image')
                                ->select('p.name AS parent_name')
                                ->select('COALESCE(n.id, false) AS notify')
                                ->leftJoin('users AS u ON r.userid = u.id')
                                ->leftJoin('categories AS c ON r.category = c.id')
                                ->leftJoin('categories AS p ON c.parent_id = p.id')
                                ->leftJoin('notify AS n ON r.userid = n.userid AND r.id = n.upcomingid')
                                ->limit($limit)
                                ->offset($offset);
        if (!$all) {
            $results = $results->where('r.filled_torrent_id != 0');
        }
        if (!$desc) {
            $results = $results->where('expected >= NOW()');
        }
        if (!empty($orderby)) {
            $order = $orderby . ($desc ? ' DESC' : '');
            $results = $results->orderBy($order);
        }
        $results = $results->orderBy('r.userid');
        $cooker = [];
        foreach ($results as $result) {
            $result['notified'] = $result['notify'] == 0 ? 0 : 1;
            $result['notify'] = $result['notify'] == 0 ? 'Notify' : 'UnNotify';
            if (!empty($result['parent_name'])) {
                $result['cat'] = $result['parent_name'] . '::' . $result['cat'];
            }
            $cooker[] = $result;
        }

        return $cooker;
    }

}