<?php

require_once dirname(__FILE__, 3) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
global $session, $site_config;

$lang = array_merge(load_language('global'), load_language('userdetails'));
extract($_POST);

header('content-type: application/json');
if (!$session->validateToken($csrf)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}

$current_user = $session->get('userID');
if (empty($current_user)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}
$isStaff = in_array($current_user, $site_config['is_staff']['allowed']);
$hasAccess = $current_user === $uid || $isStaff ? true : false;

if ($type === 'torrents' && $hasAccess) {
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
} elseif ($type === 'seeding' && $hasAccess) {
    $torrents = get_seeding($uid);
    if (empty($torrents)) {
        echo json_encode(['content' => main_div('You are not seeding any torrents')]);
        die();
    }
    $data = maketable($torrents && $hasAccess);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div('You are not seeding any torrents')]);
        die();
    }
} elseif ($type === 'leeching' && $hasAccess) {
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
} elseif ($type === 'snatched' && $hasAccess) {
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
} elseif ($type === 'snatched_staff' && $isStaff) {
    $torrents = get_snatched_staff($uid);
    if (empty($torrents)) {
        echo json_encode(['content' => main_div('You have not downloaded any torrents')]);
        die();
    }
    $data = staff_snatchtable($torrents, $uid);
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

function get_uploaded(int $userid)
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

function get_seeding(int $userid)
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

function get_leeching(int $userid)
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

function get_snatched(int $userid)
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

function get_snatched_staff(int $userid)
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
        ->select('snatched.*')
        ->select('torrents.name AS torrent_name')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.size')
        ->select('torrents.owner')
        ->select('categories.name AS catname')
        ->select('categories.image')
        ->select('peers.agent')
        ->select('peers.connectable')
        ->select('peers.port')
        ->select('INET6_NTOA(peers.ip) AS ip')
        ->innerJoin('torrents ON snatched.torrentid = torrents.id')
        ->innerJoin('categories ON torrents.category = categories.id')
        ->leftJoin('peers ON torrents.id = peers.torrent')
        ->where('snatched.userid = ?', $userid)
        ->orderBy('last_action DESC')
        ->fetchAll();

    file_put_contents('/var/log/nginx/data.log', json_encode($torrents) . PHP_EOL, FILE_APPEND);

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
        $catimage = "<img src='$catimage' title='$catname' alt='$catname' width='42' height='42' class='tooltipper'>";
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

function snatchtable(array $torrents)
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
                <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($torrent['image']) . "' alt='" . htmlsafechars($torrent['catname']) . "' width='42' height='42'>
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
                <span class='has-text-success'><b>{$lang['userdetails_yes']}</b></span>" : "
                <span class='has-text-danger'><b>{$lang['userdetails_no']}</b></span>") . '
            </td>
        </tr>';
    }
    $table = main_table($body, $heading);

    return $table;
}

