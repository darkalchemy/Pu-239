<?php

/**
 * @param string $imdb_id
 *
 * @return bool
 *
 * @throws \Envms\FluentPDO\Exception
 */
function find_images(string $imdb_id, $type = 'poster')
{
    global $cache, $fluent;

    $images = $cache->get($type . '_' . $imdb_id);
    if ($images === false || is_null($images)) {
        $images = $fluent->from('images')
            ->select(null)
            ->select('url')
            ->where('type = ?', $type)
            ->where('imdb_id=?', $imdb_id)
            ->fetchAll();

        $cache->set($type . '_' . $imdb_id, $images, 0);
    }

    if ($images) {
        shuffle($images);
        $image = $images[0]['url'];

        return $image;
    }

    return false;
}
