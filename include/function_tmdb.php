<?php

function get_tv_by_day($dates)
{
    global $cache;

    $tmdb_data = $cache->get('tmdb_tv_' . $dates);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $_ENV['TMDB_API_KEY'];
        $url = "https://api.themoviedb.org/3/discover/tv?air_date.gte={$dates}&air_date.lte={$dates}&api_key=$apikey";
        $content = fetch($url);
        if (!$content) {
            return false;
        }
        $json = json_decode($content, true);
        $pages = $json['total_pages'];
        $tmdb_data = get_movies($json);
        for ($i = 2; $i <= $pages; ++$i) {
            $purl = "$url&page=$i";
            $content = fetch($url);
            $json = json_decode($content, true);
            $tmdb_data = array_merge($tmdb_data, get_movies($json));
        }
        usort($tmdb_data, 'nameSort');
        $cache->set('tmdb_tv_' . $dates, $tmdb_data, 86400);
    }

    return $tmdb_data;
}

function get_movies_by_week($dates)
{
    global $cache;

    $tmdb_data = $cache->get('tmdb_movies_' . $dates[0]);
    if ($tmdb_data === false || is_null($tmdb_data)) {
        $apikey = $_ENV['TMDB_API_KEY'];
        $url = "https://api.themoviedb.org/3/discover/movie?primary_release_date.gte={$dates[0]}&primary_release_date.lte={$dates[1]}&api_key=$apikey&sort_by=release_date.asc&include_adult=false&include_video=false";
        $content = fetch($url);
        if (!$content) {
            return false;
        }
        $json = json_decode($content, true);
        $pages = $json['total_pages'];
        $tmdb_data = get_movies($json);

        for ($i = 2; $i <= $pages; ++$i) {
            $purl = "$url&page=$i";
            $content = fetch($url);
            $json = json_decode($content, true);
            $tmdb_data = array_merge($tmdb_data, get_movies($json));
        }
        usort($tmdb_data, 'dateSort');
        $cache->set('tmdb_movies_' . $dates[0], $tmdb_data, 86400);
    }

    return $tmdb_data;
}

function get_movies($json)
{
    foreach ($json['results'] as $movie) {
        if ($movie['original_language'] === 'en') {
            $movies[] = $movie;
        }
    }

    return $movies;
}

function nameSort($a, $b)
{
    return strcmp($a['name'], $b['name']);
}

function dateSort($a, $b)
{
    return strcmp($a['release_date'], $b['release_date']);
}

function getStartAndEndDate($year, $week)
{
    return [
      (new DateTime())->setISODate($year, $week, 0)->format('Y-m-d'), // Sunday
      (new DateTime())->setISODate($year, $week, 6)->format('Y-m-d'),  // Saturday
   ];
}
