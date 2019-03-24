<?php

namespace Pu239;

/**
 * Class Searchcloud.
 */
class Searchcloud
{
    protected $cache;
    protected $fluent;
    protected $site_config;

    public function __construct()
    {
        global $fluent, $cache, $site_config;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
    }

    /**
     * @param array $limit
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get(array $limit)
    {
        $search = $this->fluent->from('searchcloud')
            ->select('INET6_NTOA(ip) AS ip')
            ->orderBy('howmuch DESC')
            ->limit("{$limit}")
            ->fetchAll();

        return $search;
    }

    /**
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_count()
    {
        $search = $this->fluent->from('searchcloud')
            ->select('COUNT(*) AS count')
            ->fetch('count');

        return $search;
    }

    /**
     * @param array $terms
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function delete(array $terms)
    {
        foreach ($terms as $term) {
            $this->fluent->deleteFrom('searchcloud')
                ->where('id = ?', $term)
                ->execute();
        }
        $this->cache->delete('searchcloud_');
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('searchcloud', $values)
            ->onDuplicateKeyUpdate($update)
            ->execute();
    }
}
