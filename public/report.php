<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
$user = check_user_status();
$lang = array_merge(load_language('global'), load_language('report'));
global $container, $site_config;

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('sceditor_js'),
    ],
];
$HTMLOUT = $id_2 = '';

if (!$site_config['staff']['reports']) {
    stderr('Error', 'The report system is offline');
}
$id = !empty($_GET['id']) ? (int) $_GET['id'] : (!empty($_POST['id']) ? (int) $_POST['id'] : 0);
if (!is_valid_id($id)) {
    stderr($lang['report_error'], $lang['report_error1']);
}
$type = isset($_GET['type']) ? htmlsafechars($_GET['type']) : (!empty($_POST['type']) ? htmlsafechars($_POST['type']) : '');
$typesallowed = [
    'User',
    'Comment',
    'Request_Comment',
    'Offer_Comment',
    'Request',
    'Offer',
    'Torrent',
    'Hit_And_Run',
    'Post',
];
if (!in_array($type, $typesallowed)) {
    stderr($lang['report_error'], $lang['report_error2']);
}
if (isset($_POST['do_it'])) {
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : 0;
    $id_2 = !empty($_POST['id_2']) ? (int) $_POST['id_2'] : 0;
    $do_it = !empty($_POST['do_it']) ? (int) $_POST['do_it'] : 0;
    if (!is_valid_id($do_it)) {
        stderr($lang['report_error'], $lang['report_error3']);
    }

    $reason = !empty($_POST['body']) ? htmlsafechars($_POST['body']) : '';
    if (empty($reason)) {
        stderr($lang['report_error'], $lang['report_error4']);
    }
    $fluent = $container->get(Database::class);
    $previous = $fluent->from('reports')
                       ->select(null)
                       ->select('id')
                       ->where('reported_by = ?', $user['id'])
                       ->where('reporting_what = ?', $id)
                       ->where('reporting_type = ?', $type)
                       ->fetch('id');

    if (!empty($previous)) {
        stderr($lang['report_error5'], "{$lang['report_error6']} <b>" . str_replace('_', ' ', $type) . "</b> {$lang['report_id']} <b>$id</b>!");
    }

    $values = [
        'reported_by' => $user['id'],
        'reporting_what' => $id,
        'reporting_type' => $type,
        'reason' => $reason,
        'added' => TIME_NOW,
        '2nd_value' => $id_2,
    ];

    $fluent->insertInto('reports')
           ->values($values)
           ->execute();
    $cache = $container->get(Cache::class);
    $cache->delete('new_report_');
    $session = $container->get(Session::class);
    $session->set('is-success', str_replace('_', ' ', $type) . " {$lang['report_id']} {$id} report sent.");
    header("Location: {$site_config['paths']['baseurl']}");
    die();
}

$HTMLOUT .= main_div("
    <form method='post' action='{$site_config['paths']['baseurl']}/report.php' enctype='multipart/form-data' accept-charset='utf-8'>
    <h1>Report: " . str_replace('_', ' ', $type) . "</h1>
        {$lang['report_report']} <b>" . str_replace('_', ' ', $type) . "</b> {$lang['report_id']} <b>$id</b> {$lang['report_report1']} <a class='is-link' href='{$site_config['paths']['baseurl']}/rules.php' target='_blank'>{$lang['report_rules']}</a>?</td></tr>
        <p class='top10'><b>{$lang['report_reason']}</b></p>" . BBcode('', 'w-100', 200) . "
        <input type='hidden' name='id' value='$id'>
        <input type='hidden' name='type' value='$type'>
        <input type='hidden' name='do_it' value='1'>
        <input type='submit' class='button is-small margin20' value='{$lang['report_confirm']}'>
    </form>", '', 'padding20 has-text-centered');
echo stdhead('Report', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
die();
