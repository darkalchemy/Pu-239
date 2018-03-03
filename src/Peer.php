<?php

namespace DarkAlchemy\Pu239;

class Peer
{
    protected $cache;
    protected $fluent;
    protected $config;

    /**
     * Peer constructor.
     */
    public function __construct()
    {
        global $cache, $fluent, $site_config;

        $this->cache  = $cache;
        $this->fluent = $fluent;
        $this->config = $site_config;
    }

    public function getPeersFromUserId($user_id)
    {
        $peers = $this->cache->get('MyPeers_' . $user_id);
        if ($peers === false || is_null($peers)) {
            $peers['yes']  = $peers['no']  = 0;
            $peers['conn'] = 3;
            $query         = $this->fluent->from('peers')
                ->select(null)
                ->select('COUNT(*) AS count')
                ->select('seeder')
                ->select('ANY_VALUE(connectable) AS connectable')
                ->where('userid = ?', $user_id)
                ->groupBy('seeder');

            foreach ($query as $a) {
                $key           = 'yes' == $a['seeder'] ? 'yes' : 'no';
                $peers[$key]   = number_format((int) $a['count']);
                $peers['conn'] = 'no' == $a['connectable'] ? 1 : 2;
            }
            $this->cache->set('MyPeers_' . $user_id, $peers, $this->config['expires']['MyPeers_']);
        }

        return $peers;
    }
}
