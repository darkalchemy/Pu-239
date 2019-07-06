<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Mood.
 * @package Pu239
 */
class Mood
{
    protected $fluent;
    protected $cache;

    /**
     * Mood constructor.
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
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get()
    {
        $moods = $this->cache->get('moods_');
        if ($moods === false || is_null($moods)) {
            $query = $this->fluent->from('moods')
                                  ->fetchAll();
            foreach ($query as $mood) {
                $moods['image'][$mood['id']] = $mood['image'];
                $moods['name'][$mood['id']] = $mood['name'];
            }
            $this->cache->set('moods_', $moods, 86400);
        }

        return $moods;
    }
}
