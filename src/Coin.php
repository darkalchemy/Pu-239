<?php

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Coin.
 */
class Coin
{
    protected $cache;
    protected $fluent;
    protected $site_config;

    public function __construct()
    {
        global $fluent, $cache, $site_config;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
    }

    /**
     * @param int $tid
     *
     * @return bool|mixed
     *
     * @throws Exception
     */
    public function get(int $tid)
    {
        $coins = $this->cache->get('coin_points_' . $tid);
        if ($coins === false || is_null($coins)) {
            $coins = $this->fluent->from('coins')
                ->select(null)
                ->select('userid')
                ->select('points')
                ->where('torrentid=?', $tid)
                ->fetch();

            $this->cache->set('coin_points_' . $tid, $coins, 0);
        }

        return $coins;
    }
}
