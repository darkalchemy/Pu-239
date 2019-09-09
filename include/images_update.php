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
    echo "Already running.\n";
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 * @throws InvalidManipulation
 * @throws Exception
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
    $time_start = microtime(true);
    set_time_limit(1800);
    ignore_user_abort(true);
    $cache = $container->get(Cache::class);
    $cache->set('images_update_', 'running', 1800);
    $fluent = $container->get(Database::class);
    $images_class = $container->get(Image::class);
    $torrent = $container->get(Torrent::class);

    $fluent->deleteFrom('images')
           ->where("url = 'N/A' OR url = '' OR url IS NULL")
           ->execute();
    $fluent->deleteFrom('person')
           ->where("imdb_id = '' OR imdb_id IS NULL")
           ->execute();

    get_upcoming();
    get_movies_in_theaters();
    get_bluray_info();
    get_schedule();

    $today = date('Y-m-d');
    $date = new DateTime($today);
    $yesterday = $date->modify('-1 day')
                      ->format('Y-m-d');
    $tomorrow = $date->modify('+1 day')
                     ->format('Y-m-d');
    $day_after = $date->modify('+2 day')
                     ->format('Y-m-d');

    get_movies_by_vote_average(100);
    get_tv_by_day($yesterday);
    get_tv_by_day($today);
    get_tv_by_day($tomorrow);
    get_tv_by_day($day_after);

    $no_posters = $fluent->from('torrents')
                         ->select(null)
                         ->select('DISTINCT imdb_id')
                         ->where('imdb_id IS NOT NULL')
                         ->where('poster = ""')
                         ->fetchAll();

    $fanart_images = $temp = [];
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
    echo 'Checked ' . count($no_posters) . ' torrents that do not have posters' . "\n";

    $images = $fluent->from('images')
                     ->select(null)
                     ->select('id')
                     ->select('imdb_id')
                     ->select('url')
                     ->where('imdb_id IS NOT NULL')
                     ->where('tmdb_id = 0')
                     ->where('checked + 3600 < ?', TIME_NOW)
                     ->orderBy('id DESC')
                     ->limit(50)
                     ->fetchAll();

    $values = [];
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
        $images_class->update($values, $update);
    }
    echo 'Checked ' . count($values) . " image tmdb_ids\n";

    $images = $fluent->from('images')
                     ->select(null)
                     ->select('id')
                     ->select('tmdb_id')
                     ->select('url')
                     ->select('type')
                     ->where('tmdb_id != 0')
                     ->where('imdb_id IS NULL')
                     ->where('checked + 3600 < ?', TIME_NOW)
                     ->orderBy('id DESC')
                     ->limit(50)
                     ->fetchAll();

    $values1 = $values = [];
    foreach ($images as $image) {
        $imdb = get_imdbid($image['tmdb_id']);
        if (!empty($imdb['imdb_id'])) {
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
    echo 'Checked ' . count($values) . " image imdb_ids\n";

    if (!empty($values1)) {
        $update = [
            'checked' => TIME_NOW,
        ];
        $images_class->update($values1, $update);
    }
    echo 'Checked and failed to find ' . count($values1) . " image imdb_ids\n";

    $imdb_ids = $fluent->from('images')
                       ->select(null)
                       ->select('id')
                       ->select('imdb_id')
                       ->select('url')
                       ->where('imdb_id IS NOT NULL')
                       ->where('updated + 3600 < ?', TIME_NOW)
                       ->orderBy('id DESC')
                       ->limit(50)
                       ->fetchAll();

    foreach ($imdb_ids as $id) {
        $temp = getMovieImagesByID($id['imdb_id'], false, 'moviebackground');
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
    echo 'Checked ' . count($imdb_ids) . ' images with empty tmdb_id' . "\n";

    $tmdb_ids = $fluent->from('images')
                       ->select(null)
                       ->select('id')
                       ->select('tmdb_id')
                       ->select('url')
                       ->where('tmdb_id > 0')
                       ->where('updated + 3600 < ?', TIME_NOW)
                       ->orderBy('id DESC')
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
    echo 'Checked ' . count($tmdb_ids) . ' images with empty imdb_id' . "\n";
    if (!empty($fanart_images)) {
        $images_class->insert_update($fanart_images);
    }
    if (!empty($values)) {
        $update = [
            'updated' => TIME_NOW,
        ];
        $images_class->update($values, $update);
        echo 'Updated ' . count($values) . " image imdb_ids\n";
        unset($values);
    }

    $images = $fluent->from('images')
                     ->select(null)
                     ->select('id')
                     ->select('url')
                     ->select('type')
                     ->where('fetched = "no"')
                     ->orderBy('id DESC')
                     ->limit(50)
                     ->fetchAll();

    $values = [];
    echo 'Fetching, resizing and optimizing ' . count($images) . "\n";
    foreach ($images as $image) {
        if (url_proxy($image['url'], true)) {
            $values[] = [
                'url' => $image['url'],
                'fetched' => 'yes',
            ];
            if ($image['type'] === 'poster') {
                url_proxy($image['url'], true, 450);
                url_proxy($image['url'], true, 250);
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
    echo 'Fetched, optimized and resized ' . count($values) . " images\n";

    $books = $fluent->from('torrents')
                    ->select(null)
                    ->select('id')
                    ->select('title')
                    ->select('isbn')
                    ->select('poster')
                    ->where('info_updated + 3600 < ?', TIME_NOW)
                    ->orderBy('id DESC')
                    ->limit(50)
                    ->fetchAll();

    echo 'Fetching book data for ' . count($books) . "\n";
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
    echo count($books) . " torrents google books info cached\n";

    $imdbids = $fluent->from('torrents')
                      ->select(null)
                      ->select('id')
                      ->select('imdb_id')
                      ->where('imdb_id IS NOT NULL')
                      ->where('info_updated + 3600 < ?', TIME_NOW)
                      ->orderBy('id DESC')
                      ->limit(50)
                      ->fetchAll();
    echo 'Fetching IMDb data and finding images for ' . count($imdbids) . ' torrents with imdb_id set' . "\n";
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
    echo count($imdbids) . " torrents imdb info cached\n";

    $torrents = $fluent->from('torrents')
                       ->select(null)
                       ->select('id')
                       ->where('descr != ""');
    $count = 0;
    foreach ($torrents as $tor) {
        $torrent->format_descr($tor['id']);
        ++$count;
    }
    echo $count . " torrents descr info cached\n";

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
                tvmaze($ids['tvmaze_id'], $tor['id'], $season, $episode, $poster);
                ++$count;
            }
        }
        echo $count . " torrents tvmaze info cached\n";
    }

    $offer_links = $fluent->from('offers')
                          ->select(null)
                          ->select('id')
                          ->select('link as url')
                          ->where('link IS NOT NULL')
                          ->where('updated + 3600 < ?', TIME_NOW)
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
        echo count($offer_links) . " offers imdb info cached\n";
    }

    $request_links = $fluent->from('requests')
                            ->select(null)
                            ->select('id')
                            ->select('link as url')
                            ->where('link IS NOT NULL')
                            ->where('updated + 3600 < ?', TIME_NOW)
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
        echo count($request_links) . " requests imdb info cached\n";
    }

    $persons = $fluent->from('person')
                      ->select(null)
                      ->select('id')
                      ->select('imdb_id')
                      ->where('updated + 3600 < ?', TIME_NOW)
                      ->orderBy('id DESC')
                      ->limit(50)
                      ->fetchAll();

    foreach ($persons as $person) {
        get_imdb_person($person['imdb_id']);
    }

    if (!empty($persons)) {
        echo count($persons) . " persons imdb info cached\n";
    }

    $cache->delete('backgrounds_');
    $cache->delete('images_update_');

    $time_end = microtime(true);
    $run_time = $time_end - $time_start;
    echo "Run time: $run_time seconds\n";

    write_log('Images Cleanup: Completed');
}
