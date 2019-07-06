<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Coin.
 * @package Pu239
 */
class Coin
{
    protected $cache;
    protected $fluent;
    protected $container;

    /**
     * Coin constructor.
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
     * @param int $tid
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get(int $tid)
    {
        $coins = $this->cache->get('coin_points_' . $tid);
        if ($coins === false || is_null($coins)) {
            $coins = $this->fluent->from('coins')
                                  ->select(null)
                                  ->select('userid')
                                  ->select('points')
                                  ->where('torrentid = ?', $tid)
                                  ->fetch();

            $this->cache->set('coin_points_' . $tid, $coins, 0);
        }

        return $coins;
    }
}
