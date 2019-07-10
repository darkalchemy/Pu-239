<?php

declare(strict_types = 1);

namespace Pu239;

use Envms\FluentPDO\Exception;
use PDOStatement;
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
     * @return array|bool|mixed|PDOStatement
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
     * @param string $peer_id
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_torrent_count(int $tid, string $torrent_pass, bool $by_class, string $peer_id)
    {
        $count = $this->fluent->from('peers')
                              ->select(null)
                              ->select('COUNT(id) AS count')
                              ->where('torrent = ?', $tid)
                              ->where('peer_id != ?', $peer_id)
                              ->where('torrent_pass = ?', $torrent_pass);

        if ($by_class) {
            $count = $count->where('to_go > 0');
        }

        $count = $count->fetch('count');

        return $count;
    }

    /**
     * @param string $peerid
     * @param int    $tid
     * @param string $info_hash
     *
     * @throws Exception
     *
     * @return bool
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
     * @return bool|mixed
     */
    public function insert_update(array $values, array $update)
    {
        $id = $this->fluent->from('peers')
                           ->select(null)
                           ->select('id')
                           ->where('torrent = ?', $values['torrent'])
                           ->where('peer_id = ?', $values['peer_id'])
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
     * @throws Exception
     *
     * @return bool|int|PDOStatement
     */
    public function update(array $set, int $id)
    {
        $result = $this->fluent->update('peers')
                               ->set($set)
                               ->where('id = ?', $id)
                               ->execute();

        return $result;
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

    /**
     * @param int $limit
     * @param int $offset
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function get_peers(int $limit, int $offset)
    {
        $results = $this->fluent->from('peers AS p')
                                ->select(null)
                                ->select('p.id')
                                ->select('p.userid')
                                ->select('p.torrent')
                                ->select('p.torrent_pass')
                                ->select('LEFT(p.peer_id, 8) AS peer_id')
                                ->select('INET6_NTOA(p.ip) AS ip')
                                ->select('p.port')
                                ->select('p.uploaded')
                                ->select('p.downloaded')
                                ->select('p.to_go')
                                ->select('p.seeder')
                                ->select('p.started')
                                ->select('p.last_action')
                                ->select('p.connectable')
                                ->select('p.agent')
                                ->select('p.finishedat')
                                ->select('p.downloadoffset')
                                ->select('p.uploadoffset')
                                ->select('t.name')
                                ->select('t.size')
                                ->leftJoin('torrents AS t ON p.torrent = t.id')
                                ->orderBy('p.started')
                                ->limit($limit)
                                ->offset($offset)
                                ->fetchAll();

        return $results;
    }
}
