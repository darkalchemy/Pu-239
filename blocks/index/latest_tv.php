<?php

declare(strict_types = 1);

use Pu239\Image;
use Pu239\Torrent;

global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$last5tvtorrents = $torrent->get_latest($site_config['categories']['tv']);

$latest_tv .= "
    <a id='latesttvtorrents-hash'></a>
    <div id='latesttvtorrents' class='box'>
        <div class='has-text-centered'>
            <div class='module table-wrapper'>
                <div class='badge badge-new'></div>
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th class='has-text-centered w-10 min-100'>{$lang['index_mow_type']}</th>
                            <th class='w-50 min-350'>{$lang['last5torrents_tv_title']}</th>
                            <th class='has-text-centered tooltipper' title='{$lang['index_download']}'><i class='icon-download icon' aria-hidden='true'></i></th>
                            <th class='has-text-centered'>{$lang['index_mow_snatched']}</th>
                            <th class='has-text-centered'>{$lang['last5torrents_seeders']}</th>
                            <th class='has-text-centered'>{$lang['last5torrents_leechers']}</th>
                        </tr>
                    </thead>
                    <tbody>";

$images_class = $container->get(Image::class);
foreach ($last5tvtorrents as $last) {
    $last['text'] = $last['name'] . '(' . $last['year'] . ')';
    if (empty($last['poster']) && !empty($last['imdb_id'])) {
        $last['poster'] = $images_class->find_images($last['imdb_id']);
    }
    $last['poster'] = empty($last['poster']) ? "<img src='{$site_config['paths']['images_baseurl']}noposter.png' alt='Poster for {$last['name']}' class='tooltip-poster'>" : "<img src='" . url_proxy($last['poster'], true, 250) . "' class='tooltip-poster'>";
    if ($last['anonymous'] === '1' && ($user['class'] < UC_STAFF || $last['owner'] === $user['id'])) {
        $last['uploader'] = get_anonymous_name();
    } else {
        $last['username'] = !empty($last['username']) ? format_comment($last['username']) : 'unknown';
        $last['uploader'] = "<span class='" . $last['classname'] . "'>" . $last['username'] . '</span>';
    }

    $last['block_id'] = "last_tv_id_{$last['id']}";
    $latest_tv .= torrent_tooltip_wrapper($last);
}
if (count($last5tvtorrents) === 0) {
    $latest_tv .= "
                        <tr>
                            <td colspan='6'>{$lang['last5torrents_no_torrents']}</td>
                        </tr>";
}
$latest_tv .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
