<?php

namespace Pu239;

/**
 * Class PollVoter.
 */
class PollVoter
{
    protected $cache;
    protected $fluent;
    protected $site_config;
    protected $user_stuffs;
    protected $poll_stuffs;

    public function __construct()
    {
        global $fluent, $cache, $site_config, $user_stuffs;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
        $this->user_stuffs = $user_stuffs;
        $this->poll_stuffs = new Poll();
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
     * @param int $poll_id
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function delete(int $poll_id)
    {
        $this->fluent->deleteFrom('poll_voters')
            ->where('poll_id = ?', $poll_id)
            ->execute();
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

    /**
     * @throws \Envms\FluentPDO\Exception
     */
    public function delete_users_cache()
    {
        $ids = $this->user_stuffs->get_all_ids();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->cache->delete('poll_data_' . $id['id']);
            }
        }
    }

    /**
     * @param int $userid
     *
     * @return array|bool|mixed
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_user_poll(int $userid)
    {
        $poll_data = $this->cache->get('poll_data_' . $userid);
        if ($poll_data === false || is_null($poll_data)) {
            $poll_data = $this->poll_stuffs->get_all(1);
            if (!empty($poll_data)) {
                $vote_data = $this->fluent->from('poll_voters')
                    ->select(null)
                    ->select('INET6_NTOA(ip) AS ip')
                    ->select('user_id')
                    ->select('vote_date')
                    ->where('user_id = ?', $userid)
                    ->where('poll_id = ?', $poll_data['pid'])
                    ->limit('1')
                    ->fetch();

                $poll_data['ip'] = $vote_data['ip'];
                $poll_data['user_id'] = $vote_data['user_id'];
                $poll_data['vote_date'] = $vote_data['vote_date'];
                $poll_data['time'] = TIME_NOW;
            }

            $this->cache->set('poll_data_' . $userid, $poll_data, $this->site_config['expires']['poll_data']);
        }

        return $poll_data;
    }
}
