<?php

namespace DarkAlchemy\Pu239;

/**
 * Class PollVoter.
 */
class PollVoter
{
    protected $cache;
    protected $fluent;
    protected $site_config;
    protected $user_stuffs;

    public function __construct()
    {
        global $fluent, $cache, $site_config, $user_stuffs;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
        $this->user_stuffs = $user_stuffs;
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
        $search = $this->fluent->from('poll_voters')
            ->select('INET6_NTOA(ip) AS ip')
            ->orderBy('howmuch DESC')
            ->limit('?, ?', $limit[0], $limit[1])
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
        $search = $this->fluent->from('poll_voters')
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
            $this->fluent->deleteFrom('poll_voters')
                ->where('id = ?', $term)
                ->execute();
        }
        $this->cache->delete('poll_voters');
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('poll_voters', $values)
            ->onDuplicateKeyUpdate($update)
            ->execute();
    }

    /**
     * @param array $values
     *
     * @return int
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function add(array $values)
    {
        $id = $this->fluent->insertInto('poll_voters')
            ->values($values)
            ->execute();

        return $id;
    }

    public function delete_users_cache()
    {
        $ids = $this->user_stuffs->get_all_ids();
        foreach ($ids as $id) {
            $this->cache->delete('poll_data_' . $id);
        }
    }
}
