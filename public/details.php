<?php

$time_start = microtime(true);
require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_comments.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_rating.php';
require_once INCL_DIR . 'function_details.php';
check_user_status();
global $CURUSER, $site_config, $session, $cache, $user_stuffs, $BLOCKS, $torrent_stuffs, $comment_stuffs;

$isfree = [];
$coin_stuffs = new Pu239\Coin();
$lang = array_merge(load_language('global'), load_language('details'));
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

if (!isset($_GET['id'])) {
    $session->set('is-warning', "[h3]{$lang['details_user_error']}[/h3] {$lang['details_missing_id']}");
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}

if (!is_valid_id($_GET['id'])) {
    $session->set('is-warning', "[h3]{$lang['details_user_error']}[/h3] {$lang['details_bad_id']}{$_GET['id']}");
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}

$id = (int) $_GET['id'];
$dt = TIME_NOW;

$torrent = $torrent_stuffs->get($id);
$next = $previous = '';
if (!empty($torrent['previous']['id'])) {
    $previous = "<a href='{$site_config['baseurl']}/details.php?id={$torrent['previous']['id']}&amp;hit=1' class='tooltipper' title='" . htmlsafechars($torrent['previous']['name']) . "'><i class='icon-left-open size_2' aria-hidden='true'></i></a>";
}
if (!empty($torrent['next']['id'])) {
    $next = "<a href='{$site_config['baseurl']}/details.php?id={$torrent['next']['id']}&amp;hit=1' class='tooltipper' title='" . htmlsafechars($torrent['next']['name']) . "'><i class='icon-right-open size_2' aria-hidden='true'></i></a>";
}
if (empty($torrent)) {
    $session->set('is-warning', "[h3]{$lang['details_user_error']}[/h3] {$lang['details_bad_id']}{$_GET['id']}");
    header("Location: {$site_config['baseurl']}/index.php");
    die();
}
if (isset($_GET['hit'])) {
    $torrent['views'] = (int) $torrent['views'] + 1;
    $set = [
        'views' => $torrent['views'],
    ];
    $torrent_stuffs->update($set, $id);
}
$owned = $moderator = 0;
$owner = $torrent['owner'];
if ($CURUSER['class'] >= UC_STAFF) {
    $owned = $moderator = 1;
} elseif ($CURUSER['id'] === $owner) {
    $owned = 1;
}
if ($moderator) {
    if (isset($_POST['checked']) && $_POST['checked'] == $id) {
        $set = [
            'checked_by' => $CURUSER['id'],
            'checked_when' => $dt,
        ];
        $torrent_stuffs->update($set, $id);
        $torrent['checked_by'] = $CURUSER['id'];
        $torrent['checked_when'] = $dt;
        $cache->set('checked_by_' . $id, $CURUSER['id'], 0);
        write_log("Torrent [url={$site_config['baseurl']}details.php?id=$id](" . htmlsafechars($torrent['name']) . ")[/url] was checked by {$CURUSER['username']}");
        if (!empty($_GET['returnto'])) {
            $returnto = str_replace('&amp;', '&', $_GET['returnto']);
            header("Location: {$site_config['baseurl']}" . urldecode($returnto));
            die();
        }
        $session->set('is-success', "Torrents has been 'Checked'");
    } elseif (isset($_POST['rechecked']) && $_POST['rechecked'] == $id) {
        $set = [
            'checked_by' => $CURUSER['id'],
            'checked_when' => $dt,
        ];
        $torrent_stuffs->update($set, $id);
        $torrent['checked_by'] = $CURUSER['id'];
        $torrent['checked_when'] = $dt;
        $cache->set('checked_by_' . $id, $CURUSER['id'], 0);
        write_log("Torrent [url={$site_config['baseurl']}details.php?id=$id](" . htmlsafechars($torrent['name']) . ")[/url] was re-checked by {$CURUSER['username']}");
        $session->set('is-success', "Torrents has been 'Re-Checked'");
    } elseif (isset($_POST['clearchecked']) && $_POST['clearchecked'] == $id) {
        $set = [
            'checked_by' => 0,
            'checked_when' => 0,
        ];
        $torrent_stuffs->update($set, $id);
        $torrent['checked_by'] = 0;
        $torrent['checked_when'] = 0;
        $cache->delete('checked_by_' . $id);
        write_log("Torrent [url={$site_config['baseurl']}details.php?id=$id](" . htmlsafechars($torrent['name']) . ")[/url] was un-checked by {$CURUSER['username']}");
        $session->set('is-success', "Torrents has been 'Un-Checked'");
    } elseif (isset($_POST['clear_cache']) && $_POST['clear_cache'] == $id) {
        $cache->deleteMulti([
            'torrent_details_' . $id,
            'top5_tor_',
            'last5_tor_',
            'torrent_xbt_data_' . $id,
            'torrent_descr_',
            $id,
            'tvshow_ids_' . hash('sha512', get_show_name($torrent['name'])),
            'imdb_fullset_title_' . $torrent['imdb_id'],
            'book_fullset_' . $torrent['id'],
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
            $cache->deleteMulti([
                'tvshow_episode_info_' . $ids['tvmaze_id'],
                'tvmaze_' . $ids['tvmaze_id'],
            ]);
        }
        if (!empty($ids['thetvdb_id'])) {
            $cache->deleteMulti([
                'show_images_' . $ids['thetvdb_id'],
                'movie_images_' . $ids['thetvdb_id'],
            ]);
        }

        $session->set('is-success', 'Torrent Cache Cleared');
        header("Location: {$site_config['baseurl']}/details.php?id=$id");
        die();
    }
}
if ($CURUSER['downloadpos'] !== 1) {
    $session->set('is-warning', '[h2]Download Disabled[/h2]Your download rights have been disabled.');
}
$HTMLOUT = '';
if (isset($_GET['uploaded'])) {
    $HTMLOUT .= "<meta http-equiv='refresh' content='1;url=download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "'>";
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
    preg_match('/^https?\:\/\/(.*?)imdb\.com\/title\/(tt\d{7})/i', $torrent['url'], $imdb_tmp);
    $imdb_id = !empty($imdb_tmp[2]) ? $imdb_tmp[2] : '';
    if (empty($imdb_id)) {
        $text = preg_replace('/\s+/', '', $torrent['descr']);
        preg_match('/imdb\.com\/title\/(tt\d{7})/i', $text, $match);
        if (isset($match[1])) {
            $imdb_id = $match[1];
        }
    }

    if (!empty($imdb_id)) {
        $set = [
            'imdb_id' => $imdb_id,
        ];
        $torrent_stuffs->update($set, $id);
        $torrent['imdb_id'] = $imdb_id;
    }
}
if ($BLOCKS['google_books_api_on'] && in_array($torrent['category'], $site_config['ebook_cats'])) {
    $search = $torrent['name'];
    if (!empty($torrent['isbn'])) {
        $search = $torrent['isbn'];
    }

    $ebook_data = $cache->get('book_fullset_' . $torrent['id']);
    if ($ebook_data === false || is_null($ebook_data)) {
        $ebook_data = "
            <a id='book-hash'></a>
            <div id='book' data-isbn='{$torrent['isbn']}' data-name='{$torrent['name']}' data-tid='{$torrent['id']}' data-csrf='" . $session->get('csrf_token') . "'>
                <div id='isbn_outer'>
                </div>
            </div>";
    }
}
if ($BLOCKS['tvmaze_api_on'] && in_array($torrent['category'], $site_config['tv_cats'])) {
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
            <div id='tvmaze' data-tvmazeid='{$ids['tvmaze_id']}' data-name='{$torrent['name']}' data-tid='{$torrent['id']}' data-csrf='" . $session->get('csrf_token') . "'>
                <div id='tvmaze_outer'>
                </div>
            </div>";
        }
    }
}
if ($BLOCKS['imdb_api_on'] && in_array($torrent['category'], $site_config['movie_cats']) && !empty($torrent['imdb_id'])) {
    $imdb_data = $cache->get('imdb_fullset_title_' . $torrent['imdb_id']);
    if ($imdb_data === false || is_null($imdb_data)) {
        $imdb_data = "
            <a id='imdb-hash'></a>
            <div id='imdb' data-imdbid='{$torrent['imdb_id']}' data-tid='{$torrent['id']}' data-poster='{$torrent['poster']}' data-csrf='" . $session->get('csrf_token') . "'>
                <div id='imdb_outer'>
                </div>
            </div>";
    }
    $imdb_data = "
            <div class='padding20'>{$imdb_data}</div>";
}
if (!empty($torrent['youtube'])) {
    preg_match('/(watch\?v=|watch\?.+&v=)(.{11})/i', $torrent['youtube'], $match);
    if (isset($match[2])) {
        $youtube_id = $match[2];
        $youtube = "
            <a id='youtube-hash'></a>
            <div class='responsive-container'>
                <iframe width='1920px' height='1080px' data-src='https://youtube.com/embed/{$youtube_id}?vq=hd1080' controls autoplay='false' frameborder='0' allowfullscreen class='lazy'></iframe>
            </div>";
    }
}
if (!empty($torrent['subs'])) {
    require_once CACHE_DIR . 'subs.php';
    $subtitles = explode(',', $torrent['subs']);
    $subtitle = "
        <div class='level-left'>";
    foreach ($subtitles as $sub) {
        $key = array_search($sub, array_column($subs, 'id'));
        $keys[] = $key;
        if (isset($key)) {
            $subtitle .= "
            <image class='sub_flag tooltipper left10' src='{$subs[$key]['pic']}' alt='" . htmlsafechars($subs[$key]['name']) . "' title='" . htmlsafechars($subs[$key]['name']) . "'>";
        }
    }
    $subtitle .= '
        </div>';
}
$banner_image = get_banner($torrent['imdb_id']);
$banner = !empty($banner_image) ? "<img src='" . url_proxy($banner_image, true, 1000, 185) . "' class='w-100 round10 bottom20'>" : '';
if (!empty($torrent['name'])) {
    $title = "
            <div class='bottom20 w-100'>
                $banner
                <div class='bg-00 round10 columns padding20 is-gapless level'>
                    <span class='column is-1 size_7 has-text-left padding20'>$previous</span>
                    <h1 class='column has-text-centered padding20'>" . htmlsafechars($torrent['name'], ENT_QUOTES) . "</h1>
                    <span class='column is-1 size_7 has-text-right padding20'>$next</span>
                </div>
            </div>";
}

