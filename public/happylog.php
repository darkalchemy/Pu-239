<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $user_stuffs, $happylog_stuffs;

$lang = load_language('global');
$HTMLOUT = '';
$id = !empty($_GET['id']) ? (int) $_GET['id'] : '';
if (empty($id)) {
    stderr('Err', 'I dont think so!');
}
$user = $user_stuffs->getUserFromId($id);
if (empty($user)) {
    stderr('Error', 'User not found');
}
$count = $happylog_stuffs->get_count($id);
$perpage = 30;
$pager = pager($perpage, $count, "{$site_config['baseurl']}/happylog.php?id=$id&amp;");
$res = $happylog_stuffs->get_by_userid($id, $pager['pdo']);
$HTMLOUT .= "
    <h1 class='has-text-centered'>Happy hour log for " . format_username($id) . '</h1>';
if ($count > 0) {
    $HTMLOUT .= $count > $perpage ? $pager['pagertop'] : '';
    $heading = "
        <tr>
            <td class='colhead w-50'>Torrent Name</td>
            <td class='colhead'>Multiplier</td>
            <td class='colhead' nowrap='nowrap'>Date started</td>
        </tr>";
    $body = '';
    foreach ($res as $arr) {
        $body .= "
        <tr>
            <td><a href='{$site_config['baseurl']}/details.php?id={$arr['torrentid']}'>" . htmlsafechars($arr['name']) . "</a></td>
            <td>{$arr['multi']}</td>
            <td nowrap='nowrap'>" . get_date($arr['date'], 'LONG', 1, 0) . '</td>
        </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);
    $HTMLOUT .= $count > $perpage ? $pager['pagerbottom'] : '';
} else {
    $HTMLOUT .= main_div('No torrents downloaded in happy hour!');
}
echo stdhead('Happy hour log for ' . htmlsafechars($user['username']) . '') . wrapper($HTMLOUT) . stdfoot();
