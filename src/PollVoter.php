<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class PollVoter.
 */
class PollVoter
{
    protected $cache;
    protected $fluent;
    protected $site_config;
    protected $users_class;
    protected $polls_class;
    protected $settings;

    /**
     * PollVoter constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     * @param User     $users_class
     * @param Poll     $polls_class
     * @param Settings $settings
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, User $users_class, Poll $polls_class, Settings $settings)
    {
        $this->settings = $settings;
        $this->site_config = $this->settings->get_settings();
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->users_class = $users_class;
        $this->polls_class = $polls_class;
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function get_count()
    {
        $search = $this->fluent->from('poll_voters')
                               ->select('COUNT(vid) AS count')
                               ->fetch('count');

        return $search;
    }

    /**
     * @param int $poll_id
     *
     * @throws Exception
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
     * @throws Exception
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('poll_voters', $values)
                     ->onDuplicateKeyUpdate($update)
                     ->execute();
    }

    /**
     *
     * @param array $values
     *
     * @throws Exception
     *
     * @return int
     */
    public function add(array $values)
    {
        $id = $this->fluent->insertInto('poll_voters')
                           ->values($values)
                           ->execute();

        return $id;
    }

    /**
     * @throws Exception
     */
    public function delete_users_cache()
    {
        $ids = $this->users_class->get_all_ids();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                $this->cache->delete('poll_data_' . $id['id']);
            }
        }
    }

    /**
     *
     * @param int $userid
     *
     * @throws Exception
     *
     * @return array|bool|mixed
     */
    public function get_user_poll(int $userid)
    {
        $poll_data = $this->cache->get('poll_data_' . $userid);
        if ($poll_data === false || is_null($poll_data)) {
            $poll_data = $this->polls_class->get_all(1);
            if (!empty($poll_data)) {
                $vote_data = $this->fluent->from('poll_voters')
                                          ->select(null)
                                          ->select('user_id')
                                          ->select('vote_date')
                                          ->where('user_id = ?', $userid)
                                          ->where('poll_id = ?', $poll_data['pid'])
                                          ->limit(1)
                                          ->fetch();

                $poll_data['user_id'] = $vote_data['user_id'];
                $poll_data['vote_date'] = $vote_data['vote_date'];
                $poll_data['time'] = TIME_NOW;
            }

            $this->cache->set('poll_data_' . $userid, $poll_data, $this->site_config['expires']['poll_data']);
        }

        return $poll_data;
    }
}
