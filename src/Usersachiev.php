<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;

/**
 * Class Usersachiev.
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
     * @return bool|string
     */
    public function insert(array $values, array $update)
    {
        try {
            $count = (int) ($this->limit / max(array_map('count', $values)));
            foreach (array_chunk($values, $count) as $t) {
                $this->fluent->insertInto('usersachiev', $t)
                             ->onDuplicateKeyUpdate($update)
                             ->execute();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * @param array $values
     *
     * @return bool|int|string
     */
    public function add(array $values)
    {
        try {
            return $this->fluent->insertInto('usersachiev')
                                ->values($values)
                                ->ignore()
                                ->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array $set
     * @param int   $userid
     *
     * @return bool|int|PDOStatement|string
     */
    public function update(array $set, int $userid)
    {
        try {
            return $this->fluent->update('usersachiev')
                                ->set($set)
                                ->where('userid = ?', $userid)
                                ->execute();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
