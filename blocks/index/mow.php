<?php

declare(strict_types = 1);

use Pu239\Image;
use Pu239\Torrent;

require_once PARTIALS_DIR . 'torrent_table.php';
global $container, $lang, $site_config, $CURUSER;

$torrent = $container->get(Torrent::class);
$motw = $torrent->get_mow();

$torrents_mow .= "
    <a id='mow-hash'></a>
    <div id='mow' class='box'>
        <div class='has-text-centered'>
            <div class='table-wrapper module'>
                <div class='badge badge-hot'></div>" . torrent_table();

$images_class = $container->get(Image::class);
foreach ($motw as $last) {
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

    $last['block_id'] = "mow_id_{$last['id']}";
    $torrents_mow .= torrent_tooltip_wrapper($last);
}

if (count($motw) === 0) {
    $torrents_mow .= "
                        <tr>
                            <td colspan='7'>{$lang['index_mow_no']}!</td>
                        </tr>";
}
$torrents_mow .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
