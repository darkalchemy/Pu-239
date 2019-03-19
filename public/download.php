<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_happyhour.php';
require_once INCL_DIR . 'function_password.php';
require_once CLASS_DIR . 'class.bencdec.php';
dbconn();
global $site_config, $cache, $user_stuffs;

$lang = array_merge(load_language('global'), load_language('download'));
$T_Pass = isset($_GET['torrent_pass']) && strlen($_GET['torrent_pass']) == 64 ? $_GET['torrent_pass'] : '';
if (!empty($T_Pass)) {
    $user = $user_stuffs->get_user_from_torrent_pass($T_Pass);
    if (!$user) {
        die($lang['download_passkey']);
    }
} else {
    global $CURUSER;

    check_user_status();
    $user = $CURUSER;
}
$id = isset($_GET['torrent']) ? (int) $_GET['torrent'] : 0;
$usessl = get_scheme() === 'https' ? 1 : 0;
$zipuse = isset($_GET['zip']) && $_GET['zip'] == 1 ? true : false;
$text = isset($_GET['text']) && $_GET['text'] == 1 ? true : false;
if (!is_valid_id($id)) {
    stderr($lang['download_user_error'], $lang['download_no_id']);
}
$res = sql_query('SELECT name, owner, vip, category, filename, info_hash, size FROM torrents WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
$fn = TORRENTS_DIR . $id . '.torrent';
if (!$row || !is_file($fn) || !is_readable($fn)) {
    stderr('Err', 'There was an error with the file or with the query, please contact staff');
}
if (($user['downloadpos'] == 0 || $user['can_leech'] == 0 || $user['downloadpos'] > 1 || $user['suspended'] === 'yes') && !($user['id'] == $row['owner'])) {
    stderr('Error', 'Your download rights have been disabled.');
}
if (($user['seedbonus'] === 0 || $user['seedbonus'] < $site_config['bonus_per_download'])) {
    stderr('Error', "You don't have enough karma to download, trying seeding back some torrents =]");
}
if ($user['class'] === 0 && ($user['uploaded'] - $user['downloaded']) < $row['size']) {
    stderr('Error', "You don't have enough upload credit to download, trying seeding back some torrents =]");
}
if ($row['vip'] == 1 && $user['class'] < UC_VIP) {
    stderr('VIP Access Required',
        'You must be a VIP In order to view details or download this torrent! You may become a Vip By Donating to our site. Donating ensures we stay online to provide you more Vip-Only Torrents!');
}

if (happyHour('check') && happyCheck('checkid', $row['category']) && $site_config['happy_hour']) {
    $multiplier = happyHour('multiplier');
    happyLog($user['id'], $id, $multiplier);
    sql_query('INSERT INTO happyhour (userid, torrentid, multiplier ) VALUES (' . sqlesc($user['id']) . ',' . sqlesc($id) . ',' . sqlesc($multiplier) . ')') or sqlerr(__FILE__, __LINE__);
    $cache->delete($user['id'] . '_happy');
}
if ($site_config['seedbonus_on'] && $row['owner'] != $user['id']) {
    sql_query('UPDATE users SET seedbonus = seedbonus-' . sqlesc($site_config['bonus_per_download']) . ' WHERE id = ' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($user['seedbonus'] - $site_config['bonus_per_download']);
    $cache->update_row('user' . $user['id'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_cache']);
}
sql_query('UPDATE torrents SET hits = hits + 1 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$torrents = $cache->get('torrent_details_' . $id);
$update['hits'] = $torrents['hits'] + 1;
$cache->update_row('torrent_details_' . $id, [
    'hits' => $update['hits'],
], $site_config['expires']['torrent_details']);

if (isset($_GET['slot'])) {
    $added = (TIME_NOW + 14 * 86400);
    $slots_sql = sql_query('SELECT * FROM freeslots WHERE torrentid = ' . sqlesc($id) . ' AND userid = ' . sqlesc($user['id'])) or sqlerr(__FILE__, __LINE__);
    $slot = mysqli_fetch_assoc($slots_sql);
    $used_slot = $slot['torrentid'] == $id && $slot['userid'] == $user['id'];
    if ($_GET['slot'] === 'free') {
        if ($used_slot && $slot['free'] === 'yes') {
            stderr('Doh!', 'Freeleech slot already in use.');
        }
        if ($user['freeslots'] < 1) {
            stderr('Doh!', 'No Slots.');
        }
        $user['freeslots'] = ($user['freeslots'] - 1);
        sql_query('UPDATE users SET freeslots = freeslots - 1 WHERE id = ' . sqlesc($user['id']) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        if ($used_slot && $slot['doubleup'] === 'yes') {
            sql_query('UPDATE freeslots SET free = "yes", addedfree = ' . $added . ' WHERE torrentid = ' . $id . ' AND userid = ' . $user['id'] . ' AND doubleup = "yes"') or sqlerr(__FILE__, __LINE__);
        } elseif ($used_slot && $slot['doubleup'] === 'no') {
            sql_query('INSERT INTO freeslots (torrentid, userid, free, addedfree) VALUES (' . sqlesc($id) . ', ' . sqlesc($user['id']) . ', "yes", ' . $added . ')') or sqlerr(__FILE__, __LINE__);
        } else {
            sql_query('INSERT INTO freeslots (torrentid, userid, free, addedfree) VALUES (' . sqlesc($id) . ', ' . sqlesc($user['id']) . ', "yes", ' . $added . ')') or sqlerr(__FILE__, __LINE__);
        }
    } /* doubleslot **/ elseif ($_GET['slot'] === 'double') {
        if ($used_slot && $slot['doubleup'] === 'yes') {
            stderr('Doh!', 'Doubleseed slot already in use.');
        }
        if ($user['freeslots'] < 1) {
            stderr('Doh!', 'No Slots.');
        }
        $user['freeslots'] = ($user['freeslots'] - 1);
        sql_query('UPDATE users SET freeslots = freeslots - 1 WHERE id = ' . sqlesc($user['id']) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        if ($used_slot && $slot['free'] === 'yes') {
            sql_query('UPDATE freeslots SET doubleup = "yes", addedup = ' . $added . ' WHERE torrentid = ' . sqlesc($id) . ' AND userid = ' . sqlesc($user['id']) . ' AND free = "yes"') or sqlerr(__FILE__, __LINE__);
        } elseif ($used_slot && $slot['free'] === 'no') {
            sql_query('INSERT INTO freeslots (torrentid, userid, doubleup, addedup) VALUES (' . sqlesc($id) . ', ' . sqlesc($user['id']) . ', "yes", ' . $added . ')') or sqlerr(__FILE__, __LINE__);
        } else {
            sql_query('INSERT INTO freeslots (torrentid, userid, doubleup, addedup) VALUES (' . sqlesc($id) . ', ' . sqlesc($user['id']) . ', "yes", ' . $added . ')') or sqlerr(__FILE__, __LINE__);
        }
    } else {
        stderr('ERROR', 'What\'s up doc?');
    }
    $cache->delete('fllslot_' . $user['id']);
    make_freeslots($user['id'], 'fllslot_');
    $user['freeslots'] = ($user['freeslots'] - 1);
    $cache->update_row('user' . $user['id'], [
        'freeslots' => $user['freeslots'],
    ], $site_config['expires']['user_cache']);
}
$cache->deleteMulti([
    'top5_tor_',
    'last5_tor_',
    'scroll_torrents_',
    'slider_torrents_',
    'staff_picks_',
    'motw_',
]);

$dict = bencdec::decode_file($fn, $site_config['max_torrent_size']);
$dict['announce'] = $site_config['announce_urls'][$usessl] . '?torrent_pass=' . $user['torrent_pass'];
$dict['uid'] = (int) $user['id'];
$tor = bencdec::encode($dict);
if ($zipuse) {
    require_once INCL_DIR . 'phpzip.php';
    $row['name'] = str_replace([
        ' ',
        '.',
        '-',
    ], '_', $row['name']);
    $file_name = TORRENTS_DIR . $row['name'] . '.torrent';
    if (file_put_contents($file_name, $tor)) {
        $zip = new PHPZip();
        $files = [
            $file_name,
        ];
        $file_name = TORRENTS_DIR . $row['name'] . '.zip';
        $zip->Zip($files, $file_name);
        $zip->forceDownload($file_name);
        unlink(TORRENTS_DIR . $row['name'] . '.torrent');
        unlink(TORRENTS_DIR . $row['name'] . '.zip');
    } else {
        stderr('Error', 'Can\'t create the new file, please contatct staff');
    }
} else {
    if ($text) {
        header('Content-Disposition: attachment; filename="[' . $site_config['site_name'] . ']' . $row['name'] . '.txt"');
        header('Content-Type: text/plain');
        echo $tor;
    } else {
        header('Content-Disposition: attachment; filename="[' . $site_config['site_name'] . ']' . $row['filename'] . '"');
        header('Content-Type: application/x-bittorrent');
        echo $tor;
    }
}
