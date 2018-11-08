<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
global $session;

$lang = array_merge(load_language('global'), load_language('userdetails'));
extract($_POST);

header('content-type: application/json');
if (!$session->validateToken($csrf)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}

if ($type === 'torrents') {
    $torrents = get_uploaded($uid);
    if (empty($torrents)) {
        echo json_encode(['content' => main_div('You have not uploaded any torrents')]);
        die();
    }
    $data = maketable($torrents);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div('You have not uploaded any torrents')]);
        die();
    }
} elseif ($type === 'seeding') {
    $torrents = get_seeding($uid);
    if (empty($torrents)) {
        echo json_encode(['content' => main_div('You are not seeding any torrents')]);
        die();
    }
    $data = maketable($torrents);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div('You are not seeding any torrents')]);
        die();
    }
} elseif ($type === 'leeching') {
    $torrents = get_leeching($uid);
    if (empty($torrents)) {
        echo json_encode(['content' => main_div('You have not leeching any torrents')]);
        die();
    }
    $data = maketable($torrents);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div('You are not leeching any torrents')]);
        die();
    }
} elseif ($type === 'snatched') {
    $torrents = get_snatched($uid);
    if (empty($torrents)) {
        echo json_encode(['content' => main_div('You have not downloaded any torrents')]);
        die();
    }
    $data = snatchtable($torrents);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div('You are downloaded any torrents')]);
        die();
    }
}

echo json_encode(['fail' => 'invalid']);
die();


function get_uploaded($userid)
{
    global $fluent, $site_config;

    $count = $fluent->from('torrents')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('owner = ?', $userid)
        ->fetch('count');

    if ($count === 0) {
        return false;
    }

    $query = $fluent->from('torrents')
        ->select(null)
        ->select('torrents.id AS torrentid')
        ->select('torrents.name')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.size')
        ->select('categories.name AS catname')
        ->select('categories.image')
        ->innerJoin('categories ON torrents.category = categories.id')
        ->where('torrents.owner = ?', $userid)
        ->orderBy('torrents.name');
    foreach ($query as $results) {
        $sums = $fluent->from('snatched')
            ->select(null)
            ->select('SUM(uploaded) AS uploaded')
            ->select('SUM(downloaded) AS downloaded')
            ->where('userid = ?', $userid)
            ->where('torrentid = ?', $results['torrentid'])
            ->fetch();

        $results['uploaded'] = $sums['uploaded'];
        $results['downloaded'] = $sums['downloaded'];

        $torrents[] = $results;
    }

    return $torrents;
}

function get_seeding($userid)
{
    global $fluent, $site_config;

    $count = $fluent->from('peers')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('userid = ?', $userid)
        ->where('peers.seeder = "yes"')
        ->fetch('count');

    if ($count === 0) {
        return false;
    }

    $torrents = $fluent->from('peers')
        ->select(null)
        ->select('peers.torrent AS torrentid')
        ->select('peers.uploaded')
        ->select('peers.downloaded')
        ->select('peers.seeder')
        ->select('peers.last_action')
        ->select('torrents.added')
        ->select('torrents.name')
        ->select('torrents.size')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.owner')
        ->select('categories.name AS catname')
        ->select('categories.image')
        ->innerJoin('torrents ON peers.torrent = torrents.id')
        ->innerJoin('categories ON torrents.category = categories.id')
        ->where('peers.userid = ?', $userid)
        ->where('peers.seeder = "yes"')
        ->orderBy('last_action DESC')
        ->fetchAll();

    return $torrents;
}

function get_leeching($userid)
{
    global $fluent, $site_config;

    $count = $fluent->from('peers')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('userid = ?', $userid)
        ->where('peers.seeder = "no"')
        ->fetch('count');

    if ($count === 0) {
        return false;
    }

    $torrents = $fluent->from('peers')
        ->select(null)
        ->select('peers.torrent AS torrentid')
        ->select('peers.uploaded')
        ->select('peers.downloaded')
        ->select('peers.seeder')
        ->select('peers.last_action')
        ->select('torrents.added')
        ->select('torrents.name')
        ->select('torrents.size')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.owner')
        ->select('categories.name AS catname')
        ->select('categories.image')
        ->innerJoin('torrents ON peers.torrent = torrents.id')
        ->innerJoin('categories ON torrents.category = categories.id')
        ->where('peers.userid = ?', $userid)
        ->where('peers.seeder = "no"')
        ->orderBy('last_action DESC')
        ->fetchAll();

    return $torrents;
}

function get_snatched($userid)
{
    global $fluent, $site_config;

    $count = $fluent->from('snatched')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('userid = ?', $userid)
        ->fetch('count');

    if ($count === 0) {
        return false;
    }

    $torrents = $fluent->from('snatched')
        ->select(null)
        ->select('snatched.*')
        ->select('torrents.name')
        ->select('torrents.category AS catid')
        ->select('categories.name AS catname')
        ->select('categories.image')
        ->innerJoin('torrents ON snatched.torrentid = torrents.id')
        ->innerJoin('categories ON torrents.category = categories.id')
        ->where('snatched.userid = ?', $userid)
        ->orderBy('last_action DESC')
        ->fetchAll();

    return $torrents;
}

