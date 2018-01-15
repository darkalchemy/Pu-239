<?php
/**
 * @param $id
 *
 * @return bool
 */
function remove_torrent_peers($id)
{
    global $cache;
    if (!is_int($id) || $id < 1) {
        return false;
    }
    $delete = 0;
    $seed_key = 'torrents_seeds_' . $id;
    $leech_key = 'torrents_leechs_' . $id;
    $comp_key = 'torrents_comps_' . $id;
    $delete += $cache->delete($seed_key);
    $delete += $cache->delete($leech_key);
    $delete += $cache->delete($comp_key);

    return (bool)$delete;
}

/**
 * @param $infohash
 *
 * @return bool
 */
function remove_torrent($infohash)
{
    global $cache;
    file_put_contents('/var/log/nginx/cache.log', 'pass 0' . PHP_EOL, FILE_APPEND);
    if (strlen($infohash) != 20 || !bin2hex($infohash)) {
        return false;
    }
    file_put_contents('/var/log/nginx/cache.log', 'pass 1' . PHP_EOL, FILE_APPEND);
    $key = 'torrent_hash_' . bin2hex($infohash);
    file_put_contents('/var/log/nginx/cache.log', $key . PHP_EOL, FILE_APPEND);
    $torrent = $cache->get($key);
    file_put_contents('/var/log/nginx/cache.log', json_encode($torrent) . PHP_EOL, FILE_APPEND);
    if ($torrent === false || is_null($torrent)) {
        $cache->delete($key);
        return false;
    }
    file_put_contents('/var/log/nginx/cache.log', 'pass 2' . PHP_EOL, FILE_APPEND);
    $cache->delete($key);
    if (is_array($torrent)) {
        remove_torrent_peers($torrent['id']);
    }
    file_put_contents('/var/log/nginx/cache.log', 'pass 3' . PHP_EOL, FILE_APPEND);

    $cache->delete('top5_tor_');
    $cache->delete('last5_tor_');
    $cache->delete('scroll_tor_');
    $cache->delete('torrent_details_' . $id);
    $cache->delete('torrent_details_text' . $id);

    return true;
}
