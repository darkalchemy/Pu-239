<?php

/**
 * @param $dates
 *
 * @return array|bool|mixed
 */
function get_tv_by_day($dates)
{
    global $cache, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $tmdb_data = $cache->get('tmdb_tv_' . $dates);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $_ENV['TMDB_API_KEY'];
        if (empty($apikey)) {
            return false;
        }
        $url = "https://api.themoviedb.org/3/discover/tv?air_date.gte={$dates}&air_date.lte={$dates}&api_key=$apikey&with_original_language=en";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_tv_' . $dates, 0, 900);

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
    global $cache, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $tmdb_data = $cache->get('tmdb_movies_' . $dates[0]);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $_ENV['TMDB_API_KEY'];
        if (empty($apikey)) {
            return false;
        }
        $url = "https://api.themoviedb.org/3/discover/movie?primary_release_date.gte={$dates[0]}&primary_release_date.lte={$dates[1]}&api_key=$apikey&sort_by=release_date.asc&include_adult=false&include_video=false&with_original_language=en";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_movies_' . $dates[0], 0, 900);

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
        $cache->set('tmdb_movies_' . $dates[0], $tmdb_data, 86400);
    }

    return $tmdb_data;
}

/**
 * @return array|bool|mixed
 */
function get_movies_in_theaters()
{
    global $cache, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $tmdb_data = $cache->get('tmdb_movies_in_theaters_');
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $_ENV['TMDB_API_KEY'];
        if (empty($apikey)) {
            return false;
        }
        $url = "https://api.themoviedb.org/3/movie/now_playing?api_key=$apikey&language=en-US&region=US";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_movies_in_theaters_', 0, 900);

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
    global $cache, $BLOCKS;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $page = $count / 20;
    $tmdb_data = $cache->get('tmdb_movies_vote_average_' . $count);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $_ENV['TMDB_API_KEY'];
        if (empty($apikey)) {
            return false;
        }
        $min_votes = 5000;
        $url = "https://api.themoviedb.org/3/discover/movie?api_key=$apikey&with_original_language=en&language=en-US&sort_by=vote_average.desc&include_adult=false&include_video=false&vote_count.gte=$min_votes";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_movies_vote_average_' . $count, 0, 900);

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
    global $cache, $BLOCKS, $fluent;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }
    $id = $cache->get('tmdb_imdbid_tmdbid_' . $imdbid);
    if ($id === false || is_null($id)) {
        $id = $fluent->from('images')
            ->select(null)
            ->select($type)
            ->where('imdb_id = ?', $imdbid)
            ->limit(1)
            ->fetch($type);

        if ($id) {
            $cache->set('tmdb_imdbid_tmdbid_' . $imdbid, $id, 86400);
        } else {
            $cache->set('tmdb_imdbid_tmdbid_' . $imdbid, 0, 86400);
        }
    }

    if ($id) {
        return $id;
    }
    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $apikey = $_ENV['TMDB_API_KEY'];
    if (empty($apikey)) {
        return false;
    }

    $json = $cache->get('tmdb_movie_' . $id);
    if ($json === false || is_null($json)) {
        $url = "https://api.themoviedb.org/3/movie/{$imdbid}?api_key={$apikey}&language=en-US";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_movies_by_id_' . $imdbid, 0, 86400);

            return false;
        }
        $json = json_decode($content, true);
        $cache->set('tmdb_movie_' . $imdbid, $json, 86400);
    }
    if (!empty($json['id'])) {
        if ($type === 'tmdb_id') {
            $set = [
                'tmdb_id' => $json['id'],
            ];
            $fluent->update('images')
                ->set($set)
                ->where('imdb_id = ?', $imdbid)
                ->execute();
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
                $insert = $cache->get('insert_tmdb_tmdbid_' . $movie['id']);
                if ($insert === false || is_null($insert)) {
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

                    $cache->set('insert_tmdb_tmdbid_' . $movie['id'], 0, 604800);
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

function get_imdbid($tmdbid)
{
    global $cache, $BLOCKS, $fluent;

    if (!$BLOCKS['tmdb_api_on']) {
        return false;
    }

    $json = $cache->get('tmdb_get_imdbid_' . $tmdbid);
    if ($json === false || is_null($json)) {
        $apikey = $_ENV['TMDB_API_KEY'];
        $url = "https://api.themoviedb.org/3/movie/{$tmdbid}/external_ids?api_key={$apikey}";
        $content = fetch($url);
        if (!$content) {
            $cache->set('tmdb_movies_by_id_' . $tmdbid, 0, 86400);

            return false;
        }
        $json = json_decode($content, true);
        $cache->set('tmdb_get_imdbid_' . $tmdbid, $json, 86400);
    }

    if (!empty($json['imdb_id'])) {
        return $json['imdb_id'];
    }
}