function maketable(array $torrents)
{
    global $site_config, $lang;

    $heading = "
        <tr>
            <th>{$lang['userdetails_type']}</th>
            <th>{$lang['userdetails_name']}</th>
            <th>{$lang['userdetails_size']}</th>
            <th>{$lang['userdetails_se']}</th>
            <th>{$lang['userdetails_le']}</th>
            <th>{$lang['userdetails_upl']}</th>" . (RATIO_FREE ? '' : "
            <th>{$lang['userdetails_downl']}</th>") . "
            <th>{$lang['userdetails_ratio']}</th>
        </tr>";
    $body = '';
    foreach ($torrents as $torrent) {
        if ($torrent['downloaded'] > 0) {
            $ratio = number_format($torrent['uploaded'] / $torrent['downloaded'], 3);
            $ratio = "<span style='color: " . get_ratio_color($ratio) . ";'>$ratio</span>";
        } elseif ($torrent['uploaded'] > 0) {
            $ratio = "{$lang['userdetails_inf']}";
        } else {
            $ratio = '---';
        }
        $catimage = "{$site_config['pic_baseurl']}caticons/" . get_category_icons() . "/{$torrent['image']}";
        $catname = htmlsafechars($torrent['catname']);
        $catimage = "<img src='$catimage' title='$catname' alt='$catname' width='42' height='42' class='tooltipper' />";
        $size = str_replace(' ', '<br>', mksize($torrent['size']));
        $uploaded = str_replace(' ', '<br>', mksize($torrent['uploaded']));
        $downloaded = str_replace(' ', '<br>', mksize($torrent['downloaded']));
        $seeders = number_format($torrent['seeders']);
        $leechers = number_format($torrent['leechers']);
        $body .= "
        <tr>
            <td class='has-text-centered'>$catimage</td>
            <td>
                <a href='{$site_config['baseurl']}/details.php?id={$torrent['torrentid']}&amp;hit=1'><b>" . htmlsafechars($torrent['name']) . "</b></a>
            </td>
            <td class='has-text-centered'>$size</td>
            <td class='has-text-centered'>$seeders</td>
            <td class='has-text-centered'>$leechers</td>
            <td class='has-text-centered'>$uploaded</td>" . (RATIO_FREE ? '' : "
            <td class='has-text-centered'>$downloaded</td>") . "
            <td class='has-text-centered'>$ratio</td>
        </tr>";
    }

    $table = main_table($body, $heading);

    return $table;
}

function snatchtable($torrents)
{
    global $site_config, $lang;

    $heading = "
        <tr>
            <th>{$lang['userdetails_s_cat']}</th>
            <th>{$lang['userdetails_s_torr']}</th>
            <th>{$lang['userdetails_s_up']}</th>
            <th>{$lang['userdetails_rate']}</th>" . (RATIO_FREE ? '' : "
            <th>{$lang['userdetails_downl']}</th>") . (RATIO_FREE ? '' : "
            <th>{$lang['userdetails_rate']}</th>") . "
            <th>{$lang['userdetails_ratio']}</th>
            <th>{$lang['userdetails_activity']}</th>
            <th>{$lang['userdetails_s_fin']}</th>
        </tr>";
    $body = '';
    foreach ($torrents as $torrent) {
        $upspeed = ($torrent['upspeed'] > 0 ? mksize($torrent['upspeed']) : ($torrent['seedtime'] > 0 ? mksize($torrent['uploaded'] / ($torrent['seedtime'] + $torrent['leechtime'])) : mksize(0)));
        $downspeed = ($torrent['downspeed'] > 0 ? mksize($torrent['downspeed']) : ($torrent['leechtime'] > 0 ? mksize($torrent['downloaded'] / $torrent['leechtime']) : mksize(0)));
        $ratio = ($torrent['downloaded'] > 0 ? number_format($torrent['uploaded'] / $torrent['downloaded'], 3) : ($torrent['uploaded'] > 0 ? 'Inf.' : '---'));
        $XBT_or_PHP = (XBT_TRACKER ? $torrent['fid'] : $torrent['torrentid']);
        $XBT_or_PHP_TIME = (XBT_TRACKER ? $torrent['completedtime'] : $torrent['complete_date']);
        $body .= "
        <tr>
            <td>
                <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($torrent['image']) . "' alt='" . htmlsafechars($torrent['catname']) . "' width='42' height='42' />
            </td>
            <td>
                <a href='{$site_config['baseurl']}/details.php?id=" . (int) $XBT_or_PHP . "'><b>" . (strlen($torrent['name']) > 50 ? substr($torrent['name'], 0, 50 - 3) . '...' : htmlsafechars($torrent['name'])) . '</b></a>
            </td>
            <td>' . mksize($torrent['uploaded']) . "</td>
            <td>$upspeed/s</td>" . (RATIO_FREE ? '' : '
            <td>' . mksize($torrent['downloaded']) . '</td>') . (RATIO_FREE ? '' : "
            <td>$downspeed/s</td>") . "
            <td>$ratio</td>
            <td>" . mkprettytime($torrent['seedtime'] + $torrent['leechtime']) . '</td>
            <td>' . ($XBT_or_PHP_TIME != 0 ? "
                <span class='has-text-lime'><b>{$lang['userdetails_yes']}</b></span>" : "
                <span class='has-text-red'><b>{$lang['userdetails_no']}</b></span>") . '
            </td>
        </tr>';
    }
    $table = main_table($body, $heading);

    return $table;
}

