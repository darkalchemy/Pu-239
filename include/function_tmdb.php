<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Image;

/**
 * @param $dates
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function get_tv_by_day($dates)
{
    global $container, $site_config, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }
    $cache = $container->get(Cache::class);
    $tmdb_data = $cache->get('tmdb_tv_' . $dates);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $site_config['api']['tmdb'];
        if (empty($apikey)) {
            return false;
        }
        $url = "https://api.themoviedb.org/3/discover/tv?air_date.gte={$dates}&air_date.lte={$dates}&api_key={$apikey}&with_original_language={$site_config['language']['tmdb']}";
        $content = fetch($url, false);
        if (!$content) {
            $cache->set('tmdb_tv_' . $dates, [], 3600);

            return false;
        }
        $json = json_decode($content, true);
        if (isset($json['results'])) {
            $pages = $json['total_pages'];
            $tmdb_data = get_movies($json);
            for ($i = 2; $i <= $pages; ++$i) {
                $purl = "$url&page=$i";
                $content = fetch($purl, false);
                $json = json_decode($content, true);
                $tmdb_data = array_merge($tmdb_data, get_movies($json));
            }
            usort($tmdb_data, 'nameSort');
            $cache->set('tmdb_tv_' . $dates, $tmdb_data, 86400);
        } else {
            $cache->set('tmdb_tv_' . $dates, [], 3600);

            return false;
        }
    }
    if (!empty($tmdb_data) && is_array($tmdb_data)) {
        foreach ($tmdb_data as $movie) {
            $imdb_id = get_imdbid($movie['id']);
            $imdb_id = !empty($imdb_id) ? $imdb_id : '';
            if (!empty($movie['poster_path'])) {
                insert_image((int) $movie['id'], $imdb_id, "https://image.tmdb.org/t/p/original{$movie['poster_path']}", 'poster');
            }
            if (!empty($movie['backdrop_path'])) {
                insert_image((int) $movie['id'], $imdb_id, "https://image.tmdb.org/t/p/original{$movie['backdrop_path']}", 'background');
            }
        }
    }

    return $tmdb_data;
}

/**
 * @param $dates
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool
 */
function get_movies_by_week($dates)
{
    global $site_config, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $apikey = $site_config['api']['tmdb'];
    if (empty($apikey)) {
        return false;
    }
    $url = "https://api.themoviedb.org/3/discover/movie?primary_release_date.gte={$dates[0]}&primary_release_date.lte={$dates[1]}&api_key=$apikey&sort_by=release_date.asc&include_adult=false&include_video=false&with_original_language={$site_config['language']['tmdb']}";
    $content = fetch($url, false);
    if (!$content) {
        return false;
    }
    $json = json_decode($content, true);
    $pages = $json['total_pages'];
    $tmdb_data = get_movies($json);

    for ($i = 2; $i <= $pages; ++$i) {
        $purl = "$url&page=$i";
        $content = fetch($purl, false);
        $json = json_decode($content, true);
        $tmdb_data = array_merge($tmdb_data, get_movies($json));
    }
    usort($tmdb_data, 'dateSort');

    return $tmdb_data;
}

