<?php

namespace Pu239;

/**
 * Class Mood.
 */
class Mood
{
    protected $fluent;
    protected $cache;

    public function __construct()
    {
        global $cache, $fluent;

        $this->fluent = $fluent;
        $this->cache = $cache;
    }

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
