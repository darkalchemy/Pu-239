<?php

require_once INCL_DIR . 'function_tmdb.php';
require_once INCL_DIR . 'function_fanart.php';

/**
 * @param      $media
 * @param      $tid
 * @param      $imdb
 * @param      $type
 * @param null $season
 *
 * @return bool|mixed
 *
 * @throws Exception
 */
function get_image_by_id($media, $tid, $imdb, $type, $season = null)
{
    if ($media === 'tv') {
        $image = getTVImagesByTVDb($imdb, $type, $season);
    } elseif ($media === 'movie') {
        $image = getMovieImagesByID($imdb, $type);
    } else {
        $tmdbid = get_movie_id($imdb, 'tmdb_id');
        $image = getMovieImagesByID($tmdbid, $type);
    }

    return $image;
}
