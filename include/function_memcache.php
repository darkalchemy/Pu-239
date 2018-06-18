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

    return (bool) $delete;
}

/**
 * @param $infohash
 *
 * @return bool
 */
function remove_torrent($infohash)
{
    global $cache;

    if (strlen($infohash) != 20 || !bin2hex($infohash)) {
        return false;
    }
    $key = 'torrent_hash_' . bin2hex($infohash);
    $torrent = $cache->get($key);
    if ($torrent === false || is_null($torrent)) {
        $cache->delete($key);

        return false;
    }
    $cache->delete($key);
    if (is_array($torrent)) {
        remove_torrent_peers($torrent['id']);
    }

    $cache->delete('top5_tor_');
    $cache->delete('last5_tor_');
    $cache->delete('scroll_tor_');
    $cache->delete('torrent_details_' . $torrent['id']);
    $cache->delete('torrent_details_text' . $torrent['id']);

    return true;
}
