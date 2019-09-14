<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Offer
 */
class Offer
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
}