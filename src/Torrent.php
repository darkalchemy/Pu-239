<?php

namespace DarkAlchemy\Pu239;

class Torrent
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

        $this->fluent->deleteFrom('coins')
            ->where('torrentid = ?', $tid)
            ->execute();

        $this->fluent->deleteFrom('rating')
            ->where('torrent = ?', $tid)
            ->execute();

        unlink(TORRENTS_DIR . $tid . '.torrent');
        $this->clear_caches();
    }

    /**
     * @param array $set
     * @param int   $tid
     *
     * @return bool|int|\PDOStatement
     *
     * @throws \Envms\FluentPDO\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\UnbegunTransaction
     */
    public function set(array $set, int $tid)
    {
        $query = $this->fluent->update('torrents')
            ->set($set)
            ->where('id = ?', $tid)
            ->execute();

        if ($query) {
            $this->cache->update_row('torrent_details_' . $tid, $set, $this->site_config['expires']['torrent_details']);
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
            ->orderBy('id DESC')
            ->fetchAll();

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
            ->orderBy('id DESC')
            ->fetchAll();

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
            ->where('visible = ?', $visible)
            ->orderBy('id DESC')
            ->fetchAll();

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
                $seed_key = 'torrents_seeds_' . $torrent['id'];
                $leech_key = 'torrents_leechs_' . $torrent['id'];
                $comp_key = 'torrents_comps_' . $torrent['id'];
                $this->cache->set($seed_key, $torrent['seeders'], $ttl);
                $this->cache->set($leech_key, $torrent['leechers'], $ttl);
                $this->cache->set($comp_key, $torrent['times_completed'], $ttl);
            } else {
                $this->cache->set($key, 'empty', 900);

                return false;
            }
        } else {
            $seed_key = 'torrents_seeds_' . $torrent['id'];
            $leech_key = 'torrents_leechs_' . $torrent['id'];
            $comp_key = 'torrents_comps_' . $torrent['id'];
            $torrent['seeders'] = $this->cache->get($seed_key);
            $torrent['leechers'] = $this->cache->get($leech_key);
            $torrent['times_completed'] = $this->cache->get($comp_key);
            if ($torrent['seeders'] === false || $torrent['leechers'] === false || $torrent['times_completed'] === false || is_null($torrent['seeders']) || is_null($torrent['leechers']) || is_null($torrent['times_completed'])) {
                $torrent = $this->get($tid, true);
                if (!empty($torrent)) {
                    $this->cache->set($seed_key, $torrent['seeders'], $ttl);
                    $this->cache->set($leech_key, $torrent['leechers'], $ttl);
                    $this->cache->set($comp_key, $torrent['times_completed'], $ttl);
                    $this->cache->set($key, $torrent, $ttl);
                } else {
                    $this->cache->delete($key);

                    return false;
                }
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
            'info_hash' => $torrent['info_hash'],
        ];

        return $announce;
    }

    /**
     * @param int $tid
     * @param int $seeders
     * @param int $leechers
     * @param int $times_completed
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

        $this->set($set, $tid);
    }

    /**
     * @param string $infohash
     *
     * @return bool
     */
    public function remove_torrent(string $infohash)
    {
        if (strlen($infohash) != 20) {
            return false;
        }
        $torrent = $this->get_torrent_from_hash($infohash);
        if (is_array($torrent)) {
            $key = 'torrent_hash_' . bin2hex($infohash);
            $this->cache->deleteMulti([
                $key,
                'peers_' . $torrent['owner'],
                'coin_points_' . $torrent['id'],
                'latest_comments_',
                'top5_tor_',
                'last5_tor_',
                'scroll_tor_',
                'torrent_details_' . $torrent['id'],
                'torrent_details_txt_' . $torrent['id'],
                'lastest_tor_',
                'slider_tor_',
                'torrent_poster_count_',
                'torrent_banner_count_',
                'backgrounds_',
                'posters_',
                'similiar_tor_' . $torrent['id'],
            ]);
            $this->clear_caches();
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

    public function add(array $values)
    {
        $id = $this->fluent->insertInto('torrents')
            ->values($values)
            ->execute();

        $this->clear_caches();

        return $id;
    }
}
