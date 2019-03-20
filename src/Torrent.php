<?php

namespace Pu239;

/**
 * Class Torrent.
 */
class Torrent
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
     * @param int $tid
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function delete_by_id(int $tid)
    {
        $this->fluent->deleteFrom('torrents')
            ->where('id = ?', $tid)
            ->execute();

        $query = $this->fluent->getPdo()
            ->prepare('DELETE likes, comments
                       FROM likes
                       LEFT JOIN comments ON comments.id = likes.comment_id
                       WHERE comments.torrent = ?');
        $query->bindParam(1, $tid);
        $query->execute();

        $this->fluent->deleteFrom('comments')
            ->where('torrent = ?', $tid)
            ->execute();

        $this->fluent->deleteFrom('coins')
            ->where('torrentid = ?', $tid)
            ->execute();

        $this->fluent->deleteFrom('rating')
            ->where('torrent = ?', $tid)
            ->execute();

        $this->fluent->deleteFrom('snatched')
            ->where('torrentid = ?', $tid)
            ->execute();

        $this->fluent->deleteFrom('peers')
            ->where('torrent = ?', $tid)
            ->execute();

        $this->fluent->deleteFrom('deathrow')
            ->where('tid = ?', $tid)
            ->execute();

        if (file_exists(TORRENTS_DIR . $tid . '.torrent')) {
            unlink(TORRENTS_DIR . $tid . '.torrent');
        }
        $this->clear_caches();
    }

    /**
     * @param array $set
     * @param int   $tid
     * @param bool  $seeders
     *
     * @return bool|int|\PDOStatement
     *
     * @throws \Envms\FluentPDO\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
     */
    public function update(array $set, int $tid, bool $seeders = false)
    {
        $query = $this->fluent->update('torrents')
            ->set($set)
            ->where('id = ?', $tid)
            ->execute();

        if ($query) {
            $this->cache->update_row('torrent_details_' . $tid, $set, $this->site_config['expires']['torrent_details']);
            if ($seeders) {
                $this->cache->deleteMulti([
                    'scroll_torrents_',
                    'slider_torrents_',
                    'last5_torrents_',
                    'top5_torrents_',
                    'motw_',
                    'staff_picks_',
                ]);
            }
        }

        return $query;
    }

    /**
     * @param int  $tid
     * @param bool $fresh
     *
     * @return bool|mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get(int $tid, bool $fresh = false)
    {
        $torrent = $this->cache->get('torrent_details_' . $tid);
        if ($torrent === false || is_null($torrent) || $fresh) {
            $torrent = $this->fluent->from('torrents')
                ->select('HEX(info_hash) AS info_hash')
                ->select('LENGTH(nfo) AS nfosz')
                ->select("IF(num_ratings < {$this->site_config['minvotes']}, NULL, ROUND(rating_sum / num_ratings, 1)) AS rating")
                ->where('id = ?', $tid)
                ->fetch();

            $torrent['previous'] = $this->fluent->from('torrents')
                ->select(null)
                ->select('id')
                ->select('name')
                ->where('id < ?', $torrent['id'])
                ->orderBy('id DESC')
                ->limit(1)
                ->fetch();

            $torrent['next'] = $this->fluent->from('torrents')
                ->select(null)
                ->select('id')
                ->select('name')
                ->where('id > ?', $torrent['id'])
                ->orderBy('id')
                ->limit(1)
                ->fetch();

            $this->cache->set('torrent_details_' . $tid, $torrent, $this->site_config['expires']['torrent_details']);
        }

        return $torrent;
    }

    /**
     * @param string $item
     * @param int    $tid
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_item(string $item, int $tid)
    {
        $result = $this->fluent->from('torrents')
            ->select(null)
            ->select($item)
            ->where('id = ?', $tid)
            ->fetch($item);

        return $result;
    }

    /**
     * @param int $userid
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_all_snatched(int $userid)
    {
        $torrents = $this->fluent->from('torrents AS t')
            ->select(null)
            ->select('t.id')
            ->select('t.filename')
            ->innerJoin('snatched AS s ON t.id = s.torrentid')
            ->where('s.userid = ?', $userid)
            ->orderBy('id DESC');

        return $torrents;
    }

    /**
     * @param int $userid
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_all_by_owner(int $userid)
    {
        $torrents = $this->fluent->from('torrents')
            ->select(null)
            ->select('id')
            ->select('filename')
            ->where('owner = ?', $userid)
            ->orderBy('id DESC');

        return $torrents;
    }

    /**
     * @param string $visible
     *
     * @return mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_all(string $visible)
    {
        $torrents = $this->fluent->from('torrents')
            ->select(null)
            ->select('id')
            ->select('filename')
            ->select('hits')
            ->where('visible = ?', $visible)
            ->orderBy('id DESC');

        return $torrents;
    }

    /**
     * @param string $info_hash
     *
     * @return array|bool
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_torrent_from_hash(string $info_hash)
    {
        $key = 'torrent_hash_' . bin2hex($info_hash);
        $ttl = 21600;
        $torrent = $this->cache->get($key);
        if ($torrent === false || is_null($torrent) || !is_array($torrent)) {
            $tid = $this->fluent->from('torrents')
                ->select(null)
                ->select('id')
                ->where('HEX(info_hash) = ?', bin2hex($info_hash))
                ->fetch('id');
            if (!empty($tid)) {
                $torrent = $this->get($tid);
                $this->cache->set($key, $torrent, $ttl);
            } else {
                $this->cache->set($key, 'empty', 900);

                return false;
            }
        }

        $announce = [
            'id' => $torrent['id'],
            'category' => $torrent['category'],
            'banned' => $torrent['banned'],
            'free' => $torrent['free'],
            'silver' => $torrent['silver'],
            'vip' => $torrent['vip'],
            'seeders' => $torrent['seeders'],
            'leechers' => $torrent['leechers'],
            'times_completed' => $torrent['times_completed'],
            'ts' => $torrent['added'],
            'visible' => $torrent['visible'],
            'owner' => $torrent['owner'],
            'added' => $torrent['added'],
            'info_hash' => $torrent['info_hash'],
        ];

        return $announce;
    }

    /**
     * @param int $tid
     * @param int $seeders
     * @param int $leechers
     * @param int $times_completed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function adjust_torrent_peers(int $tid, int $seeders = 0, int $leechers = 0, int $times_completed = 0)
    {
        $torrent = $this->get($tid);
        $set['seeders'] = $torrent['seeders'];
        $set['leechers'] = $torrent['leechers'];
        $set['times_completed'] = $torrent['times_completed'];

        if ($seeders > 0) {
            ++$set['seeders'];
        } elseif ($seeders < 0) {
            --$set['seeders'];
        }
        if ($leechers > 0) {
            ++$set['leechers'];
        } elseif ($leechers < 0) {
            --$set['leechers'];
        }
        if ($times_completed > 0) {
            ++$set['times_completed'];
        }

        $set['seeders'] = max(0, $set['seeders']);
        $set['leechers'] = max(0, $set['leechers']);

        $this->update($set, $tid);
    }

    /**
     * @param string   $infohash
     * @param int|null $tid
     * @param int|null $owner
     * @param int|null $added
     *
     * @return bool
     *
     * @throws \Envms\FluentPDO\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
     */
    public function remove_torrent(string $infohash, int $tid = null, int $owner = null, int $added = null)
    {
        if (strlen($infohash) != 20) {
            return false;
        }
        if (empty($tid) || empty($owner) || empty($added)) {
            $torrent = $this->get_torrent_from_hash($infohash);
            $tid = $torrent['id'];
            $owner = $torrent['owner'];
            $added = $torrent['added'];
        }
        if (!empty($tid) && !empty($owner)) {
            $key = 'torrent_hash_' . bin2hex($infohash);
            $this->cache->deleteMulti([
                $key,
                'peers_' . $owner,
                'coin_points_' . $tid,
                'latest_comments_',
                'top5_torrents_',
                'last5_torrents_',
                'scroll_torrents_',
                'torrent_details_' . $tid,
                'lastest_torrents_',
                'slider_torrents_',
                'torrent_poster_count_',
                'torrent_banner_count_',
                'backgrounds_',
                'posters_',
                'banners_',
                'get_torrent_count_',
                'torrent_descr_' . $tid,
                'staff_picks_',
                'motw_',
            ]);
            $this->clear_caches();
        }

        if ($added > TIME_NOW - (14 * 86400)) {
            $seedbonus = $this->user_stuffs->get_item('seedbonus', $owner);
            $set = [
                'seedbonus' => $seedbonus - $this->site_config['bonus_per_delete'],
            ];
            $this->fluent->update('users')
                ->set($set)
                ->where('id = ?', $owner)
                ->execute();

            $this->cache->update_row('user' . $owner, $set, $this->site_config['expires']['user_cache']);
        }

        return true;
    }

    public function clear_caches()
    {
        $keys = $this->cache->get('where_keys_');
        if (is_array($keys)) {
            $this->cache->deleteMulti($keys);
            $this->cache->delete('where_keys_');
        }

        $hashes = $this->cache->get('hashes_');
        if (!empty($hashes)) {
            $this->cache->deleteMulti($hashes);
            $this->cache->delete('hashes_');
        }
    }

    /**
     * @param array $values
     *
     * @return bool|int
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function add(array $values)
    {
        $id = $this->fluent->insertInto('torrents')
            ->values($values)
            ->execute();

        $this->clear_caches();

        return $id;
    }

    /**
     * @return bool|mixed
     *
     * @throws \Envms\FluentPDO\Exception
     */
    public function get_torrent_count()
    {
        $count = $this->cache->get('get_torrent_count_');
        if ($count === false || is_null($count)) {
            $count = $this->fluent->from('torrents')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->fetch('count');

            $this->cache->set('get_torrent_count_', $count, 86400);
        }

        return $count;
    }
}
