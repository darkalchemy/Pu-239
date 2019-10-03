<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

require_once __DIR__ . '/../../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
header('content-type: application/json');
global $container;

if (empty($user)) {
    echo json_encode(['fail' => 'csrf']);
    die();
}
$uid = $user['class'] < UC_STAFF ? $user['id'] : (int) $_POST['uid'];
$type = $_POST['type'];
if ($type === 'torrents') {
    $torrents = get_uploaded($uid);
    if (!$torrents) {
        echo json_encode(['content' => main_div(_('You have not uploaded any torrents'), null, 'padding20')]);
        die();
    }
    $data = maketable($torrents);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div(_('You have not uploaded any torrents'), null, 'padding20')]);
        die();
    }
} elseif ($type === 'seeding') {
    $torrents = get_seeding($uid);
    if (!$torrents) {
        echo json_encode(['content' => main_div(_('You are not seeding any torrents'), null, 'padding20')]);
        die();
    }
    $data = maketable($torrents);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div(_('You are not seeding any torrents'), null, 'padding20')]);
        die();
    }
} elseif ($type === 'leeching') {
    $torrents = get_leeching($uid);
    if (!$torrents) {
        echo json_encode(['content' => main_div(_('You are not leeching any torrents'), null, 'padding20')]);
        die();
    }
    $data = maketable($torrents);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div(_('You are not leeching any torrents'), null, 'padding20')]);
        die();
    }
} elseif ($type === 'snatched') {
    $torrents = get_snatched($uid);
    if (!$torrents) {
        echo json_encode(['content' => main_div(_('You have not downloaded any torrents'), null, 'padding20')]);
        die();
    }
    $data = snatchtable($torrents);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div(_('You have not downloaded any torrents'), null, 'padding20')]);
        die();
    }
} elseif ($type === 'snatched_staff' && $user['class'] >= UC_STAFF) {
    $torrents = get_snatched_staff($uid);
    if (!$torrents) {
        echo json_encode(['content' => main_div(_('You have not downloaded any torrents'), null, 'padding20')]);
        die();
    }
    $data = staff_snatchtable($torrents, $uid);
    if (!empty($data)) {
        echo json_encode(['content' => $data]);
        die();
    } else {
        echo json_encode(['content' => main_div(_('You have not downloaded any torrents'), null, 'padding20')]);
        die();
    }
}

echo json_encode(['fail' => 'invalid']);
die();

/**
 * @param int $userid
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool
 */
function get_uploaded(int $userid)
{
    global $container;

    $torrents = [];
    $fluent = $container->get(Database::class);
    $count = $fluent->from('torrents')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('owner = ?', $userid)
                    ->fetch('count');

    if ($count === 0) {
        return false;
    }
    $cache = $container->get(Cache::class);
    $query = $fluent->from('torrents AS t')
                    ->select(null)
                    ->select('t.id AS torrentid')
                    ->select('t.name')
                    ->select('t.seeders')
                    ->select('t.leechers')
                    ->select('t.size')
                    ->select('c.name AS catname')
                    ->select('c.image')
                    ->select('p.name AS parent_name')
                    ->leftJoin('categories AS c ON t.category = c.id')
                    ->leftJoin('categories AS p ON c.parent_id = p.id')
                    ->where('t.owner = ?', $userid)
                    ->orderBy('t.name');

    foreach ($query as $results) {
        $sums = $cache->get("sums_{$userid}_{$results['torrentid']}");
        if ($sums === false || is_null($sums)) {
            $sums = $fluent->from('snatched')
                           ->select(null)
                           ->select('SUM(uploaded) AS uploaded')
                           ->select('SUM(downloaded) AS downloaded')
                           ->where('userid = ?', $userid)
                           ->where('torrentid = ?', $results['torrentid'])
                           ->fetch();

            $results['uploaded'] = $sums['uploaded'];
            $results['downloaded'] = $sums['downloaded'];
            $sums = $results;
            $cache->set("sums_{$userid}_{$results['torrentid']}", $sums, 300);
        }
        $torrents[] = $sums;
    }

    return $torrents;
}

