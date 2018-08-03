<?php

function getTVImagesByTVDb($thetvdb_id, $type = 'showbackground', $season = 0)
{
    global $cache;

    $types = [
        'showbackground',
        'tvposter',
        'tvbanner',
        'seasonposter',
        'seasonbanner',
    ];

    if ($season != 0 && ($type === 'banner' || $type === 'poster')) {
        $type = 'season' . $type;
    } elseif ($type === 'banner' || $type === 'poster') {
        $type = 'tv' . $type;
    }

    $key = $_ENV['FANART_API_KEY'];
    if (empty($key) || empty($thetvdb_id) || !in_array($type, $types)) {
        return null;
    }
    $fanart = $cache->get('show_images_' . $thetvdb_id);
    if ($fanart === false || is_null($fanart)) {
        $url = 'http://webservice.fanart.tv/v3/tv/';
        $fanart = fetch($url . $thetvdb_id . '?api_key=' . $key);
        if ($fanart != null) {
            $fanart = json_decode($fanart, true);
            $cache->set('show_images_' . $thetvdb_id, $fanart, 604800);
        } else {
            $cache->set('show_images_' . $thetvdb_id, 0, 86400);

            return null;
        }
    }
    if (!empty($fanart[$type])) {
        $images = [];
        foreach ($fanart[$type] as $image) {
            if (empty($image['lang']) || $image['lang'] === 'en') {
                if ($season != 0) {
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
        $url = 'http://webservice.fanart.tv/v3/movies/';
        $fanart = fetch($url . $imdb . '?api_key=' . $key);
        if ($fanart != null) {
            $fanart = json_decode($fanart, true);
            $cache->set('movie_images_' . $imdb, $fanart, 604800);
        } else {
            $cache->set('movie_images_' . $imdb, 0, 86400);

            return null;
        }
    }
    if (!empty($fanart[$type])) {
        $images = [];
        foreach ($fanart[$type] as $image) {
            if (empty($image['lang']) || $image['lang'] === 'en') {
                $images[] = $image['url'];
            }
        }
        if (!empty($images)) {
            $insert = $cache->get('insert_fanart_imdb_' . $imdb);
            if ($insert === false || is_null($insert)) {
                $insert = '';
                foreach ($images as $image) {
                    $type = str_replace('movie', '', $type);
                    $insert .= (empty($insert) ? '' : ', ') . "('$imdb', '$image', '$type')";
                }
                if (!empty($insert)) {
                    $sql = "INSERT IGNORE INTO images (imdb_id, url, type) VALUES $insert";
                    sql_query($sql) or sqlerr(__FILE__, __LINE__);
                }
                $cache->set('insert_fanart_imdb_' . $imdb, 0, 604800);
            }
            shuffle($images);

            return $images[0];
        }
    }

    return null;
}
