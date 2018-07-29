<?php

namespace DarkAlchemy\Pu239;

class Torrent
{
    protected $cache;
    protected $fluent;

    public function __construct()
    {
        global $fluent, $cache;

        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    public function delete_by_id(array $tid)
    {
        $this->fluent->deleteFrom('torrents')
            ->where('id = ?', $tid['id'])
            ->execute();

        $query = $this->fluent->getPdo()
            ->prepare('DELETE likes, comments
                                                    FROM likes
                                                    LEFT JOIN comments ON comments.id = likes.comment_id
                                                    WHERE comments.torrent = ?');
        $query->bindParam(1, $tid['id']);
        $query->execute();

        $this->fluent->deleteFrom('coins')
            ->where('torrentid = ?', $tid['id'])
            ->execute();

        $this->fluent->deleteFrom('rating')
            ->where('torrent = ?', $tid['id'])
            ->execute();
    }
}
