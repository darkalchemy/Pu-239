<?php

require_once INCL_DIR . 'ann_functions.php';

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
    $torrent = get_torrent_from_hash($infohash);
    if (is_array($torrent)) {
        remove_torrent_peers($torrent['id']);
        $key = 'torrent_hash_' . bin2hex($infohash);
        $cache->deleteMulti([
            $key,
            'peers_' . $torrent['owner'],
            'coin_points_' . $torrent['id'],
            'latest_comments_',
            'top5_tor_',
            'last5_tor_',
            'scroll_tor_',
            'torrent_details_' . $torrent['id'],
            'torrent_details_txt_' . $torrent['id'],
            'lastest_tor_',
            'slider_tor_',
            'torrent_poster_count_',
            'torrent_banner_count_',
            'backgrounds_',
            'posters_',
            'similiar_tor_' . $torrent['id'],
        ]);
        $hashes = $cache->get('hashes_');
        if (!empty($hashes)) {
            foreach ($hashes as $hash) {
                $cache->delete('suggest_torrents_' . $hash);
            }
            $cache->delete('hashes_');
        }
    }

    return true;
}
