<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'function_happyhour.php';
require_once INCL_DIR . 'password_functions.php';
require_once CLASS_DIR . 'class.bencdec.php';
dbconn();
global $CURUSER, $site_config, $cache;

$lang   = array_merge(load_language('global'), load_language('download'));
$T_Pass = isset($_GET['torrent_pass']) && 64 == strlen($_GET['torrent_pass']) ? $_GET['torrent_pass'] : '';
if (!empty($T_Pass)) {
    $q0 = sql_query('SELECT * FROM users WHERE torrent_pass = ' . sqlesc($T_Pass)) or sqlerr(__FILE__, __LINE__);
    if (0 == mysqli_num_rows($q0)) {
        die($lang['download_passkey']);
    } else {
        $CURUSER = mysqli_fetch_assoc($q0);
    }
} else {
    check_user_status();
}
$id     = isset($_GET['torrent']) ? (int) $_GET['torrent'] : 0;
$ssluse = isset($_GET['ssl'])     && 1 == $_GET['ssl'] || 3 == $CURUSER['ssluse'] ? 1 : 0;
$zipuse = isset($_GET['zip'])     && 1 == $_GET['zip'] ? true : false;
$text   = isset($_GET['text'])    && 1 == $_GET['text'] ? true : false;
if (!is_valid_id($id)) {
    stderr($lang['download_user_error'], $lang['download_no_id']);
}
$res = sql_query('SELECT name, owner, vip, category, filename, info_hash FROM torrents WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_assoc($res);
$fn  = $site_config['torrent_dir'] . '/' . $id . '.torrent';
if (!$row || !is_file($fn) || !is_readable($fn)) {
    stderr('Err', 'There was an error with the file or with the query, please contact staff');
}
if ((0 == $CURUSER['downloadpos'] || 0 == $CURUSER['can_leech'] || $CURUSER['downloadpos'] > 1 || 'yes' == $CURUSER['suspended']) && !($CURUSER['id'] == $row['owner'])) {
    stderr('Error', 'Your download rights have been disabled.');
}
if ((0 === $CURUSER['seedbonus'] || $CURUSER['seedbonus'] < $site_config['bonus_per_download'])) {
    stderr('Error', 'Your dont have enough credit to download, trying seeding back some torrents =]');
}
if (1 == $row['vip'] && $CURUSER['class'] < UC_VIP) {
    stderr('VIP Access Required', 'You must be a VIP In order to view details or download this torrent! You may become a Vip By Donating to our site. Donating ensures we stay online to provide you more Vip-Only Torrents!');
}

if (happyHour('check') && happyCheck('checkid', $row['category']) && !XBT_TRACKER && true == $site_config['happy_hour']) {
    $multiplier = happyHour('multiplier');
    happyLog($CURUSER['id'], $id, $multiplier);
    sql_query('INSERT INTO happyhour (userid, torrentid, multiplier ) VALUES (' . sqlesc($CURUSER['id']) . ',' . sqlesc($id) . ',' . sqlesc($multiplier) . ')') or sqlerr(__FILE__, __LINE__);
    $cache->delete($CURUSER['id'] . '_happy');
}
if (1 == $site_config['seedbonus_on'] && $row['owner'] != $CURUSER['id']) {
    sql_query('UPDATE users SET seedbonus = seedbonus-' . sqlesc($site_config['bonus_per_download']) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $update['seedbonus'] = ($CURUSER['seedbonus'] - $site_config['bonus_per_download']);
    $cache->update_row('user' . $CURUSER['id'], [
        'seedbonus' => $update['seedbonus'],
    ], $site_config['expires']['user_cache']);
}
sql_query('UPDATE torrents SET hits = hits + 1 WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$update['hits'] = ($torrents['hits'] + 1);
$cache->update_row('torrent_details_' . $id, [
    'hits' => $update['hits'],
], $site_config['expires']['torrent_details']);

if (isset($_GET['slot'])) {
    $added     = (TIME_NOW + 14 * 86400);
    $slots_sql = sql_query('SELECT * FROM freeslots WHERE torrentid = ' . sqlesc($id) . ' AND userid = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $slot      = mysqli_fetch_assoc($slots_sql);
    $used_slot = $slot['torrentid'] == $id && $slot['userid'] == $CURUSER['id'];
    if ('free' == $_GET['slot']) {
        if ($used_slot && 'yes' == $slot['free']) {
            stderr('Doh!', 'Freeleech slot already in use.');
        }
        if ($CURUSER['freeslots'] < 1) {
            stderr('Doh!', 'No Slots.');
        }
        $CURUSER['freeslots'] = ($CURUSER['freeslots'] - 1);
        sql_query('UPDATE users SET freeslots = freeslots - 1 WHERE id = ' . sqlesc($CURUSER['id']) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        if ($used_slot && 'yes' == $slot['doubleup']) {
            sql_query('UPDATE freeslots SET free = "yes", addedfree = ' . $added . ' WHERE torrentid = ' . $id . ' AND userid = ' . $CURUSER['id'] . ' AND doubleup = "yes"') or sqlerr(__FILE__, __LINE__);
        } elseif ($used_slot && 'no' == $slot['doubleup']) {
            sql_query('INSERT INTO freeslots (torrentid, userid, free, addedfree) VALUES (' . sqlesc($id) . ', ' . sqlesc($CURUSER['id']) . ', "yes", ' . $added . ')') or sqlerr(__FILE__, __LINE__);
        } else {
            sql_query('INSERT INTO freeslots (torrentid, userid, free, addedfree) VALUES (' . sqlesc($id) . ', ' . sqlesc($CURUSER['id']) . ', "yes", ' . $added . ')') or sqlerr(__FILE__, __LINE__);
        }
    } /* doubleslot **/
    elseif ('double' == $_GET['slot']) {
        if ($used_slot && 'yes' == $slot['doubleup']) {
            stderr('Doh!', 'Doubleseed slot already in use.');
        }
        if ($CURUSER['freeslots'] < 1) {
            stderr('Doh!', 'No Slots.');
        }
        $CURUSER['freeslots'] = ($CURUSER['freeslots'] - 1);
        sql_query('UPDATE users SET freeslots = freeslots - 1 WHERE id = ' . sqlesc($CURUSER['id']) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        if ($used_slot && 'yes' == $slot['free']) {
            sql_query('UPDATE freeslots SET doubleup = "yes", addedup = ' . $added . ' WHERE torrentid = ' . sqlesc($id) . ' AND userid = ' . sqlesc($CURUSER['id']) . ' AND free = "yes"') or sqlerr(__FILE__, __LINE__);
        } elseif ($used_slot && 'no' == $slot['free']) {
            sql_query('INSERT INTO freeslots (torrentid, userid, doubleup, addedup) VALUES (' . sqlesc($id) . ', ' . sqlesc($CURUSER['id']) . ', "yes", ' . $added . ')') or sqlerr(__FILE__, __LINE__);
        } else {
            sql_query('INSERT INTO freeslots (torrentid, userid, doubleup, addedup) VALUES (' . sqlesc($id) . ', ' . sqlesc($CURUSER['id']) . ', "yes", ' . $added . ')') or sqlerr(__FILE__, __LINE__);
        }
    } else {
        stderr('ERROR', 'What\'s up doc?');
    }
    $cache->delete('fllslot_' . $CURUSER['id']);
    make_freeslots($CURUSER['id'], 'fllslot_');
    $user['freeslots'] = ($CURUSER['freeslots'] - 1);
    $cache->update_row('user' . $CURUSER['id'], [
        'freeslots' => $user['freeslots'],
    ], $site_config['expires']['user_cache']);
}
/* end **/
$cache->delete('MyPeers_' . $CURUSER['id']);
$cache->delete('top5_tor_');
$cache->delete('last5_tor_');
$cache->delete('scroll_tor_');
if (!isset($CURUSER['torrent_pass']) || 64 != strlen($CURUSER['torrent_pass'])) {
    $passkey                 = make_password(16);
    $uid                     = $CURUSER['id'];
    $CURUSER['torrent_pass'] = $passkey;
    sql_query('UPDATE users SET torrent_pass = ' . sqlesc($CURUSER['torrent_pass']) . ' WHERE id = ' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->update_row('user' . $CURUSER['id'], [
        'torrent_pass' => $CURUSER['torrent_pass'],
    ], $site_config['expires']['user_cache']);
}
$dict = bencdec::decode_file($fn, $site_config['max_torrent_size']);
if (XBT_TRACKER) {
    $dict['announce'] = $site_config['xbt_prefix'] . $CURUSER['torrent_pass'] . $site_config['xbt_suffix'];
} else {
    $dict['announce'] = $site_config['announce_urls'][$ssluse] . '?torrent_pass=' . $CURUSER['torrent_pass'];
}
$dict['uid'] = (int) $CURUSER['id'];
$tor         = bencdec::encode($dict);
if ($zipuse) {
    require_once INCL_DIR . 'phpzip.php';
    $row['name'] = str_replace([
                                   ' ',
                                   '.',
                                   '-',
                               ], '_', $row['name']);
    $file_name = $site_config['torrent_dir'] . '/' . $row['name'] . '.torrent';
    if (file_put_contents($file_name, $tor)) {
        $zip   = new PHPZip();
        $files = [
            $file_name,
        ];
        $file_name = $site_config['torrent_dir'] . '/' . $row['name'] . '.zip';
        $zip->Zip($files, $file_name);
        $zip->forceDownload($file_name);
        unlink($site_config['torrent_dir'] . '/' . $row['name'] . '.torrent');
        unlink($site_config['torrent_dir'] . '/' . $row['name'] . '.zip');
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
