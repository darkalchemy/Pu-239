<?php

declare(strict_types = 1);

use Pu239\Image;
use Pu239\Torrent;

global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$top5torrents = $torrent->get_top();

$torrents_top .= "
    <a id='toptorrents-hash'></a>
    <div id='toptorrents' class='box'>
        <div class='has-text-centered'>
        <div class='module table-wrapper'>
            <div class='badge badge-top'></div>
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                        <th class='w-50 minw-150'>{$lang['top5torrents_title']}</th>
                        <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_seeders']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_leechers']}</th>
                    </tr>
                </thead>
                <tbody>";
$images_class = $container->get(Image::class);
foreach ($top5torrents as $top5torrentarr) {
    $imdb_id = $subtitles = $year = $rating = $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = $times_completed = $genre = '';
    extract($top5torrentarr);
    if (empty($poster) && !empty($imdb_id)) {
        $poster = $images_class->find_images($imdb_id);
    }
    $poster = empty($poster) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' class='tooltip-poster'>" : "<img src='" . url_proxy($poster, true, 250) . "' class='tooltip-poster'>";

    if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
        $uploader = '<span>' . get_anonymous_name() . '</span>';
    } else {
        $username = !empty($username) ? htmlsafechars($username) : 'unknown';
        $uploader = "<span class='" . get_user_class_name((int) $class, true) . "'>" . $username . '</span>';
    }

    $block_id = "top_id_{$id}";
    $torrents_top .= torrent_tooltip_wrapper(htmlsafechars($name) . " ($year)", $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre);
}
if (count($top5torrents) === 0) {
    $torrents_top .= "
                    <tr>
                        <td colspan='5'>{$lang['top5torrents_no_torrents']}</td>
                    </tr>";
}
$torrents_top .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