/**
 * @param int $userid
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool
 */
function get_seeding(int $userid)
{
    global $container;

    $fluent = $container->get(Database::class);
    $count = $fluent->from('peers')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('userid = ?', $userid)
                    ->where('seeder = "yes"')
                    ->fetch('count');

    if ($count === 0) {
        return false;
    }

    $torrents = $fluent->from('peers AS z')
                       ->select(null)
                       ->select('z.torrent AS torrentid')
                       ->select('z.uploaded')
                       ->select('z.downloaded')
                       ->select('z.seeder')
                       ->select('z.last_action')
                       ->select('t.added')
                       ->select('t.name')
                       ->select('t.size')
                       ->select('t.seeders')
                       ->select('t.leechers')
                       ->select('t.owner')
                       ->select('c.name AS catname')
                       ->select('c.image')
                       ->select('p.name AS parent_name')
                       ->innerJoin('torrents AS t ON z.torrent = t.id')
                       ->leftJoin('categories AS c ON t.category = c.id')
                       ->leftJoin('categories AS p ON c.parent_id = p.id')
                       ->where('z.userid = ?', $userid)
                       ->where('z.seeder = "yes"')
                       ->orderBy('z.last_action DESC')
                       ->fetchAll();

    return $torrents;
}

/**
 * @param int $userid
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool
 */
function get_leeching(int $userid)
{
    global $container;

    $fluent = $container->get(Database::class);
    $count = $fluent->from('peers')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('userid = ?', $userid)
                    ->where('seeder = "no"')
                    ->fetch('count');

    if ($count === 0) {
        return false;
    }

    $torrents = $fluent->from('peers AS z')
                       ->select(null)
                       ->select('z.torrent AS torrentid')
                       ->select('z.uploaded')
                       ->select('z.downloaded')
                       ->select('z.seeder')
                       ->select('z.last_action')
                       ->select('t.added')
                       ->select('t.name')
                       ->select('t.size')
                       ->select('t.seeders')
                       ->select('t.leechers')
                       ->select('t.owner')
                       ->select('c.name AS catname')
                       ->select('c.image')
                       ->select('p.name AS parent_name')
                       ->innerJoin('torrents AS t ON z.torrent = t.id')
                       ->leftJoin('categories AS c ON t.category = c.id')
                       ->leftJoin('categories AS p ON c.parent_id = p.id')
                       ->where('z.userid = ?', $userid)
                       ->where('z.seeder = "no"')
                       ->orderBy('z.last_action DESC')
                       ->fetchAll();

    return $torrents;
}

/**
 * @param int $userid
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool
 */
function get_snatched(int $userid)
{
    global $container;

    $fluent = $container->get(Database::class);
    $count = $fluent->from('snatched')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('userid = ?', $userid)
                    ->fetch('count');

    if ($count === 0) {
        return false;
    }

    $torrents = $fluent->from('snatched AS s')
                       ->select('t.name')
                       ->select('t.category AS catid')
                       ->select('c.name AS catname')
                       ->select('c.image')
                       ->select('p.name AS parent_name')
                       ->innerJoin('torrents AS t ON s.torrentid = t.id')
                       ->leftJoin('categories AS c ON t.category = c.id')
                       ->leftJoin('categories AS p ON c.parent_id = p.id')
                       ->where('s.userid = ?', $userid)
                       ->orderBy('s.last_action DESC')
                       ->fetchAll();

    return $torrents;
}

/**
 * @param int $userid
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return array|bool
 */
