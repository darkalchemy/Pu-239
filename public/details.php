<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Coin;
use Pu239\Comment;
use Pu239\Session;
use Pu239\Torrent;
use Pu239\User;

$time_start = microtime(true);
require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_comments.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_rating.php';
require_once INCL_DIR . 'function_details.php';
require_once INCL_DIR . 'function_categories.php';
require_once INCL_DIR . 'function_event.php';
$user = check_user_status();
global $container, $site_config, $BLOCKS;

$isfree = [];
$is_free = get_events_data();
$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('details_js'),
        get_file_name('sceditor_js'),
    ],
];

$sections = [
    'title' => null,
    'imdb_data' => 'main_div',
    'tvmaze_data' => 'main_div',
    'ebook_data' => 'main_div',
    'descr' => 'main_div',
    'youtube' => 'main_div',
    'slots' => null,
    'info_block' => 'main_table',
    'points' => 'main_table',
    'audit' => 'main_table',
    'add_comment' => 'main_div',
    'comments' => null,
];
$session = $container->get(Session::class);
if (!isset($_GET['id'])) {
    $session->set('is-warning', '[h3]' . _('Error') . '[/h3] ' . _('Missing ID') . '');
    header("Location: {$site_config['paths']['baseurl']}/index.php");
    die();
}

