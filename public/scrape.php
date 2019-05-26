<?php

declare(strict_types = 1);

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'ann_config.php';
require_once INCL_DIR . 'function_announce.php';
require_once CLASS_DIR . 'class_bt_options.php';
if (empty($_SERVER['QUERY_STRING'])) {
    err("It takes 46 muscles to frown but only 4 to flip 'em the bird.");
}
$q = explode('&', $_SERVER['QUERY_STRING']);
$_GET = [];
foreach ($q as $p) {
    $ps = explode('=', $p, 2);
    $p1 = rawurldecode(trim($ps[0]));
    $p2 = rawurldecode(trim($ps[1]));
    if (strlen($p1) > 0) {
        if (!isset($_GET[$p1])) {
            $_GET[$p1] = $p2;
        } elseif (!is_array($_GET[$p1])) {
            $temp = $_GET[$p1];
            unset($_GET[$p1]);
            $_GET[$p1] = [];
            $_GET[$p1][] = $temp;
            $_GET[$p1][] = $p2;
        } else {
            $_GET[$p1][] = $p2;
        }
    }
}

if (empty($_GET['torrent_pass']) || strlen($_GET['torrent_pass']) != 64) {
    err('torrent pass not valid, please redownload your torrent file');
}
$torrent_pass = $_GET['torrent_pass'];
if (!$torrent_pass) {
    err('empty torrent pass');
}
$user = $user_stuffs->get_user_from_torrent_pass($torrent_pass);
if (empty($user) || $user['enabled'] === 'no' || $user['parked'] === 'yes' || $user['downloadpos'] != 1) {
    err('scrape user error');
}
$numhash = 1;
if (!empty($_GET['info_hash']) && is_array($_GET['info_hash'])) {
    $numhash = count($_GET['info_hash']);
} elseif (empty($_GET['info_hash'])) {
    $numhash = 0;
}
$torrents = [];
if ($numhash < 1) {
    err('Scrape Error d5:filesdee');
} elseif ($numhash === 1) {
    $torrent = $torrent_stuffs->get_torrent_from_hash($_GET['info_hash']);
    if ($torrent) {
        $torrents[$_GET['info_hash']] = $torrent;
    }
} else {
    foreach ($_GET['info_hash'] as $hash) {
        $torrent = $torrent_stuffs->get_torrent_from_hash($hash);
        if ($torrent) {
            $torrents[$hash] = $torrent;
        }
    }
}
if (count($torrents) === 0) {
    err('torrent error');
}
$resp = 'd5:filesd';
foreach ($torrents as $info_hash => $torrent) {
    $resp .= '20:' . $info_hash . 'd8:completei' . $torrent['seeders'] . 'e10:downloadedi' . $torrent['times_completed'] . 'e10:incompletei' . $torrent['leechers'] . 'ee';
}
$resp .= 'ee';
benc_resp_raw($resp);
