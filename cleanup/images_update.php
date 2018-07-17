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
    require_once INCL_DIR . 'function_bluray.php';
    global $fluent, $cache;

    set_time_limit(1200);
    ignore_user_abort(true);

    get_upcoming();
    get_movies_in_theaters();
    get_bluray_info();

    $today = date('Y-m-d');
    $date = new DateTime($today);
    $tomorrow = $date->modify('+1 day')->format('Y-m-d');

    $year = date('Y');
    $week = date('W');
    $next_week = $week + 1;
    $dates = getStartAndEndDate($year, $week);
    get_movies_by_week($dates);

    $dates = getStartAndEndDate($year, $next_week);
    get_movies_by_week($dates);

    get_movies_by_vote_average(100);
    get_tv_by_day($today);
    get_tv_by_day($tomorrow);
    $tvmaze_data = get_schedule();
    if (!empty($tvmaze_data)) {
        insert_images_from_schedule($tvmaze_data, $today);
        insert_images_from_schedule($tvmaze_data, $tomorrow);
    }

    $links = $fluent->from('torrents')
        ->select(null)
        ->select('url')
        ->where('url != NULL');

    foreach ($links as $link) {
        if (!empty($link['url'])) {
            preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $link['url'], $imdb);
            $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        }
        get_imdb_info($imdb, false);
        get_omdb_info($imdb, false);
    }

    $links = $fluent->from('offers')
        ->select(null)
        ->select('link as url')
        ->where('link != NULL');

    foreach ($links as $link) {
        if (!empty($link['url'])) {
            preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $link['url'], $imdb);
            $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        }
        get_imdb_info($imdb, false);
        get_omdb_info($imdb, false);
    }

    $links = $fluent->from('requests')
        ->select(null)
        ->select('link as url')
        ->where('link != NULL');

    foreach ($links as $link) {
        if (!empty($link['url'])) {
            preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt[\d]{7})/i', $link['url'], $imdb);
            $imdb = !empty($imdb[2]) ? $imdb[2] : '';
        }
        get_imdb_info($imdb, false);
        get_omdb_info($imdb, false);
    }

    $images = $fluent->from('images')
        ->select(null)
        ->select('url')
        ->select('type');

    foreach ($images as $image) {
        url_proxy($image['url'], true);
        if ($image['type'] === 'poster') {
            url_proxy($image['url'], true, 150);
            url_proxy($image['url'], true, 150, null, 10);
        }
    }

    $cache->delete('backgrounds_');

    if ($data['clean_log']) {
        write_log('Images Cleanup: Completed using 1 query');
    }
}
