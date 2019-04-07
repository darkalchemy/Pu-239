<?php

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Userblock.
 */
class Userblock
{
    protected $fluent;
    protected $cache;
    protected $site_config;

    public function __construct()
    {
        global $site_config, $cache, $fluent;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->site_config = $site_config;
    }

    /**
     * @param int $userid
     *
     * @return bool|mixed
     *
     * @throws Exception
     */
    public function get(int $userid)
    {
        $blocks = $this->cache->get('userblocks_' . $userid);
        if ($blocks === false || is_null($blocks)) {
            $blocks = $this->fluent->from('user_blocks')
                                   ->select(null)
                                   ->select('index_page')
                                   ->select('global_stdhead')
                                   ->select('userdetails_page')
                                   ->where('userid=?', $userid)
                                   ->fetch();

            $this->cache->set('userblocks_' . $userid, $blocks, $this->site_config['expires']['u_status']);
        }

        return $blocks;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function add(array $values)
    {
        $this->fluent->insertInto('user_blocks')
                     ->values($values)
                     ->execute();
    }
}