if (!is_valid_id((int) $_GET['id'])) {
    $session->set('is-warning', '[h3]' . _('Error') . '[/h3] ' . _('Invalid ID') . " {$_GET['id']}");
    header("Location: {$site_config['paths']['baseurl']}/index.php");
    die();
}
$scheme = $session->get('scheme') === 'http' ? '' : '&amp;ssl=1';
$id = (int) $_GET['id'];
$dt = TIME_NOW;
$torrents_class = $container->get(Torrent::class);
$torrent = $torrents_class->get($id);
$next = $previous = '';
if (!empty($torrent['previous']['id'])) {
    $previous = "<a href='{$site_config['paths']['baseurl']}/details.php?id={$torrent['previous']['id']}&amp;hit=1' class='tooltipper' title='" . htmlsafechars((string) $torrent['previous']['name']) . "'><i class='icon-left-open size_2' aria-hidden='true'></i></a>";
}
if (!empty($torrent['next']['id'])) {
    $next = "<a href='{$site_config['paths']['baseurl']}/details.php?id={$torrent['next']['id']}&amp;hit=1' class='tooltipper' title='" . htmlsafechars((string) $torrent['next']['name']) . "'><i class='icon-right-open size_2' aria-hidden='true'></i></a>";
}
if (empty($torrent)) {
    $session->set('is-warning', '[h3]' . _('Error') . '[/h3] ' . _('Invalid ID') . "{$_GET['id']}");
    header("Location: {$site_config['paths']['baseurl']}/index.php");
    die();
}
if (isset($_GET['hit'])) {
    $torrent['views'] = $torrent['views'] + 1;
    $set = [
        'views' => $torrent['views'],
    ];
    $torrents_class->update($set, $id);
}
$owned = $moderator = 0;
$owner = $torrent['owner'];
if (has_access($user['class'], UC_STAFF, 'torrent_mod')) {
    $owned = $moderator = 1;
} elseif ($user['id'] === $owner) {
    $owned = 1;
}
$cache = $container->get(Cache::class);
if ($moderator) {
    if (isset($_POST['checked']) && $_POST['checked'] == $id) {
        $set = [
            'checked_by' => $user['id'],
            'checked_when' => $dt,
        ];
        $torrents_class->update($set, $id);
        $torrent['checked_by'] = $user['id'];
        $torrent['checked_when'] = $dt;
        write_log(_fe('Torrent {0}({1}){2} was checked by {3}', "[url={$site_config['paths']['baseurl']}details.php?id=$id]", htmlsafechars((string) $torrent['name']), '[/url]', $user['username']));
        if (!empty($_GET['returnto'])) {
            $returnto = str_replace('&amp;', '&', $_GET['returnto']);
            header("Location: {$site_config['paths']['baseurl']}" . urldecode($returnto));
            die();
        }
        $session->set('is-success', "Torrents has been 'Checked'");
    } elseif (isset($_POST['rechecked']) && $_POST['rechecked'] == $id) {
        $set = [
            'checked_by' => $user['id'],
            'checked_when' => $dt,
        ];
        $torrents_class->update($set, $id);
        $torrent['checked_by'] = $user['id'];
        $torrent['checked_when'] = $dt;
        write_log("Torrent [url={$site_config['paths']['baseurl']}details.php?id=$id](" . htmlsafechars((string) $torrent['name']) . ")[/url] was re-checked by {$user['username']}");
        $session->set('is-success', "Torrents has been 'Re-Checked'");
    } elseif (isset($_POST['clearchecked']) && $_POST['clearchecked'] == $id) {
        $set = [
            'checked_by' => 0,
            'checked_when' => 0,
        ];
        $torrents_class->update($set, $id);
        $torrent['checked_by'] = 0;
        $torrent['checked_when'] = 0;
        write_log("Torrent [url={$site_config['paths']['baseurl']}details.php?id=$id](" . htmlsafechars((string) $torrent['name']) . ")[/url] was un-checked by {$user['username']}");
        $session->set('is-success', "Torrents has been 'Un-Checked'");
    } elseif (isset($_POST['clear_cache']) && $_POST['clear_cache'] == $id) {
        $cache->deleteMulti([
            'motw_',
            'torrent_details_' . $id,
            'torrent_descr_' . $id,
            'top_torrents_',
            'latest_torrents_',
            'tvshow_ids_' . $torrent['imdb_id'],
            'staff_picks_',
            'tvshow_ids_' . hash('sha256', get_show_name($torrent['name'])),
            'imdb_fullset_title_' . $torrent['imdb_id'],
            'imdb_' . str_replace('tt', '', $torrent['imdb_id']),
            'book_fullset_' . $torrent['id'],
            'slider_torrents_',
            'scroller_torrents_',
        ]);
        if (!empty($imdb_id)) {
            $cache->delete('tvshow_ids_' . $torrent['imdb_id']);
        }
        if (!empty($torrent['imdb_id'])) {
            $ids = get_show_id_by_imdb($torrent['imdb_id']);
        } else {
            $ids = get_show_id($torrent['name']);
        }
        if (!empty($ids['tvmaze_id'])) {
            preg_match('/S(\d+)E(\d+)/i', $torrent['name'], $match);
            $episode = !empty($match[2]) ? $match[2] : 0;
            $season = !empty($match[1]) ? $match[1] : 0;
            $cache->deleteMulti([
                'tvshow_episode_info_' . $ids['tvmaze_id'] . $season . $episode,
                'tvmaze_' . $ids['tvmaze_id'],
                'tvmaze_fullset_' . $ids['tvmaze_id'],
            ]);
        }
        if (!empty($ids['thetvdb_id'])) {
            $cache->deleteMulti([
                'show_images_' . $ids['thetvdb_id'],
                'movie_images_' . $ids['thetvdb_id'],
            ]);
        }

        $session->set('is-success', 'Torrent Cache Cleared');
        header("Location: {$site_config['paths']['baseurl']}/details.php?id=$id");
        die();
    }
}
if ($user['downloadpos'] !== 1) {
    $session->set('is-warning', '[h2]Download Disabled[/h2]Your download rights have been disabled.');
}
$HTMLOUT = '';
if (isset($_GET['uploaded'])) {
    $HTMLOUT .= "<meta http-equiv='refresh' content='1;url=download.php?torrent={$id}" . $scheme . "'>";
}
$categories = genrelist(false);
$change = [];
foreach ($categories as $key => $value) {
    $change[$value['id']] = [
        'id' => $value['id'],
        'name' => $value['name'],
        'image' => $value['image'],
    ];
}
if (empty($torrent['imdb_id']) && !empty($torrent['url'])) {
    preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt\d{7,8})/i', $torrent['url'], $imdb_tmp);
    $imdb_id = !empty($imdb_tmp[2]) ? $imdb_tmp[2] : '';
    if (empty($imdb_id)) {
        $text = preg_replace('/\s+/', '', $torrent['descr']);
        preg_match('/imdb\.com\/title\/(tt\d{7,8})/i', $text, $match);
        if (isset($match[1])) {
            $imdb_id = $match[1];
        }
    }

    if (!empty($imdb_id)) {
        $set = [
            'imdb_id' => $imdb_id,
        ];
        $torrents_class->update($set, $id);
        $torrent['imdb_id'] = $imdb_id;
    }
}
if ($BLOCKS['google_books_api_on'] && in_array($torrent['category'], $site_config['categories']['ebook'])) {
    $search = $torrent['name'];
    if (!empty($torrent['isbn'])) {
        $search = $torrent['isbn'];
    }

    $ebook_data = $cache->get('book_fullset_' . $torrent['id']);
    if ($ebook_data === false || is_null($ebook_data)) {
        if (!empty($torrent['isbn']) || !empty($torrent['title'])) {
            $ebook_data = "
            <a id='book-hash'></a>
            <div id='book' data-isbn='{$torrent['isbn']}' data-name='{$torrent['title']}' data-tid='{$torrent['id']}'>
                <div id='isbn_outer'>
                </div>
            </div>";
        }
    }
}
if ($BLOCKS['tvmaze_api_on'] && in_array($torrent['category'], $site_config['categories']['tv'])) {
    if (!empty($torrent['imdb_id'])) {
        $ids = get_show_id_by_imdb($torrent['imdb_id']);
    } else {
        $ids = get_show_id($torrent['name']);
    }
    if (!empty($ids['tvmaze_id'])) {
        $tvmaze_data = $cache->get('tvmaze_fullset_' . $ids['tvmaze_id']);
        if (empty($tvmaze_data)) {
            $tvmaze_data = "
            <a id='tvmaze-hash'></a>
            <div id='tvmaze' data-tvmazeid='{$ids['tvmaze_id']}' data-name='{$torrent['name']}' data-tid='{$torrent['id']}'>
                <div id='tvmaze_outer'>
                </div>
            </div>";
        }
    }
}
if ($BLOCKS['imdb_api_on'] && (in_array($torrent['category'], $site_config['categories']['movie']) || in_array($torrent['category'], $site_config['categories']['tv'])) && !empty($torrent['imdb_id'])) {
    $imdb_id = $torrent['imdb_id'];
    $imdb_data = $cache->get('imdb_fullset_title_' . $torrent['imdb_id']);
    if ($imdb_data === false || is_null($imdb_data)) {
        $imdb_data = "
            <a id='imdb-hash'></a>
            <div id='imdb' data-imdbid='{$torrent['imdb_id']}' data-tid='{$torrent['id']}' data-poster='{$torrent['poster']}'>
                <div id='imdb_outer'>
                </div>
            </div>";
    }
}
if (!empty($torrent['youtube'])) {
    preg_match('/(watch\?v=|watch\?.+&v=)(.{8,11})/i', $torrent['youtube'], $match);
    if (isset($match[2])) {
        $youtube_id = $match[2];
        $youtube = "
            <a id='youtube-hash'></a>
            <div class='responsive-container'>
                <iframe width='1920' height='1080' src='https://www.youtube.com/embed/{$youtube_id}?enablejsapi=1autoplay=0&fs=0&iv_load_policy=3&showinfo=0&rel=0&cc_load_policy=0&start=0&end=0&origin={$site_config['paths']['baseurl']}&vq=hd1080&wmode=opaque' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>
            </div>";
    }
}
if (!empty($torrent['subs'])) {
    $subs = $container->get('subtitles');
    $subtitles = explode('|', $torrent['subs']);
    $Subs = [];
    foreach ($subtitles as $k => $subname) {
        foreach ($subs as $sub) {
            if (strtolower($sub['name']) === strtolower($subname)) {
                $Subs[] = "<img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' class='sub_flag tooltipper left10' alt='" . htmlsafechars($sub['name']) . "' title='" . htmlsafechars($sub['name']) . ' ' . _('Subtitle') . "'>";
            }
        }
    }
    $subtitles = '';
    if (!empty($Subs)) {
        $subtitles = "<span class='level-left'>" . implode(' ', $Subs) . '</span>';
    }
}
if (!empty($torrent['audios'])) {
    $subs = $container->get('subtitles');
    $audios = explode('|', $torrent['audios']);
    $Audios = [];
    foreach ($audios as $k => $subname) {
        foreach ($subs as $sub) {
            if (strtolower($sub['name']) === strtolower($subname)) {
                $Audios[] = "<img src='{$site_config['paths']['images_baseurl']}/{$sub['pic']}' class='sub_flag tooltipper left10' alt='" . htmlsafechars($sub['name']) . "' title='" . htmlsafechars($sub['name']) . ' ' . _('Audio') . "'>";
            }
        }
    }
    $audios = '';
    if (!empty($Audios)) {
        $audios = "<span class='level-left'>" . implode(' ', $Audios) . '</span>';
    }
}
if (!empty($torrent['imdb_id'])) {
    $banner_image = get_banner($torrent['imdb_id']);
    $banner = !empty($banner_image) && !is_bool($banner_image) ? "<img src='" . url_proxy((string) $banner_image, true, 1000, 185) . "' class='w-100 round10 bottom20'>" : '';
    if (!empty($torrent['name'])) {
        $title = "
            <div class='bottom20 w-100'>
                $banner
                <div class='bg-00 round10 columns padding20 is-gapless level'>
                    <span class='column is-1 size_7 has-text-left padding20'>$previous</span>
                    <h1 class='column has-text-centered padding20 torrent-name tooltipper' title='" . htmlsafechars((string) $torrent['name']) . "'>" . htmlsafechars((string) $torrent['name']) . "</h1>
                    <span class='column is-1 size_7 has-text-right padding20'>$next</span>
                </div>
            </div>";
    }
}
$torrent['free_color'] = '#0f0';
$torrent['silver_color'] = 'silver';
require_once PARTIALS_DIR . 'free_details.php';
$info_block = '';
if ($user['class'] >= (UC_MIN + 1) && $torrent['nfosz'] > 0) {
    $info_block .= tr(_('NFO'), "<div class='left10'><a href='{$site_config['paths']['baseurl']}/viewnfo.php?id={$torrent['id']}'>" . _('View NFO') . '</a> (' . mksize($torrent['nfosz']) . ')</div>', 1);
}
if (!empty($torrent['subs'])) {
    $info_block .= tr(_('Subtitles'), $subtitles, 1);
}
if (!empty($torrent['audios'])) {
    $info_block .= tr(_('Audios'), $audios, 1);
}
if ($torrent['visible'] === 'no') {
    $info_block .= tr(_('Visible'), '<div class="left10">' . _('No') . '(' . _('dead') . ')</div>', 1);
}
if ($moderator) {
    $info_block .= tr(_('Banned'), "<div class='left10'>{$torrent['banned']}</div>", 1);
}
if ($torrent['nuked'] === 'yes') {
    $reason = !empty($torrent['nukereason']) ? $torrent['nukereason'] : '';
    $info_block .= tr('Nuked', "<div class='level-left left10'><img src='{$site_config['paths']['images_baseurl']}nuked.gif' alt='Nuked' class='tooltipper icon right5' title='Nuked'>$reason</div>", 1);
}
$torrent['cat_name'] = htmlsafechars((string) $change[$torrent['category']]['name']);
if (isset($torrent['cat_name'])) {
    $info_block .= tr(_('Type'), '<div class="left10">' . htmlsafechars((string) $torrent['cat_name']) . '</div>', 1);
} else {
    $info_block .= tr(_('Type'), '<div class="left10">None</div>', 1);
}
$lastseed = $torrents_class->get_items(['last_action'], $id);
$info_block .= tr('Rating', '<div class="left10">' . getRate($id, 'torrent') . '</div>', 1);
$info_block .= tr(_('Last&#160;seeder'), '<div class="left10">' . _('Last Activity ') . get_date(strtotime($lastseed), '', 0, 1) . '</div>', 1);
if (!isset($_GET['filelist'])) {
    $info_block .= tr(_('Num Files'), "<div class='level-left is-flex left10'><a href='{$site_config['paths']['baseurl']}/filelist.php?id=$id' class='tooltipper' title='" . _('See full list') . "'>{$torrent['numfiles']} file" . plural($torrent['numfiles']) . '</a></div>', 1);
}
$info_block .= tr(_('Size'), '<div class="left10">' . mksize($torrent['size']) . ' (' . number_format($torrent['size']) . ' ' . _('bytes ') . ')</div>', 1);
$info_block .= tr(_('Added'), '<div class="left10">' . get_date((int) $torrent['added'], 'LONG') . '</div>', 1);
$info_block .= tr(_('Views'), "<div class='left10'>{$torrent['views']}</div>", 1);
$info_block .= tr(_('Hits'), "<div class='left10'>{$torrent['hits']}</div>", 1);
$info_block .= tr(_('Snatched'), '<div class="left10">' . ($torrent['times_completed'] > 0 ? "<a href='{$site_config['paths']['baseurl']}/snatches.php?id={$id}'>{$torrent['times_completed']} " . _(' time') . plural($torrent['times_completed']) . '</a>' : '0 ' . _(' time') . '') . '</div>', 1);
$info_block .= tr(_('Peers'), "<div class='left10'><a href='{$site_config['paths']['baseurl']}/peerlist.php?id=$id#seeders' class='tooltipper' title='" . _('See full list') . "'>{$torrent['seeders']} seeder" . plural($torrent['seeders']) . ' + ' . $torrent['leechers'] . ' leecher' . plural($torrent['leechers']) . ' = ' . ($torrent['seeders'] + $torrent['leechers']) . _(' peer(s) total') . '', 1);