/**
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function get_movies_in_theaters()
{
    global $container, $site_config, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }
    $cache = $container->get(Cache::class);
    $tmdb_data = $cache->get('tmdb_movies_in_theaters_');
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $site_config['api']['tmdb'];
        if (empty($apikey)) {
            return false;
        }
        $url = "https://api.themoviedb.org/3/movie/now_playing?api_key=$apikey&language={$site_config['language']['tmdb_movie']}&region={$site_config['language']['tmdb_movie_region']}";
        $content = fetch($url, false);
        if (!$content) {
            $cache->set('tmdb_movies_in_theaters_', 'failed', 3600);

            return false;
        }
        $json = json_decode($content, true);
        $pages = $json['total_pages'];
        $tmdb_data = get_movies($json);
        for ($i = 2; $i <= $pages; ++$i) {
            $purl = "$url&page=$i";
            $content = fetch($purl, false);
            $json = json_decode($content, true);
            $tmdb_data = array_merge($tmdb_data, get_movies($json));
        }
        $cache->set('tmdb_movies_in_theaters_', $tmdb_data, 86400);
    }
    if (!empty($tmdb_data)) {
        foreach ($tmdb_data as $movie) {
            $imdb_id = get_imdbid($movie['id']);
            $imdb_id = !empty($imdb_id) ? $imdb_id : '';
            if (!empty($movie['poster_path'])) {
                insert_image((int) $movie['id'], $imdb_id, "https://image.tmdb.org/t/p/original{$movie['poster_path']}", 'poster');
            }
            if (!empty($movie['backdrop_path'])) {
                insert_image((int) $movie['id'], $imdb_id, "https://image.tmdb.org/t/p/original{$movie['backdrop_path']}", 'background');
            }
            if (!empty($imdb_id)) {
                get_imdb_info_short($imdb_id);
            }
        }
    }

    return $tmdb_data;
}

/**
 * @param $count
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return array|bool|mixed
 */
function get_movies_by_vote_average($count)
{
    global $container, $site_config, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $page = $count / 20;
    $cache = $container->get(Cache::class);
    $cache->delete('tmdb_movies_vote_average_' . $count);
    $tmdb_data = $cache->get('tmdb_movies_vote_average_' . $count);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $site_config['api']['tmdb'];
        if (empty($apikey)) {
            return false;
        }
        $min_votes = 5000;
        $url = "https://api.themoviedb.org/3/discover/movie?api_key=$apikey&with_original_language={$site_config['language']['tmdb']}&language={$site_config['language']['tmdb_movie']}&sort_by=vote_average.desc&include_adult=false&include_video=false&vote_count.gte=$min_votes";
        $content = fetch($url, false);
        if (!$content) {
            $cache->set('tmdb_movies_vote_average_' . $count, 'failed', 3600);

            return false;
        }
        $json = json_decode($content, true);
        $pages = $json['total_pages'] <= $page ? $json['total_pages'] : $page;
        $tmdb_data = get_movies($json);
        for ($i = 2; $i <= $pages; ++$i) {
            $purl = "$url&page=$i";
            $content = fetch($purl, false);
            $json = json_decode($content, true);
            $tmdb_data = array_merge($tmdb_data, get_movies($json));
        }
        $cache->set('tmdb_movies_vote_average_' . $count, $tmdb_data, 86400);
    }
    if (!empty($tmdb_data)) {
        foreach ($tmdb_data as $movie) {
            $imdb_id = get_imdbid($movie['id']);
            $imdb_id = !empty($imdb_id) ? $imdb_id : '';
            if (!empty($movie['poster_path'])) {
                insert_image((int) $movie['id'], $imdb_id, "https://image.tmdb.org/t/p/original{$movie['poster_path']}", 'poster');
            }
            if (!empty($movie['backdrop_path'])) {
                insert_image((int) $movie['id'], $imdb_id, "https://image.tmdb.org/t/p/original{$movie['backdrop_path']}", 'background');
            }
            if (!empty($imdb_id)) {
                get_imdb_info_short($imdb_id);
            }
        }
    }

    return $tmdb_data;
}

/**
 * @param $imdbid
 * @param $type
 *
 * @throws Exception
 *
 * @return bool|mixed
 */
