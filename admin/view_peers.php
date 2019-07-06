<?php

declare(strict_types = 1);

use Pu239\Peer;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_viewpeers'));
$HTMLOUT = $count = '';
global $container, $site_config;

$peer = $container->get(Peer::class);
$count = $peer->get_count();
$peersperpage = 25;
$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['wpeers_h2']}</h1>
    <div class='size_4 has-text-centered margin20'>{$lang['wpeers_there']}" . $count . $lang['wpeers_peer'] . ($count > 1 ? $lang['wpeers_ps'] : '') . "{$lang['wpeers_curr']}</div>";
$pager = pager($peersperpage, $count, 'staffpanel.php?tool=view_peers&amp;');
if ($count > $peersperpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$results = $peer->get_peers($pager['pdo']['limit'], $pager['pdo']['offset']);
if (!empty($results)) {
    $heading = "
    <tr>
        <th>{$lang['wpeers_user']}</th>
        <th>{$lang['wpeers_torrent']}</th>
        <th>{$lang['wpeers_ip']}</th>
        <th>{$lang['wpeers_port']}</th>
        <th>{$lang['wpeers_client']}</th>
        <th>{$lang['wpeers_peer_id']}</th>
        <th>{$lang['wpeers_up']}</th>" . ($site_config['site']['ratio_free'] ? '' : "
        <th>{$lang['wpeers_dn']}</th>") . "
        <th>{$lang['wpeers_con']}</th>
        <th>{$lang['wpeers_seed']}</th>
        <th>{$lang['wpeers_start']}</th>
        <th>{$lang['wpeers_last']}</th>
        <th>{$lang['wpeers_upoff']}</th>" . ($site_config['site']['ratio_free'] ? '' : "
        <th>{$lang['wpeers_dnoff']}</th>") . "
        <th>{$lang['wpeers_togo']}</th>
        <th>{$lang['wpeers_size']}</th>
    </tr>";
    $body = '';
    foreach ($results as $row) {
        $smallname = substr(htmlsafechars($row['name']), 0, 25);
        if ($smallname != htmlsafechars($row['name'])) {
            $smallname .= '...';
        }
        $body .= '
    <tr>
        <td>' . format_username((int) $row['userid']) . '</td>
        <td><a href="' . $site_config['paths']['baseurl'] . '/details.php?id=' . (int) ($row['torrent']) . '">' . $smallname . '</a></td>
        <td>' . htmlsafechars($row['ip']) . '</td>
        <td>' . $row['port'] . '</td>
        <td>' . htmlsafechars(str_replace('/', "\n", trim($row['agent']))) . '</td>
        <td>' . htmlsafechars(str_replace('-', '', $row['peer_id'])) . '</td>
        <td>' . mksize($row['uploaded']) . '</td>' . ($site_config['site']['ratio_free'] ? '' : '
        <td>' . mksize($row['downloaded']) . '</td>') . '
        <td>' . ($row['connectable'] == 'yes' ? "<i class='icon-ok icon has-text-success tooltipper' title='{$lang['wpeers_yes']}'></i>" : "<i class='icon-cancel icon has-text-danger tooltipper' title='{$lang['wpeers_no']}'></i>") . '</td>
        <td>' . ($row['seeder'] == 'yes' ? "<i class='icon-ok icon has-text-success tooltipper' title='{$lang['wpeers_yes']}'></i>" : "<i class='icon-cancel icon has-text-danger tooltipper' title='{$lang['wpeers_no']}'></i>") . '</td>
        <td>' . get_date((int) $row['started'], 'DATE') . '</td>
        <td>' . get_date((int) $row['last_action'], 'DATE', 0, 1) . '</td>
        <td>' . mksize($row['uploadoffset']) . '</td>' . ($site_config['site']['ratio_free'] ? '' : '
        <td>' . mksize($row['downloadoffset']) . '</td>') . '
        <td>' . mksize($row['to_go']) . '</td>
        <td>' . mksize($row['size']) . '</td>
    </tr>';
    }

    $HTMLOUT .= main_table($body, $heading);
} else {
    $HTMLOUT .= stderr('', $lang['wpeers_notfound'], 'bottom20');
}
if ($count > $peersperpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['wpeers_peerover']) . wrapper($HTMLOUT) . stdfoot();
die();
