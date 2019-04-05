<?php

/**
 * @param $dates
 *
 * @return array|bool|mixed
 */
function get_tv_by_day($dates)
{
    global $cache, $BLOCKS, $site_config;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $tmdb_data = $cache->get('tmdb_tv_' . $dates);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $site_config['api']['tmdb'];
        if (empty($apikey)) {
            return false;
        }
        $url = "https://api.themoviedb.org/3/discover/tv?air_date.gte={$dates}&air_date.lte={$dates}&api_key=$apikey&with_original_language={$site_config['language']['tmdb']}";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_tv_' . $dates, 'failed', 3600);

            return false;
        }
        $json = json_decode($content, true);
        $pages = $json['total_pages'];
        $tmdb_data = get_movies($json);
        for ($i = 2; $i <= $pages; ++$i) {
            $purl = "$url&page=$i";
            $content = fetch($purl);
            $json = json_decode($content, true);
            $tmdb_data = array_merge($tmdb_data, get_movies($json));
        }
        usort($tmdb_data, 'nameSort');
        $cache->set('tmdb_tv_' . $dates, $tmdb_data, 86400);
    }

    return $tmdb_data;
}

/**
 * @param $dates
 *
 * @return array|bool|mixed
 */
function get_movies_by_week($dates)
{
    global $cache, $BLOCKS, $site_config;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $apikey = $site_config['api']['tmdb'];
    if (empty($apikey)) {
        return false;
    }
    $url = "https://api.themoviedb.org/3/discover/movie?primary_release_date.gte={$dates[0]}&primary_release_date.lte={$dates[1]}&api_key=$apikey&sort_by=release_date.asc&include_adult=false&include_video=false&with_original_language={$site_config['language']['tmdb']}";
    $content = fetch($url);
    if (!$content) {
        return false;
    }
    $json = json_decode($content, true);
    $pages = $json['total_pages'];
    $tmdb_data = get_movies($json);

    for ($i = 2; $i <= $pages; ++$i) {
        $purl = "$url&page=$i";
        $content = fetch($purl);
        $json = json_decode($content, true);
        $tmdb_data = array_merge($tmdb_data, get_movies($json));
    }
    usort($tmdb_data, 'dateSort');

    return $tmdb_data;
}

/**
 * @return array|bool|mixed
 */
function get_movies_in_theaters()
{
    global $cache, $BLOCKS, $site_config;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $tmdb_data = $cache->get('tmdb_movies_in_theaters_');
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $site_config['api']['tmdb'];
        if (empty($apikey)) {
            return false;
        }
        $url = "https://api.themoviedb.org/3/movie/now_playing?api_key=$apikey&language={$site_config['language']['tmdb_movie']}&region={$site_config['language']['tmdb_movie_region']}";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_movies_in_theaters_', 'failed', 3600);

            return false;
        }
        $json = json_decode($content, true);
        $pages = $json['total_pages'];
        $tmdb_data = get_movies($json);
        for ($i = 2; $i <= $pages; ++$i) {
            $purl = "$url&page=$i";
            $content = fetch($purl);
            $json = json_decode($content, true);
            $tmdb_data = array_merge($tmdb_data, get_movies($json));
        }
        $cache->set('tmdb_movies_in_theaters_', $tmdb_data, 86400);
    }

    return $tmdb_data;
}

/**
 * @param $count
 *
 * @return array|bool|mixed
 */
function get_movies_by_vote_average($count)
{
    global $cache, $BLOCKS, $site_config;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $page = $count / 20;
    $tmdb_data = $cache->get('tmdb_movies_vote_average_' . $count);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $site_config['api']['tmdb'];
        if (empty($apikey)) {
            return false;
        }
        $min_votes = 5000;
        $url = "https://api.themoviedb.org/3/discover/movie?api_key=$apikey&with_original_language={$site_config['language']['tmdb']}&language={$site_config['language']['tmdb_movie']}&sort_by=vote_average.desc&include_adult=false&include_video=false&vote_count.gte=$min_votes";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_movies_vote_average_' . $count, 'failed', 3600);

            return false;
        }
        $json = json_decode($content, true);
        $pages = $json['total_pages'] <= $page ? $json['total_pages'] : $page;
        $tmdb_data = get_movies($json);

        for ($i = 2; $i <= $pages; ++$i) {
            $purl = "$url&page=$i";
            $content = fetch($purl);
            $json = json_decode($content, true);
            $tmdb_data = array_merge($tmdb_data, get_movies($json));
        }
        $cache->set('tmdb_movies_vote_average_' . $count, $tmdb_data, 86400);
    }

    return $tmdb_data;
}

/**
 * @param $imdbid
 * @param $type
 *
 * @return bool|mixed
 *
 * @throws Exception
 */
function get_movie_id($imdbid, $type)
{
    global $cache, $BLOCKS, $fluent, $site_config;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $id = $fluent->from('images')
        ->select(null)
        ->select($type)
        ->where('imdb_id=?', $imdbid)
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
    $content = fetch($url);
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
}

/**
 * @param $json
 *
 * @return array|bool
 */
function get_movies($json)
{
    global $cache, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $movies = [];
    foreach ($json['results'] as $movie) {
        if ($movie['original_language'] === 'en') {
            if (!empty($movie['id'])) {
                $images = '';
                if (!empty($movie['poster_path'])) {
                    $images .= "({$movie['id']}, 'https://image.tmdb.org/t/p/w185{$movie['poster_path']}', 'poster')";
                }
                if (!empty($movie['backdrop_path'])) {
                    $images .= (empty($images) ? '' : ', ') . "({$movie['id']}, 'https://image.tmdb.org/t/p/w1280{$movie['backdrop_path']}', 'background')";
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
 * @return array
 *
 * @throws Exception
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
 * @return bool
 */
function get_imdbid($tmdbid)
{
    global $cache, $BLOCKS, $site_config;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $apikey = $site_config['api']['tmdb'];
    $url = "https://api.themoviedb.org/3/movie/{$tmdbid}/external_ids?api_key={$apikey}";
    $content = fetch($url);
    if (!$content) {
        return false;
    }
    $json = json_decode($content, true);

    if (!empty($json['imdb_id'])) {
        return $json['imdb_id'];
    }
}

function update_tmdb_id($tmdb_id, $imdb_id)
{
    global $fluent;

    $set = [
        'tmdb_id' => $tmdb_id,
    ];
    $fluent->update('images')
        ->set($set)
        ->where('imdb_id=?', $imdb_id)
        ->execute();
}