function get_movie_id($imdbid, $type)
{
    global $container, $site_config, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }
    $fluent = $container->get(Database::class);
    $id = $fluent->from('images')
                 ->select(null)
                 ->select($type)
                 ->where('imdb_id = ?', $imdbid)
                 ->limit(1)
                 ->fetch($type);

    if ($id) {
        if ($type === 'tmdb_id') {
            update_tmdb_id($id, $imdbid);
        }

        return $id;
    }

    $apikey = $site_config['api']['tmdb'];
    if (empty($apikey)) {
        return false;
    }

    $url = "https://api.themoviedb.org/3/movie/{$imdbid}?api_key={$apikey}&language={$site_config['language']['tmdb_movie']}";
    $content = fetch($url, false);
    if (!$content) {
        return false;
    }
    $json = json_decode($content, true);

    if (!empty($json['id'])) {
        if ($type === 'tmdb_id') {
            update_tmdb_id($json['id'], $imdbid);
        }

        return $json['id'];
    }

    return null;
}

/**
 * @param $json
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool
 */
function get_movies($json)
{
    global $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $movies = [];
    foreach ($json['results'] as $movie) {
        if ($movie['original_language'] === 'en') {
            if (!empty($movie['id'])) {
                $images = '';
                if (!empty($movie['poster_path'])) {
                    $images .= "({$movie['id']}, 'https://image.tmdb.org/t/p/original{$movie['poster_path']}', 'poster')";
                }
                if (!empty($movie['backdrop_path'])) {
                    $images .= (empty($images) ? '' : ', ') . "({$movie['id']}, 'https://image.tmdb.org/t/p/original{$movie['backdrop_path']}', 'background')";
                }
                if (!empty($images)) {
                    $sql = "INSERT IGNORE INTO images (tmdb_id, url, type) VALUES $images";
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);
                }
                $movies[] = $movie;
            }
        }
    }

    return $movies;
}

/**
 * @param $a
 * @param $b
 *
 * @return int
 */
function nameSort($a, $b)
{
    return strcmp($a['name'], $b['name']);
}

/**
 * @param $a
 * @param $b
 *
 * @return int
 */
function dateSort($a, $b)
{
    return strcmp($a['release_date'], $b['release_date']);
}

/**
 * @param $year
 * @param $week
 *
 * @throws Exception
 *
 * @return array
 */
function getStartAndEndDate($year, $week)
{
    return [
        // Sunday
        (new DateTime())->setISODate($year, $week, 0)
                        ->format('Y-m-d'),
        // Saturday
        (new DateTime())->setISODate($year, $week, 6)
                        ->format('Y-m-d'),
    ];
}

/**
 * @param $tmdbid
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return bool|null
 */
function get_imdbid($tmdbid)
{
    global $site_config, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $apikey = $site_config['api']['tmdb'];
    $url = "https://api.themoviedb.org/3/movie/{$tmdbid}/external_ids?api_key={$apikey}";
    $content = fetch($url, false);
    if (!$content) {
        return false;
    }
    $json = json_decode($content, true);

    if (!empty($json['imdb_id'])) {
        return $json['imdb_id'];
    }

    return null;
}

/**
 * @param $tmdb_id
 * @param $imdb_id
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function update_tmdb_id($tmdb_id, $imdb_id)
{
    global $container;

    $set = [
        'tmdb_id' => $tmdb_id,
    ];
    $fluent = $container->get(Database::class);
    $fluent->update('images')
           ->set($set)
           ->where('imdb_id = ?', $imdb_id)
           ->execute();
}

/**
 * @param int    $tmdb_id
 * @param string $imdb_id
 * @param string $url
 * @param string $type
 *
 * @throws DependencyException
 * @throws NotFoundException
 */
function insert_image(int $tmdb_id, string $imdb_id, string $url, string $type)
{
    global $container;

    $images_class = $container->get(Image::class);
    $cache = $container->get(Cache::class);
    $hash = hash('sha256', $url);
    $inserted = $cache->get('insert_image_' . $hash);
    if ($inserted === false || is_null($inserted)) {
        $values = [
            'tmdb_id' => $tmdb_id,
            'imdb_id' => $imdb_id,
            'url' => $url,
            'type' => $type,
        ];

        $images_class->insert($values);
        $cache->get($type . '_' . $imdb_id);
        $cache->set('insert_image_' . $hash, 'inserted', 86400);
    }
}
