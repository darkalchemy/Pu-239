<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_viewpeers'));
$HTMLOUT = $count = '';

/**
 * @param $a
 *
 * @return string
 */
function XBT_IP_CONVERT($a)
{
    $b = [
        0,
        0,
        0,
        0,
    ];
    $c = 16777216.0;
    $a += 0.0;
    for ($i = 0; $i < 4; ++$i) {
        $k = (int) ($a / $c);
        $a -= $c * $k;
        $b[$i] = $k;
        $c /= 256.0;
    }
    $d = implode('.', $b);

    return $d;
}

$Which_ID = (XBT_TRACKER ? 'fid' : 'id');
$Which_Table = (XBT_TRACKER ? 'xbt_files_users' : 'peers');
$res = sql_query("SELECT COUNT($Which_ID) FROM $Which_Table") or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$count = $row[0];
$peersperpage = 15;
$HTMLOUT .= "<h1 class='has-text-centered'>{$lang['wpeers_h2']}</h1>
<div class='size_4 has-text-centered margin20'>{$lang['wpeers_there']}" . htmlsafechars($count) . "{$lang['wpeers_peer']}" . ($count > 1 ? $lang['wpeers_ps'] : '') . "{$lang['wpeers_curr']}</div>";
$pager = pager($peersperpage, $count, 'staffpanel.php?tool=view_peers&amp;action=view_peers&amp;');
if ($count > $peersperpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (XBT_TRACKER) {
    $sql = "SELECT x.fid, x.uid, x.left, x.active, x.peer_id, x.ipa, x.uploaded, x.downloaded, x.leechtime, x.seedtime, x.upspeed, x.downspeed, x.mtime, x.completedtime, u.torrent_pass, u.username, t.seeders, t.leechers, t.name FROM `xbt_files_users` AS x LEFT JOIN users AS u ON u.id=x.uid LEFT JOIN torrents AS t ON t.id=x.fid WHERE `left` >= 0 AND t.leechers >= 0 ORDER BY fid DESC {$pager['limit']}";
} else {
    $sql = "SELECT p.id, p.userid, p.torrent, p.torrent_pass, p.peer_id, INET6_NTOA(p.ip) AS ip, p.port, p.uploaded, p.downloaded, p.to_go, p.seeder, p.started, p.last_action, p.connectable, p.agent, p.finishedat, p.downloadoffset, p.uploadoffset, u.username, t.name FROM peers AS p LEFT JOIN users AS u ON u.id = p.userid LEFT JOIN torrents AS t ON t.id = p.torrent WHERE started != 0 ORDER BY p.started DESC {$pager['limit']}";
}
$result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($result) != 0) {
    if (XBT_TRACKER) {
        $HTMLOUT .= "<table class='table table-bordered table-striped'>
<tr>
<td>{$lang['wpeers_user']}</td>
<td>{$lang['wpeers_torrent']}</td>
<td>{$lang['wpeers_ip']}</td>
<td>{$lang['wpeers_up']}</td>
" . ($site_config['ratio_free'] == true ? '' : "<td>{$lang['wpeers_dn']}</td>") . "
<td>{$lang['wpeers_pssky']}</td>
<td>{$lang['wpeers_seed']}</td>
<td>{$lang['wpeers_last']}</td>
<td>{$lang['wpeers_uspeed']}</td>
" . ($site_config['ratio_free'] == true ? '' : "<td>{$lang['wpeers_dspeed']}</td>") . "
<td>{$lang['wpeers_togo']}</td>
</tr>";
    } else {
        $HTMLOUT .= "<table class='table table-bordered table-striped'>
<tr>
<td>{$lang['wpeers_user']}</td>
<td>{$lang['wpeers_torrent']}</td>
<td>{$lang['wpeers_ip']}</td>
<td>{$lang['wpeers_port']}</td>
<td>{$lang['wpeers_up']}</td>
" . ($site_config['ratio_free'] == true ? '' : "<td>{$lang['wpeers_dn']}</td>") . "
<td>{$lang['wpeers_pssky']}</td>
<td>{$lang['wpeers_con']}</td>
<td>{$lang['wpeers_seed']}</td>
<td>{$lang['wpeers_start']}</td>
<td>{$lang['wpeers_last']}</td>
<td>{$lang['wpeers_upoff']}</td>
" . ($site_config['ratio_free'] == true ? '' : "<td>{$lang['wpeers_dnoff']}</td>") . "
<td>{$lang['wpeers_togo']}</td>
</tr>";
    }
    while ($row = mysqli_fetch_assoc($result)) {
        $smallname = substr(htmlsafechars($row['name']), 0, 25);
        if ($smallname != htmlsafechars($row['name'])) {
            $smallname .= '...';
        }
        if (XBT_TRACKER) {
            $upspeed = ($row['upspeed'] > 0 ? mksize($row['upspeed']) : ($row['seedtime'] > 0 ? mksize($row['uploaded'] / ($row['seedtime'] + $row['leechtime'])) : mksize(0)));
            $downspeed = ($row['downspeed'] > 0 ? mksize($row['downspeed']) : ($row['leechtime'] > 0 ? mksize($row['downloaded'] / $row['leechtime']) : mksize(0)));
        }
        if (XBT_TRACKER) {
            $HTMLOUT .= '<tr>
<td>' . format_username($row['uid']) . '</td>
<td><a href="details.php?id=' . (int) ($row['fid']) . '">' . $smallname . '</a></td>
<td>' . htmlsafechars(XBT_IP_CONVERT($row['ipa'])) . '</td>
<td>' . htmlsafechars(mksize($row['uploaded'])) . '</td>
' . ($site_config['ratio_free'] == true ? '' : '<td>' . htmlsafechars(mksize($row['downloaded'])) . '</td>') . '
<td>' . htmlsafechars($row['torrent_pass']) . '</td>
<td>' . ($row['seeders'] >= 1 ? "<img src='" . $site_config['pic_baseurl'] . "aff_tick.gif' alt='{$lang['wpeers_yes']}' title='{$lang['wpeers_yes']}' />" : "<img src='" . $site_config['pic_baseurl'] . "aff_cross.gif' alt='{$lang['wpeers_no']}' title='{$lang['wpeers_no']}' />") . '</td>
<td>' . get_date($row['mtime'], 'DATE', 0, 1) . '</td>
<td>' . htmlsafechars(mksize($row['upspeed'])) . '/s</td>
' . ($site_config['ratio_free'] == true ? '' : '<td>' . htmlsafechars(mksize($row['downspeed'])) . '/s</td>') . '
<td>' . htmlsafechars(mksize($row['left'])) . '</td>
</tr>';
        } else {
            $HTMLOUT .= '<tr>
<td>' . format_username($row['userid']) . '</td>
<td><a href="details.php?id=' . (int) ($row['torrent']) . '">' . $smallname . '</a></td>
<td>' . htmlsafechars($row['ip']) . '</td>
<td>' . htmlsafechars($row['port']) . '</td>
<td>' . htmlsafechars(mksize($row['uploaded'])) . '</td>
' . ($site_config['ratio_free'] == true ? '' : '<td>' . htmlsafechars(mksize($row['downloaded'])) . '</td>') . '
<td>' . htmlsafechars($row['torrent_pass']) . '</td>
<td>' . ($row['connectable'] == 'yes' ? "<img src='" . $site_config['pic_baseurl'] . "aff_tick.gif' alt='{$lang['wpeers_yes']}' title='{$lang['wpeers_yes']}' />" : "<img src='" . $site_config['pic_baseurl'] . "aff_cross.gif' alt='{$lang['wpeers_no']}' title='{$lang['wpeers_no']}' />") . '</td>
<td>' . ($row['seeder'] == 'yes' ? "<img src='" . $site_config['pic_baseurl'] . "aff_tick.gif' alt='{$lang['wpeers_yes']}' title='{$lang['wpeers_yes']}' />" : "<img src='" . $site_config['pic_baseurl'] . "aff_cross.gif' alt='{$lang['wpeers_no']}' title='{$lang['wpeers_no']}' />") . '</td>
<td>' . get_date($row['started'], 'DATE') . '</td>
<td>' . get_date($row['last_action'], 'DATE', 0, 1) . '</td>
<td>' . htmlsafechars(mksize($row['uploadoffset'])) . '</td>
' . ($site_config['ratio_free'] == true ? '' : '<td>' . htmlsafechars(mksize($row['downloadoffset'])) . '</td>') . '
<td>' . htmlsafechars(mksize($row['to_go'])) . '</td>
</tr>';
        }
    }
    $HTMLOUT .= '</table>';
} else {
    $HTMLOUT .= $lang['wpeers_notfound'];
}
if ($count > $peersperpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['wpeers_peerover']) . wrapper($HTMLOUT) . stdfoot();
die();