function get_snatched_staff(int $userid)
{
    global $container;

    $fluent = $container->get(Database::class);
    $count = $fluent->from('snatched')
                    ->select(null)
                    ->select('COUNT(id) AS count')
                    ->where('userid = ?', $userid)
                    ->fetch('count');

    if ($count === 0) {
        return false;
    }

    $torrents = $fluent->from('snatched AS s')
                       ->select('t.name AS torrent_name')
                       ->select('t.seeders')
                       ->select('t.leechers')
                       ->select('t.size')
                       ->select('t.owner')
                       ->select('c.name AS catname')
                       ->select('p.name AS parent_name')
                       ->select('c.image')
                       ->select('z.agent')
                       ->select('z.connectable')
                       ->select('z.port')
                       ->select('INET6_NTOA(z.ip) AS ip')
                       ->innerJoin('torrents AS t ON s.torrentid = t.id')
                       ->leftJoin('categories AS c ON t.category = c.id')
                       ->leftJoin('categories AS p ON c.parent_id = p.id')
                       ->leftJoin('peers AS z ON t.id = z.torrent AND z.userid = s.userid')
                       ->where('s.userid = ?', $userid)
                       ->orderBy('s.last_action DESC')
                       ->fetchAll();

    return $torrents;
}

/**
 * @param array $torrents
 *
 * @return string
 */
function maketable(array $torrents)
{
    global $site_config;

    $heading = '
        <tr>
            <th>' . _('Type') . '</th>
            <th>' . _('Name') . '</th>
            <th>' . _('Size') . '</th>
            <th>' . _('Seeders') . '</th>
            <th>' . _('Leechers') . '</th>
            <th>' . _('Uploaded') . '</th>' . ($site_config['site']['ratio_free'] ? '' : '
            <th>' . _('Downloaded') . '</th>') . '
            <th>' . _('Ratio') . '</th>
        </tr>';
    $body = '';
    foreach ($torrents as $torrent) {
        if ($torrent['downloaded'] > 0) {
            $ratio = $torrent['uploaded'] / $torrent['downloaded'];
            $ratio = "<span style='color: " . get_ratio_color($ratio) . ";'>" . number_format($ratio, 3) . '</span>';
        } elseif ($torrent['uploaded'] > 0) {
            $ratio = _('Inf.');
        } else {
            $ratio = '---';
        }

        $cat_info = cat_image($torrent);
        $size = mksize($torrent['size']);
        $uploaded = mksize($torrent['uploaded']);
        $downloaded = mksize($torrent['downloaded']);
        $seeders = number_format($torrent['seeders']);
        $leechers = number_format($torrent['leechers']);
        $body .= "
        <tr>
            <td class='has-text-centered'>$cat_info</td>
            <td>
                <a class='is-link' href='{$site_config['paths']['baseurl']}/details.php?id={$torrent['torrentid']}&amp;hit=1'><b>" . htmlsafechars($torrent['name']) . "</b></a>
            </td>
            <td class='has-text-centered'>$size</td>
            <td class='has-text-centered'>$seeders</td>
            <td class='has-text-centered'>$leechers</td>
            <td class='has-text-centered'>$uploaded</td>" . ($site_config['site']['ratio_free'] ? '' : "
            <td class='has-text-centered'>$downloaded</td>") . "
            <td class='has-text-centered'>$ratio</td>
        </tr>";
    }

    $table = main_table($body, $heading);

    return $table;
}

/**
 * @param array $torrents
 *
 * @return string
 */
