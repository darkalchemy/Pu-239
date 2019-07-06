<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Usersachiev.
 * @package Pu239
 */
class Usersachiev
{
    protected $cache;
    protected $fluent;
    protected $env;
    protected $limit;
    protected $settings;
    protected $site_config;

    /**
     * Usersachiev constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     * @param Settings $settings
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, Settings $settings)
    {
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->settings = $settings;
        $this->site_config = $this->settings->get_settings();
        $this->limit = $this->site_config['db']['query_limit'];
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws Exception
     */
    public function insert(array $values, array $update)
    {
        $count = (int) ($this->limit / max(array_map('count', $values)));
        foreach (array_chunk($values, $count) as $t) {
            $this->fluent->insertInto('usersachiev', $t)
                         ->onDuplicateKeyUpdate($update)
                         ->execute();
        }
    }

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function add(array $values)
    {
        $this->fluent->insertInto('usersachiev')
                     ->values($values)
                     ->ignore()
                     ->execute();
    }

    /**
     * @param array $set
     * @param int   $userid
     *
     * @throws Exception
     */
    public function update(array $set, int $userid)
    {
        $this->fluent->update('usersachiev')
            ->set($set)
            ->where('user_id = ?', $userid)
            ->execute();
    }
}
