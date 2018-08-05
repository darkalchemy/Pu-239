<?php

require_once INCL_DIR . 'function_tvmaze.php';
require_once INCL_DIR . 'function_books.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_omdb.php';
require_once INCL_DIR . 'function_tmdb.php';
require_once INCL_DIR . 'function_fanart.php';

function get_image_by_id($media, $tid, $id, $type, $season = null)
{
    if ($media === 'tv') {
        $image = getTVImagesByTVDb($id, $type, $season);
    } elseif ($media === 'movie') {
        $image = getMovieImagesByID($id, $type);
    } else {
        $id = get_movie_id($id, 'tmdb_id');
        $image = getMovieImagesByID($id, $type);
    }

    if (!empty($image)) {
        save_changes($tid, $type, $image);
    }
}

function save_changes($tid, $type, $poster)
{
    global $cache, $fluent, $site_config, $torrents;

    $type = str_replace([
        'movieposter',
        'moviebanner',
        'moviebackground',
        'showbackground',
    ], [
        'poster',
        'banner',
        'background',
        'background',
    ], $type);
    $set = [
        $type => $poster,
    ];
    $cache->update_row('torrent_details_' . $tid, $set, $site_config['expires']['torrent_details']);
    $fluent->update('torrents')
        ->set($set)
        ->where('id = ?', $tid)
        ->execute();
    $torrents[$type] = $poster;
    if ($type === 'background') {
        $cache->delete('backgrounds_');
    }
    clear_image_cache();
}
