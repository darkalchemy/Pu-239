<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;

require_once INCL_DIR . 'function_tmdb.php';
require_once INCL_DIR . 'function_fanart.php';

/**
 * @param string   $media
 * @param string   $imdb
 * @param string   $type
 * @param int|null $season
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return array|bool|mixed
 */
function get_image_by_id(string $media, string $imdb, string $type, ?int $season = null)
{
    $image = '';
    if ($media === 'tv') {
        $image = getTVImagesByTVDb($imdb, $type, $season);
    } elseif ($media === 'movie') {
        $image = getMovieImagesByID($imdb, true, $type);
    } else {
        $tmdbid = get_movie_id($imdb, 'tmdb_id');
        if ($tmdbid) {
            $image = getMovieImagesByID((string) $tmdbid, true, $type);
        }
    }

    return $image;
}
