<?php

declare(strict_types = 1);

use Envms\FluentPDO\Literal;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Image;
use Pu239\Torrent;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bittorrent.php';

global $container;
$cache = $container->get(Cache::class);
if (!empty($argv[1]) && $argv[1] === 'force') {
    $cache->delete('images_update_');
}

echo "===================================================\n";
echo get_date((int) TIME_NOW, 'LONG', 1, 0) . "\n";

$cleanup_check = $cache->get('images_update_');
global $site_config;

if (user_exists($site_config['chatbot']['id']) && ($cleanup_check === false || is_null($cleanup_check))) {
    images_update();
} else {
    $item_count = (int) $cache->get('item_count_');
    echo _fe("Already started {0}. Processing items {1}", get_date($cleanup_check, 'LONG', 0, 0), $item_count) . ".\n";
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 * @throws InvalidManipulation
 * @throws Exception`
 */
function images_update()
{
    global $container, $BLOCKS, $site_config;

    require_once INCL_DIR . 'function_tmdb.php';
    require_once INCL_DIR . 'function_tvmaze.php';
    require_once INCL_DIR . 'function_imdb.php';
    require_once INCL_DIR . 'function_bluray.php';
    require_once INCL_DIR . 'function_books.php';
    require_once INCL_DIR . 'function_fanart.php';
    require_once INCL_DIR . 'function_get_images.php';
    $max = 5000;
    $time_start = microtime(true);
    set_time_limit(1800);
    ignore_user_abort(true);
    $cache = $container->get(Cache::class);
    $cache->set('images_update_', time(), 1800);
    $fluent = $container->get(Database::class);
    $images_class = $container->get(Image::class);
    $torrent = $container->get(Torrent::class);
    $fluent->deleteFrom('images')
        ->where("url = 'N/A' OR url = '' OR url IS NULL")
        ->execute();
    $fluent->deleteFrom('person')
        ->where("imdb_id = '' OR imdb_id IS NULL")
        ->execute();
    fetch_person_info(50);
    echo _('Caching IMDb Movies Coming Soon') . "\n";
    get_upcoming(true);
    echo _('Caching IMDb Movies In Theaters') . "\n";
    get_in_theaters(true);
    $item_count = (int) $cache->get('item_count_') + 5;
    $item_count = $item_count >= $max ? $max : $item_count;
    echo _f('Caching IMDb Top %d Movies', $item_count) . "\n";
    get_top_movies($item_count, $item_count <= 100);
    echo _f('Caching IMDb Top %d TV Shows', $item_count) . "\n";
    get_top_tvshows($item_count, $item_count <= 100);
    echo _f('Caching IMDb %d Newest Movies', $item_count) . "\n";
    movies_by_release_date($item_count, $item_count <= 100);
    echo _f('Caching IMDb %d Oscar Winners', $item_count) . "\n";
    get_oscar_winners($item_count, $item_count <= 100);
    echo _f('Caching IMDb %d Top Anime', $item_count) . "\n";
    get_top_anime($item_count, $item_count <= 100);
    echo _f('Caching TMDb Top %d Movies', $item_count >= 500 ? 500 : $item_count) . "\n";
    get_movies_by_vote_average($item_count >= 500 ? 500 : $item_count, $item_count <= 100);
    if ($item_count >= 100) {
        $count = 100;
        echo _f('Caching IMDb Top %d Movies', $count) . "\n";
        get_top_movies($count, true);
        echo _f('Caching IMDb Top %d TV Shows', $count) . "\n";
        get_top_tvshows($count, true);
        echo _f('Caching IMDb %d Newest Movies', $count) . "\n";
        movies_by_release_date($count, true);
        echo _f('Caching IMDb %d Oscar Winners', $count) . "\n";
        get_oscar_winners($count, true);
        echo _f('Caching IMDb %d Top Anime', $count) . "\n";
        get_top_anime($count, true);
        echo _f('Caching TMDb Top %d Movies', $count) . "\n";
        get_movies_by_vote_average($count, true);
    }
    $cache->set('item_count_', $item_count, 0);
    echo _('Caching TMDb Movies In Theaters') . "\n";
    get_movies_in_theaters(true);
    echo _('Caching New Releases from Blu-ray.com') . "\n";
    get_bluray_info();
    echo _('Caching TVMaze TV Schedule') . "\n";
    get_schedule();

    $today = date('Y-m-d');
    $date = new DateTime($today);
    $yesterday = $date->modify('-1 day')->format('Y-m-d');
    $tomorrow = $date->modify('+1 day')->format('Y-m-d');
    $day_after = $date->modify('+2 day')->format('Y-m-d');

    echo _f('Caching TMDb TV for %s', $yesterday) . "\n";
    get_tv_by_day($yesterday);
    echo _f('Caching TMDb TV for %s', $today) . "\n";
    get_tv_by_day($today);
    echo _f('Caching TMDb TV for %s', $tomorrow) . "\n";
    get_tv_by_day($tomorrow);
    echo _f('Caching TMDb TV for %s', $day_after) . "\n";
    get_tv_by_day($day_after);
    $no_posters = $fluent->from('torrents')
        ->select(null)
        ->select('DISTINCT imdb_id')
        ->where('imdb_id IS NOT NULL')
        ->where('poster = ""')
        ->fetchAll();

    fetch_person_info(50);
    $fanart_images = $temp = [];
    echo _('Fetching images from Fanart.com for torrents') . "\n";
    foreach ($no_posters as $no_poster) {
        $temp = getMovieImagesByID($no_poster['imdb_id'], false, 'moviebackground');
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
        $temp = getMovieImagesByID($no_poster['imdb_id'], false, 'movieposter');
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
        $temp = getMovieImagesByID($no_poster['imdb_id'], false, 'moviebanner');
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
    }
    echo _f('Checked %d torrents that do not have posters', count($no_posters)) . "\n";

    $images = $fluent->from('images')
        ->select(null)
        ->select('imdb_id')
        ->select('url')
        ->where('imdb_id IS NOT NULL')
        ->where('tmdb_id = 0')
        ->where('checked + 604800 < ?', TIME_NOW)
        ->orderBy('added DESC')
        ->limit(50)
        ->fetchAll();

    $values = [];
    foreach ($images as $imdb_id) {
        get_movie_id($imdb_id['imdb_id'], 'tmdb_id');
        get_imdb_info_short($imdb_id['imdb_id']);
        $values[] = [
            'checked' => TIME_NOW,
            'url' => $imdb_id['url'],
        ];
    }
    if (!empty($values)) {
        $update = [
            'checked' => TIME_NOW,
        ];
        $images_class->update($values, $update);
    }
    echo _f('Checked %d image tmdb_ids', count($values)) . "\n";

    $images = $fluent->from('images')
        ->select(null)
        ->select('tmdb_id')
        ->select('url')
        ->select('type')
        ->where('tmdb_id != 0')
        ->where('imdb_id IS NULL')
        ->where('checked + 604800 < ?', TIME_NOW)
        ->orderBy('added DESC')
        ->limit(50)
        ->fetchAll();

    $values1 = $values = [];
    foreach ($images as $image) {
        $imdb = get_imdbid($image['tmdb_id']);
        if (!empty($imdb['imdb_id'])) {
            get_imdb_info_short($imdb['imdb_id']);
            $values[] = [
                'imdb_id' => $imdb['imdb_id'],
                'url' => $image['url'],
                'checked' => TIME_NOW,
            ];
        } else {
            $values1[] = [
                'url' => $image['url'],
                'checked' => TIME_NOW,
            ];
        }
    }
    if (!empty($values)) {
        $update = [
            'imdb_id' => new Literal('VALUES(imdb_id)'),
            'checked' => TIME_NOW,
        ];
        $images_class->update($values, $update);
    }
    echo _f('Checked %d image imdb_ids', count($values)) . "\n";

    if (!empty($values1)) {
        $update = [
            'checked' => TIME_NOW,
        ];
        $images_class->update($values1, $update);
    }
    echo _f('Checked and failed to find %d image imdb_ids', count($values1)) . "\n";

    $imdb_ids = $fluent->from('images')
        ->select(null)
        ->select('imdb_id')
        ->select('url')
        ->where('imdb_id IS NOT NULL')
        ->where('updated + 604800 < ?', TIME_NOW)
        ->orderBy('added DESC')
        ->limit(50)
        ->fetchAll();

    foreach ($imdb_ids as $id) {
        $temp = getMovieImagesByID($id['imdb_id'], false, 'moviebackground');
        get_imdb_info_short($id['imdb_id']);
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
        $temp = getMovieImagesByID($id['imdb_id'], false, 'movieposter');
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
        $temp = getMovieImagesByID($id['imdb_id'], false, 'moviebanner');
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
    }
    echo _f('Checked %d images with empty tmdb_id', count($imdb_ids)) . "\n";

    $tmdb_ids = $fluent->from('images')
        ->select(null)
        ->select('tmdb_id')
        ->select('url')
        ->where('tmdb_id > 0')
        ->where('updated + 604800 < ?', TIME_NOW)
        ->orderBy('added DESC')
        ->limit(50)
        ->fetchAll();

    foreach ($tmdb_ids as $id) {
        $temp = getMovieImagesByID((string) $id['tmdb_id'], false, 'moviebackground');
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
        $temp = getMovieImagesByID((string) $id['tmdb_id'], false, 'movieposter');
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
        $temp = getMovieImagesByID((string) $id['tmdb_id'], false, 'moviebanner');
        if (!empty($temp)) {
            foreach ($temp as $item) {
                $fanart_images[] = $item;
            }
        }
    }
    echo _f('Checked %d images with empty imdb_id', count($tmdb_ids)) . "\n";
    if (!empty($fanart_images)) {
        $images_class->insert_update($fanart_images);
    }
    if (!empty($values)) {
        $update = [
            'updated' => TIME_NOW,
        ];
        $images_class->update($values, $update);
        echo _f('Updated %d image imdb_ids', count($values)) . "\n";
        unset($values);
    }

    $images = $fluent->from('images')
        ->select(null)
        ->select('url')
        ->select('type')
        ->where('fetched = "no"')
        ->orderBy('added DESC')
        ->limit(50)
        ->fetchAll();

    $values = [];
    echo _f('Fetching, resizing and optimizing %d images', count($images)) . "\n";
    foreach ($images as $image) {
        if (url_proxy($image['url'], true)) {
            $values[] = [
                'url' => $image['url'],
                'fetched' => 'yes',
            ];
            if ($image['type'] === 'poster') {
                url_proxy($image['url'], true, 450);
                url_proxy($image['url'], true, 250);
                url_proxy($image['url'], true, 100);
                url_proxy($image['url'], true, null, 300);
                url_proxy($image['url'], true, 250, null, 20);
            } elseif ($image['type'] === 'banner') {
                url_proxy($image['url'], true, 1000, 185);
            }
        }
    }
    if (!empty($values)) {
        $update = [
            'fetched' => 'yes',
        ];
        $images_class->update($values, $update);
    }
    echo _f('Fetched, optimized and resized %d images', count($values)) . "\n";

    $books = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('title')
        ->select('isbn')
        ->select('poster')
        ->where('info_updated + 604800 < ?', TIME_NOW)
        ->where('isbn IS NOT NULL')
        ->where("isbn != ''")
        ->orderBy('id DESC')
        ->limit(50)
        ->fetchAll();

    echo _f('Fetching book data for %d books', count($books)) . "\n";
    foreach ($books as $book) {
        if (!empty($book['isbn']) || !empty($book['title'])) {
            if (get_book_info($book['isbn'], $book['title'], $book['id'], $book['poster'])) {
                $set = [
                    'info_updated' => TIME_NOW,
                ];
                $fluent->update('torrents')
                    ->set($set)
                    ->where('id = ?', $book['id'])
                    ->execute();
            }
        }
    }
    if (!empty($books)) {
        echo _f('%d torrents google books info cached', count($books)) . "\n";
    }

    $imdbids = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->select('imdb_id')
        ->where('imdb_id IS NOT NULL')
        ->where('info_updated + 604800 < ?', TIME_NOW)
        ->orderBy('id DESC')
        ->limit(50)
        ->fetchAll();
    echo _f('Fetching IMDb data and finding images for %d torrents with imdb_id set', count($imdbids)) . "\n";
    foreach ($imdbids as $imdbid) {
        get_imdb_info($imdbid['imdb_id'], true, false, $imdbid['id'], null);
        $images_class->find_images($imdbid['imdb_id'], 'poster');
        $images_class->find_images($imdbid['imdb_id'], 'banner');
        $images_class->find_images($imdbid['imdb_id'], 'background');
        update_torrent_data($imdbid['imdb_id']);
        $set = [
            'info_updated' => TIME_NOW,
        ];
        $fluent->update('torrents')
            ->set($set)
            ->where('imdb_id = ?', $imdbid['imdb_id'])
            ->execute();
    }
    echo _f('%d torrents imdb info cached', count($imdbids)) . "\n";

    $torrents = $fluent->from('torrents')
        ->select(null)
        ->select('id')
        ->where('descr != ""');
    $count = 0;
    foreach ($torrents as $tor) {
        $torrent->format_descr($tor['id']);
        ++$count;
    }
    echo _f('%d torrents descr info cached', $count) . "\n";

    if ($BLOCKS['tvmaze_api_on']) {
        $in = str_repeat('?,', count($site_config['categories']['tv']) - 1) . '?';
        $torrents = $fluent->from('torrents')
            ->select(null)
            ->select('id')
            ->select('name')
            ->select('imdb_id')
            ->select('poster')
            ->where('category IN (' . $in . ')', $site_config['categories']['tv']);

        $count = 0;
        foreach ($torrents as $tor) {
            if (!empty($tor['imdb_id'])) {
                $ids = get_show_id_by_imdb($tor['imdb_id']);
            } else {
                $ids = get_show_id($tor['name']);
            }
            if (!empty($ids['tvmaze_id'])) {
                preg_match('/S(\d+)E(\d+)/i', $tor['name'], $match);
                $episode = !empty($match[2]) ? (int) $match[2] : 0;
                $season = !empty($match[1]) ? (int) $match[1] : 0;
                if (empty($tor['poster'])) {
                    $poster = get_image_by_id('tv', (string) $ids['tvmaze_id'], 'poster', $season);
                }
                $poster = empty($poster) ? '' : $poster;
                tvmaze($ids['tvmaze_id'], $tor['id'], $season, $episode, $poster, true);
                ++$count;
            }
        }
        echo _f('%d torrents tvmaze info cached', $count) . "\n";
    }

    $offer_links = $fluent->from('offers')
        ->select(null)
        ->select('id')
        ->select('url')
        ->where('url IS NOT NULL')
        ->where('updated + 604800 < ?', TIME_NOW)
        ->orderBy('id DESC')
        ->limit(50)
        ->fetchAll();
    foreach ($offer_links as $link) {
        preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7,8})/i', $link['url'], $imdb);
        $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        if (!empty($imdb) && !in_array($imdb, $imdb_ids)) {
            get_imdb_info($imdb, true, false, null, null);
            $images_class->find_images($imdb, 'poster');
            $images_class->find_images($imdb, 'banner');
            $images_class->find_images($imdb, 'background');
            update_torrent_data($imdb);
            $set = [
                'updated' => TIME_NOW,
            ];
            $fluent->update('offers')
                ->set($set)
                ->where('id = ?', $link['id'])
                ->execute();
        }
    }
    if (!empty($offer_links)) {
        echo _f('%d offers imdb info cached', count($offer_links)) . "\n";
    }

    $request_links = $fluent->from('requests')
        ->select(null)
        ->select('id')
        ->select('url')
        ->where('url IS NOT NULL')
        ->where('updated + 604800 < ?', TIME_NOW)
        ->orderBy('id DESC')
        ->limit(50)
        ->fetchAll();

    foreach ($request_links as $link) {
        preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7,8})/i', $link['url'], $imdb);
        $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        if (!empty($imdb) && !in_array($imdb, $imdb_ids)) {
            get_imdb_info($imdb, true, false, null, null);
            $images_class->find_images($imdb, 'poster');
            $images_class->find_images($imdb, 'banner');
            $images_class->find_images($imdb, 'background');
            update_torrent_data($imdb);
            $set = [
                'updated' => TIME_NOW,
            ];
            $fluent->update('requests')
                ->set($set)
                ->where('id = ?', $link['id'])
                ->execute();
        }
    }
    if (!empty($request_links)) {
        echo _f('%d requests imdb info cached', count($request_links)) . "\n";
    }
    fetch_person_info(50);
    passthru('php ' . BIN_DIR . 'resize_multi_threads.php');

    $cache->delete('backgrounds_');
    $cache->delete('images_update_');

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    echo _f('Run time: %s seconds', $run_time) . "\n";

    write_log('Images Cleanup: Completed');
}

function fetch_person_info(int $count): void
{
    global $container;
    $fluent = $container->get(Database::class);
    $persons = $fluent->from('person')
        ->select(null)
        ->select('imdb_id')
        ->select('photo')
        ->where('updated < UNIX_TIMESTAMP() - 604800')
        ->orderBy('updated DESC')
        ->limit($count)
        ->fetchAll();

    echo _f('Fetching imdb_info for %d persons', count($persons)) . "\n";
    foreach ($persons as $person) {
        get_imdb_person($person['imdb_id']);
    }

    if (!empty($persons)) {
        echo _f('%d persons imdb info cached', count($persons)) . "\n";
    }
}
