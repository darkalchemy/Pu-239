<?php

/**
 * @param        $thetvdb_id
 * @param string $type
 * @param int    $season
 *
 * @return bool|mixed
 */
function getTVImagesByTVDb($thetvdb_id, $type = 'showbackground', $season = 0)
{
    global $cache, $BLOCKS;

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
            $cache->set('show_images_' . $thetvdb_id, 0, 86400);

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
    global $cache, $BLOCKS, $fluent;

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

    $fanart = $cache->get('movie_images_' . $id);
    if ($fanart === false || is_null($fanart)) {
        $url = 'https://webservice.fanart.tv/v3/movies/';
        $fanart = fetch($url . $id . '?api_key=' . $key);
        if ($fanart) {
            $fanart = json_decode($fanart, true);
            $cache->set('movie_images_' . $id, $fanart, 604800);
        } else {
            $cache->set('movie_images_' . $id, 0, 86400);

            return false;
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
            $insert = $cache->get('insert_fanart_id_' . $id);
            if ($insert === false || is_null($insert)) {
                foreach ($images as $image) {
                    $type = str_replace('movie', '', $type);
                    if (!preg_match('/^tt/', $id)) {
                        $values = [
                            'tmdb_id' => $id,
                            'url' => $image,
                            'type' => $type,
                        ];
                    } else {
                        $values = [
                            'imdb_id' => $id,
                            'url' => $image,
                            'type' => $type,
                        ];
                    }
                    $fluent->insertInto('images')
                        ->values($values)
                        ->ignore()
                        ->execute();

                    $cache->set('insert_fanart_imdb_' . $id, 0, 604800);
                }
            }
            shuffle($images);

            return $images[0];
        }
    }

    return false;
}
