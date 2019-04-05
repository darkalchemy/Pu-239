<?php

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Comment.
 */
class Comment
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
     * @param int $count
     * @param int $perpage
     *
     * @return array
     *
     * @throws Exception
     */
    public function get_torrent_comment(int $tid, int $count, int $perpage)
    {
        require_once INCL_DIR . 'function_pager.php';
        $pager = pager($perpage, $count, $this->site_config['paths']['baseurl'] . "/details.php?id=$tid&amp;", [
            'lastpagedefault' => 1,
        ]);
        $comments = $this->fluent->from('comments')
            ->where('torrent = ?', $tid)
            ->orderBy('id DESC')
            ->limit($pager['pdo'])
            ->fetchAll();

        return [
            $comments,
            $pager,
        ];
    }
}
