<?php

declare(strict_types = 1);

use Pu239\Image;
use Pu239\Torrent;

require_once PARTIALS_DIR . 'torrent_table.php';
global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$last5tvtorrents = $torrent->get_latest($site_config['categories']['tv']);

$latest_tv .= "
    <a id='latesttvtorrents-hash'></a>
    <div id='latesttvtorrents' class='box'>
        <div class='has-text-centered'>
            <div class='module table-wrapper'>
                <!-- <div class='badge badge-new'></div> -->" . torrent_table($lang['last5torrents_tv_title']);

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
                            <td colspan='7'>{$lang['last5torrents_no_torrents']}</td>
                        </tr>";
}
$latest_tv .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
