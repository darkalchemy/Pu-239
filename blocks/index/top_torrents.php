<?php

declare(strict_types = 1);

use Pu239\Image;
use Pu239\Torrent;

require_once PARTIALS_DIR . 'torrent_table.php';
global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$top5torrents = $torrent->get_top();

$torrents_top .= "
    <a id='toptorrents-hash'></a>
    <div id='toptorrents' class='box'>
        <div class='has-text-centered'>
            <div class='module table-wrapper'>
                <!-- <div class='badge badge-top'></div> -->" . torrent_table($lang['top5torrents_title']);
$images_class = $container->get(Image::class);
foreach ($top5torrents as $last) {
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

    $last['block_id'] = "top_id_{$last['id']}";
    $torrents_top .= torrent_tooltip_wrapper($last);
}
if (count($top5torrents) === 0) {
    $torrents_top .= "
                    <tr>
                        <td colspan='7'>{$lang['top5torrents_no_torrents']}</td>
                    </tr>";
}
$torrents_top .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
