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
}