function snatchtable(array $torrents)
{
    global $site_config;

    $heading = '
        <tr>
            <th>' . _('Category') . '</th>
            <th>' . _('Torrent') . '</th>
            <th>' . _('Uploaded') . '</th>
            <th>' . _('Rate') . '</th>' . ($site_config['site']['ratio_free'] ? '' : '
            <th>' . _('Downloaded') . '</th>') . ($site_config['site']['ratio_free'] ? '' : '
            <th>' . _('Rate') . '</th>') . '
            <th>' . _('Ratio') . '</th>
            <th>' . _('Activity') . '</th>
            <th>' . _('Finished') . '</th>
        </tr>';
    $body = '';
    foreach ($torrents as $torrent) {
        $upspeed = $torrent['upspeed'] > 0 ? mksize($torrent['upspeed']) : ($torrent['seedtime'] > 0 ? mksize($torrent['uploaded'] / ($torrent['seedtime'] + $torrent['leechtime'])) : mksize(0));
        $downspeed = ($torrent['downspeed'] > 0 ? mksize($torrent['downspeed']) : ($torrent['leechtime'] > 0 ? mksize($torrent['downloaded'] / $torrent['leechtime']) : mksize(0)));
        $ratio = ($torrent['downloaded'] > 0 ? number_format($torrent['uploaded'] / $torrent['downloaded'], 3) : ($torrent['uploaded'] > 0 ? 'Inf.' : '---'));
        $XBT_or_PHP = $torrent['torrentid'];
        $XBT_or_PHP_TIME = $torrent['complete_date'];
        $cat_info = cat_image($torrent);
        $body .= "
        <tr>
            <td>$cat_info</td>
            <td>
                <a class='is-link' href='{$site_config['paths']['baseurl']}/details.php?id={$XBT_or_PHP}'><b>" . (strlen($torrent['name']) > 50 ? substr($torrent['name'], 0, 50 - 3) . '...' : htmlsafechars($torrent['name'])) . '</b></a>
            </td>
            <td>' . mksize($torrent['uploaded']) . "</td>
            <td>$upspeed/s</td>" . ($site_config['site']['ratio_free'] ? '' : '
            <td>' . mksize($torrent['downloaded']) . '</td>') . ($site_config['site']['ratio_free'] ? '' : "
            <td>$downspeed/s</td>") . "
            <td>$ratio</td>
            <td>" . mkprettytime($torrent['seedtime'] + $torrent['leechtime']) . '</td>
            <td>' . ($XBT_or_PHP_TIME != 0 ? "
                <span class='has-text-success'><b>" . _('Yes') . '</b></span>' : "
                <span class='has-text-danger'><b>" . _('No') . '</b></span>') . '
            </td>
        </tr>';
    }
    $table = main_table($body, $heading);

    return $table;
}