if (!empty($torrent['descr'])) {
    $descr = $cache->get('torrent_descr_' . $id);
    if ($descr === false || is_null($descr)) {
        $descr = "
            <div id='descr' data-tid='{$torrent['id']}'>
                <div id='descr_outer'>
                </div>
            </div>";
    }
}

$torrent['addup'] = !empty($torrent['addedup']) ? get_date((int) $torrent['addedup'], 'DATE') : '';
$torrent['addfree'] = !empty($torrent['addedfree']) ? get_date((int) $torrent['addedfree'], 'DATE') : '';
$torrent['idk'] = $dt + 14 * 86400;
$torrent['freeimg'] = '<img src="' . $site_config['paths']['images_baseurl'] . 'freedownload.gif" alt="">';
$torrent['doubleimg'] = '<img src="' . $site_config['paths']['images_baseurl'] . 'doubleseed.gif" alt="">';
$slot = make_freeslots($user['id'], 'fllslot_', false);
$torrent['addedfree'] = $torrent['addedup'] = $free_slot = $double_slot = '';
if (!empty($slot)) {
    foreach ($slot as $sl) {
        if ($sl['torrentid'] == $id && $sl['free'] === 'yes') {
            $free_slot = 1;
            $torrent['addedfree'] = $sl['addedfree'];
        }
        if ($sl['torrentid'] == $id && $sl['doubleup'] === 'yes') {
            $double_slot = 1;
            $torrent['addedup'] = $sl['addedup'];
        }
        if ($free_slot && $double_slot) {
            break;
        }
    }
}
$torrent['tags'] = empty($torrent['tags']) ? str_replace([
    ' ',
    '_',
    '-',
    ':',
], ',', $torrent['name']) : $torrent['tags'];
$tags = explode(',', $torrent['tags']);
$keywords = '';
foreach ($tags as $tag) {
    if (strlen($tag) >= 3) {
        $keywords .= "
        <a href='{$site_config['paths']['baseurl']}/browse.php?search=$tag&amp;searchin=all&amp;incldead=1'>" . htmlsafechars((string) $tag) . '</a>';
    }
}
$points = tr(_('Tags'), "<div class='left10'>$keywords</div>", 1);
$coin_class = $container->get(Coin::class);
$coins = $coin_class->get($id);
$my_points = $total_coins = 0;
$coin_users = [];
if (!empty($coins)) {
    $users_class = $container->get(User::class);
    foreach ($coins as $coin) {
        $my_points = $coin['userid'] === $user['id'] ? $coin['points'] : $my_points;
        $total_coins += $coin['points'];
        $coin_user = $users_class->getUserFromId($coin['userid']);
        if ($coin_user['anonymous_until'] < TIME_NOW && $coin_user['perms'] < PERMS_STEALTH) {
            $coin_users[] = format_username($coin_user['id']);
        }
    }
}
$coin_users = !empty($coin_users) ? '
    <div>Coins provided by: ' . implode(', ', $coin_users) . '</div>' : '';

