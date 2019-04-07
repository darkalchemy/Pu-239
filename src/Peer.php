<?php

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;

/**
 * Class Peer.
 */
class Peer
{
    protected $cache;
    protected $fluent;
    protected $site_config;
    protected $limit;

    public function __construct()
    {
        global $cache, $fluent, $site_config;

        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->site_config = $site_config;
        $this->limit = $this->site_config['database']['query_limit'];
    }

    /**
     * @param int $userid
     *
     * @return bool|mixed
     *
     * @throws Exception
     */
    public function getPeersFromUserId(int $userid)
    {
        $peers = $this->cache->get('peers_' . $userid);
        if ($peers === false || is_null($peers)) {
            $peers['yes'] = $peers['no'] = $peers['conn_yes'] = $peers['conn_no'] = $peers['count'] = 0;
            $peers['conn'] = 3;
            $peers['percentage'] = 0;
            $query = $this->fluent->from('peers')
                                  ->select(null)
                                  ->select('seeder')
                                  ->select('connectable')
                                  ->where('userid=?', $userid);

            foreach ($query as $a) {
                $key = $a['seeder'] === 'yes' ? 'yes' : 'no';
                ++$peers[$key];
                $conn = $a['connectable'] === 'yes' ? 'conn_yes' : 'conn_no';
                ++$peers[$conn];
                ++$peers['count'];
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
            $this->cache->set('peers_' . $userid, $peers, $this->site_config['expires']['peers_']);
        }

        return $peers;
    }

    /**
     * @param int $tid
     *
     * @return array|bool|mixed|PDOStatement
     *
     * @throws Exception
     */
    public function get_torrent_peers_by_tid(int $tid)
    {
        $peers = $this->cache->get('torrent_peers_' . $tid);
        if ($peers === false || is_null($peers)) {
            $peers = $this->fluent->from('peers')
                                  ->select(null)
                                  ->select('id')
                                  ->select('seeder')
                                  ->select('peer_id')
                                  ->select('INET6_NTOA(ip) AS ip')
                                  ->select('port')
                                  ->select('uploaded')
                                  ->select('downloaded')
                                  ->select('userid')
                                  ->select('(UNIX_TIMESTAMP(NOW()) - last_action) AS announcetime')
                                  ->select('last_action AS ts')
                                  ->select('UNIX_TIMESTAMP(NOW()) AS nowts')
                                  ->select('prev_action AS prevts')
                                  ->where('torrent = ?', $tid)
                                  ->fetchAll();

            $this->cache->set('torrent_peers_' . $tid, $peers, 60);
        }

        return $peers;
    }

    /**
     * @param int    $tid
     * @param string $torrent_pass
     * @param bool   $by_class
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function get_torrent_count(int $tid, string $torrent_pass, bool $by_class)
    {
        $count = $this->fluent->from('peers')
                              ->select(null)
                              ->select('COUNT(*) AS count')
                              ->where('torrent = ?', $tid)
                              ->where('torrent_pass = ?', $torrent_pass);

        if ($by_class) {
            $count = $count->where('to_go>0');
        }

        $count = $count->fetch('count');

        return $count;
    }

    /**
     * @param string $peerid
     * @param int    $tid
     * @param string $info_hash
     *
     * @return bool
     *
     * @throws Exception
     */
    public function delete_by_peerid(string $peerid, int $tid, string $info_hash)
    {
        $result = $this->fluent->deleteFrom('peers')
                               ->where('HEX(peer_id) = ?', bin2hex($peerid))
                               ->execute();

        if ($result) {
            $key = 'torrent_hash_' . bin2hex($info_hash);
            $this->cache->deleteMulti([
                $key,
                'torrent_details_' . $tid,
                'torrent_peers_' . $tid,
            ]);
        }

        return $result;
    }

    /**
     * @param int    $pid
     * @param int    $tid
     * @param string $info_hash
     *
     * @return bool
     *
     * @throws Exception
     */
    public function delete_by_id(int $pid, int $tid, string $info_hash)
    {
        $result = $this->fluent->deleteFrom('peers', $pid)
                               ->execute();

        if ($result) {
            $key = 'torrent_hash_' . bin2hex($info_hash);
            $this->cache->deleteMulti([
                $key,
                'torrent_details_' . $tid,
                'torrent_peers_' . $tid,
            ]);
        }

        return $result;
    }

    /**
     * @param array $values
     * @param array $update
     *
     * @return bool|mixed
     *
     * @throws Exception
     */
    public function insert_update(array $values, array $update)
    {
        $id = $this->fluent->from('peers')
                           ->select(null)
                           ->select('id')
                           ->where('torrent = ?', $values['torrent'])
                           ->where('peer_id=?', $values['peer_id'])
                           ->where('port = ?', $values['port'])
                           ->where('INET6_NTOA(ip) = ?', $values['ip'])
                           ->fetch('id');

        if (empty($id)) {
            $values['ip'] = inet_pton($values['ip']);
            $this->insert($values);
        } else {
            $this->update($update, $id);

            return $id;
        }

        return false;
    }

    /**
     * @param array $values
     *
     * @throws Exception
     */
    public function insert(array $values)
    {
        $this->fluent->insertInto('peers')
                     ->values($values)
                     ->execute();
    }

    /**
     * @param array $set
     * @param int   $id
     *
     * @return bool|int|PDOStatement
     *
     * @throws Exception
     */
    public function update(array $set, int $id)
    {
        $result = $this->fluent->update('peers')
                               ->set($set)
                               ->where('id = ?', $id)
                               ->execute();

        return $result;
    }
}
