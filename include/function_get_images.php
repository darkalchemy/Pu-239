<?php

require_once INCL_DIR . 'function_tvmaze.php';
require_once INCL_DIR . 'function_books.php';
require_once INCL_DIR . 'function_imdb.php';
require_once INCL_DIR . 'function_omdb.php';
require_once INCL_DIR . 'function_tmdb.php';
require_once INCL_DIR . 'function_fanart.php';

function get_image_by_id($media, $tid, $imdb, $type, $season = null, $save = true)
{
    if ($media === 'tv') {
        $image = getTVImagesByTVDb($imdb, $type, $season);
    } elseif ($media === 'movie') {
        $image = getMovieImagesByID($imdb, $type);
    } else {
        $tmdbid = get_movie_id($imdb, 'tmdb_id');
        $image = getMovieImagesByID($tmdbid, $type);
    }
    if (!empty($image) && $save) {
        save_changes($tid, $type, $image);
    }

    return $image;
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
