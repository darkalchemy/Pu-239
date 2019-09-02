<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Peer;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_bt_client.php';
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
$valid_sort = [
    'id',
    'userid',
    'name',
    'ip',
    'port',
    'agent',
    'uploaded',
    'downloaded',
    'connectable',
    'seeder',
    'started',
    'ts',
    'uploadoffset',
    'downloadoffset',
    'to_go',
    'size',
    'delete',
];
$column = isset($_GET['sort'], $valid_sort[$_GET['sort']]) ? $valid_sort[$_GET['sort']] : 'started';
if (isset($_GET['delete']) && is_valid_id((int) $_GET['delete'])) {
    $fluent = $container->get(Database::class);
    $fluent->deleteFrom('peers')
        ->where('id = ?', (int) $_GET['delete'])
        ->execute();
    $session = $container->get(Session::class);
    $session->set('is-success', 'Peer ' . $_GET['delete'] . ' has been deleted.');
}
$pagerlink = $ascdesc = '';
$type = isset($_GET['type']) ? $_GET['type'] : 'desc';
foreach ($valid_sort as $key => $value) {
    if ($value === $column) {
        switch (htmlsafechars($type)) {
            case 'desc':
                $ascdesc = 'DESC';
                $linkascdesc = 'desc';
                break;

            default:
                $ascdesc = '';
                $linkascdesc = 'asc';
                break;
        }
        $pagerlink = "sort={$key}&amp;type={$linkascdesc}&amp;";
    }
}
for ($i = 0; $i <= count($valid_sort); ++$i) {
    if (isset($_GET['sort']) && (int) $_GET['sort'] === $i) {
        $link[$i] = isset($type) && $type === 'desc' ? 'asc' : 'desc';
    } else {
        $link[$i] = 'desc';
    }
}
$pager = pager($peersperpage, $count, $site_config['paths']['baseurl'] . '/staffpanel.php?tool=view_peers&amp;' . $pagerlink);
if ($count > $peersperpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$results = $peer->get_all_peers($pager['pdo']['limit'], $pager['pdo']['offset'], $column, $ascdesc);
if (!empty($results)) {
    $heading = "
    <tr>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=1&amp;type={$link[1]}'>{$lang['wpeers_user']}</a></th>
        <th class='has-text-centered min-150'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=2&amp;type={$link[2]}'>{$lang['wpeers_torrent']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=3&amp;type={$link[3]}'>{$lang['wpeers_ip']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=4&amp;type={$link[4]}'>{$lang['wpeers_port']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=5&amp;type={$link[5]}'>{$lang['wpeers_client']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=6&amp;type={$link[6]}'>{$lang['wpeers_up']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=7&amp;type={$link[7]}'>{$lang['wpeers_dn']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=8&amp;type={$link[8]}'>{$lang['wpeers_con']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=9&amp;type={$link[9]}'>{$lang['wpeers_seed']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=10&amp;type={$link[10]}'>{$lang['wpeers_start']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=11&amp;type={$link[11]}'>{$lang['wpeers_last']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=12&amp;type={$link[12]}'>{$lang['wpeers_upoff']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=13&amp;type={$link[13]}'>{$lang['wpeers_dnoff']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=14&amp;type={$link[14]}'>{$lang['wpeers_togo']}</a></th>
        <th class='has-text-centered'><a href='{$_SERVER['PHP_SELF']}?tool=view_peers&amp;sort=15&amp;type={$link[15]}'>{$lang['wpeers_size']}</a></th>
        <th class='has-text-centered'>Delete</th>
    </tr>";
    $body = '';
    foreach ($results as $row) {
        $smallname = format_comment($row['name']);
        $body .= '
    <tr>
        <td class="has-text-centered">' . format_username((int) $row['userid']) . '</td>
        <td><a href="' . $site_config['paths']['baseurl'] . '/details.php?id=' . $row['torrent'] . '">' . $smallname . '</a></td>
        <td class="has-text-centered">' . htmlsafechars($row['ip']) . '</td>
        <td class="has-text-centered">' . $row['port'] . '</td>
        <td class="has-text-centered">' . htmlsafechars(getagent($row['agent'], $row['peer_id'])) . '</td>
        <td class="has-text-centered">' . mksize($row['uploaded']) . '</td>
        <td class="has-text-centered">' . mksize($row['downloaded']) . '</td>
        <td class="has-text-centered">' . ($row['connectable'] == 'yes' ? "<i class='icon-ok icon has-text-success tooltipper' title='{$lang['wpeers_yes']}'></i>" : "<i class='icon-cancel icon has-text-danger tooltipper' title='{$lang['wpeers_no']}'></i>") . '</td>
        <td class="has-text-centered">' . ($row['seeder'] == 'yes' ? "<i class='icon-ok icon has-text-success tooltipper' title='{$lang['wpeers_yes']}'></i>" : "<i class='icon-cancel icon has-text-danger tooltipper' title='{$lang['wpeers_no']}'></i>") . '</td>
        <td class="has-text-centered">' . get_date((int) $row['started'], 'DATE', 0, 1) . '</td>
        <td class="has-text-centered">' . get_date((int) $row['ts'], 'DATE', 0, 1) . '</td>
        <td class="has-text-centered">' . mksize($row['uploadoffset']) . '</td>
        <td class="has-text-centered">' . mksize($row['downloadoffset']) . '</td>
        <td class="has-text-centered">' . mksize($row['to_go']) . '</td>
        <td class="has-text-centered">' . mksize($row['size']) . '</td>
        <td class="has-text-centered">
            <a href="' . $_SERVER['PHP_SELF'] . '?tool=view_peers&amp;delete=' . $row["id"] . '" class="tooltipper" title="Delete Peer">
                <i class="icon-trash-empty icon has-text-danger" aria-hidden="true"></i>
            </a>
        </td>
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
