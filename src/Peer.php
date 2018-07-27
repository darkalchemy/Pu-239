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

        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->config = $site_config;
    }

    public function getPeersFromUserId($user_id)
    {
        $peers = $this->cache->get('peers_' . $user_id);
        if ($peers === false || is_null($peers)) {
            $peers['yes'] = $peers['no'] = $peers['conn_yes'] = $peers['conn_no'] = $peers['count'] = 0;
            $peers['conn'] = 3;
            $peers['percentage'] = 0;
            $query = $this->fluent->from('peers')
                ->select(null)
                ->select('seeder')
                ->select('connectable')
                ->where('userid = ?', $user_id);

            foreach ($query as $a) {
                $key = $a['seeder'] === 'yes' ? 'yes' : 'no';
                $peers[$key] += 1;
                $conn = $a['connectable'] === 'yes' ? 'conn_yes' : 'conn_no';
                $peers[$conn] += 1;
                $peers['count'] += 1;
            }
            if ($peers['conn_no'] === 0 && $peers['conn_yes'] > 0) {
                $peers['conn'] = 2;
            } elseif ($peers['conn_no'] > 0) {
                $peers['conn'] = 1;
            }
            if ($peers['count'] > 0) {
                if ($peers['conn_no'] === 0 && $peers['conn_yes'] > 0) {
                    $peers['percentage'] = 100;
                } elseif ($peers['conn_yes'] > 0) {
                    $peers['percentage'] = ceil(($peers['conn_yes'] / $peers['count']) * 100);
                }
            }
            $this->cache->set('peers_' . $user_id, $peers, $this->config['expires']['peers_']);
        }

        return $peers;
    }
}
