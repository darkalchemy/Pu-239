<?php

function getTVImagesByImdb($thetvdb_id, $type = 'showbackground', $season = 0)
{
    global $cache;

    $types = [
        'showbackground',
        'tvposter',
        'tvbanner',
        'seasonposter',
        'seasonbanner',
    ];

    if (0 != $season && ('banner' === $type || 'poster' === $type)) {
        $type = 'season' . $type;
    } elseif ('banner' === $type || 'poster' === $type) {
        $type = 'tv' . $type;
    }

    $key = $_ENV['FANART_API_KEY'];
    if (empty($key) || empty($thetvdb_id) || !in_array($type, $types)) {
        return null;
    }

    $fanart = $cache->get('show_images_' . $thetvdb_id);
    if ($fanart === false || is_null($fanart)) {
        $url    = 'http://webservice.fanart.tv/v3/tv/';
        $fanart = fetch($url . $thetvdb_id . '?api_key=' . $key);
        if (null != $fanart) {
            $fanart = json_decode($fanart, true);
            $cache->set('show_images_' . $thetvdb_id, $fanart, 604800);
        }
    }
    if ($fanart) {
        $images = [];
        foreach ($fanart[$type] as $image) {
            if (empty($image['lang']) || 'en' === $image['lang']) {
                if (0 != $season) {
                    if ($image['season'] == $season) {
                        $images[] = $image['url'];
                    }
                } else {
                    $images[] = $image['url'];
                }
            }
        }
        if (!empty($images)) {
            shuffle($images);

            return $images[0];
        }
    }

    return null;
}

/**
 * @param        $imdb
 * @param string $type
 *
 * @return null|string
 */
function getMovieImagesByImdb($imdb, $type = 'moviebackground')
{
    global $cache;

    $types = [
        'moviebackground',
        'movieposter',
        'moviebanner',
    ];
    $key = $_ENV['FANART_API_KEY'];
    if (empty($key) || empty($imdb) || !in_array($type, $types)) {
        return null;
    }

    $fanart = $cache->get('movie_images_' . $imdb);
    if ($fanart === false || is_null($fanart)) {
        $url    = 'http://webservice.fanart.tv/v3/movies/';
        $fanart = fetch($url . $imdb . '?api_key=' . $key);
        if (null != $fanart) {
            $fanart = json_decode($fanart, true);
            $cache->set('movie_images_' . $imdb, $fanart, 604800);
        }
    }
    if ($fanart) {
        $images = [];
        foreach ($fanart[$type] as $image) {
            if (empty($image['lang']) || 'en' === $image['lang']) {
                $images[] = $image['url'];
            }
        }
        if (!empty($images)) {
            shuffle($images);

            return $images[0];
        }
    }

    return null;
}

/**
 * @param $url
 *
 * @return null|\Psr\Http\Message\StreamInterface
 */
function fetch($url)
{
    $client = new GuzzleHttp\Client();
    $res    = $client->request('GET', $url);
    if (200 === $res->getStatusCode()) {
        return $res->getBody();
    }

    return null;
}