$points .= tr('Karma Points', '
                    <div class="left10">
                        <p>In total ' . $total_coins . ' Karma Points given to this torrent of which ' . $my_points . ' from you</p>
                        <span>
                            <a href="' . $site_config['paths']['baseurl'] . '/coins.php?id=' . $id . '&amp;points=10">
                                <img src="' . $site_config['paths']['images_baseurl'] . '10coin.png" alt="10" class="tooltipper" title="10 Points">
                            </a>
                            <a href="' . $site_config['paths']['baseurl'] . '/coins.php?id=' . $id . '&amp;points=20">
                                <img src="' . $site_config['paths']['images_baseurl'] . '20coin.png" alt="20" class="tooltipper" title="20 Points">
                            </a>
                            <a href="' . $site_config['paths']['baseurl'] . '/coins.php?id=' . $id . '&amp;points=50">
                                <img src="' . $site_config['paths']['images_baseurl'] . '50coin.png" alt="50" class="tooltipper" title="50 Points">
                            </a>
                            <a href="' . $site_config['paths']['baseurl'] . '/coins.php?id=' . $id . '&amp;points=100">
                                <img src="' . $site_config['paths']['images_baseurl'] . '100coin.png" alt="100" class="tooltipper" title="100 Points">
                            </a>
                            <a href="' . $site_config['paths']['baseurl'] . '/coins.php?id=' . $id . '&amp;points=200">
                                <img src="' . $site_config['paths']['images_baseurl'] . '200coin.png" alt="200" class="tooltipper" title="200 Points">
                            </a>
                            <a href="' . $site_config['paths']['baseurl'] . '/coins.php?id=' . $id . '&amp;points=500">
                                <img src="' . $site_config['paths']['images_baseurl'] . '500coin.png" alt="500" class="tooltipper" title="500 Points">
                            </a>
                            <a href="' . $site_config['paths']['baseurl'] . '/coins.php?id=' . $id . '&amp;points=1000">
                                <img src="' . $site_config['paths']['images_baseurl'] . '1000coin.png" alt="1000" class="tooltipper" title="1000 Points">
                            </a>
                        </span>
                        <div>By clicking on the coins you can give Karma Points to the uploader of this torrent.</div>' . $coin_users . '
                    </div>', 1);
$downl = $user['downloaded'] + $torrent['size'];
$sr = $user['uploaded'] / $downl;
switch (true) {
    case $sr >= 4:
        $s = 'w00t';
        break;

    case $sr >= 2:
        $s = 'grin';
        break;

    case $sr >= 1:
        $s = 'smile1';
        break;

    case $sr >= 0.5:
        $s = 'noexpression';
        break;

    case $sr >= 0.25:
        $s = 'sad';
        break;

    case $sr > 0.00:
        $s = 'cry';
        break;

    default:
        $s = 'w00t';
        break;
}
$sr = floor(($sr * 1000) / 1000);
$sr = "
        <img src='{$site_config['paths']['images_baseurl']}smilies/{$s}.gif' alt='' class='emoticon right10'>
        <span class='right10' style='color: " . get_ratio_color($sr) . ";'>" . number_format($sr, 3) . '</span>';
if ($torrent['free'] >= 1 || $torrent['freetorrent'] >= 1 || $is_free['free'] > 1 || $isfree['yep'] || $free_slot || $double_slot != 0 || strtotime($user['personal_freeleech']) > TIME_NOW) {
    $points .= tr('Ratio After Download', "<div class='left10'><div class='level-left'><del>{$sr} " . _('Your new ratio if you download this torrent') . "</del></div><div class='top10'><span class='has-text-success'>[FREE] </span>(Only upload stats are recorded)</div></div>", 1);
} else {
    $points .= tr('Ratio After Download', "<div class='level-left left10'>{$sr} " . _('Your new ratio if you download this torrent') . '</div>', 1);
}
$info_hash = $torrent['info_hash'];
$points .= tr(_('Info hash'), "<div title='$info_hash' class='tooltipper left10'>" . substr($info_hash, 0, 40) . '<br>' . substr($info_hash, 40, 80) . '</div>', 1);

$url = $site_config['paths']['baseurl'] . '/edit.php?id=' . $torrent['id'];
if (isset($_GET['returnto'])) {
    $url .= '&amp;returnto=' . urlencode($_GET['returnto']);
}
$rowuser = !empty($owner) ? format_username((int) $owner) : _('unknown');
$uprow = $torrent['anonymous'] === '1' ? (!$moderator && !$owner ? '' : $rowuser . ' - ') . '<i>' . get_anonymous_name() . '</i>' : $rowuser;
$audit = tr('Upped by', "<div class='level-left left10'>$uprow</div>", 1);
$users_class = $container->get(User::class);
$torrent_cache['rep'] = $users_class->get_item('reputation', $owner);
if ($torrent_cache['rep']) {
    $member_reputation = get_reputation($users_class->getUserFromId($owner), 'torrents', true, $id, ($torrent['anonymous'] === '1' ? true : false));
    $audit .= tr('Reputation', "
        <div class='level-left left10'>
            $member_reputation counts towards uploaders Reputation
        </div>", 1);
}
$audit .= tr('Report Torrent', "
    <form action='{$site_config['paths']['baseurl']}/report.php?type=Torrent&amp;id=$id' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
        <div class='level-left'>
            <input class='button is-small left10 details-button' type='submit' name='submit' value='Report This Torrent'>
            <div class='left10'>
                For breaking the
                <a href='{$site_config['paths']['baseurl']}/rules.php'>
                    <span class='has-text-success'>&nbsp;rules</span>
                </a>
            </div>
        </div>
    </form>", 1);

if ($owned) {
    $audit .= tr('Edit Torrent', "<a href='$url' class='button is-small details-button left10'>" . _('Edit this torrent') . '</a>', 1);
}
if ($moderator) {
    $audit .= tr('Clear Cache', "
                    <form method='post' action='{$site_config['paths']['baseurl']}/details.php?id={$torrent['id']}' enctype='multipart/form-data' accept-charset='utf-8'>
                        <input type='hidden' name='clear_cache' value={$torrent['id']}>
                        <input type='submit' class='button is-small details-button left10' value='Clear Cache'>
                    </form>", 1);

    $returnto = '';
    if (!empty($_GET['returnto'])) {
        $returnto = '&amp;returnto=' . urlencode($_GET['returnto']);
    }
    if (!empty($torrent['checked_by'])) {
        $checked_by = $torrent['checked_by'];
        $audit .= tr('Checked by', "
                    <div class='bottom10 left10'>" . format_username((int) $torrent['checked_by']) . (isset($torrent['checked_when']) && $torrent['checked_when'] > 0 ? ' checked: ' . get_date((int) $torrent['checked_when'], 'DATE', 0, 1) : '') . "</div>
                    <div class='bottom10 left10'>
                        <form method='post' action='{$site_config['paths']['baseurl']}/details.php?id={$torrent['id']}{$returnto}' enctype='multipart/form-data' accept-charset='utf-8'>
                            <input type='hidden' name='rechecked' value={$torrent['id']}>
                            <input type='submit' class='button is-small details-button bottom10' value='Re-Check this torrent'>
                        </form>
                        <form method='post' action='{$site_config['paths']['baseurl']}/details.php?id={$torrent['id']}' enctype='multipart/form-data' accept-charset='utf-8'>
                            <input type='hidden' name='clearchecked' value={$torrent['id']}>
                            <input type='submit' class='button is-small details-button' value='Un-Check this torrent'>
                        </form>
                    </div>", 1);
    } else {
        $audit .= tr('Checked by', "
                    <form method='post' action='{$site_config['paths']['baseurl']}/details.php?id={$torrent['id']}{$returnto}' enctype='multipart/form-data' accept-charset='utf-8'>
                        <input type='hidden' name='checked' value={$torrent['id']}>
                        <input type='submit' class='button is-small details-button left10' value='Check this torrent'>
                    </form>", 1);
    }
}

$audit .= tr(_('Thanks list'), "
        <noscript>
            <iframe id='thanked' src ='{$site_config['paths']['baseurl']}/ajax/thanks.php?torrentid={$id}'>
                Your browser does not support iframes. And it has Javascript disabled!
            </iframe>
        </noscript>
        <div id='thanks_holder' data-tid='{$torrent['id']}' class='left10'></div>", 1);
$next_reseed = 0;
if ($torrent['last_reseed'] > 0) {
    $next_reseed = $torrent['last_reseed'] + 172800;
}
$audit .= tr('Request Reseed', "
        <form method='post' action='{$site_config['paths']['baseurl']}/takereseed.php' enctype='multipart/form-data' accept-charset='utf-8'>
            <div class='level-left is-flex'>
                <span class='left10'>
                    <select name='pm_what'>
                        <option value='last10'>last10</option>
                        <option value='owner'>uploader</option>
                    </select>
                </span>
                <span class='left10'>
                    <input type='hidden' name='uploader' value='" . (int) $owner . "'>
                    <input type='hidden' name='reseedid' value='$id'>
                    <input type='hidden' name='name' value='{$torrent['name']}'>
                    <input type='submit' class='button is-small' " . (($next_reseed > $dt) ? 'disabled' : '') . " value='SendPM'>
                </span>
            </div>
        </form>", 1);
if ($torrent['allow_comments'] === 'yes' || $moderator) {
    $comments = '';
    $add_comment = "
    <a id='startcomments'></a>
    <div class='has-text-centered'>
        <h2>Leave a Comment</h2>
        <a href='{$site_config['paths']['baseurl']}/takethankyou.php?id={$torrent['id']}'>
            <img src='{$site_config['paths']['images_baseurl']}smilies/thankyou.gif' class='tooltipper' alt='Thank You' title='Give a quick \"Thank You\"'>
        </a>
        <form name='comment' method='post' action='{$site_config['paths']['baseurl']}/comment.php' enctype='multipart/form-data' accept-charset='utf-8'>
            <input type='hidden' name='action' value='add'>
            <input type='hidden' name='tid' value='{$torrent['id']}'>
            " . BBcode('', '', 200) . "
            <div class='has-text-centered'>
                <input class='button is-small margin20' type='submit' value='Submit'>
            </div>
        </form>
    </div>";

    $count = $torrent['comments'];
    if (!$count) {
        $comments .= "
            <h2 class='has-text-centered top10'>" . _('No Comments yet!') . '</h2>';
    } else {
        $perpage = 15;
        $comments_class = $container->get(Comment::class);
        $torrent_comments = $comments_class->get_torrent_comment($torrent['id'], $count, $perpage);
        $pager = $torrent_comments[1];
        if ($count > $perpage) {
            $comments .= $pager['pagertop'];
        }

        $comments .= commenttable($torrent_comments[0], 'torrent');

        if ($count > $perpage) {
            $comments .= $pager['pagerbottom'];
        }
    }
} else {
    $comments = "
        <a id='startcomments'></a>" . main_div(_('Sorry Comments disabled!'), 'has-text-centered', 'padding20');
}
if ($user['downloadpos'] === 1 || $owner) {
    $slots = "
        <div class='tooltip_templates'>
            <div id='balloon1' class='text-justify'>
                Once chosen this torrent will be Freeleech {$torrent['freeimg']} until " . get_date((int) $torrent['idk'], 'DATE') . " and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.
            </div>
        </div>
        <div class='tooltip_templates'>
            <div id='balloon2' class='text-justify'>
                Once chosen this torrent will be Doubleseed {$torrent['doubleimg']} until " . get_date((int) $torrent['idk'], 'DATE') . " and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.
            </div>
        </div>
        <div class='tooltip_templates'>
            <div id='balloon3' class='text-justify'>
                Remember to show your gratitude and Thank the Uploader. <img src='{$site_config['paths']['images_baseurl']}smilies/smile1.gif' alt=''>
            </div>
        </div>";

    if ($free_slot && !$double_slot) {
        $slots .= '<div class="has-text-centered bottom10">' . $torrent['freeimg'] . ' <span class="has-text-success">Freeleech Slot In Use!</span> (only upload stats are recorded) - Expires: 12:01AM ' . $torrent['addfree'] . '</div>';
        $freeslot = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=double' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . (int) $user['freeslots'] . ' Slots Remaining.' : '';
        $freeslot_zip = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=double&amp;zip=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . (int) $user['freeslots'] . ' Slots Remaining.' : '';
        $freeslot_text = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=double&amp;text=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a>- " . (int) $user['freeslots'] . ' Slots Remaining.' : '';
    } elseif (!$free_slot && $double_slot) {
        $slots .= '<div class="has-text-centered bottom10">' . $torrent['doubleimg'] . ' <span class="has-text-success">Doubleseed Slot In Use!</span> (upload stats x2) - Expires: 12:01AM ' . $torrent['addup'] . '</div>';
        $freeslot = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=free' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> - " . (int) $user['freeslots'] . ' Slots Remaining.' : '';
        $freeslot_zip = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=free&amp;zip=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> - " . (int) $user['freeslots'] . ' Slots Remaining.' : '';
        $freeslot_text = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=free&amp;text=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> - " . (int) $user['freeslots'] . ' Slots Remaining.' : '';
    } elseif ($free_slot && $double_slot) {
        $slots .= main_div('<div class="has-text-centered padding20">' . $torrent['freeimg'] . ' ' . $torrent['doubleimg'] . ' <span class="has-text-success padding10">Freeleech and Doubleseed Slots In Use!</span> (upload stats x2 and no download stats are recorded)<div class="padding10">Freeleech Expires: 12:01AM ' . $torrent['addfree'] . ' and Doubleseed Expires: 12:01AM ' . $torrent['addup'] . '</div></div>', 'bottom20');
        $freeslot = $freeslot_zip = $freeslot_text = '';
    } else {
        $freeslot = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=free' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=double' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . $user['freeslots'] . ' Slots Remaining. ' : '';
        $freeslot_zip = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=free&amp;zip=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=double&amp;zip=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . $user['freeslots'] . ' Slots Remaining.' : '';
        $freeslot_text = $user['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;slot=free&amp;text=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> Use: <a class='index dt-tooltipper-small' href='download.php?torrent={$id}" . $scheme . "&amp;slot=double&amp;text=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . $user['freeslots'] . ' Slots Remaining.' : '';
    }
    $Free_Slot = $freeslot;
    $Free_Slot_Zip = $freeslot_zip;
    $Free_Slot_Text = $freeslot_text;
    $slots .= main_table("
                    <tr>
                        <td class='rowhead w-1'>" . _('Download') . "</td>
                        <td>
                            <a class='index' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "'>" . htmlsafechars($torrent['filename']) . "</a><br>{$Free_Slot}
                        </td>
                    </tr>
                    <tr>
                        <td>" . _('Zip') . "</td>
                        <td>
                            <a class='index' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;zip=1'>" . htmlsafechars($torrent['filename']) . ".zip</a><br>{$Free_Slot_Zip}
                        </td>
                    </tr>
                    <tr>
                        <td>" . _('Text') . "</td>
                        <td>
                            <a class='index' href='{$site_config['paths']['baseurl']}/download.php?torrent={$id}" . $scheme . "&amp;text=1'>" . htmlsafechars($torrent['filename']) . ".txt</a><br>{$Free_Slot_Text}
                        </td>
                    </tr>", null, null, 'bottom20');
}
$i = 0;
foreach ($sections as $section => $wrapper) {
    if (empty(${$section})) {
        unset($sections[$section]);
    }
    ++$i;
}
$i = 0;
foreach ($sections as $section => $wrapper) {
    ++$i;
    $class = $i >= count($sections) ? '' : 'bottom20';
    if ($wrapper === 'main_table') {
        $HTMLOUT .= main_table(${$section}, null, null, $class);
    } elseif ($wrapper === 'main_div') {
        $HTMLOUT .= main_div(${$section}, $class);
    } else {
        $HTMLOUT .= ${$section};
    }
}

$title = _('Torrent Details');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
