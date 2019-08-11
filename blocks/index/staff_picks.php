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
                        <th class='has-text-centered w-10 min-100'>{$lang['index_mow_type']}</th>
                        <th class='w-50 min-350'>{$lang['staff_picks']}</th>
                        <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_seeders']}</th>
                        <th class='has-text-centered'>{$lang['top5torrents_leechers']}</th>
                    </tr>
                </thead>
                <tbody>";
$images_class = $container->get(Image::class);
foreach ($staff_picks as $last) {
    $last['text'] = $last['name'] . '(' . $last['year'] . ')';
    if (empty($last['poster']) && !empty($last['imdb_id'])) {
        $last['poster'] = $images_class->find_images($last['imdb_id']);
    }
    $last['poster'] = empty($last['poster']) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' alt='Poster for {$last['name']}' class='tooltip-poster'>" : "<img src='" . url_proxy($last['poster'], true, 250) . "' alt='Poster for {$last['name']}' class='tooltip-poster'>";
    if ($last['anonymous'] === '1' && ($user['class'] < UC_STAFF || $last['owner'] === $user['id'])) {
        $last['uploader'] = get_anonymous_name();
    } else {
        $last['username'] = !empty($last['username']) ? format_comment($last['username']) : 'unknown';
        $last['uploader'] = "<span class='" . $last['classname'] . "'>" . $last['username'] . '</span>';
    }

    $last['block_id'] = "staff_pick_id_{$last['id']}";
    $staffpicks .= torrent_tooltip_wrapper($last);
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
