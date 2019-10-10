<?php

declare(strict_types = 1);

namespace Pu239;

/**
 * Class Ach_bonus.
 */
class Ach_bonus
{
    protected $fluent;
    protected $env;
    protected $limit;
    protected $settings;
    protected $site_config;

    /**
     * Ach_bonus constructor.
     *
     * @param Database $fluent
     */
    public function __construct(Database $fluent)
    {
        $this->fluent = $fluent;
    }

    /**
     * @return string
     */
    public function get_random()
    {
        try {
            return $this->fluent->from('ach_bonus')
                                ->orderBy('RAND()')
                                ->limit(1)
                                ->fetch();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
