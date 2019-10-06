<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Torrent;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bt_client.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$id = (int) $_GET['id'];
if (!isset($id) || !is_valid_id($id)) {
    stderr(_('Error'), _('Invalid ID'));
}
$HTMLOUT = '';
/**
 * @param       $name
 * @param       $arr
 * @param       $torrent
 * @param mixed $user
 *
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function dltable($name, $arr, $torrent, $user)
{
    global $site_config;

    if (!count($arr)) {
        return $htmlout = main_div('<div><b>' . _fe('No {0} data available', $name) . '</b></div>', '', 'padding20 has-text-centered');
    }
    $heading = '
        <tr>
            <th>' . _('User/IP') . '</th>
            <th>' . _('Connectable') . '</th>
            <th>' . _('Uploaded') . '</th>
            <th>' . _('Rate') . '</th>' . ($site_config['site']['ratio_free'] ? '' : '
            <th>' . _('Downloaded') . '</th>') . ($site_config['site']['ratio_free'] ? '' : '
            <th>' . _('Rate') . '</th>') . '
            <th>' . _('Ratio') . '</th>
            <th>' . _('Complete') . '</th>
            <th>' . _('Connected') . '</th>
            <th>' . _('Idle') . '</th>
            <th>' . _('Client') . '</th>
        </tr>';
    $now = TIME_NOW;
    $mod = $user['class'] >= UC_STAFF;
    $body = '';
    foreach ($arr as $e) {
        $body .= '
        <tr>';
        if ($e['username']) {
            if ((($e['tanonymous'] === '1' && $e['owner'] === $e['userid'] || $e['anonymous_until'] > TIME_NOW || $e['paranoia'] >= 2) && $user['id'] != $e['userid']) && $user['class'] < UC_STAFF) {
                $username = get_anonymous_name();
                $body .= "
            <td><b>$username</b></td>";
            } else {
                $body .= '
            <td>' . format_username((int) $e['userid']) . '</td>';
            }
        } else {
            $body .= '
            <td>' . ($mod ? $e['ip'] : preg_replace('/\.\d+$/', '.xxx', $e['ip'])) . '</td>';
        }
        $secs = max(1, ($now - $e['st']) - ($now - $e['la']));
        $body .= '<td>' . ($e['connectable'] === 'yes' ? _('Yes') : "<span class='has-text-danger'>" . _('No') . '</span>') . "</td>\n";
        $body .= '<td>' . mksize($e['uploaded']) . "</td>\n";
        $body .= '<td><span style="white-space: nowrap;">' . mksize(($e['uploaded'] - $e['uploadoffset']) / $secs) . "/s</span></td>\n";
        $body .= ($site_config['site']['ratio_free'] ? '' : '<td>' . mksize($e['downloaded']) . '</td>') . "\n";
        if ($e['seeder'] === 'no') {
            $body .= ($site_config['site']['ratio_free'] ? '' : '<td><span style="white-space: nowrap;">' . mksize(($e['downloaded'] - $e['downloadoffset']) / $secs) . '/s</span></td>') . "\n";
        } else {
            $body .= ($site_config['site']['ratio_free'] ? '' : '<td><span style="white-space: nowrap;">' . mksize(($e['downloaded'] - $e['downloadoffset']) / max(1, $e['finishedat'] - $e['st'])) . '/s</span></td>') . "\n";
        }
        $body .= '<td>' . member_ratio($e['uploaded'], $e['downloaded']) . "</td>\n";
        $body .= '<td>' . sprintf('%.2f%%', 100 * (1 - ($e['to_go'] / $torrent['size']))) . "</td>\n";
        $body .= '<td>' . mkprettytime($now - $e['st']) . "</td>\n";
        $body .= '<td>' . mkprettytime($now - $e['la']) . "</td>\n";
        $body .= '<td>' . htmlsafechars(getagent($e['agent'], $e['peer_id'])) . "</td>\n";
        $body .= '</tr>';
    }
    $htmlout = "<h3 class='has-text-centered'>" . count($arr) . " $name" . plural(count($arr)) . '</h3>' . main_table($body, $heading);

    return $htmlout;
}

global $container, $site_config;

$torrents_class = $container->get(Torrent::class);
$torrent = $torrents_class->get($id);
if (empty($torrent)) {
    stderr(_('Error'), _('Invalid ID'));
}
$downloaders = [];
$seeders = [];
$fluent = $container->get(Database::class);
$peers = $fluent->from('peers AS p')
                ->select('t.anonymous AS tanonymous')
                ->select('t.owner')
                ->select('p.seeder')
                ->select('p.finishedat')
                ->select('p.downloadoffset')
                ->select('p.uploadoffset')
                ->select('INET6_NTOA(p.ip) AS ip')
                ->select('p.port')
                ->select('p.uploaded')
                ->select('p.downloaded')
                ->select('p.to_go')
                ->select('p.started AS st')
                ->select('p.connectable')
                ->select('p.agent')
                ->select('p.last_action AS la')
                ->select('p.userid')
                ->select('p.peer_id')
                ->select('u.username')
                ->select('u.anonymous_until')
                ->select('u.paranoia')
                ->innerJoin('torrents AS t ON t.id = p.torrent')
                ->leftJoin('users AS u ON u.id = p.userid')
                ->where('p.torrent = ?', $id)
                ->fetchAll();

if (empty($peers)) {
    stderr(_('Error'), _('No downloader/uploader data available!'));
}
foreach ($peers as $subrow) {
    if ($subrow['seeder'] === 'yes') {
        $seeders[] = $subrow;
    } else {
        $downloaders[] = $subrow;
    }
}

/**
 * @param $a
 * @param $b
 *
 * @return int
 */
function leech_sort($a, $b)
{
    if (isset($_GET['usort'])) {
        return seed_sort($a, $b);
    }
    $x = $a['to_go'];
    $y = $b['to_go'];
    if ($x == $y) {
        return 0;
    }
    if ($x < $y) {
        return -1;
    }

    return 1;
}

/**
 * @param $a
 * @param $b
 *
 * @return int
 */
function seed_sort($a, $b)
{
    $x = $a['uploaded'];
    $y = $b['uploaded'];
    if ($x == $y) {
        return 0;
    }
    if ($x < $y) {
        return 1;
    }

    return -1;
}

usort($seeders, 'seed_sort');
usort($downloaders, 'leech_sort');
$HTMLOUT .= "
    <h1 class='has-text-centered'>" . _fe('Peerlist for {0}{1}{2}', "<a href='{$site_config['paths']['baseurl']}/details.php?id=$id'>", format_comment($torrent['name']), '</a>') . '</h1>';
$HTMLOUT .= dltable(_('Seeder') . "<a id='seeders'></a>", $seeders, $torrent, $user);
$HTMLOUT .= '<br>' . dltable(_('Leecher') . "<a id='leechers'></a>", $downloaders, $torrent, $user);
$title = _('Peerlist');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
