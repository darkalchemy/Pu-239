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
    $seed_key = 'torrents::seeds:::' . $id;
    $leech_key = 'torrents::leechs:::' . $id;
    $comp_key = 'torrents::comps:::' . $id;
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
    if (strlen($infohash) != 20 || !bin2hex($infohash)) {
        return false;
    }
    $key = 'torrent::hash:::' . md5($infohash);
    $torrent = $cache->get($key);
    if ($torrent === false || is_null($torrent)) {
        return false;
    }
    $cache->delete($key);
    if (is_array($torrent)) {
        remove_torrent_peers($torrent['id']);
    }

    return true;
}
