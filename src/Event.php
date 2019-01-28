<?php

namespace Pu239;

/**
 * Class Event.
 */
class Event
{
    protected $cache;
    protected $fluent;
    protected $config;

    public function __construct()
    {
        global $cache, $fluent, $site_config;

        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->config = $site_config;
    }

    /**
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_event()
    {
        $event = $this->cache->get('site_events_');
        if ($event === false || is_null($event)) {
            $event = $this->fluent->from('events')
                ->select(null)
                ->select('startTime')
                ->select('endTime')
                ->select('freeleechEnabled')
                ->select('duploadEnabled')
                ->select('hdownEnabled')
                ->orderBy('startTime')
                ->limit(1)
                ->fetch();

            $this->cache->set('site_events_', $event, 3600);
        }

        return $event;
    }
}
