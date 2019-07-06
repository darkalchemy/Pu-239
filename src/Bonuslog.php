<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Bonuslog.
 * @package Pu239
 */
class Bonuslog
{
    protected $cache;
    protected $fluent;

    /**
     * Ban constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     */
    public function __construct(Cache $cache, Database $fluent)
    {
        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function insert(array $values)
    {
        $this->fluent->insertInto('bonuslog')
                     ->values($values)
                     ->execute();

        $this->cache->deleteMulti([
            'top_donators1_',
            'top_donators2_',
            'top_donators3_',
            'freeleech_alerts_',
            'bonus_points_',
        ]);
    }
}
