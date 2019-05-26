<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
$lang = array_merge(load_language('global'), load_language('userdetails'));
$stdfoot = [
    'js' => [
        get_file_name('checkport_js'),
    ],
];
global $CURUSER;

if ($CURUSER >= UC_STAFF && !empty($_GET['id']) && is_valid_id($_GET['id'])) {
    $id = (int) $_GET['id'];
} else {
    $id = $CURUSER['id'];
}
$user = format_username((int) $id);

$completed = "
    <h1 class='has-text-centered'>$user Port Status</h1>";
$completed .= main_div("
    <div id='ipports' data-uid='{$id}'></div>
    <div class='columns top10 is-variable is-0-mobile is-1-tablet is-2-desktop padding20'>
        <span class='has-text-centered column is-one-third'>
            <input class='w-100' type='text' id='userip' placeholder='Your Torrent Client IP [" . getip() . "]'>
        </span>
        <span class='has-text-centered column is-one-third'>
            <input class='w-100' type='text' id='userport' placeholder='Your Torrent Client Port'>
        </span>
        <span class='has-text-centered column is-one-third'>
            <input class='w-100' type='text' id='ipport' placeholder='Check Status' readonly>
        </span>
    </div>
    <div class='has-text-centered'>
        <input id='portcheck' type='submit' value='Test Connectivity' class='button is-small margin20'>
    </div>");

echo stdhead('Check My Ports') . wrapper($completed) . stdfoot($stdfoot);
