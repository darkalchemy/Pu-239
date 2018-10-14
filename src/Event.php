<?php

namespace DarkAlchemy\Pu239;

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

        return $event;
    }
}
