<?php

declare(strict_types = 1);

use Pu239\Database;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$HTMLOUT = $count = '';
global $container, $site_config;

$fluent = $container->get(Database::class);
$count1 = $fluent->from('torrents')
                 ->select(null)
                 ->select('COUNT(id) AS count')
                 ->fetch('count');

$perpage = 15;
$pager = pager($perpage, $count1, 'staffpanel.php?tool=uploader_info&amp;');
$counted = $fluent->from('torrents AS t')
                  ->select(null)
                  ->select('COUNT(t.id) AS how_many_torrents')
                  ->select('t.owner')//->select('t.added')
                  ->select('u.class')
                  ->select('u.uploaded')
                  ->select('u.downloaded')
                  ->leftJoin('users AS u ON t.owner = u.id')
                  ->groupBy('t.owner')//->groupBy('t.added')
                  ->orderBy('how_many_torrents DESC')
                  ->limit($pager['pdo']['limit'])
                  ->offset($pager['pdo']['offset'])
                  ->fetchAll();

if ($count1 > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
$heading = '
    <tr>
        <th>' . _('Rank') . '</th>
        <th>' . _('Torrents') . '</th>
        <th>' . _('Member') . '</th>
        <th>' . _('Class') . '</th>
        <th>' . _('Ratio') . '</th>
        <th>' . _('Send PM') . '</th>
    </tr>';
$i = 0;
$body = '';
foreach ($counted as $arr) {
    ++$i;
    $ratio = member_ratio((float) $arr['uploaded'], (float) $arr['downloaded']);
    $body .= '
    <tr>
        <td>' . $i . '</td>
        <td>' . (int) $arr['how_many_torrents'] . '</td>
        <td>' . format_username((int) $arr['owner']) . '</td>
        <td>' . get_user_class_name((int) $arr['class']) . '</td>
        <td>' . $ratio . '</td>
        <td>
            <a href="messages.php?action=send_message&amp;receiver=' . (int) $arr['owner'] . '" class="button is-small tooltipper" title="' . _('Send PM') . '">' . _('Send PM') . '</a>
        </td>
    </tr>';
}
$HTMLOUT .= main_table($body, $heading);
if ($count1 > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$title = _('Uploader Stats');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
