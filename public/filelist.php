<?php

declare(strict_types = 1);

use Pu239\Database;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
check_user_status();
global $container, $site_config;

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if (!is_valid_id($id)) {
    stderr('USER ERROR', 'Bad id');
}

$fluent = $container->get(Database::class);
$count = $fluent->from('files')
                ->select(null)
                ->select('COUNT(id) AS count')
                ->where('torrent = ?', $id)
                ->fetch('count');
$perpage = 50;
$pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/filelist.php?id=$id&amp;");
$HTMLOUT = '';
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}

$files = $fluent->from('files')
                ->where('torrent = ?', $id)
                ->orderBy('id')
                ->limit($pager['pdo']['limit'])
                ->offset($pager['pdo']['offset']);

$header = "
            <tr>
                <th class='has-text-centered w-1'>" . _('Type') . '</th>
                <th>' . _('Path') . "</th>
                <th class='has-text-right w-10'>" . _('Size') . '</th>
            </tr>';
$body = '';
foreach ($files as $subrow) {
    $ext = pathinfo($subrow['filename'], PATHINFO_EXTENSION);
    $ext = !empty($ext) ? $ext : 'Unknown';
    if (!file_exists(IMAGES_DIR . "icons/{$ext}.png")) {
        $ext = 'Unknown';
    }
    $body .= "
            <tr>
                <td class='has-text-centered'>
                    <img src='{$site_config['paths']['images_baseurl']}icons/" . htmlsafechars($ext) . ".png' class='tooltipper icon' alt='" . htmlsafechars($ext) . " file' title='" . htmlsafechars($ext) . " file'></td>
                <td>" . htmlsafechars($subrow['filename']) . "</td>
                <td class='has-text-right'>" . mksize($subrow['size']) . '</td>
            </tr>';
}

$HTMLOUT .= main_table($body, $header);

if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$title = _('Filelist');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
