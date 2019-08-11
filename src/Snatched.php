<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;

/**
 * Class Snatched.
 */
class Snatched
{
    protected $cache;
    protected $fluent;
    protected $user;
    protected $site_config;
    protected $settings;

    /**
     * Snatched constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     * @param User     $user
     * @param Settings $settings
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, User $user, Settings $settings)
    {
        $this->settings = $settings;
        $this->site_config = $this->settings->get_settings();
        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->user = $user;
    }

    /**
     * @param int $userid
     * @param int $tid
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get_snatched(int $userid, int $tid)
    {
        $snatches = $this->fluent->from('snatched AS a')
                                 ->select(null)
                                 ->select('a.id')
                                 ->select('a.torrentid')
                                 ->select('a.seedtime')
                                 ->select('a.leechtime')
                                 ->select('a.uploaded')
                                 ->select('a.downloaded')
                                 ->select('a.real_uploaded')
                                 ->select('a.real_downloaded')
                                 ->select('a.finished')
                                 ->select('a.timesann')
                                 ->select('a.complete_date')
                                 ->select('a.start_date AS start_snatch')
                                 ->select('(UNIX_TIMESTAMP(NOW()) - a.last_action) AS announcetime')
                                 ->select('t.size')
                                 ->select('t.name')
                                 ->leftJoin('torrents AS t ON a.torrentid = t.id')
                                 ->where('a.torrentid = ?', $tid)
                                 ->where('a.userid = ?', $userid)
                                 ->fetch();

        return $snatches;
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @throws Exception
     */
    public function insert(array $values, array $update)
    {
        $this->fluent->insertInto('snatched', $values)
                     ->onDuplicateKeyUpdate($update)
                     ->execute();
    }

    /**
     * @param array $set
     * @param int   $tid
     * @param int   $userid
     *
     * @throws Exception
     */
    public function update(array $set, int $tid, int $userid)
    {
        $this->fluent->update('snatched')
                     ->set($set)
                     ->where('torrentid = ?', $tid)
                     ->where('userid = ?', $userid)
                     ->execute();
    }

    /**
     * @param array $set
     * @param int   $id
     *
     * @throws Exception
     *
     * @return bool|int|PDOStatement
     */
    public function update_by_id(array $set, int $id)
    {
        $result = $this->fluent->update('snatched')
                               ->set($set)
                               ->where('id = ?', $id)
                               ->execute();

        return $result;
    }

