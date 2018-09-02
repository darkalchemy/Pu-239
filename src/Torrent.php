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

        unlink("{$this->site_config['torrent_dir']}/{$tid}.torrent");
    }

    /**
     * @param array $set
     * @param int   $tid
     *
     * @return bool|int|\PDOStatement
     *
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
     * @param int $tid
     *
     * @return bool|mixed
     */
    public function get(int $tid)
    {
        $torrent = $this->cache->get('torrent_details_' . $tid);
        if ($torrent === false || is_null($torrent)) {
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
}
