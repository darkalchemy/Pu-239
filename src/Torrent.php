<?php

declare(strict_types = 1);

namespace Pu239;

use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Exception;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use PDOStatement;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 * Class Torrent.
 */
class Torrent
{
    protected $cache;
    protected $fluent;
    protected $site_config;
    protected $users_class;
    protected $settings;
    protected $image;

    /**
     * Torrent constructor.
     *
     * @param Cache    $cache
     * @param Database $fluent
     * @param User     $users_class
     * @param Image    $image
     * @param Settings $settings
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, User $users_class, Image $image, Settings $settings)
    {
        $this->settings = $settings;
        $this->site_config = $this->settings->get_settings();
        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->image = $image;
        $this->users_class = $users_class;
    }

    /**
     * @param int $tid
     *
     * @throws Exception
     */
    public function delete_by_id(int $tid)
    {
        $this->fluent->deleteFrom('torrents')
                     ->where('id = ?', $tid)
                     ->execute();

        $query = $this->fluent->getPdo()
                              ->prepare('DELETE likes, comments
                       FROM likes
                       LEFT JOIN comments ON comments.id=likes.comment_id
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
    }

    /**
     * @param array $items
     * @param int   $tid
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get_items(array $items, int $tid)
    {
        $torrent = $this->get($tid);
        if (empty($torrent)) {
            return false;
        }
        $count = count($items);
        $list = [];
        if ($count === 1) {
            if (!empty($torrent[$items[0]])) {
                return $torrent[$items[0]];
            }

            return false;
        }

        foreach ($items as $item) {
            if (!empty($torrent[$item])) {
                $list[$item] = $torrent[$item];
            }
        }

        if (!empty($list)) {
            return $list;
        }

        return false;
    }

    /**
     * @param int  $tid
     * @param bool $fresh
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get(int $tid, bool $fresh = false)
    {
        $torrent = $this->cache->get('torrent_details_' . $tid);
        if ($torrent === false || is_null($torrent) || $fresh) {
            $torrent = $this->fluent->from('torrents')
                                    ->select('HEX(info_hash) AS info_hash')
                                    ->select('LENGTH(nfo) AS nfosz')
                                    ->select("IF(num_ratings < {$this->site_config['site']['minvotes']}, NULL, ROUND(rating_sum / num_ratings, 1)) AS rating")
                                    ->where('id = ?', $tid)
                                    ->fetch();
            if (empty($torrent)) {
                return $torrent;
            }

            $torrent['previous'] = $this->fluent->from('torrents')
                                                ->select(null)
                                                ->select('id')
                                                ->select('name')
                                                ->where('id < ?', $tid)
                                                ->orderBy('id DESC')
                                                ->limit(1)
                                                ->fetch();

            $torrent['next'] = $this->fluent->from('torrents')
                                            ->select(null)
                                            ->select('id')
                                            ->select('name')
                                            ->where('id > ?', $tid)
                                            ->orderBy('id')
                                            ->limit(1)
                                            ->fetch();

            $this->cache->set('torrent_details_' . $tid, $torrent, $this->site_config['expires']['torrent_details']);
        }

        return $torrent;
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_all_snatched(int $userid)
    {
        $torrents = $this->fluent->from('torrents AS t')
                                 ->select(null)
                                 ->select('t.id')
                                 ->select('t.filename')
                                 ->innerJoin('snatched AS s ON t.id=s.torrentid')
                                 ->where('s.userid = ?', $userid)
                                 ->orderBy('id DESC');

        return $torrents;
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     *
     * @return mixed
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
     * @throws Exception
     *
     * @return mixed
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
     * @param int $tid
     * @param int $seeders
     * @param int $leechers
     * @param int $times_completed
     *
     * @throws Exception
     * @throws UnbegunTransaction
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
     * @param array $set
     * @param int   $tid
     * @param bool  $seeders
     *
     * @throws UnbegunTransaction
     * @throws Exception
     *
     * @return bool|int|PDOStatement
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
                    'scroller_torrents_',
                    'slider_torrents_',
                    'latest_torrents_',
                    'top_torrents_',
                    'motw_',
                    'staff_picks_',
                ]);
            }
        }

        return $query;
    }

    /**
     * @param string   $infohash
     * @param int|null $tid
     * @param int|null $owner
     * @param int|null $added
     *
     * @throws UnbegunTransaction
     * @throws Exception
     *
     * @return bool
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
                'top_torrents_',
                'latest_torrents_',
                'latest_torrents_' . implode('_', $this->site_config['categories']['movie']),
                'latest_torrents_' . implode('_', $this->site_config['categories']['tv']),
                'scroller_torrents_',
                'torrent_details_' . $tid,
                'latest_torrents_',
                'slider_torrents_',
                'torrent_poster_count_',
                'torrent_banner_count_',
                'backgrounds_',
                'get_torrent_count_',
                'torrent_descr_' . $tid,
                'staff_picks_',
                'motw_',
            ]);
        }

        if ($added > TIME_NOW - (14 * 86400)) {
            $seedbonus = $this->users_class->get_item('seedbonus', $owner);
            $set = [
                'seedbonus' => $seedbonus - $this->site_config['bonus']['per_delete'],
            ];
            $this->fluent->update('users')
                         ->set($set)
                         ->where('id = ?', $owner)
                         ->execute();

            $this->cache->update_row('user_' . $owner, $set, $this->site_config['expires']['user_cache']);
        }

        return true;
    }

    /**
     * @param string $info_hash
     *
     * @throws Exception
     *
     * @return array|bool
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
            'size' => $torrent['size'],
            'info_hash' => $torrent['info_hash'],
        ];

        return $announce;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     *
     * @return bool|int
     */
    public function add(array $values)
    {
        $id = $this->fluent->insertInto('torrents')
                           ->values($values)
                           ->execute();

        return $id;
    }

    /**
     * @throws Exception
     *
     * @return bool|mixed
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

    /**
     * @throws Exception
     *
     * @return array
     */
    public function get_latest_scroller()
    {
        $scroller_torrents = [];
        $torrents = $this->cache->get('scroller_torrents_');
        if ($torrents === false || is_null($torrents)) {
            $torrents = $this->fluent->from('torrents AS t')
                                     ->select(null)
                                     ->select('t.id')
                                     ->select('t.added')
                                     ->select('t.seeders')
                                     ->select('t.leechers')
                                     ->select('t.name')
                                     ->select('t.size')
                                     ->select('t.poster')
                                     ->select('t.anonymous')
                                     ->select('t.owner')
                                     ->select('t.imdb_id')
                                     ->select('t.times_completed')
                                     ->select('t.rating')
                                     ->select('t.year')
                                     ->select('t.subs AS subtitles')
                                     ->select('t.audios')
                                     ->select('t.newgenre AS genre')
                                     ->select('u.username')
                                     ->select('u.class')
                                     ->select('p.name AS parent_name')
                                     ->select('c.name AS cat')
                                     ->select('c.image')
                                     ->leftJoin('users AS u ON t.owner = u.id')
                                     ->leftJoin('categories AS c ON t.category = c.id')
                                     ->leftJoin('categories AS p ON c.parent_id = p.id')
                                     ->where('visible = "yes"')
                                     ->where('t.imdb_id != ""')
                                     ->orderBy('t.added DESC');

            $scrollers = [];
            foreach ($torrents as $torrent) {
                if (!empty($torrent['parent_name'])) {
                    $torrent['cat'] = $torrent['parent_name'] . ' :: ' . $torrent['cat'];
                }
                if (!empty($torrent['poster'])) {
                    $scrollers[] = $torrent;
                } else {
                    $images = $this->cache->get('posters_' . $torrent['imdb_id']);
                    if ($images === false || is_null($images)) {
                        $images = $this->fluent->from('images')
                                               ->select(null)
                                               ->select('url')
                                               ->where('type = "poster"')
                                               ->where('imdb_id = ?', $torrent['imdb_id'])
                                               ->where('fetched = "yes"')
                                               ->fetchAll();

                        if (!empty($images)) {
                            $this->cache->set('posters_' . $torrent['imdb_id'], $images, 86400);
                        } else {
                            $this->cache->set('posters_' . $torrent['imdb_id'], [], 3600);
                        }
                    }
                    if (!empty($images)) {
                        $scrollers[] = $torrent;
                    }
                }
                if (count($scrollers) >= $this->site_config['latest']['scroller_limit']) {
                    break;
                }
            }
            $torrents = $scrollers;
            $this->cache->set('scroller_torrents_', $torrents, $this->site_config['expires']['scroll_torrents']);
        }
        if (!empty($torrents)) {
            foreach ($torrents as $torrent) {
                if (empty($torrent['poster'])) {
                    $images = $this->cache->get('posters_' . $torrent['imdb_id']);
                    if (!empty($images)) {
                        shuffle($images);
                        $torrent['poster'] = $images[0]['url'];
                    }
                    $scroller_torrents[] = $torrent;
                }
            }
        }

        return $scroller_torrents;
    }

    /**
     * @throws Exception
     *
     * @return array
     */
    public function get_latest_slider()
    {
        $sliding_torrents = [];
        $torrents = $this->cache->get('slider_torrents_');
        if ($torrents === false || is_null($torrents)) {
            $torrents = $this->fluent->from('torrents AS t')
                                     ->select(null)
                                     ->select('t.id')
                                     ->select('t.added')
                                     ->select('t.seeders')
                                     ->select('t.leechers')
                                     ->select('t.name')
                                     ->select('t.size')
                                     ->select('t.poster')
                                     ->select('t.anonymous')
                                     ->select('t.owner')
                                     ->select('t.imdb_id')
                                     ->select('t.times_completed')
                                     ->select('t.rating')
                                     ->select('t.year')
                                     ->select('t.subs AS subtitles')
                                     ->select('t.audios')
                                     ->select('t.newgenre AS genre')
                                     ->select('u.username')
                                     ->select('u.class')
                                     ->select('p.name AS parent_name')
                                     ->select('c.name AS cat')
                                     ->select('c.image')
                                     ->leftJoin('users AS u ON t.owner = u.id')
                                     ->leftJoin('categories AS c ON t.category = c.id')
                                     ->leftJoin('categories AS p ON c.parent_id = p.id')
                                     ->where('t.imdb_id != ""')
                                     ->where('visible = "yes"')
                                     ->orderBy('t.added DESC');

            $sliders = [];
            foreach ($torrents as $torrent) {
                if (!empty($torrent['parent_name'])) {
                    $torrent['cat'] = $torrent['parent_name'] . ' :: ' . $torrent['cat'];
                }
                if (empty($torrent['poster'])) {
                    $posters = $this->cache->get('posters_' . $torrent['imdb_id']);
                    if ($posters === false || is_null($posters)) {
                        $posters = $this->fluent->from('images')
                                                ->select(null)
                                                ->select('url')
                                                ->where('type = "poster"')
                                                ->where('imdb_id = ?', $torrent['imdb_id'])
                                                ->where('fetched = "yes"')
                                                ->fetchAll();

                        if (!empty($posters)) {
                            $this->cache->set('posters_' . $torrent['imdb_id'], $posters, 86400);
                        } else {
                            $this->cache->set('posters_' . $torrent['imdb_id'], [], 3600);
                        }
                    }
                }
                $banners = $this->cache->get('banners_' . $torrent['imdb_id']);
                if ($banners === false || is_null($banners)) {
                    $banners = $this->fluent->from('images')
                                            ->select(null)
                                            ->select('url')
                                            ->where('type = "banner"')
                                            ->where('imdb_id = ?', $torrent['imdb_id'])
                                            ->fetchAll();
                    if (!empty($banners)) {
                        $this->cache->set('banners_' . $torrent['imdb_id'], $banners, 86400);
                    } else {
                        $this->cache->set('banners_' . $torrent['imdb_id'], [], 3600);
                    }
                }
                if (!empty($banners) && !empty($posters)) {
                    $sliders[] = $torrent;
                }

                if (count($sliders) >= $this->site_config['latest']['slider_limit']) {
                    break;
                }
            }

            $torrents = $sliders;
            $this->cache->set('slider_torrents_', $torrents, $this->site_config['expires']['slider_torrents']);
        }

        if (!empty($torrents)) {
            foreach ($torrents as $torrent) {
                if (empty($torrent['poster'])) {
                    $images = $this->cache->get('posters_' . $torrent['imdb_id']);
                    if (!empty($images)) {
                        shuffle($images);
                        $torrent['poster'] = $images[0]['url'];
                    }
                }
                $images = $this->cache->get('banners_' . $torrent['imdb_id']);
                if (!empty($images)) {
                    shuffle($images);
                    $torrent['banner'] = $images[0]['url'];
                }
                if (!empty($torrent['banner']) && !empty($torrent['poster'])) {
                    $sliding_torrents[] = $torrent;
                }
            }
        }

        return $sliding_torrents;
    }

    /**
     * @throws Exception
     *
     * @return array|bool|mixed
     */
    public function get_staff_picks()
    {
        $torrents = [];
        $staff_picks = $this->cache->get('staff_picks_');
        if ($staff_picks === false || is_null($staff_picks)) {
            $staff_picks = $this->fluent->from('torrents AS t')
                                        ->select(null)
                                        ->select('t.id')
                                        ->select('t.added')
                                        ->select('t.seeders')
                                        ->select('t.leechers')
                                        ->select('t.name')
                                        ->select('t.size')
                                        ->select('t.poster')
                                        ->select('t.anonymous')
                                        ->select('t.owner')
                                        ->select('t.imdb_id')
                                        ->select('t.times_completed')
                                        ->select('t.rating')
                                        ->select('t.year')
                                        ->select('t.subs AS subtitles')
                                        ->select('t.audios')
                                        ->select('t.newgenre AS genre')
                                        ->select('u.username')
                                        ->select('u.class')
                                        ->select('p.name AS parent_name')
                                        ->select('c.name AS cat')
                                        ->select('c.image')
                                        ->select("REPLACE(LOWER(z.classname), ' ', '_') AS classname")
                                        ->leftJoin('users AS u ON t.owner = u.id')
                                        ->leftJoin('categories AS c ON t.category = c.id')
                                        ->leftJoin('categories AS p ON c.parent_id = p.id')
                                        ->leftJoin('class_config AS z ON u.class = z.value')
                                        ->where('t.staff_picks != 0')
                                        ->where('visible = "yes"')
                                        ->where("z.name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX')")
                                        ->orderBy('t.staff_picks DESC')
                                        ->limit($this->site_config['latest']['staff_picks']);

            foreach ($staff_picks as $torrent) {
                if (!empty($torrent['parent_name'])) {
                    $torrent['cat'] = $torrent['parent_name'] . ' :: ' . $torrent['cat'];
                }
                $torrents[] = $torrent;
            }
            $staff_picks = $torrents;
            $this->cache->set('staff_picks_', $torrents, $this->site_config['expires']['staff_picks']);
        }
        if (!empty($staff_picks)) {
            foreach ($staff_picks as $staff_pick) {
                if (empty($staff_pick['poster']) && !empty($staff_pick['imdb_id'])) {
                    $this->image->find_images($staff_pick['imdb_id']);
                }
            }
        }

        return $staff_picks;
    }

    /**
     * @throws Exception
     *
     * @return array|bool|mixed
     */
    public function get_top()
    {
        $torrents = [];
        $top_torrents = $this->cache->get('top_torrents_');
        if ($top_torrents === false || is_null($top_torrents)) {
            $top_torrents = $this->fluent->from('torrents AS t')
                                         ->select(null)
                                         ->select('t.id')
                                         ->select('t.added')
                                         ->select('t.seeders')
                                         ->select('t.leechers')
                                         ->select('t.name')
                                         ->select('t.size')
                                         ->select('t.poster')
                                         ->select('t.anonymous')
                                         ->select('t.owner')
                                         ->select('t.imdb_id')
                                         ->select('t.times_completed')
                                         ->select('t.rating')
                                         ->select('t.year')
                                         ->select('t.subs AS subtitles')
                                         ->select('t.audios')
                                         ->select('t.newgenre AS genre')
                                         ->select('u.username')
                                         ->select('u.class')
                                         ->select('p.name AS parent_name')
                                         ->select('c.name AS cat')
                                         ->select('c.image')
                                         ->select("REPLACE(LOWER(z.classname), ' ', '_') AS classname")
                                         ->leftJoin('users AS u ON t.owner = u.id')
                                         ->leftJoin('categories AS c ON t.category = c.id')
                                         ->leftJoin('categories AS p ON c.parent_id = p.id')
                                         ->leftJoin('class_config AS z ON u.class = z.value')
                                         ->where('visible = "yes"')
                                         ->where("z.name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX')")
                                         ->orderBy('t.seeders + t.leechers DESC')
                                         ->limit($this->site_config['latest']['torrents_limit']);

            foreach ($top_torrents as $torrent) {
                if (!empty($torrent['parent_name'])) {
                    $torrent['cat'] = $torrent['parent_name'] . ' :: ' . $torrent['cat'];
                }
                $torrents[] = $torrent;
            }
            $top_torrents = $torrents;
            $this->cache->set('top_torrents_', $torrents, $this->site_config['expires']['top_torrents']);
        }
        if (!empty($top_torrents)) {
            foreach ($top_torrents as $torrent) {
                if (empty($torrent['poster']) && !empty($torrent['imdb_id'])) {
                    $this->image->find_images($torrent['imdb_id']);
                }
            }
        }

        return $top_torrents;
    }

    /**
     * @param array $categories
     *
     * @throws Exception
     *
     * @return array|bool|mixed
     */
    public function get_latest(array $categories)
    {
        $torrents = [];
        $in = !empty($categories) ? str_repeat('?,', count($categories) - 1) . '?' : '';
        $string = !empty($categories) ? implode('_', $categories) : '';
        $latest_torrents = $this->cache->get('latest_torrents_' . $string);
        if ($latest_torrents === false || is_null($latest_torrents)) {
            $latest_torrents = $this->fluent->from('torrents AS t')
                                            ->select(null)
                                            ->select('t.id')
                                            ->select('t.added')
                                            ->select('t.seeders')
                                            ->select('t.leechers')
                                            ->select('t.name')
                                            ->select('t.size')
                                            ->select('t.poster')
                                            ->select('t.anonymous')
                                            ->select('t.owner')
                                            ->select('t.imdb_id')
                                            ->select('t.times_completed')
                                            ->select('t.rating')
                                            ->select('t.year')
                                            ->select('t.subs AS subtitles')
                                            ->select('t.audios')
                                            ->select('t.newgenre AS genre')
                                            ->select('u.username')
                                            ->select('u.class')
                                            ->select('p.name AS parent_name')
                                            ->select('c.name AS cat')
                                            ->select('c.image')
                                            ->select("REPLACE(LOWER(z.classname), ' ', '_') AS classname")
                                            ->leftJoin('users AS u ON t.owner = u.id')
                                            ->leftJoin('class_config AS z ON u.class = z.value')
                                            ->leftJoin('categories AS c ON t.category = c.id')
                                            ->leftJoin('categories AS p ON c.parent_id = p.id')
                                            ->where('visible = "yes"')
                                            ->where("z.name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX')");
            if (!empty($categories)) {
                $latest_torrents = $latest_torrents->where('category IN (' . $in . ')', $categories);
            }
            $latest_torrents = $latest_torrents->orderBy('t.added DESC')
                                               ->limit($this->site_config['latest']['torrents_limit']);

            foreach ($latest_torrents as $torrent) {
                if (!empty($torrent['parent_name'])) {
                    $torrent['cat'] = $torrent['parent_name'] . ' :: ' . $torrent['cat'];
                }
                $torrents[] = $torrent;
            }
            $latest_torrents = $torrents;
            $this->cache->set('latest_torrents_' . $string, $torrents, $this->site_config['expires']['last_torrents']);
        }
        if (!empty($latest_torrents)) {
            foreach ($latest_torrents as $torrent) {
                if (empty($torrent['poster']) && !empty($torrent['imdb_id'])) {
                    $this->image->find_images($torrent['imdb_id']);
                }
            }
        }

        return $latest_torrents;
    }

    /**
     * @throws Exception
     *
     * @return array|bool|mixed
     */
    public function get_mow()
    {
        $motw = $this->cache->get('motw_');
        if ($motw === false || is_null($motw)) {
            $motw = [];
            $torrents = $this->fluent->from('torrents AS t')
                                     ->select(null)
                                     ->select('t.id')
                                     ->select('t.added')
                                     ->select('t.seeders')
                                     ->select('t.leechers')
                                     ->select('t.name')
                                     ->select('t.size')
                                     ->select('t.poster')
                                     ->select('t.anonymous')
                                     ->select('t.owner')
                                     ->select('t.imdb_id')
                                     ->select('t.times_completed')
                                     ->select('t.rating')
                                     ->select('t.year')
                                     ->select('t.subs AS subtitles')
                                     ->select('t.audios')
                                     ->select('t.newgenre AS genre')
                                     ->select('u.username')
                                     ->select('u.class')
                                     ->select('p.name AS parent_name')
                                     ->select('c.name AS cat')
                                     ->select('c.image')
                                     ->select("REPLACE(LOWER(z.classname), ' ', '_') AS classname")
                                     ->leftJoin('users AS u ON t.owner = u.id')
                                     ->leftJoin('categories AS c ON t.category = c.id')
                                     ->leftJoin('categories AS p ON c.parent_id = p.id')
                                     ->leftJoin('class_config AS z ON u.class = z.value')
                                     ->leftJoin('avps AS a ON t.id = a.value_u')
                                     ->orderBy('t.seeders + t.leechers DESC')
                                     ->where('a.arg', 'bestfilmofweek')
                                     ->where("z.name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX')");

            foreach ($torrents as $torrent) {
                if (!empty($torrent['parent_name'])) {
                    $torrent['cat'] = $torrent['parent_name'] . ' :: ' . $torrent['cat'];
                }
                $motw[] = $torrent;
            }
            $this->cache->set('motw_', $motw, $this->site_config['expires']['motw']);
        }
        if (!empty($motw)) {
            foreach ($motw as $torrent) {
                if (empty($torrent['poster']) && !empty($torrent['imdb_id'])) {
                    $this->image->find_images($torrent['imdb_id']);
                }
            }
        }

        return $motw;
    }

    /**
     * @throws Exception
     */
    public function get_plots()
    {
        $imdbs = $this->cache->get('imdbs_');
        if ($imdbs === false || is_null($imdbs)) {
            $imdbs = $this->fluent->from('torrents')
                                  ->select(null)
                                  ->select('imdb_id')
                                  ->where('imdb_id != ""')
                                  ->fetchAll();

            $this->cache->set('imdbs_', $imdbs, 3600);
        }
        foreach ($imdbs as $imdb) {
            $this->get_plot($imdb['imdb_id']);
        }
    }

    /**
     * @param string $imdb
     *
     * @throws Exception
     *
     * @return bool|mixed|null
     */
    public function get_plot(string $imdb)
    {
        $plot = $this->cache->get('imdb_plot_' . $imdb);
        if ($plot === false || is_null($plot)) {
            $plot = $this->fluent->from('imdb_info')
                                 ->select(null)
                                 ->select('plot')
                                 ->where('imdb_id = ?', str_replace('tt', '', $imdb))
                                 ->fetch('plot');

            if (!empty($plot)) {
                $this->cache->set('imdb_plot_' . $imdb, $plot, 86400);
            } else {
                $this->cache->set('imdb_plot_' . $imdb, 'No plot set', 3600);
            }
        }
        if (!empty($plot)) {
            return $plot;
        }

        return null;
    }

    /**
     * @param int $torrentid
     *
     * @throws InvalidManipulation
     * @throws Exception
     * @throws DependencyException
     * @throws NotFoundException
     *
     * @return false|mixed|string|string[]|null
     */
    public function format_descr(int $torrentid)
    {
        $descr = $this->cache->get('torrent_descr_' . $torrentid);
        if ($descr === false || is_null($descr)) {
            $torrent = $this->get($torrentid);
            if (!empty($torrent)) {
                $descr = $torrent['descr'];
                if (!empty($descr)) {
                    if (!preg_match('/\[pre\].*\[\/pre\]/isU', $descr)) {
                        $descr = '[pre]' . $descr . '[/pre]';
                    }
                    require_once INCL_DIR . 'function_bbcode.php';
                    $descr = format_comment($descr);
                    $this->cache->set('torrent_descr_' . $torrentid, $descr, 86400);
                }
            }
        }

        return $descr;
    }
}
