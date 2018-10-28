<?php

function find_images(string $imdb_id)
{
    global $cache, $fluent;
    $posters = $cache->get('posters_' . $imdb_id);
    if ($posters === false || is_null($posters)) {
        $posters = $fluent->from('images')
            ->select(null)
            ->select('url')
            ->where('type = "poster"')
            ->where('imdb_id = ?', $imdb_id)
            ->fetchAll();

         $cache->set('posters_', $imdb_id, 0);
    }

    if ($posters) {
        shuffle($posters);
        $poster = $posters[0]['url'];

        return $poster;
    }
    return false;
}