$torrent['free_color'] = '#0f0';
$torrent['silver_color'] = 'silver';
require_once ROOT_DIR . 'free_details.php';
$info_block = '';
if ($CURUSER['class'] >= (UC_MIN + 1) && $torrent['nfosz'] > 0) {
    $info_block .= tr($lang['details_nfo'], "<div class='left10'><a href='{$site_config['baseurl']}/viewnfo.php?id={$torrent['id']}'>{$lang['details_view_nfo']}</a> (" . mksize($torrent['nfosz']) . ')</div>', 1);
}
if (!empty($torrent['subs'])) {
    $info_block .= tr($lang['details_subs'], $subtitle, 1);
}
if ($torrent['visible'] === 'no') {
    $info_block .= tr($lang['details_visible'], '<div class="left10">' . $lang['details_no'] . $lang['details_dead'] . '</div>', 1);
}
if ($moderator) {
    $info_block .= tr($lang['details_banned'], "<div class='left10'>{$torrent['banned']}</div>", 1);
}
if ($torrent['nuked'] === 'yes') {
    $reason = !empty($torrent['nukereason']) ? $torrent['nukereason'] : '';
    $info_block .= tr('Nuked', "<div class='level-left left10'><img src='{$site_config['pic_baseurl']}nuked.gif' alt='Nuked' class='tooltipper icon right5' title='Nuked'>$reason</div>", 1);
}
$torrent['cat_name'] = htmlsafechars($change[$torrent['category']]['name']);
if (isset($torrent['cat_name'])) {
    $info_block .= tr($lang['details_type'], '<div class="left10">' . htmlsafechars($torrent['cat_name']) . '</div>', 1);
} else {
    $info_block .= tr($lang['details_type'], '<div class="left10">None</div>', 1);
}
$lastseed = $torrent_stuffs->get_item('last_action', $id);
$info_block .= tr('Rating', '<div class="left10">' . getRate($id, 'torrent') . '</div>', 1);
$info_block .= tr($lang['details_last_seeder'], '<div class="left10">' . $lang['details_last_activity'] . get_date($lastseed, '', 0, 1) . '</div>', 1);
if (!isset($_GET['filelist'])) {
    $info_block .= tr($lang['details_num_files'], "<div class='level-left is-flex left10'>{$torrent['numfiles']} file" . plural($torrent['numfiles']) . "<a href='{$site_config['baseurl']}/filelist.php?id=$id' class='button is-small left10'>{$lang['details_list']}</a></div>", 1);
}
$info_block .= tr($lang['details_size'], '<div class="left10">' . mksize($torrent['size']) . ' (' . number_format($torrent['size']) . " {$lang['details_bytes']})</div>", 1);
$info_block .= tr($lang['details_added'], '<div class="left10">' . get_date($torrent['added'], 'LONG') . '</div>', 1);
$info_block .= tr($lang['details_views'], "<div class='left10'>{$torrent['views']}</div>", 1);
$info_block .= tr($lang['details_hits'], "<div class='left10'>{$torrent['hits']}</div>", 1);
$info_block .= tr($lang['details_snatched'], '<div class="left10">' . ($torrent['times_completed'] > 0 ? "<a href='{$site_config['baseurl']}snatches.php?id={$id}'>{$torrent['times_completed']} {$lang['details_times']}" . plural($torrent['times_completed']) . '</a>' : "0 {$lang['details_times']}") . '</div>', 1);
$info_block .= tr($lang['details_peers'], '<div class="left10">' . $torrent['seeders'] . ' seeder' . plural($torrent['seeders']) . ' + ' . $torrent['leechers'] . ' leecher' . plural($torrent['leechers']) . ' = ' . ($torrent['seeders'] + $torrent['leechers']) . "{$lang['details_peer_total']}<br>
    <a href='{$site_config['baseurl']}/peerlist.php?id=$id#seeders' class='top10 button is-small'>{$lang['details_list']}</a></div>", 1);

if (!empty($torrent['descr'])) {
    $descr = $cache->get('torrent_descr_' . $id);
    if ($descr === false || is_null($descr)) {
        $descr = "
            <div id='descr' data-tid='{$torrent['id']}' data-csrf='" . $session->get('csrf_token') . "'>
                <div id='descr_outer'>
                </div>
            </div>";
    }
}

$torrent['addup'] = !empty($torrent['addedup']) ? get_date($torrent['addedup'], 'DATE') : '';
$torrent['addfree'] = !empty($torrent['addedfree']) ? get_date($torrent['addedfree'], 'DATE') : '';
$torrent['idk'] = $dt + 14 * 86400;
$torrent['freeimg'] = '<img src="' . $site_config['pic_baseurl'] . 'freedownload.gif" alt="">';
$torrent['doubleimg'] = '<img src="' . $site_config['pic_baseurl'] . 'doubleseed.gif" alt="">';
$slot = make_freeslots($CURUSER['id'], 'fllslot_');
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
        <a href='{$site_config['baseurl']}/browse.php?search=$tag&amp;searchin=all&amp;incldead=1'>" . htmlsafechars($tag) . '</a>';
    }
}
$points = tr($lang['details_tags'], "<div class='left10'>$keywords</div>", 1);
$torrent['torrent_points_'] = $coin_stuffs->get($id);
$my_points = isset($torrent['torrent_points_'][$CURUSER['id']]) ? $torrent['torrent_points_'][$CURUSER['id']] : 0;
$points .= tr('Karma Points', '
                    <div class="left10">
                        <p>In total ' . (int) $torrent['points'] . ' Karma Points given to this torrent of which ' . $my_points . ' from you</p>
                        <p>
                            <a href="' . $site_config['baseurl'] . '/coins.php?id=' . $id . '&amp;points=10">
                                <img src="' . $site_config['pic_baseurl'] . '10coin.png" alt="10" class="tooltipper" title="10 Points">
                            </a>
                            <a href="' . $site_config['baseurl'] . '/coins.php?id=' . $id . '&amp;points=20">
                                <img src="' . $site_config['pic_baseurl'] . '20coin.png" alt="20" class="tooltipper" title="20 Points">
                            </a>
                            <a href="' . $site_config['baseurl'] . '/coins.php?id=' . $id . '&amp;points=50">
                                <img src="' . $site_config['pic_baseurl'] . '50coin.png" alt="50" class="tooltipper" title="50 Points">
                            </a>
                            <a href="' . $site_config['baseurl'] . '/coins.php?id=' . $id . '&amp;points=100">
                                <img src="' . $site_config['pic_baseurl'] . '100coin.png" alt="100" class="tooltipper" title="100 Points">
                            </a>
                            <a href="' . $site_config['baseurl'] . '/coins.php?id=' . $id . '&amp;points=200">
                                <img src="' . $site_config['pic_baseurl'] . '200coin.png" alt="200" class="tooltipper" title="200 Points">
                            </a>
                            <a href="' . $site_config['baseurl'] . '/coins.php?id=' . $id . '&amp;points=500">
                                <img src="' . $site_config['pic_baseurl'] . '500coin.png" alt="500" class="tooltipper" title="500 Points">
                            </a>
                            <a href="' . $site_config['baseurl'] . '/coins.php?id=' . $id . '&amp;points=1000">
                                <img src="' . $site_config['pic_baseurl'] . '1000coin.png" alt="1000" class="tooltipper" title="1000 Points">
                            </a>
                        </p>
                        <p>By clicking on the coins you can give Karma Points to the uploader of this torrent.</p>
                    </div>', 1);
$downl = $CURUSER['downloaded'] + $torrent['size'];
$sr = $CURUSER['uploaded'] / $downl;
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
    <img src='{$site_config['pic_baseurl']}smilies/{$s}.gif' alt='' class='emoticon right10'>
    <span style='color: " . get_ratio_color($sr) . ";'>" . number_format($sr, 3) . '</span>';
if ($torrent['free'] >= 1 || $torrent['freetorrent'] >= 1 || $isfree['yep'] || $free_slot || $double_slot != 0 || $CURUSER['free_switch'] != 0) {
    $points .= tr('Ratio After Download', "<div class='left10'><div class='level-left'><del>{$sr} Your new ratio if you download this torrent.</del></div><div class='top10'><span class='has-text-success'>[FREE] </span>(Only upload stats are recorded)</div></div>", 1);
} else {
    $points .= tr('Ratio After Download', "<div class='level-left left10'>{$sr} Your new ratio if you download this torrent.</div>", 1);
}
$info_hash = $torrent['info_hash'];
$points .= tr($lang['details_info_hash'], "<div title='$info_hash' class='tooltipper left10'>" . substr($info_hash, 0, 40) . '<br>' . substr($info_hash, 40, 80) . '</div>', 1);

$url = $site_config['baseurl'] . '/edit.php?id=' . $torrent['id'];
if (isset($_GET['returnto'])) {
    $url .= '&amp;returnto=' . urlencode($_GET['returnto']);
}
$editlink = "<a href='$url' class='button is-small bottom10'>";
$rowuser = !empty($owner) ? format_username($owner) : $lang['details_unknown'];
$uprow = $torrent['anonymous'] === 'yes' ? (!$moderator && !$owner ? '' : $rowuser . ' - ') . '<i>' . get_anonymous_name() . '</i>' : $rowuser;
$audit = tr('Upped by', "<div class='level-left left10'>$uprow</div>", 1);
$torrent_cache['rep'] = $user_stuffs->get_item('reputation', $owner);
if ($torrent_cache['rep']) {
    $member_reputation = get_reputation($user_stuffs->getUserFromId($owner), 'torrents', $torrent['anonymous'], $id);
    $audit .= tr('Reputation', "
        <div class='level-left left10'>
            $member_reputation counts towards uploaders Reputation
        <div>", 1);
}
$audit .= tr('Report Torrent', "
    <form action='{$site_config['baseurl']}/report.php?type=Torrent&amp;id=$id' method='post'>
        <div class='level-left'>
            <input class='button is-small left10' type='submit' name='submit' value='Report This Torrent'>
            <div class='left10'>
                For breaking the
                <a href='{$site_config['baseurl']}/rules.php'>
                    <span class='has-text-success'>&nbsp;rules</span>
                </a>
            </div>
        </div>
    </form>", 1);

if ($owned) {
    $audit .= tr('Edit Torrent', "<a href='$url' class='button is-small left10'>{$lang['details_edit']}</a>", 1);
}
if ($moderator) {
    $audit .= tr('Clear Cache', "
                    <form method='post' action='{$site_config['baseurl']}/details.php?id={$torrent['id']}'>
                        <input type='hidden' name='clear_cache' value={$torrent['id']}>
                        <input type='submit' class='button is-small left10' value='Clear Cache'>
                    </form>", 1);

    $returnto = '';
    if (!empty($_GET['returnto'])) {
        $returnto = '&amp;returnto=' . urlencode(htmlsafechars($_GET['returnto']));
    }
    if (!empty($torrent['checked_by'])) {
        $checked_by = $cache->get('checked_by_' . $id);
        if ($checked_by === false || is_null($checked_by)) {
            $checked_by = $torrent['checked_by'];
            $cache->set('checked_by_' . $id, $checked_by, 0);
        }
        $audit .= tr('Checked by', "
                    <div class='bottom10 left10'>" . format_username($torrent['checked_by']) . (isset($torrent['checked_when']) && $torrent['checked_when'] > 0 ? ' checked: ' . get_date($torrent['checked_when'], 'DATE', 0, 1) : '') . "</div>
                    <div class='bottom10 left10'>
                        <form method='post' action='{$site_config['baseurl']}/details.php?id={$torrent['id']}{$returnto}'>
                            <input type='hidden' name='rechecked' value={$torrent['id']}>
                            <input type='submit' class='button is-small bottom10' value='Re-Check this torrent'>
                        </form>
                        <form method='post' action='{$site_config['baseurl']}/details.php?id={$torrent['id']}'>
                            <input type='hidden' name='clearchecked' value={$torrent['id']}>
                            <input type='submit' class='button is-small' value='Un-Check this torrent'>
                        </form>
                    </div>", 1);
    } else {
        $audit .= tr('Checked by', "
                    <form method='post' action='{$site_config['baseurl']}/details.php?id={$torrent['id']}{$returnto}'>
                        <input type='hidden' name='checked' value={$torrent['id']}>
                        <input type='submit' class='button is-small left10' value='Check this torrent'>
                    </form>", 1);
    }
}

$audit .= tr($lang['details_thanks'], "
        <noscript>
            <iframe id='thanked' src ='{$site_config['baseurl']}/ajax/thanks.php?torrentid={$id}'>
                <p>Your browser does not support iframes. And it has Javascript disabled!</p>
            </iframe>
        </noscript>
        <div id='thanks_holder' data-tid='{$torrent['id']}' data-csrf='" . $session->get('csrf_token') . "' class='left10'></div>", 1);
$next_reseed = 0;
if ($torrent['last_reseed'] > 0) {
    $next_reseed = $torrent['last_reseed'] + 172800;
}
$audit .= tr('Request Reseed', "
        <form method='post' action='{$site_config['baseurl']}/takereseed.php'>
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
                    <input type='hidden' name='csrf' value='" . $session->get('csrf_token') . "'>
                    <input type='submit' class='button is-small'" . (($next_reseed > $dt) ? ' disabled' : '') . " value='SendPM'>
                </span>
            </form>
        </div>", 1);
if ($torrent['allow_comments'] === 'yes' || $moderator) {
    $comments = '';
    $add_comment = "
    <a name='startcomments'></a>
    <div class='has-text-centered'>
        <h2>Leave a Comment</h2>
        <a href='{$site_config['baseurl']}/takethankyou.php?id={$torrent['id']}'>
            <img src='{$site_config['pic_baseurl']}smilies/thankyou.gif' class='tooltipper' alt='Thank You' title='Give a quick \"Thank You\"'>
        </a>
    <form name='comment' method='post' action='{$site_config['baseurl']}/comment.php'>
        <input type='hidden' name='csrf' value='" . $session->get('csrf_token') . "'>
        <input type='hidden' name='action' value='add'>
        <input type='hidden' name='tid' value='{$torrent['id']}'>
        </div>" . BBcode(null, null, 200) . "
        <div class='has-text-centered'>
            <input class='button is-small margin20' type='submit' value='Submit'>
        </div>
    </form>";

    $count = $torrent['comments'];
    if (!$count) {
        $comments .= "
            <h2 class='has-text-centered top10'>{$lang['details_no_comment']}</h2>";
    } else {
        $perpage = 15;
        $torrent_comments = $comment_stuffs->get_torrent_comment($torrent['id'], $count, $perpage);
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
        <a id='startcomments'></a>
        <div class='has-text-centered'>{$lang['details_com_disabled']}</div>";
}
if ($CURUSER['downloadpos'] === 1 || $owner) {
    $slots = "
        <div class='tooltip_templates'>
            <div id='balloon1' class='text-justify'>
                Once chosen this torrent will be Freeleech {$torrent['freeimg']} until " . get_date($torrent['idk'], 'DATE') . " and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.
            </div>
        </div>
        <div class='tooltip_templates'>
            <div id='balloon2' class='text-justify'>
                Once chosen this torrent will be Doubleseed {$torrent['doubleimg']} until " . get_date($torrent['idk'], 'DATE') . " and can be resumed or started over using the regular download link. Doing so will result in one Freeleech Slot being taken away from your total.
            </div>
        </div>
        <div class='tooltip_templates'>
            <div id='balloon3' class='text-justify'>
                Remember to show your gratitude and Thank the Uploader. <img src='{$site_config['pic_baseurl']}smilies/smile1.gif' alt=''>
            </div>
        </div>";

    if ($free_slot && !$double_slot) {
        $slots .= '<div class="has-text-centered bottom10">' . $torrent['freeimg'] . ' <span class="has-text-success">Freeleech Slot In Use!</span> (only upload stats are recorded) - Expires: 12:01AM ' . $torrent['addfree'] . '</div>';
        $freeslot = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=double' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining.' : '';
        $freeslot_zip = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=double&amp;zip=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining.' : '';
        $freeslot_text = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=double&amp;text=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a>- " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining.' : '';
    } elseif (!$free_slot && $double_slot) {
        $slots .= '<div class="has-text-centered bottom10">' . $torrent['doubleimg'] . ' <span class="has-text-success">Doubleseed Slot In Use!</span> (upload stats x2) - Expires: 12:01AM ' . $torrent['addup'] . '</div>';
        $freeslot = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=free' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> - " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining.' : '';
        $freeslot_zip = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=free&amp;zip=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> - " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining.' : '';
        $freeslot_text = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=free&amp;text=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> - " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining.' : '';
    } elseif ($free_slot && $double_slot) {
        $slots .= '<div class="has-text-centered bottom10">' . $torrent['freeimg'] . ' ' . $torrent['doubleimg'] . ' <span class="has-text-success">Freeleech and Doubleseed Slots In Use!</span> (upload stats x2 and no download stats are recorded)<p>Freeleech Expires: 12:01AM ' . $torrent['addfree'] . ' and Doubleseed Expires: 12:01AM ' . $torrent['addup'] . '</p></div>';
        $freeslot = $freeslot_zip = $freeslot_text = '';
    } else {
        $freeslot = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=free' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=double' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining. ' : '';
        $freeslot_zip = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=free&amp;zip=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=double&amp;zip=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining.' : '';
        $freeslot_text = $CURUSER['freeslots'] >= 1 ? "Use: <a class='index dt-tooltipper-small' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=free&amp;text=1' data-tooltip-content='#balloon1' rel='balloon1' onclick=\"return confirm('Are you sure you want to use a freeleech slot?')\"><span class='has-text-success'>Freeleech Slot</span></a> Use: <a class='index dt-tooltipper-small' href='download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "&amp;slot=double&amp;text=1' data-tooltip-content='#balloon2' rel='balloon2' onclick=\"return confirm('Are you sure you want to use a doubleseed slot?')\"><span class='has-text-success'>Doubleseed Slot</span></a> - " . htmlsafechars($CURUSER['freeslots']) . ' Slots Remaining.' : '';
    }
    $Free_Slot = $freeslot;
    $Free_Slot_Zip = $freeslot_zip;
    $Free_Slot_Text = $freeslot_text;
    $slots .= main_table("
                    <tr>
                        <td class='rowhead' width='3%'>{$lang['details_download']}</td>
                        <td>
                            <a class='index' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "'>" . htmlsafechars($torrent['filename']) . "</a><br>{$Free_Slot}
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['details_zip']}</td>
                        <td>
                            <a class='index' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "'&amp;zip=1'>" . htmlsafechars($torrent['filename']) . ".zip</a><br>{$Free_Slot_Zip}
                        </td>
                    </tr>
                    <tr>
                        <td>{$lang['details_text']}</td>
                        <td>
                            <a class='index' href='{$site_config['baseurl']}/download.php?torrent={$id}" . (get_scheme() === 'https' ? '&amp;ssl=1' : '') . "'&amp;text=1'>" . htmlsafechars($torrent['filename']) . ".txt</a><br>{$Free_Slot_Text}
                        </td>
                    </tr>", null, null, 'bottom20');
}
$i = 0;
foreach ($sections as $section => $container) {
    if (empty(${$section})) {
        unset($sections[$section]);
    }
    ++$i;
}
$i = 0;
foreach ($sections as $section => $container) {
    ++$i;
    $class = $i >= count($sections) ? '' : 'bottom20';
    if ($container === 'main_table') {
        $HTMLOUT .= main_table(${$section}, null, null, $class);
    } elseif ($container === 'main_div') {
        $HTMLOUT .= main_div(${$section}, $class);
    } else {
        $HTMLOUT .= ${$section};
    }
}

echo stdhead(htmlsafechars($torrent['name'], ENT_QUOTES), $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