/**
 * @param array $torrents
 * @param int   $userid
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function staff_snatchtable(array $torrents, int $userid)
{
    global $site_config;

    $heading = '
                    <tr>
                        <th>' . _('Category') . '</th>
                        <th>' . _('Torrent') . '</th>
                        <th>' . _('S / L') . '</th>
                        <th>' . _('Uploaded') . '' . ($site_config['site']['ratio_free'] ? '' : _('/ Down')) . '</th>
                        <th>' . _('Size') . '</th>
                        <th>' . _('Ratio') . '</th>
                        <th>' . _('Client') . '</th>
                    </tr>';
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
            $dl_speed = mksize($arr['downloaded'] / ($arr['complete_date'] - $arr['start_date'] + 1));
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

            case $dl_speed > 100:
                $dlc = 'Chartreuse';
                break;

            default:
                $dlc = 'white';
                break;
        }
        if ($arr['downloaded'] > 0) {
            $ratio = $arr['uploaded'] / $arr['downloaded'];
            $ratio = "<span style='color: " . get_ratio_color($ratio) . ";'><b>" . _('Ratio:') . '</b><br>' . number_format($ratio, 3) . '</span>';
        } elseif ($arr['uploaded'] > 0) {
            $ratio = _('Inf.');
        } else {
            $ratio = 'N/A';
        }
        $cat_info = cat_image($arr);
        $body .= '
            <tr>
                <td>' . ($arr['owner'] === $userid ? "
                    <b><span class='is-orange'>" . _('Owner') . '</span></b><br>' : '' . ($arr['complete_date'] != '0' ? "
                    <b><span class='is-lightgreen'>" . _('Finished') . '</span></b><br>' : "
                    <b><span class='has-text-danger'>" . _('Not Finished') . '</span></b><br>') . '') . $cat_info . "
                </td>
                <td>
                    <a class='is-link' href='{$site_config['paths']['baseurl']}/details.php?id={$arr['torrentid']}'><b>" . htmlsafechars($arr['torrent_name']) . '</b></a>' . ($arr['complete_date'] != '0' ? "<br>
                    <span class='is-warning'>" . _('Started') . ': ' . get_date($arr['start_date'], 'LONG', 0, 1) . "</span><br>
                    <span class='is-orange'>" . _('Last Action') . ': ' . get_date($arr['last_action'], 'LONG', 0, 1) . '</span>' . ($arr['complete_date'] === 0 ? ($arr['owner'] == $userid ? '' : '[ ' . mksize($arr['size'] - $arr['downloaded']) . '' . _(' still to go ') . ']') : '') : '') . '<br>' . _(' Finished: ') . get_date($arr['complete_date'], 'LONG', 0, 1) . '' . ($arr['complete_date'] != 0 ? "<br>
                    <span style='color: silver;'>" . _('Time to download') . ': ' . ($arr['leechtime'] != '0' ? mkprettytime($arr['leechtime']) : mkprettytime($arr['complete_date'] - $arr['start_date']) . '') . "</span>
                    <span style='color: $dlc'>[ " . _(' DLed at') . ": $dl_speed ]</span><br>" : '<br>') . "
                    <span class='is-lightblue'>" . ($arr['seedtime'] != '0' ? _('Total seeding time') . ': ' . mkprettytime($arr['seedtime']) . " </span>
                    <span style='color: $dlc;'> " : _('Total seeding time') . ': ' . _('N/A')) . "</span>
                    <span class='is-lightgreen'>[ " . _('up speed') . ': ' . $ul_speed . ' ]</span>' . ($arr['complete_date'] == '0' ? "<br>
                    <span style='color: $dlc;'>" . _('Download speed') . ": $dl_speed</span>" : '') . '
                </td>
                <td>' . _('Seeders') . ': ' . $arr['seeders'] . '<br>' . _('Leechers') . ': ' . $arr['leechers'] . "</td>
                <td>
                    <span class='is-lightgreen'>" . _('Uploaded') . ': <br><b>' . mksize($arr['uploaded']) . '</b></span>' . ($site_config['site']['ratio_free'] ? '' : "<br>
                    <span class='is-orange'>" . _('Downloaded') . ': <br><b>' . mksize($arr['downloaded']) . '</b></span>') . '
                </td>
                <td>' . mksize($arr['size']) . ($site_config['site']['ratio_free'] ? '' : '<br>' . _('Difference of') . ": <br>
                    <span class='is-orange'><b>" . mksize($arr['size'] - $arr['downloaded']) . '</b></span>') . '
                </td>
                <td>' . $ratio . '<br>' . ($arr['seeder'] === 'yes' ? "
                    <span class='is-lightgreen'><b>" . _('Seeding') . '</b></span>' : "
                    <span class='has-text-danger'><b>" . _('Not seeding') . '</b></span>') . '
                </td>
                <td>' . (!empty($arr['agent']) ? htmlsafechars($arr['agent']) : '') . '<br>IP: ' . $arr['ip'] . '<br>' . _('port') . ': ' . $arr['port'] . '<br>' . ($arr['connectable'] === 'yes' ? '<b>' . _('Connectable') . ": </b> 
                    <span class='is-lightgreen'>" . _('Yes') . '</span>' : '<b>' . _('Connectable') . ": </b>
                    <span class='has-text-danger'><b>" . _('No') . '</b></span>') . '
                </td>
            </tr>';
    }
    $table = main_table($body, $heading);

    return $table;
}

/**
 * @param $torrent
 *
 * @return mixed|string
 */
function cat_image($torrent)
{
    global $site_config;

    $cat = '';
    if (!empty($torrent['parent_name'])) {
        $cat = $torrent['parent_name'] . ' :: ' . $torrent['catname'];
    }

    $image = !empty($catimage) && file_exists(IMAGES_DIR . 'caticons/' . get_category_icons() . "/$catimage") ? "{$site_config['paths']['images_baseurl']}caticons/" . get_category_icons() . "/$catimage" : '';
    $catname = htmlsafechars($cat);
    $catimage = !empty($image) ? "<img src='$image' title='$catname' alt='$catname' width='42' height='42' class='tooltipper'>" : $catname;

    return $catimage;
}
