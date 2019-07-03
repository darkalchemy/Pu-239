<?php

declare(strict_types = 1);

use Pu239\Image;
use Pu239\Torrent;

global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$staff_picks = $torrent->get_staff_picks();
$staffpicks = "
    <a id='staffpicks-hash'></a>
    <div id='staffpicks' class='box'>
        <div class='has-text-centered'>";

$staffpicks .= "
        <div class='table-wrapper'>
            <table class='table table-bordered table-striped'>";
$staffpicks .= "
                <thead>
                    <tr>
                        <th class='has-text-centered w-10'>{$lang['index_mow_type']}</th>
                        <th class='w-50 min-350'>{$lang['staff_picks']}</th>
                        <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_seeders']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_leechers']}</th>
                    </tr>
                </thead>
                <tbody>";
$images_class = $container->get(Image::class);
foreach ($staff_picks as $last) {
    $imdb_id = $last['imdb_id'];
    $subtitles = $last['subtitles'];
    $year = $last['year'];
    $rating = $last['rating'];
    $owner = $last['owner'];
    $anonymous = $last['anonymous'];
    $name = $last['name'];
    $poster = $last['poster'];
    $seeders = $last['seeders'];
    $leechers = $last['leechers'];
    $size = $last['size'];
    $added = $last['added'];
    $class = $last['class'];
    $username = $last['username'];
    $id = $last['id'];
    $cat = $last['cat'];
    $image = $last['image'];
    $times_completed = $last['times_completed'];
    $genre = $last['genre'];

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

    $block_id = "staff_pick_id_{$id}";
    $staffpicks .= torrent_tooltip_wrapper(htmlsafechars($name) . " ($year)", $id, $block_id, $name, $poster, $uploader, $added, $size, $seeders, $leechers, $imdb_id, $rating, $year, $subtitles, $genre);
}
if (count($staff_picks) === 0) {
    $staffpicks .= "
                        <tr>
                            <td colspan='5'>{$lang['staff_picks_no_torrents']}</td>
                        </tr>";
}
$staffpicks .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