function staff_snatchtable(array $torrents, int $userid)
{
    global $site_config, $lang;

    $heading = "
                    <tr>
                        <th>{$lang['userdetails_s_cat']}</th>
                        <th>{$lang['userdetails_s_torr']}</th>
                        <th>{$lang['userdetails_s_sl']}</th>
                        <th>{$lang['userdetails_s_up']}" . (RATIO_FREE ? '' : "{$lang['userdetails_s_down']}") . "</th>
                        <th>{$lang['userdetails_s_tsize']}</th>
                        <th>{$lang['userdetails_ratio']}</th>
                        <th>{$lang['userdetails_client']}</th>
                    </tr>";
    $body = '';
    foreach ($torrents as $arr) {
        if ($arr['upspeed'] > 0) {
            $ul_speed = ($arr['upspeed'] > 0 ? mksize($arr['upspeed']) : ($arr['seedtime'] > 0 ? mksize($arr['uploaded'] / ($arr['seedtime'] + $arr['leechtime'])) : mksize(0)));
        } else {
            $ul_speed = mksize(($arr['uploaded'] / ($arr['last_action'] - $arr['start_date'] + 1)));
        }
        if ($arr['downspeed'] > 0) {
            $dl_speed = ($arr['downspeed'] > 0 ? mksize($arr['downspeed']) : ($arr['leechtime'] > 0 ? mksize($arr['downloaded'] / $arr['leechtime']) : mksize(0)));
        } else {
            $dl_speed = mksize(($arr['downloaded'] / ($arr['complete_date'] - $arr['start_date'] + 1)));
        }
        switch (true) {
            case $dl_speed > 600:
                $dlc = 'red';
                break;

            case $dl_speed > 300:
                $dlc = 'orange';
                break;

            case $dl_speed > 200:
                $dlc = 'yellow';
                break;

            case $dl_speed < 100:
                $dlc = 'Chartreuse';
                break;
        }
        if ($arr['downloaded'] > 0) {
            $ratio = number_format($arr['uploaded'] / $arr['downloaded'], 3);
            $ratio = "<span style='color: " . get_ratio_color($ratio) . ";'><b>{$lang['userdetails_s_ratio']}</b><br>$ratio</span>";
        } elseif ($arr['uploaded'] > 0) {
            $ratio = $lang['userdetails_inf'];
        } else {
            $ratio = 'N/A';
        }
        if (!XBT_TRACKER) {
            $body .= '
            <tr>
                <td>' . ($arr['owner'] === $userid ? "
                    <b><span class='has-text-orange'>{$lang['userdetails_s_towner']}</span></b><br>" : '' . ($arr['complete_date'] != '0' ? "
                    <b><span class='has-text-lightgreen'>{$lang['userdetails_s_fin']}</span></b><br>" : "
                    <b><span class='has-text-danger'>{$lang['userdetails_s_nofin']}</span></b><br>") . '') . "
                    <img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($arr['image']) . "' alt='" . htmlsafechars($arr['catname']) . "' title='" . htmlsafechars($arr['catname']) . "' class='tooltipper'>
                </td>
                <td>
                    <a class='altlink' href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['torrentid'] . "'><b>" . htmlsafechars($arr['torrent_name']) . '</b></a>' . ($arr['complete_date'] != '0' ? "<br>
                    <span class='has-text-yellow'>{$lang['userdetails_s_started']}" . get_date($arr['start_date'], 0, 1) . "</span><br>
                    <span class='has-text-orange'>{$lang['userdetails_s_laction']} " . get_date($arr['last_action'], 0, 1) . '</span>' . ($arr['complete_date'] == '0' ? ($arr['owner'] == $id ? '' : '[ ' . mksize($arr['size'] - $arr['downloaded']) . "{$lang['userdetails_s_still']}]") : '') : '') . '<br>' . $lang['userdetails_s_finished'] . get_date($arr['complete_date'], 0, 1) . '' . ($arr['complete_date'] != '0' ? "<br>
                    <span style='color: silver;'>{$lang['userdetails_s_ttod']}" . ($arr['leechtime'] != '0' ? mkprettytime($arr['leechtime']) : mkprettytime($arr['complete_date'] - $arr['start_date']) . '') . "</span>
                    <span style='color: $dlc'>[ {$lang['userdetails_s_dled']} $dl_speed ]</span><br>" : '<br>') . "
                    <span class='has-text-lightblue'>" . ($arr['seedtime'] != '0' ? $lang['userdetails_s_tseed'] . mkprettytime($arr['seedtime']) . " </span>
                    <span style='color: $dlc;'> " : $lang['userdetails_s_tseedn']) . "</span>
                    <span class='has-text-lightgreen'> [ {$lang['userdetails_s_uspeed']} " . $ul_speed . ' ] </span>' . ($arr['complete_date'] == '0' ? "<br>
                    <span style='color: $dlc;'>{$lang['userdetails_s_dspeed']}$dl_speed</span>" : '') . "
                </td>
                <td>{$lang['userdetails_s_seed']}" . (int) $arr['seeders'] . "<br>{$lang['userdetails_s_leech']}" . (int) $arr['leechers'] . "</td>
                <td>
                    <span class='has-text-lightgreen'>{$lang['userdetails_s_upld']}<br><b>" . mksize($arr['uploaded']) . '</b></span>' . (RATIO_FREE ? '' : "<br>
                    <span class='has-text-orange'>{$lang['userdetails_s_dld']}<br><b>" . mksize($arr['downloaded']) . '</b></span>') . '
                </td>
                <td>' . mksize($arr['size']) . '' . (RATIO_FREE ? '' : "<br>{$lang['userdetails_s_diff']}<br>
                    <span class='has-text-orange'><b>" . mksize($arr['size'] - $arr['downloaded']) . '</b></span>') . '
                </td>
                <td>' . $ratio . '<br>' . ($arr['seeder'] === 'yes' ? "
                    <span class='has-text-lightgreen'><b>{$lang['userdetails_s_seeding']}</b></span>" : "
                    <span class='has-text-danger'><b>{$lang['userdetails_s_nseeding']}</b></span>") . '
                </td>
                <td>' . htmlsafechars($arr['agent']) . '<br>IP: ' . $arr['ip'] . "<br>{$lang['userdetails_s_port']}" . $arr['port'] . '<br>' . ($arr['connectable'] === 'yes' ? "<b>{$lang['userdetails_s_conn']}</b> 
                    <span class='has-text-lightgreen'>{$lang['userdetails_yes']}</span>" : "<b>{$lang['userdetails_s_conn']}</b>
                    <span class='has-text-danger'><b>{$lang['userdetails_no']}</b></span>") . '
                </td>
            </tr>';
        } else {
            $body .= '
            <tr>
                <td>' . ($arr['owner'] == $id ? "<b><span class='has-text-orange'>{$lang['userdetails_s_towner']}</span></b><br>" : '' . ($arr['completedtime'] != '0' ? "<b><span class='has-text-lightgreen'>{$lang['userdetails_s_fin']}</span></b><br>" : "<b><span class='has-text-danger'>{$lang['userdetails_s_nofin']}</span></b><br>") . '') . "<img src='{$site_config['pic_baseurl']}caticons/" . get_category_icons() . '/' . htmlsafechars($arr['image']) . "' alt='" . htmlsafechars($arr['name']) . "' title='" . htmlsafechars($arr['name']) . "'></td>" . "
                <td><a class='altlink' href='{$site_config['baseurl']}/details.php?id=" . (int) $arr['fid'] . "'><b>" . htmlsafechars($arr['torrent_name']) . '</b></a>' . ($arr['completedtime'] != '0' ? "<br><span style='color: yellow'>{$lang['userdetails_s_started']}" . get_date($arr['started'], 0, 1) . '</span><br>' : "<span color='yellow'>started:" . get_date($arr['started'], 0, 1) . "</span><br><span class='has-text-orange'>{$lang['userdetails_s_laction']}" . get_date($arr['mtime'], 0, 1) . '</span>' . get_date($arr['completedtime'], 0, 1) . ' ' . ($arr['completedtime'] == '0' ? '' . ($arr['owner'] == $id ? '' : '[ ' . mksize($arr['size'] - $arr['downloaded']) . "{$lang['userdetails_s_still']}]") . '' : '') . '') . "{$lang['userdetails_s_finished']}" . get_date($arr['completedtime'], 0, 1) . '' . ($arr['completedtime'] != '0' ? "<br><span color='silver'>{$lang['userdetails_s_ttod']}" . ($arr['leechtime'] != '0' ? mkprettytime($arr['leechtime']) : mkprettytime($arr['complete_date'] - $arr['start_date']) . '') . "</span> <span color='$dlc'>[ {$lang['userdetails_s_dled']} $dl_speed ]</span><br>" : '<br>') . "<span color='lightblue'>" . ($arr['seedtime'] != '0' ? "{$lang['userdetails_s_tseed']}" . mkprettytime($arr['seedtime']) . " </span><span color='$dlc'> " : "{$lang['userdetails_s_tseedn']}") . "</span><span class='has-text-lightgreen'> [{$lang['userdetails_s_uspeed']}" . $ul_speed . ' ] </span>' . ($arr['completedtime'] != '0' ? "<br><span color='$dlc;'>{$lang['userdetails_s_dspeed']} $dl_speed</span>" : '') . '</td>' . "
                <td>{$lang['userdetails_s_seed']}" . (int) $arr['seeders'] . "<br>{$lang['userdetails_s_leech']}" . (int) $arr['leechers'] . "</td><td><span style='color: lightgreen'>{$lang['userdetails_s_upld']}<br><b>" . mksize($arr['uploaded']) . '</b></span>' . (RATIO_FREE ? '' : "<br><span class='has-text-orange'>{$lang['userdetails_s_dld']}<br><b>" . mksize($arr['downloaded']) . '</b></span>') . '</td><td>' . mksize($arr['size']) . '' . (RATIO_FREE ? '' : "<br>{$lang['userdetails_s_diff']}<br><span class='has-text-orange'><b>" . mksize($arr['size'] - $arr['downloaded']) . '</b></span>') . '</td><td>' . $ratio . '<br>' . ($arr['active'] == 1 ? "<span class='has-text-lightgreen'><b>{$lang['userdetails_s_seeding']}</b></span>" : "<span class='has-text-danger'><b>{$lang['userdetails_s_nseeding']}</b></span>") . '</td><td>' . htmlsafechars($arr['peer_id']) . '<br>' . ($arr['connectable'] == 1 ? "<b>{$lang['userdetails_s_conn']}</b> <span class='has-text-lightgreen'>{$lang['userdetails_yes']}</span>" : "<b>{$lang['userdetails_s_conn']}</b> <span class='has-text-danger'><b>{$lang['userdetails_no']}</b></span>") . '</td>
            </tr>';
        }
    }
    $table = main_table($body, $heading);

    return $table;
}
