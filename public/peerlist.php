<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bt_client_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $site_config, $lang;

$lang = array_merge(load_language('global'), load_language('peerlist'));
$id = (int) $_GET['id'];
if (!isset($id) || !is_valid_id($id)) {
    stderr($lang['peerslist_user_error'], $lang['peerslist_invalid_id']);
}
$HTMLOUT = '';
/**
 * @param $name
 * @param $arr
 * @param $torrent
 *
 * @return string
 */
function dltable($name, $arr, $torrent)
{
    global $CURUSER, $lang, $site_config;
    if (!count($arr)) {
        return $htmlout = main_div("<div><b>{$lang['peerslist_no']} $name {$lang['peerslist_data_available']}</b></div>");
    }
    $heading = "
        <tr>
            <th>{$lang['peerslist_user_ip']}</th>
            <th>{$lang['peerslist_connectable']}</th>
            <th>{$lang['peerslist_uploaded']}</th>
            <th>{$lang['peerslist_rate']}</th>" . ($site_config['ratio_free'] ? '' : "
            <th>{$lang['peerslist_downloaded']}</th>") . ($site_config['ratio_free'] ? '' : "
            <th>{$lang['peerslist_rate']}</th>") . "
            <th>{$lang['peerslist_ratio']}</th>
            <th>{$lang['peerslist_complete']}</th>
            <th>{$lang['peerslist_connected']}</th>
            <th>{$lang['peerslist_idle']}</th>
            <th>{$lang['peerslist_client']}</th>
        </tr>";
    $now = TIME_NOW;
    $mod = $CURUSER['class'] >= UC_STAFF;
    $body = '';
    foreach ($arr as $e) {
        $body .= '
        <tr>';
        if ($e['username']) {
            if ((($e['tanonymous'] === 'yes' && $e['owner'] == $e['userid'] || $e['anonymous'] === 'yes' || $e['paranoia'] >= 2) && $CURUSER['id'] != $e['userid']) && $CURUSER['class'] < UC_STAFF) {
                $username = get_anonymous_name();
                $body .= "
            <td><b>$username</b></td>";
            } else {
                $body .= '
            <td>' . format_username($e['userid']) . '</td>';
            }
        } else {
            $body .= '
            <td>' . ($mod ? $e['ip'] : preg_replace('/\.\d+$/', '.xxx', $e['ip'])) . '</td>';
        }
        $secs = max(1, ($now - $e['st']) - ($now - $e['la']));
        $body .= '<td>' . ($e['connectable'] === 'yes' ? "{$lang['peerslist_yes']}" : "<span class='has-text-danger'>{$lang['peerslist_no']}</span>") . "</td>\n";
        $body .= '<td>' . mksize($e['uploaded']) . "</td>\n";
        $body .= '<td><span style="white-space: nowrap;">' . mksize(($e['uploaded'] - $e['uploadoffset']) / $secs) . "/s</span></td>\n";
        $body .= '' . ($site_config['ratio_free'] ? '' : '<td>' . mksize($e['downloaded']) . '</td>') . "\n";
        if ($e['seeder'] === 'no') {
            $body .= '' . ($site_config['ratio_free'] ? '' : '<td><span style="white-space: nowrap;">' . mksize(($e['downloaded'] - $e['downloadoffset']) / $secs) . '/s</span></td>') . "\n";
        } else {
            $body .= '' . ($site_config['ratio_free'] ? '' : '<td><span style="white-space: nowrap;">' . mksize(($e['downloaded'] - $e['downloadoffset']) / max(1, $e['finishedat'] - $e['st'])) . '/s</span></td>') . "\n";
        }
        $body .= '<td>' . member_ratio($e['uploaded'], $site_config['ratio_free'] ? '0' : $e['downloaded']) . "</td>\n";
        $body .= '<td>' . sprintf('%.2f%%', 100 * (1 - ($e['to_go'] / $torrent['size']))) . "</td>\n";
        $body .= '<td>' . mkprettytime($now - $e['st']) . "</td>\n";
        $body .= '<td>' . mkprettytime($now - $e['la']) . "</td>\n";
        $body .= '<td>' . htmlsafechars(getagent($e['agent'], $e['peer_id'])) . "</td>\n";
        $body .= '</tr>';
    }
    $htmlout = "<h3 class='has-text-centered'>" . count($arr) . " $name" . plural(count($arr)) . '</h3>' . main_table($body, $heading);

    return $htmlout;
}

$res = sql_query('SELECT * FROM torrents WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($res) == 0) {
    stderr("{$lang['peerslist_error']}", "{$lang['peerslist_nothing']}");
}
$row = mysqli_fetch_assoc($res);
$downloaders = [];
$seeders = [];
$subres = sql_query('SELECT u.username, u.anonymous, u.paranoia, t.owner, t.anonymous AS tanonymous, p.seeder, p.finishedat, p.downloadoffset, p.uploadoffset, p.ip, p.port, p.uploaded, p.downloaded, p.to_go, p.started AS st, p.connectable, p.agent, p.last_action AS la, p.userid, p.peer_id
    FROM peers p
    LEFT JOIN users u ON p.userid = u.id
    LEFT JOIN torrents AS t ON t.id = p.torrent
    WHERE p.torrent = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($subres) == 0) {
    stderr("{$lang['peerslist_warning']}", "{$lang['peerslist_no_data']}");
}
while ($subrow = mysqli_fetch_assoc($subres)) {
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
    <h1 class='has-text-centered'>Peerlist for <a href='{$site_config['baseurl']}/details.php?id=$id'>" . htmlsafechars($row['name']) . '</a></h1>';
$HTMLOUT .= dltable("{$lang['peerslist_seeders']}<a id='seeders'></a>", $seeders, $row);
$HTMLOUT .= '<br>' . dltable("{$lang['peerslist_leechers']}<a id='leechers'></a>", $downloaders, $row);
echo stdhead($lang['peerslist_stdhead'], true) . wrapper($HTMLOUT) . stdfoot();
