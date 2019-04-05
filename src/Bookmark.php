<?php

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Bookmark.
 */
class Bookmark
{
    protected $fluent;
    protected $cache;

    public function __construct()
    {
        global $cache, $fluent;

        $this->fluent = $fluent;
        $this->cache = $cache;
    }

    /**
     * @param int $userid
     *
     * @return array|bool|mixed
     *
     * @throws Exception
     */
    public function get(int $userid)
    {
        $bookmarks = $this->cache->get('bookmarks_' . $userid);
        if ($bookmarks === false || is_null($bookmarks)) {
            $books = $this->fluent->from('bookmarks')
                ->where('userid=?', $userid)
                ->fetchAll();

            $bookmarks = [];
            foreach ($books as $rowbook) {
                $bookmarks[] = $rowbook;
            }
            $this->cache->set('bookmarks_' . $userid, $bookmarks, 86400);
        }

        return $bookmarks;
    }
}
