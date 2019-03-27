<?php

namespace Pu239;

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

    public function get(int $userid)
    {
        $status = $this->cache->get('blocks_' . $userid);
        if ($status === false || is_null($status)) {
            $status = $this->fluent->from('blocks')
                ->where('userid = ?', $userid)
                ->fetch();

            if (empty($status)) {
                $status = [
                    'last_status' => '',
                    'last_update' => 0,
                    'archive' => '',
                ];
            }
            $this->cache->set('blocks_' . $userid, $status, $this->site_config['expires']['u_status']);
        }
    }
}
