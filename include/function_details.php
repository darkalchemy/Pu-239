<?php

/**
 * @param $imdb_id
 *
 * @return bool
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_banner($imdb_id)
{
    global $cache, $fluent;

    if (!empty($imdb_id)) {
        $images = $cache->get('banners_' . $imdb_id);
        if ($images === false || is_null($images)) {
            $images = $fluent->from('images')
                             ->select(null)
                             ->select('url')
                             ->where('type = "banner"')
                             ->where('imdb_id = ?', $imdb_id)
                             ->fetchAll();

            $cache->set('banners_' . $imdb_id, $images, 86400);
        }

        if (!empty($images)) {
            shuffle($images);

            return $images[0]['url'];
        }

        return false;
    }
}

/**
 * @param $imdb_id
 *
 * @return bool
 *
 * @throws \Envms\FluentPDO\Exception
 */
function get_poster($imdb_id)
{
    global $cache, $fluent;

    if (!empty($imdb_id)) {
        $images = $cache->get('posters_' . $imdb_id);
        if ($images === false || is_null($images)) {
            $images = $fluent->from('images')
                             ->select(null)
                             ->select('url')
                             ->where('type = "poster"')
                             ->where('imdb_id = ?', $imdb_id)
                             ->fetchAll();

            $cache->set('posters_' . $imdb_id, $images, 86400);
        }

        if (!empty($images)) {
            shuffle($images);

            return $images[0]['url'];
        }

        return false;
    }
}
