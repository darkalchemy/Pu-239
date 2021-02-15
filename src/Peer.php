<?php

declare(strict_types=1);

namespace Pu239;

use Psr\Container\ContainerInterface;

/**
 * Class Peer.
 */
class Peer
{
    protected $cache;
    protected $torrent;
    protected $env;
    protected $site_config;
    protected $limit;
    protected $container;

    public function __construct(PeerCache $cache, Torrent $torrent, Settings $settings, ContainerInterface $c)
    {
        $this->container = $c;
        $this->env = $this->container->get('env');
        $this->site_config = $settings->get_settings();
        $this->cache = $cache;
        $this->torrent = $torrent;
        $this->limit = $this->env['db']['query_limit'];
    }

    public function get_peers_from_userid(int $userid): array
    {
        $hashes = $this->get_user_peers($userid);
        $peers = $this->cache->getMulti($hashes) ?: [];
        foreach ($peers as $peer) {
            if ($peer['seeder'] === 'yes') {
                ++$seeder;
            } else {
                ++$leecher;
            }

        }
    }

    public function get_torrent_peers_from_id(int $tid): array
    {
        $hashes = $this->get_torrent_peers($tid);
        return $this->cache->getMulti($hashes) ?: [];
    }

    public function get_torrent_count(int $tid, int $userid, string $peer_id): array
    {
        $values = $this->get_user_peers($userid);
        $peers = $this->cache->getMulti($values);
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

    public function delete_peer(array $values): bool
    {
        $hash = $this->set_peer_hash($values['userid'], $values['torrent'], $values['ip'], $values['port']);
        $peers = $this->get_user_peers($values['userid']);
        foreach ($peers as $key => $peer) {
            if (
                $peer['torrent'] === $values['torrent'] &&
                $peer['ip'] === $values['ip'] &&
                $peer['port'] === $values['port']
            ) {
                unset($peers[$key]);
            }
        }
        $this->cache_peers('peers_user_' . $values['userid'], $peers);
        $peers = $this->get_torrent_peers($values['torrent']);
        foreach ($peers as $key => $peer) {
            if (
                $peer['userid'] === $values['userid'] &&
                $peer['ip'] === $values['ip'] &&
                $peer['port'] === $values['port']
            ) {
                unset($peers[$key]);
            }
        }
        $this->cache_peers('peers_torrent_' . $values['torrent'], $peers);
        return $this->cache->delete($hash);
    }

    public function flush(int $userid): bool
    {
        return $this->cache->delete('peers_user_' . $userid);
    }

    public function get_count(): int
    {
        $hashes = [];
        $users = $this->get_all_peer_ids();
        foreach ($users as $user) {
            $hashes[] = $this->get_user_peers($user);
        }
        return count($hashes);
    }

    public function insert_update(array $values): bool
    {
        $torrent = $this->torrent->get($values['torrent']);
        if (empty($torrent)) {
            return false;
        }
        $values['name'] = $torrent['name'];
        $values['size'] = $torrent['size'];
        $hash = $this->set_peer_hash($values['userid'], $values['torrent'], $values['ip'], $values['port']);
        $this->set_user_peers($values['userid'], $hash);
        $this->set_torrent_peer($values['torrent'], $hash);
        $this->set_all_peer_ids($values['userid']);
        return $this->cache->set($hash, $values, 2100);
    }

    protected function set_peer_hash(int $userid, int $torrent, string $ip, int $port): string
    {
        return base64_encode(sprintf("%d_%d_%s_%d", $userid, $torrent, $ip, $port));
    }

    protected function cache_peers(string $key, array $peers)
    {
        if (empty($peers)) {
            return $this->cache->delete($key);
        }
        $compressed = gzcompress(json_encode($peers), 6);
        file_put_contents(PHPERROR_LOGS_DIR . 'compressed_peers.log', bytesToHuman(strlen($compressed)) . PHP_EOL . FILE_APPEND);
        return $this->cache->set($key, $compressed, 2100);
    }

    protected function set_all_peer_ids(int $userid): bool
    {
        $peers = $this->get_all_peer_ids();
        if (!in_array($userid, $peers)) {
            $peers[] = $userid;
        }
        return $this->cache_peers('peers_all_user_ids_' . $userid, $peers);
    }

    protected function get_all_peer_ids(): array
    {
        $peers = $this->cache->get('peers_all_user_ids_') ?: [];
        if (!empty($peers)) {
            return json_decode(gzuncompress($peers), true);
        }
        return $peers;
    }

    protected function set_user_peers(int $userid, string $hash): bool
    {
        $peers = $this->get_user_peers($userid);
        if (!in_array($hash, $peers)) {
            $peers[] = $hash;
        }
        return $this->cache_peers('peers_user_' . $userid, $peers);
    }

    protected function get_user_peers(int $userid): array
    {
        $peers = $this->cache->get('peers_user_' . $userid) ?: [];
        if (!empty($peers)) {
            return json_decode(gzuncompress($peers), true);
        }
        return $peers;
    }

    protected function set_torrent_peer(int $torrent, string $hash): bool
    {
        $peers = $this->get_torrent_peers($torrent);
        if (!in_array($hash, $peers)) {
            $peers[] = $hash;
        }
        return $this->cache_peers('peers_torrent_' . $torrent, $peers);
    }

    protected function get_torrent_peers(int $torrent): array
    {
        $peers = $this->cache->get('peers_torrent_' . $torrent) ?: [];
        if (!empty($peers)) {
            return json_decode(gzuncompress($peers), true);
        }
        return $peers;
    }
}