    /**
     * @param int $dt
     *
     * @throws Exception
     */
    public function delete_stale(int $dt)
    {
        $this->fluent->delete('snatched')
                     ->where('last_action < ?', $dt)
                     ->execute();
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     *
     * @return bool|int|PDOStatement
     */
    public function flush(int $userid)
    {
        $result = $this->fluent->update('snatched')
                               ->set(['seeder' => 'no'])
                               ->where('userid = ?', $userid)
                               ->execute();

        return $result;
    }

    /**
     * @param array $hnr
     *
     * @throws Exception
     *
     * @return array
     */
    public function get_hit_and_runs(array $hnr)
    {
        $types = [
            'days_3',
            'days_14',
            'days_over_14',
        ];
        $snatches = $users = $cains = [];
        foreach ($types as $type) {
            $snatched = $this->fluent->from('snatched AS s')
                                     ->select('u.modcomment')
                                     ->where('s.start_date < ?', $hnr['caindays'] * 86400);
            if (!$hnr['all_torrents']) {
                $snatched = $snatched->where('s.to_go = 0 AND s.seeder = "yes"');
            }
            $snatched = $snatched->where('(s.real_uploaded < s.real_downloaded OR s.seedtime < ?)', $hnr[$type])
                                 ->where('t.added < ?', $hnr['age'] * 86400 + TIME_NOW)
                                 ->where('t.owner != s.userid')
                                 ->where('u.immunity = 0')
                                 ->leftJoin('torrents AS t ON s.torrentid = t.id')
                                 ->leftJoin('users AS u ON s.userid = u.id')
                                 ->fetchAll();

            $snatches = array_merge($snatches, $snatched);
            $this->remove_cain($hnr[$type]);
        }
        foreach ($snatches as $snatch) {
            $users[$snatch['userid']][] = $snatch;
            $cains[] = $snatch['id'];
        }
        if (!empty($cains)) {
            $this->set_cain($cains);
        }

        return $users;
    }

    /**
     * @param int $time
     *
     * @throws Exception
     */
    public function remove_cain(int $time)
    {
        $set = ['mark_of_cain' => 'no'];

        $this->fluent->update('snatched')
                     ->set($set)
                     ->where('(real_uploaded > real_downloaded OR seedtime > ?)', $time)
                     ->where('mark_of_cain = "yes"')
                     ->execute();
    }

    /**
     * @param array $cains
     *
     * @throws Exception
     */
    public function set_cain(array $cains)
    {
        $set = ['mark_of_cain' => 'yes'];
        $this->fluent->update('snatched')
                     ->set($set)
                     ->where('id', $cains)
                     ->execute();
    }

    /**
     * @throws Exception
     *
     * @return array|bool
     */
    public function get_user_to_remove_hnr()
    {
        $users = $this->fluent->from('snatched AS s')
                              ->select(null)
                              ->select('s.userid')
                              ->select('COUNT(s.id) AS count')
                              ->select('u.modcomment')
                              ->select('u.username')
                              ->where('u.downloadpos = 0')
                              ->innerJoin('users AS u ON s.userid = u.id')
                              ->groupBy('s.userid')
                              ->having('count <= ?', $this->site_config['hnr_config']['cainallowed'])
                              ->fetchAll();

        return $users;
    }

    /**
     * @throws Exception
     *
     * @return array|bool
     */
    public function get_user_to_add_hnr()
    {
        $users = $this->fluent->from('snatched AS s')
                              ->select(null)
                              ->select('s.userid')
                              ->select('COUNT(s.id) AS count')
                              ->select('u.modcomment')
                              ->select('u.username')
                              ->select('hit_and_run_total')
                              ->where('u.downloadpos = 1')
                              ->innerJoin('users AS u ON s.userid = u.id')
                              ->groupBy('s.userid')
                              ->having('count > ?', $this->site_config['hnr_config']['cainallowed'])
                              ->fetchAll();

        return $users;
    }

    /**
     * @param array $hnr
     *
     * @throws Exception
     */
    public function set_hnr(array $hnr)
    {
        $set = [
            's.hit_and_run' => TIME_NOW,
        ];
        $types = [
            'days_3',
            'days_14',
            'days_over_14',
        ];
        foreach ($types as $type) {
            $this->fluent->update('snatched AS s')
                         ->set($set)
                         ->where('(s.real_uploaded < s.real_downloaded OR s.seedtime < ?)', $hnr[$type])
                         ->where('t.owner != s.userid')
                         ->where('u.immunity = 0')
                         ->leftJoin('torrents AS t ON s.torrentid = t.id')
                         ->leftJoin('users AS u ON s.userid = u.id')
                         ->execute();
        }
    }

    /**
     * @param array $hnr
     *
     * @throws Exception
     */
    public function remove_hnr(array $hnr)
    {
        $set = [
            's.hit_and_run' => 0,
        ];
        $types = [
            'days_3',
            'days_14',
            'days_over_14',
        ];
        foreach ($types as $type) {
            $this->fluent->update('snatched AS s')
                         ->set($set)
                         ->where('(s.real_uploaded >= s.real_downloaded OR s.seedtime > ?)', $hnr[$type])
                         ->where('t.owner != s.userid')
                         ->where('u.immunity = 0')
                         ->leftJoin('torrents AS t ON s.torrentid = t.id')
                         ->leftJoin('users AS u ON s.userid = u.id')
                         ->execute();
        }
    }

    /**
     * @throws Exception
     */
    public function update_seeder()
    {
        $deadtime = TIME_NOW - floor($this->site_config['tracker']['announce_interval'] * 1.3);
        $update = [
            'seeder' => 'no',
        ];
        $this->fluent->update('snatched')
                     ->set($update)
                     ->where('last_action < ?', $deadtime)
                     ->where('seeder = "yes"')
                     ->execute();
    }
}
