<?php

namespace Pu239;

use Envms\FluentPDO\Exception;

/**
 * Class Block.
 */
class Block
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
     * @throws Exception
     */
    public function get(int $userid)
    {
        $blocks = $this->cache->get('blocks_' . $userid);
        if ($blocks === false || is_null($blocks)) {
            $blocks = $this->fluent->from('blocks')
                                   ->where('userid=?', $userid)
                                   ->fetch();

            $this->cache->set('blocks_' . $userid, $blocks, $this->site_config['expires']['user_blocks']);
        }
    }
}
