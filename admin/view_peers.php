<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $fluent;

$lang = array_merge($lang, load_language('ad_viewpeers'));
$HTMLOUT = $count = '';

$count = $fluent->from('peers')
    ->select(null)
    ->select('COUNT(*) AS count')
    ->fetch('count');

$peersperpage = 25;
$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['wpeers_h2']}</h1>
    <div class='size_4 has-text-centered margin20'>{$lang['wpeers_there']}" . htmlsafechars($count) . "{$lang['wpeers_peer']}" . ($count > 1 ? $lang['wpeers_ps'] : '') . "{$lang['wpeers_curr']}</div>";
$pager = pager($peersperpage, $count, 'staffpanel.php?tool=view_peers&amp;');
if ($count > $peersperpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$sql = "SELECT p.id, p.userid, p.torrent, p.torrent_pass, LEFT(p.peer_id, 8) AS peer_id, INET6_NTOA(p.ip) AS ip, p.port, p.uploaded, p.downloaded, p.to_go, p.seeder, p.started, p.last_action, p.connectable, p.agent, p.finishedat, p.downloadoffset, p.uploadoffset, u.username, t.name FROM peers AS p LEFT JOIN users AS u ON u.id = p.userid LEFT JOIN torrents AS t ON t.id = p.torrent WHERE started != 0 ORDER BY p.started DESC {$pager['limit']}";
$result = sql_query($sql) or sqlerr(__FILE__, __LINE__);
if (mysqli_num_rows($result) != 0) {
    $heading = "
    <tr>
        <th>{$lang['wpeers_user']}</th>
        <th>{$lang['wpeers_torrent']}</th>
        <th>{$lang['wpeers_ip']}</th>
        <th>{$lang['wpeers_port']}</th>
        <th>{$lang['wpeers_client']}</th>
        <th>{$lang['wpeers_peer_id']}</th>
        <th>{$lang['wpeers_up']}</th>" . (RATIO_FREE == true ? '' : "
        <th>{$lang['wpeers_dn']}</th>") . "
        <th>{$lang['wpeers_con']}</th>
        <th>{$lang['wpeers_seed']}</th>
        <th>{$lang['wpeers_start']}</th>
        <th>{$lang['wpeers_last']}</th>
        <th>{$lang['wpeers_upoff']}</th>" . (RATIO_FREE == true ? '' : "
        <th>{$lang['wpeers_dnoff']}</th>") . "
        <th>{$lang['wpeers_togo']}</th>
    </tr>";
    $body = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $smallname = substr(htmlsafechars($row['name']), 0, 25);
        if ($smallname != htmlsafechars($row['name'])) {
            $smallname .= '...';
        }
        $body .= '
    <tr>
        <td>' . format_username($row['userid']) . '</td>
        <td><a href="' . $site_config['baseurl'] . '/details.php?id=' . (int) ($row['torrent']) . '">' . $smallname . '</a></td>
        <td>' . htmlsafechars($row['ip']) . '</td>
        <td>' . htmlsafechars($row['port']) . '</td>
        <td>' . htmlsafechars(str_replace('/', "\n", trim($row['agent']))) . '</td>
        <td>' . htmlsafechars(str_replace('-', '', $row['peer_id'])) . '</td>
        <td>' . htmlsafechars(mksize($row['uploaded'])) . '</td>' . (RATIO_FREE == true ? '' : '
        <td>' . htmlsafechars(mksize($row['downloaded'])) . '</td>') . '
        <td>' . ($row['connectable'] == 'yes' ? "<i class='icon-ok icon has-text-success tooltipper' title='{$lang['wpeers_yes']}'></i>" : "<i class='icon-cancel icon has-text-danger tooltipper' title='{$lang['wpeers_no']}'></i>") . '</td>
        <td>' . ($row['seeder'] == 'yes' ? "<i class='icon-ok icon has-text-danger tooltipper' title='{$lang['wpeers_yes']}'></i>" : "<i class='icon-cancel icon has-text-danger tooltipper' title='{$lang['wpeers_no']}'></i>") . '</td>
        <td>' . get_date($row['started'], 'DATE') . '</td>
        <td>' . get_date($row['last_action'], 'DATE', 0, 1) . '</td>
        <td>' . htmlsafechars(mksize($row['uploadoffset'])) . '</td>' . (RATIO_FREE == true ? '' : '
        <td>' . htmlsafechars(mksize($row['downloadoffset'])) . '</td>') . '
        <td>' . htmlsafechars(mksize($row['to_go'])) . '</td>
    </tr>';
    }

    $HTMLOUT .= main_table($body, $heading);
} else {
    $HTMLOUT .= stderr('', $lang['wpeers_notfound']);
}
if ($count > $peersperpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['wpeers_peerover']) . wrapper($HTMLOUT) . stdfoot();
die();
