<?php

declare(strict_types = 1);

use Pu239\HappyLog;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
$HTMLOUT = '';
global $container, $site_config;

if (empty($user)) {
    stderr(_('Error'), 'User not found');
}
$id = $user['id'];
$happylog_class = $container->get(HappyLog::class);
$count = $happylog_class->get_count($id);
$perpage = 30;
$pager = pager($perpage, $count, "{$site_config['paths']['baseurl']}/happylog.php?id=$id&amp;");
$res = $happylog_class->get_by_userid($id, $pager['pdo']);
$HTMLOUT .= "
    <h1 class='has-text-centered'>" . _fe('Happy hour log for {0}', format_username((int) $id)) . '</h1>';
if ($count > 0) {
    $HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';
    $heading = "
        <tr>
            <td class='colhead w-50'>" . ('Name') . "</td>
            <td class='colhead'>" . ('Multiplier') . "</td>
            <td class='colhead' nowrap='nowrap'>" . ('Date Started') . '</td>
        </tr>';
    $body = '';
    foreach ($res as $arr) {
        $body .= "
        <tr>
            <td><a href='{$site_config['paths']['baseurl']}/details.php?id={$arr['torrentid']}'>" . htmlsafechars($arr['name']) . "</a></td>
            <td>{$arr['multi']}</td>
            <td nowrap='nowrap'>" . get_date((int) $arr['date'], 'LONG', 1, 0) . '</td>
        </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);
    $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
} else {
    $HTMLOUT .= main_div(_('No torrents downloaded during happy hour!'), '', 'has-text-centered padding20');
}
$title = _('Happy Log');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
