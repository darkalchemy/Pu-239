<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bittorrent.php';
global $cache;

if (!empty($argv[1]) && $argv[1] === 'force') {
    $cache->delete('images_update_');
}

echo "===================================================\n";
echo get_date(TIME_NOW, 1, 0) . "\n";

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

    $time_start = microtime(true);
    set_time_limit(12000);
    ignore_user_abort(true);
    $cache->set('images_update_', 'running', 7200);

    $fluent->deleteFrom('images')
        ->where("url = 'N/A' OR url = '' OR url IS NULL")
        ->execute();

    get_upcoming();
    get_movies_in_theaters();
    get_bluray_info();

    $today = date('Y-m-d');
    $date = new DateTime($today);
    $tomorrow = $date->modify('+1 day')->format('Y-m-d');

    get_movies_by_vote_average(100);
    get_tv_by_day($today);
    get_tv_by_day($tomorrow);

    $images = $fluent->from('images')
        ->select(null)
        ->select('imdb_id')
        ->select('url')
        ->where('imdb_id IS NOT NULL')
        ->where('tmdb_id = 0')
        ->where('checked + 604800 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($images as $imdb_id) {
        get_movie_id($imdb_id['imdb_id'], 'tmdb_id');
        $values[] = [
            'checked' => TIME_NOW,
            'url' => $imdb_id['url'],
        ];
    }

    if (!empty($values)) {
        $update = [
            'checked' => TIME_NOW,
        ];
        $image_stuffs->update($values, $update);
        echo 'Checked ' . count($values) . " image tmdb_ids\n";
        unset($values);
    }

    $images = $fluent->from('images')
        ->select(null)
        ->select('tmdb_id')
        ->select('url')
        ->select('type')
        ->where('tmdb_id != 0')
        ->where('imdb_id IS NULL')
        ->where('checked + 604800 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($images as $image) {
        $imdb = get_imdbid($image['tmdb_id']);
        if (!empty($imdb['imdb_id'])) {
            $set = [
                'imdb_id' => $imdb['imdb_id'],
                'url' => $image['url'],
                'checked' => TIME_NOW,
            ];
        }
    }
    if (!empty($values)) {
        $update = [
            'imdb_id' => new Envms\FluentPDO\Literal('VALUES(imdb_id)'),
            'checked' => TIME_NOW,
        ];
        $image_stuffs->update($values, $update);
        echo 'Checked ' . count($values) . " image imdb_ids\n";
        unset($values);
    }

    $imdb_ids = $fluent->from('images')
        ->select(null)
        ->select('DISTINCT imdb_id AS vid')
        ->select('url')
        ->where('imdb_id IS NOT NULL')
        ->where('tmdb_id = 0')
        ->where('updated + 604800 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($imdb_ids as $id) {
        getMovieImagesByID($id['vid'], 'moviebackground');
        getMovieImagesByID($id['vid'], 'movieposter');
        getMovieImagesByID($id['vid'], 'moviebanner');
        $values[] = [
            'updated' => TIME_NOW,
            'url' => $id['url'],
        ];
    }

    if (!empty($values)) {
        $update = [
            'updated' => TIME_NOW,
        ];
        $image_stuffs->update($values, $update);
        echo 'Updated ' . count($values) . " image tmdb_ids\n";
        unset($values);
    }

    $tmdb_ids = $fluent->from('images')
        ->select(null)
        ->select('DISTINCT tmdb_id AS vid')
        ->select('url')
        ->where('tmdb_id > 0')
        ->where('imdb_id IS NULL')
        ->where('updated + 604800 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($tmdb_ids as $id) {
        getMovieImagesByID($id['vid'], 'moviebackground');
        getMovieImagesByID($id['vid'], 'movieposter');
        getMovieImagesByID($id['vid'], 'moviebanner');
        $values[] = [
            'updated' => TIME_NOW,
            'url' => $id['url'],
        ];
    }

    if (!empty($values)) {
        $update = [
            'updated' => TIME_NOW,
        ];
        $image_stuffs->update($values, $update);
        echo 'Updated ' . count($values) . " image imdb_ids\n";
        unset($values);
    }

    $images = $fluent->from('images')
        ->select(null)
        ->select('url')
        ->select('type')
        ->where('fetched = "no"')
        ->orderBy('id')
        ->limit(50)
        ->fetchAll();

    foreach ($images as $image) {
        if (url_proxy($image['url'], true)) {
            $values[] = [
                'url' => $image['url'],
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
        echo 'Fetched, optimized and created needed sizes of ' . count($values) . " images\n";
        unset($values);
    }

    $books = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('name')
        ->select('isbn')
        ->select('poster')
        ->where('isbn IS NOT NULL')
        ->where('isbn != ""')
        ->where('info_updated + 604800 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($books as $book) {
        if (get_book_info($book['isbn'], $book['name'], $book['id'], $book['poster'])) {
            $set = [
                'info_updated' => TIME_NOW,
            ];
            $fluent->update('torrents')
                ->set($set)
                ->where('id = ?', $book['id'])
                ->execute();
        }
    }

    $imdbids = $fluent->from('torrents')
        ->select(null)
        ->select('imdb_id')
        ->select('id')
        ->where('imdb_id IS NOT NULL')
        ->where('info_updated + 604800 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($imdbids as $imdbid) {
        get_imdb_info($imdbid['imdb_id'], true, false, null, null);
        get_omdb_info($imdbid['imdb_id'], false);
        update_torrent_data($imdbid['imdb_id']);
        $set = [
            'info_updated' => TIME_NOW,
        ];
        $fluent->update('torrents')
            ->set($set)
            ->where('id = ?', $imdbid['imdb_id'])
            ->execute();
    }

    $offer_links = $fluent->from('offers')
        ->select(null)
        ->select('link as url')
        ->where('link IS NOT NULL')
        ->where('updated + 604800 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($offer_links as $link) {
        preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $link['url'], $imdb);
        $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        if (!empty($imdb) && !in_array($imdb, $imdb_ids)) {
            get_imdb_info($imdb, true, false, null, null);
            get_omdb_info($imdb, false);
            update_torrent_data($imdb);
            $set = [
                'updated' => TIME_NOW,
            ];
            $fluent->update('offers')
                ->set($set)
                ->where('id = ?', $$imdb)
                ->execute();
        }
    }

    $request_links = $fluent->from('requests')
        ->select(null)
        ->select('link as url')
        ->where('link IS NOT NULL')
        ->where('updated + 604800 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($request_links as $link) {
        preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $link['url'], $imdb);
        $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        if (!empty($imdb) && !in_array($imdb, $imdb_ids)) {
            get_imdb_info($imdb, true, false, null, null);
            get_omdb_info($imdb, false);
            update_torrent_data($imdb);
            $set = [
                'updated' => TIME_NOW,
            ];
            $fluent->update('requests')
                ->set($set)
                ->where('id = ?', $$imdb)
                ->execute();
        }
    }

    $persons = $fluent->from('person')
        ->select(null)
        ->select('imdb_id')
        ->select('updated')
        ->where('updated + 2592000 < ?', TIME_NOW)
        ->limit(50)
        ->fetchAll();

    foreach ($persons as $person) {
        get_imdb_person($person['imdb_id']);
    }

    $cache->delete('backgrounds_');
    $cache->delete('images_update_');

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    echo "Run time: $run_time seconds\n";

    write_log('Images Cleanup: Completed');
}
