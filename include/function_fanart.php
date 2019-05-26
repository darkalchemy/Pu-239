<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Database;
use Pu239\Image;

/**
 * @param        $thetvdb_id
 * @param string $type
 * @param int    $season
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return bool|mixed
 */
function getTVImagesByTVDb($thetvdb_id, $type = 'showbackground', $season = 0)
{
    global $container, $BLOCKS, $site_config;

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

    $key = $site_config['api']['fanart'];
    if (empty($key) || empty($thetvdb_id) || !in_array($type, $types)) {
        return false;
    }
    $url = 'https://webservice.fanart.tv/v3/tv/';
    $fanart = fetch($url . $thetvdb_id . '?api_key=' . $key, false);
    if ($fanart != null) {
        $fanart = json_decode($fanart, true);
    } else {
        return false;
    }
    if (!empty($fanart[$type])) {
        $images = [];
        $fluent = $container->get(Database::class);
        foreach ($fanart[$type] as $image) {
            if (!empty($site_config['fanart']['image_lang']) && (empty($image['lang']) || in_array($image['lang'], $site_config['fanart']['image_lang']))) {
                if ($season != 0) {
                    if ($image['season'] == $season) {
                        $images[] = $image['url'];
                    }
                } else {
                    $images[] = $image['url'];
                }
            } elseif (empty($site_config['fanart']['image_lang'])) {
                $images[] = $image['url'];
            }
        }
        if (!empty($images)) {
            $type = str_replace([
                'tv',
                'show',
                'season',
            ], '', $type);
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
            }

            shuffle($images);

            return $images[0];
        }
    }

    return false;
}

/**
 * @param string $id
 * @param bool   $store
 * @param string $type
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool|mixed
 */
function getMovieImagesByID(string $id, bool $store, string $type = 'moviebackground')
{
    global $container, $BLOCKS, $site_config;

    if (!$BLOCKS['fanart_api_on']) {
        return false;
    }
    $types = [
        'moviebackground',
        'movieposter',
        'moviebanner',
    ];
    $key = $site_config['api']['fanart'];
    if (empty($key) || empty($id) || !in_array($type, $types)) {
        return false;
    }
    $url = 'https://webservice.fanart.tv/v3/movies/';
    $fanart = fetch($url . $id . '?api_key=' . $key, false);

    if ($fanart) {
        $fanart = json_decode($fanart, true);
    } else {
        return false;
    }
    if (!empty($fanart[$type])) {
        $images = [];
        foreach ($fanart[$type] as $image) {
            $image = [
                'imdb_id' => $fanart['imdb_id'],
                'tmdb_id' => $fanart['tmdb_id'],
                'url' => $image['url'],
                'type' => str_replace('movie', '', $type),
                'updated' => TIME_NOW,
            ];
            if (!empty($site_config['fanart']['image_lang']) && (empty($image['lang']) || in_array($image['lang'], $site_config['fanart']['image_lang']))) {
                $images[] = $image;
            } elseif (empty($site_config['fanart']['image_lang'])) {
                $images[] = $image;
            }
        }
        if (!empty($images)) {
            if ($store) {
                $image_stuffs = $container->get(Image::class);
                $image_stuffs->insert($images);
                shuffle($images);

                return $images[0]['url'];
            } else {
                return $images;
            }
        }
    }

    return false;
}
