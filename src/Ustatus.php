<?php

namespace Pu239;

/**
 * Class Ustatus.
 */
class Ustatus
{
    protected $fluent;
    protected $cache;
    protected $site_config;

    public function __construct()
    {
        global $site_config, $cache, $fluent;

        $this->fluent = $fluent;
        $this->cache = $cache;
        $this->config = $site_config;
    }

    public function get(int $userid)
    {
        $status = $this->cache->get('userstatus_' . $userid);
        if ($status === false || is_null($status)) {
            $status = $this->fluent->from('ustatus')
                ->where('userid = ?', $userid)
                ->fetch();

            if (empty($status)) {
                $status = [
                    'last_status' => '',
                    'last_update' => 0,
                    'archive' => '',
                ];
            }
            $this->cache->set('userstatus_' . $userid, $status, $this->site_config['expires']['u_status']);
        }
    }
}
