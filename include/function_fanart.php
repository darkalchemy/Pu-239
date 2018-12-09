<?php

/**
 * @param        $thetvdb_id
 * @param string $type
 * @param int    $season
 *
 * @return bool|mixed
 *
 * @throws \Envms\FluentPDO\Exception
 */
function getTVImagesByTVDb($thetvdb_id, $type = 'showbackground', $season = 0)
{
    global $cache, $BLOCKS, $fluent;

    if (!$BLOCKS['fanart_api_on']) {
        return false;
    }

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
        return false;
    }
    $fanart = $cache->get('show_images_' . $thetvdb_id);
    if ($fanart === false || is_null($fanart)) {
        $url = 'https://webservice.fanart.tv/v3/tv/';
        $fanart = fetch($url . $thetvdb_id . '?api_key=' . $key);
        if ($fanart != null) {
            $fanart = json_decode($fanart, true);
            $cache->set('show_images_' . $thetvdb_id, $fanart, 604800);
        } else {
            $cache->set('show_images_' . $thetvdb_id, 'failed', 86400);

            return false;
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
            $type = str_replace([
                'tv',
                'show',
                'season',
            ], '', $type);
            $insert = $cache->get("insert_fanart_{$type}_{$thetvdb_id}");
            if ($insert === false || is_null($insert)) {
                foreach ($images as $image) {
                    $values = [
                        'imdb_id' => $fanart['imdb_id'],
                        'tmdb_id' => $fanart['tmdb_id'],
                        'thetvdb_id' => $thetvdb_id,
                        'url' => $image,
                        'type' => $type,
                    ];
                    $fluent->insertInto('images')
                        ->values($values)
                        ->ignore()
                        ->execute();

                    $cache->set("insert_fanart_{$type}_{$thetvdb_id}", $thetvdb_id, 604800);
                }
            }

            shuffle($images);

            return $images[0];
        }
    }

    return false;
}

/**
 * @param        $id
 * @param string $type
 *
 * @return bool|mixed
 *
 * @throws Exception
 */
function getMovieImagesByID($id, $type = 'moviebackground')
{
    global $cache, $BLOCKS, $image_stuffs;

    if (!$BLOCKS['fanart_api_on']) {
        return false;
    }

    $types = [
        'moviebackground',
        'movieposter',
        'moviebanner',
    ];
    $key = $_ENV['FANART_API_KEY'];
    if (empty($key) || empty($id) || !in_array($type, $types)) {
        return false;
    }

    $fanart = $cache->get("movie_images_{$type}_{$id}");
    if ($fanart === false || is_null($fanart)) {
        $url = 'https://webservice.fanart.tv/v3/movies/';
        $fanart = fetch($url . $id . '?api_key=' . $key);
        if ($fanart) {
            $fanart = json_decode($fanart, true);
            $cache->set("movie_images_{$type}_{$id}", $fanart, 604800);
        } else {
            $cache->set("movie_images_{$type}_{$id}", 'failed', 86400);

            return false;
        }
    }

    if (!empty($fanart[$type])) {
        $images = [];
        foreach ($fanart[$type] as $image) {
            if (empty($image['lang']) || $image['lang'] === 'en') {
                $images[] = [
                    'imdb_id' => $fanart['imdb_id'],
                    'tmdb_id' => $fanart['tmdb_id'],
                    'url' => $image['url'],
                    'type' => str_replace('movie', '', $type),
                ];
            }
        }
        if (!empty($images)) {
            $insert = $cache->get("insert_fanart_{$type}_{$id}");
            if ($insert === false || is_null($insert)) {
                $image_stuffs->insert($images);
                $cache->set("insert_fanart_{$type}_{$id}", $id, 604800);
            }
            shuffle($images);

            return $images[0]['url'];
        }
    }

    return false;
}
