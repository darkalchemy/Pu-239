<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Peer.
 */
class Peer
{
    protected $cache;
    protected $fluent;
    protected $env;
    protected $site_config;
    protected $limit;
    protected $container;

    /**
     * Peer constructor.
     *
     * @param Cache              $cache
     * @param Database           $fluent
     * @param Settings           $settings
     * @param ContainerInterface $c
     *
     * @throws Exception
     */
    public function __construct(Cache $cache, Database $fluent, Settings $settings, ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        $this->site_config = $settings->get_settings();
        $this->cache = $cache;
        $this->fluent = $fluent;
        $this->limit = $this->env['db']['query_limit'];
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     *
     * @return bool|mixed
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
                                  ->where('userid = ?', $userid);

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
     * @throws Exception
     *
     * @return array|bool|mixed
     */
    public function get_torrent_peers_by_tid(int $tid)
    {
        $peers = $this->cache->get('torrent_peers_' . $tid);
        if ($peers === false || is_null($peers)) {
            $peers = $this->fluent->from('peers')
                                  ->select(null)
                                  ->select('id')
                                  ->select('torrent AS tid')
                                  ->select('seeder')
                                  ->select('peer_id')
                                  ->select('INET6_NTOA(ip) AS ip')
                                  ->select('port')
                                  ->select('uploaded')
                                  ->select('downloaded')
                                  ->select('userid')
                                  ->select('UNIX_TIMESTAMP(NOW()) - last_action AS announcetime')
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
     * @param int    $limit
     * @param int    $offset
     * @param string $orderby
     * @param string $ascdesc
     *
     * @throws Exception
     *
     * @return bool|mixed
     */
    public function get_all_peers(int $limit, int $offset, string $orderby, string $ascdesc)
    {
        $peers = $this->fluent->from('peers AS p')
                              ->select(null)
                              ->select('p.id')
                              ->select('p.torrent')
                              ->select('connectable')
                              ->select('p.seeder')
                              ->select('p.peer_id')
                              ->select('INET6_NTOA(p.ip) AS ip')
                              ->select('p.port')
                              ->select('p.uploaded')
                              ->select('p.downloaded')
                              ->select('p.userid')
                              ->select('p.agent')
                              ->select('p.to_go')
                              ->select('p.uploadoffset')
                              ->select('p.downloadoffset')
                              ->select('p.started')
                              ->select('t.size')
                              ->select('(UNIX_TIMESTAMP(NOW()) - p.last_action) AS announcetime')
                              ->select('p.last_action AS ts')
                              ->select('t.name')
                              ->leftJoin('torrents AS t On p.torrent = t.id')
                              ->orderBy("$orderby $ascdesc")
                              ->limit($limit)
                              ->offset($offset)
                              ->fetchAll();

        return $peers;
    }

    /**
     * @param int    $tid
     * @param int    $userid
     * @param string $peer_id
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_torrent_count(int $tid, int $userid, string $peer_id)
    {
        $peers = $this->fluent->from('peers')
                              ->select(null)
                              ->select('to_go')
                              ->select('peer_id')
                              ->select('seeder')
                              ->select('torrent')
                              ->where('userid = ?', $userid)
                              ->fetchAll();
        $seeder = $leecher = $no_seed = 0;
        foreach ($peers as $peer) {
            if ($peer_id === $peer['peer_id'] && $peer['torrent'] === $tid) {
                if ($peer['seeder'] === 'yes') {
                    ++$seeder;
                } else {
                    ++$leecher;
                }
            }
            if ($peer['to_go'] > 0) {
                ++$no_seed;
            }
        }

        return [
            'seeder' => $seeder,
            'leecher' => $leecher,
            'no_seed' => $no_seed,
        ];
    }

    /**
     * @param int    $pid
     * @param int    $tid
     * @param string $info_hash
     *
     * @throws Exception
     *
     * @return bool
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
     * @throws Exception
     *
     * @return bool|int
     */
    public function insert_update(array $values, array $update)
    {
        $id = $this->fluent->insertInto('peers', $values)
                           ->onDuplicateKeyUpdate($update)
                           ->execute();
        $this->cache->delete('torrent_peers_' . $values['torrent']);

        return $id;
    }

    /**
     * @param int $userid
     *
     * @throws Exception
     *
     * @return bool
     */
    public function flush(int $userid)
    {
        $result = $this->fluent->deleteFrom('peers')
                               ->where('userid = ?', $userid)
                               ->execute();

        return $result;
    }

    /**
     * @throws Exception
     *
     * @return mixed
     */
    public function get_count()
    {
        $count = $this->fluent->from('peers')
                              ->select(null)
                              ->select('COUNT(id) AS count')
                              ->fetch('count');

        return $count;
    }
}
