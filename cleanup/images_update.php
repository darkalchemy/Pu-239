<?php

/**
 * @param $data
 *
 * @throws Exception
 */
function images_update($data)
{
    require_once INCL_DIR . 'function_tmdb.php';
    require_once INCL_DIR . 'function_tvmaze.php';
    require_once INCL_DIR . 'function_imdb.php';
    require_once INCL_DIR . 'function_omdb.php';
    require_once INCL_DIR . 'function_bluray.php';
    require_once INCL_DIR . 'function_books.php';
    require_once INCL_DIR . 'function_fanart.php';
    global $fluent, $cache, $image_stuffs;

    set_time_limit(1200);
    ignore_user_abort(true);

    get_upcoming();
    get_movies_in_theaters();
    get_bluray_info();

    $today = date('Y-m-d');
    $date = new DateTime($today);
    $tomorrow = $date->modify('+1 day')
        ->format('Y-m-d');

    get_movies_by_vote_average(100);
    get_tv_by_day($today);
    get_tv_by_day($tomorrow);

    $ids = [];
    $imdb_ids = $fluent->from('images')
        ->select(null)
        ->select('imdb_id AS vid')
        ->where('imdb_id IS NOT NULL')
        ->fetchAll();

    $tmdb_ids = $fluent->from('images')
        ->select(null)
        ->select('tmdb_id AS vid')
        ->where('tmdb_id > 0')
        ->fetchAll();

    $ids = array_merge($imdb_ids, $tmdb_ids);

    foreach ($ids as $id) {
        getMovieImagesByID($id['vid'], 'moviebackground');
        getMovieImagesByID($id['vid'], 'movieposter');
        getMovieImagesByID($id['vid'], 'moviebanner');
    }

    $links = $fluent->from('torrents')
        ->select(null)
        ->select('name')
        ->select('isbn')
        ->select('poster')
        ->where('isbn IS NOT NULL')
        ->where('isbn != ""');

    foreach ($links as $link) {
        get_book_info($links);
    }

    $imdbids = $fluent->from('torrents')
        ->select(null)
        ->select('imdb_id')
        ->where('imdb_id IS NOT NULL');

    foreach ($imdbids as $imdbid) {
        if (!empty($imdbid)) {
            get_imdb_info($imdbid['imdb_id'], false);
            get_omdb_info($imdbid['imdb_id'], false);
        }
    }

    $offer_links = $fluent->from('offers')
        ->select(null)
        ->select('link as url')
        ->where('link IS NOT NULL')
        ->fetchAll();

    $request_links = $fluent->from('requests')
        ->select(null)
        ->select('link as url')
        ->where('link IS NOT NULL')
        ->fetchAll();

    $links = array_merge($offer_links, $request_links);
    foreach ($links as $link) {
        preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $link['url'], $imdb);
        $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        if (!empty($imdb)) {
            get_imdb_info($imdb, false);
            get_omdb_info($imdb, false);
        }
    }

    $images = $fluent->from('images')
        ->select(null)
        ->select('tmdb_id')
        ->select('type')
        ->select('url')
        ->where('tmdb_id != 0')
        ->where('imdb_id IS NULL')
        ->limit(100)
        ->fetchAll();

    foreach ($images as $tmdbid) {
        $imdb_id = get_imdbid($tmdbid['tmdb_id']);
        if (!empty($imdb_id)) {
            $values[] = [
                'url' => $tmdbid['url'],
                'imdb_id' => $imdb_id,
                'type' => $tmdbid['type'],
            ];
        }
    }

    if (!empty($values)) {
        $update = [
            'imdb_id' => new Envms\FluentPDO\Literal('VALUES(imdb_id)'),
        ];
        $image_stuffs->update($values, $update);
        unset($values);
    }

    $images = $fluent->from('images')
        ->select(null)
        ->select('imdb_id')
        ->where('imdb_id IS NOT NULL AND (tmdb_id IS NULL OR tmdb_id = 0)')
        ->limit(100)
        ->fetchAll();

    foreach ($images as $imdb_id) {
        get_movie_id($imdb_id['imdb_id'], 'tmdb_id');
    }

    $images = $fluent->from('images')
        ->select(null)
        ->select('url')
        ->select('type')
        ->where('fetched = "no"')
        ->orderBy('id')
        ->limit(100)
        ->fetchAll();

    foreach ($images as $image) {
        if (url_proxy($image['url'], true)) {
            $values[] = [
                'url' => $image['url'],
                'type' => $image['type'],
                'fetched' => 'yes',
            ];
        }
        if ($image['type'] === 'poster') {
            url_proxy($image['url'], true, 150);
            url_proxy($image['url'], true, 150, null, 10);
        }
    }

    if (!empty($values)) {
        $update = [
            'fetched' => 'yes',
        ];
        $image_stuffs->update($values, $update);
    }

    $cache->delete('backgrounds_');

    if ($data['clean_log']) {
        write_log('Images Cleanup: Completed');
    }
}
