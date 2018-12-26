<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bittorrent.php';
dbconn();
global $cache;

if (!empty($argv[1]) && $argv[1] === 'force') {
    $cache->delete('images_update_');
}

$cleanup_check = $cache->get('images_update_');
if (user_exists($site_config['chatBotID']) && ($cleanup_check === false || is_null($cleanup_check))) {
    images_update();
} else {
    echo "Already running.\n";
}

function images_update()
{
    require_once INCL_DIR . 'function_tmdb.php';
    require_once INCL_DIR . 'function_tvmaze.php';
    require_once INCL_DIR . 'function_imdb.php';
    require_once INCL_DIR . 'function_omdb.php';
    require_once INCL_DIR . 'function_bluray.php';
    require_once INCL_DIR . 'function_books.php';
    require_once INCL_DIR . 'function_fanart.php';
    global $fluent, $cache, $image_stuffs;

    set_time_limit(12000);
    ignore_user_abort(true);
    $cache->set('images_update_', 'running', 7200);

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

    $ids_checked = $cache->get('ids_checked_');
    if ($ids_checked === false || is_null($ids_checked)) {
        $ids_checked = [];
    }
    $imdb_ids = $fluent->from('images')
        ->select(null)
        ->select('DISTINCT imdb_id AS vid')
        ->where('imdb_id IS NOT NULL')
        ->where('tmdb_id = 0')
        ->fetchAll();

    $tmdb_ids = $fluent->from('images')
        ->select(null)
        ->select('DISTINCT tmdb_id AS vid')
        ->where('tmdb_id > 0')
        ->where('imdb_id IS NULL')
        ->fetchAll();

    $ids = array_merge($imdb_ids, $tmdb_ids);
    foreach ($ids as $id) {
        if (!in_array($id['vid'], $ids_checked)) {
            getMovieImagesByID($id['vid'], 'moviebackground');
            getMovieImagesByID($id['vid'], 'movieposter');
            getMovieImagesByID($id['vid'], 'moviebanner');
            $ids_checked[] = $id['vid'];
        }
    }
    $cache->set('ids_checked_', $ids_checked, 604800);
    $links = $fluent->from('torrents')
        ->select(null)
        ->select('name')
        ->select('isbn')
        ->select('poster')
        ->where('isbn IS NOT NULL')
        ->where('isbn != ""');

    foreach ($links as $link) {
        get_book_info($link);
    }

    $imdbids = $fluent->from('torrents')
        ->select(null)
        ->select('imdb_id')
        ->where('imdb_id IS NOT NULL');

    foreach ($imdbids as $imdbid) {
        if (!empty($imdbid)) {
            get_imdb_info($imdbid['imdb_id'], true);
            get_omdb_info($imdbid['imdb_id'], false);
            update_torrent_data($imdbid['imdb_id']);
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
            get_imdb_info($imdb, true);
            get_omdb_info($imdb, false);
        }
    }

    $query = $fluent->from('images')
        ->select(null)
        ->select('tmdb_id')
        ->select('url')
        ->select('type')
        ->where('tmdb_id != 0')
        ->where('imdb_id IS NULL');

    $ids = $images = [];
    foreach ($query as $image) {
        if (!in_array($image['tmdb_id'], $ids)) {
            $ids[] = $image['tmdb_id'];
            $images[] = $image;
        }
    }

    foreach ($images as $image) {
        $imdb = get_imdbid($image['tmdb_id']);
        if (!empty($imdb['imdb_id'])) {
            $set = [
                'imdb_id' => $imdb['imdb_id'],
                'type' => $image['type'],
                'url' => $image['url'],
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
            url_proxy($image['url'], true, 450);
            url_proxy($image['url'], true, 250);
            url_proxy($image['url'], true, 250, null, 20);
        } elseif ($image['type'] === 'banner') {
            url_proxy($image['url'], true, 1000, 185);
        }
    }

    if (!empty($values)) {
        $update = [
            'fetched' => 'yes',
        ];
        $image_stuffs->update($values, $update);
    }

    $persons = $fluent->from('person')
        ->select(null)
        ->select('imdb_id')
        ->select('updated')
        ->where('updated + 604800 < ?', TIME_NOW)
//        ->limit(50)
        ->fetchAll();

dd($persons);
    foreach ($persons as $person) {
        get_imdb_person($person['imdb_id']);
    }

    $cache->delete('backgrounds_');
    $cache->delete('images_update_');

    write_log('Images Cleanup: Completed');
}
