<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\IP;
use Pu239\Peer;
use Pu239\Snatched;
use Pu239\Torrent;
use Pu239\User;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ann_config.php';
require_once INCL_DIR . 'function_announce.php';
if (isset($_SERVER['HTTP_COOKIE']) || isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
    die("It takes 46 muscles to frown but only 4 to flip 'em the bird.");
}

$dt = TIME_NOW;
$info_hash = $peer_id = $compact = $no_peer_id = '';
$torrent_updateset = $snatched_values = $user_updateset = [];
global $container, $site_config;

$ratio_free = $site_config['site']['ratio_free'];
if (empty($torrent_pass) || !strlen($torrent_pass) === 64) {
    err('Invalid Torrent Pass');
}

foreach ([
    'torrent_pass',
    'info_hash',
    'peer_id',
    'port',
    'downloaded',
    'uploaded',
    'left',
    'compact',
] as $x) {
    if (!isset($_GET[$x])) {
        err("Missing key: $x");
    } else {
        $$x = $_GET[$x];
    }
}

foreach ([
    'info_hash',
    'peer_id',
] as $x) {
    if (strlen(${$x}) != 20) {
        err("Invalid $x (" . strlen(${$x}) . ' - ' . urlencode(${$x}) . ')');
    }
}
unset($x);
$realip = $ip = $_SERVER['REMOTE_ADDR'];
$port = (int) $port;
$downloaded = (int) $downloaded;
$uploaded = (int) $uploaded;
$real_downloaded = $downloaded;
$real_uploaded = $uploaded;
$left = (int) $left;
$rsize = 30;
foreach ([
    'num want',
    'numwant',
    'num_want',
] as $x) {
    if (isset($_GET[$x])) {
        $rsize = (int) ${$x};
        break;
    }
}
if ($uploaded < 0) {
    err('invalid uploaded (less than 0)');
}
if ($downloaded < 0) {
    err('invalid downloaded (less than 0)');
}
if ($left < 0) {
    err('invalid left (less than 0)');
}
if (!$port || $port > 0xffff) {
    err('invalid port');
}
if (!isset($_GET['event'])) {
    $event = '';
}
$seeder = $left === 0 ? 'yes' : 'no';
$torrents_class = $container->get(Torrent::class);
$torrent = $torrents_class->get_torrent_from_hash($info_hash);
if (!$torrent) {
    err('torrent not registered with this tracker');
}
$users_class = $container->get(User::class);
$user = $users_class->get_user_from_torrent_pass($torrent_pass);
$peer_class = $container->get(Peer::class);
if (empty($user)) {
    err('Invalid torrent_pass. Please redownload the torrent from ' . $site_config['paths']['baseurl']);
} elseif ($user['enabled'] === 'no') {
    err("Permission denied, you're account is disabled");
} elseif ($left > 0 && $torrent['vip'] === 1 && $user['class'] < UC_VIP) {
    err('VIP Access Required, You must be a VIP In order to view details or download this torrent! You may become a VIP By Donating to our site. Donating ensures we stay online to provide you with more Excellent Torrents!');
} elseif ($user['parked'] === 'yes') {
    err('Your account is parked! (Read the FAQ)');
} elseif (($user['downloadpos'] != 1 || $user['hnrwarn'] === 'yes') && $seeder != 'yes') {
    err('Your downloading privileges have been disabled! (Read the rules)');
} elseif ($user['class'] === 0 && $seeder === 'no') {
    $count = $peer_class->get_torrent_count($torrent['id'], $torrent_pass, true, $peer_id);
    if ($count > 3) {
        err('You have reached your limit for active downloads. Only 3 active downloads at one time are allowed for this user class.');
    }
} elseif ($site_config['site']['require_credit'] && ($seeder === 'no' && ($torrent['size'] > ($user['uploaded'] - $user['downloaded'])))) {
    err('You do not have enough upload credit to download this torrent.');
}
$userid = $user['id'];
$connectable = 'yes';
$conn_ttl = 300;
$cache = $container->get(Cache::class);
if (portblacklisted($port)) {
    err("Port $port is blacklisted.");
} elseif ($site_config['tracker']['connectable_check']) {
    $connkey = 'connectable_' . $realip . '_' . $port;
    $connectable = $cache->get($connkey);
    if ($connectable === false || is_null($connectable)) {
        $sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
        if (!$sockres) {
            $connectable = 'no';
            $conn_ttl = 15;
        } else {
            $connectable = 'yes';
            @fclose($sockres);
        }
        $cache->set($connkey, $connectable, $conn_ttl);
    }
}
if ($site_config['tracker']['require_connectable'] && $connectable === 'no') {
    err("Your IP:PORT({$realip}:{$port}) does not appear to be open and/or properly forwarded. Please visit https://portforward.com/ and review their guides for port forwarding.");
}
if ($site_config['site']['ip_logging']) {
    $no_log_ip = $user['perms'] & PERMS_NO_IP;
    if ($no_log_ip) {
        $connectable = 'no';
        $ip = '127.0.0.1';
    }
    $added = get_date(TIME_NOW, 'MYSQL', 1, 0);
    if (!$no_log_ip) {
        $values = [
            'ip' => $ip,
            'userid' => $userid,
            'type' => 'announce',
            'last_access' => $added,
        ];
        $update = [
            'last_access' => $added,
        ];
        $ips_class = $container->get(IP::class);
        $ips_class->insert($values, $update, $userid);
        unset($values, $update);
    }
}

$torrent_modifier = get_slots($torrent['id'], $userid);
$torrent['freeslot'] = $torrent_modifier['freeslot'];
$torrent['doubleslot'] = $torrent_modifier['doubleslot'];
$happy_multiplier = $site_config['bonus']['happy_hour'] ? get_happy($torrent['id'], $userid) : 0;

if ($compact != 1) {
    $resp = 'd' . benc_str('interval') . 'i' . $site_config['tracker']['announce_interval'] . 'e' . benc_str('private') . 'i1e' . benc_str('peers') . 'l';
} else {
    $resp = 'd' . benc_str('interval') . 'i' . $site_config['tracker']['announce_interval'] . 'e' . benc_str('private') . 'i1e' . benc_str('min interval') . 'i' . 300 . 'e5:' . 'peers';
}
$peers = $peer_class->get_torrent_peers_by_tid($torrent['id']);
$res = $this_user_torrent = [];
foreach ($peers as $peer) {
    if ($port != $peer['port'] || $realip != $peer['ip']) {
        if ($seeder === 'yes' && $peer['seeder'] === 'no') {
            $res[] = $peer;
        } elseif ($seeder === 'no') {
            $res[] = $peer;
        }
    } elseif ($port == $peer['port'] && $realip == $peer['ip'] && $peer['peer_id'] == $peer_id) {
        $this_user_torrent = $peer;
    }
}
shuffle($res);
$res = array_slice($res, 0, $rsize);

$peer = [];
$peer_num = 0;
foreach ($res as $row) {
    if ($compact != 1) {
        $row['peer_id'] = str_pad($row['peer_id'], 20);
        if ($row['peer_id'] === $peer_id) {
            $self = $row;
            continue;
        }
        $resp .= 'd' . benc_str('ip') . benc_str($row['ip']);
        if (!$no_peer_id) {
            $resp .= benc_str('peer id') . benc_str($row['peer_id']);
        }
        $resp .= benc_str('port') . 'i' . $row['port'] . 'e' . 'e';
    } else {
        $peer_ip = explode('.', $row['ip']);
        $peer_ip = pack('C*', $peer_ip[0], $peer_ip[1], $peer_ip[2], $peer_ip[3]);
        $peer_port = pack('n*', (int) $row['port']);
        $time = (int) ($dt % 7680 / 60);
        if ($left == 0) {
            $time += 128;
        }
        $time = pack('C', $time);
        $peer[] = $time . $peer_ip . $peer_port;
        ++$peer_num;
    }
}
if ($compact != 1) {
    $resp .= 'ee';
} else {
    $o = '';
    for ($i = 0; $i < $peer_num; ++$i) {
        $o .= substr($peer[$i], 1, 6);
    }
    $resp .= strlen($o) . ':' . $o . 'e';
}

if (!isset($self)) {
    foreach ($peers as $peer) {
        if (strtolower($peer['peer_id']) === strtolower($peer_id) || strtolower($peer['peer_id']) === strtolower(preg_replace('/ *$/s', '', $peer_id))) {
            $userid = $peer['userid'];
            $self = $peer;
        }
    }
}
$useragent = substr($peer_id, 0, 8);
$agentarray = [
    'R34',
    '-AZ21',
    '-AZ22',
    '-AZ24',
    'AZ2500BT',
    'BS',
    'exbc',
    '-TS',
    'Mbrst',
    '-BB',
    '-SZ',
    'XBT',
    'turbo',
    'A301',
    'A310',
    '-UT11',
    '-UT12',
    '-UT13',
    '-UT14',
    '-UT15',
    'FUTB',
    '-BC',
    'LIME',
    'eX',
    '-ML',
    'FRS',
    '-AG',
];
foreach ($agentarray as $bannedclient) {
    if (strpos($useragent, $bannedclient) !== false) {
        err('This client is banned. Please use rTorrent, qBitTorrent, deluge, Transmission, uTorrent 2.2.1+ or any other modern torrent client.');
    }
}
$announce_wait = $site_config['tracker']['min_interval'];
if (isset($self) && $self['prevts'] > ($self['nowts'] - $announce_wait)) {
    err("There is a minimum announce time of $announce_wait seconds");
}
if (!isset($self)) {
    $count = $peer_class->get_torrent_count($torrent['id'], $torrent_pass, false, $peer_id);
    if ($count > 1) {
        err('Connection limit exceeded!');
    }
} else {
    $upthis = max(0, $uploaded - $self['uploaded']);
    $downthis = max(0, $downloaded - $self['downloaded']);
    if ($user['highspeed'] === 'no' && $upthis > 5000000) {
        $diff = $dt - $self['ts'];
        $rate = $upthis / ($diff + 1);
        $last_up = (int) $user['uploaded'];
        if ($rate > 5000000) {
            auto_enter_abnormal_upload($userid, $rate, $upthis, $diff, $torrent['id'], $agent, $realip, $last_up);
        }
    }
    if ($happy_multiplier) {
        $upthis = $upthis * $happy_multiplier;
        $downthis = 0;
    }
    if ($upthis > 0 || $downthis > 0) {
        $isfree = $isdouble = $issilver = false;
        $free = $cache->get('site_event_');
        if (!empty($free)) {
            if (($free['modifier'] === 1 || $free['modifier'] === 3) && $free['expires'] > $dt) {
                $isfree = true;
                $downthis = 0;
            }
            if (($free['modifier'] === 2 || $free['modifier'] === 3) && $free['expires'] > $dt) {
                $isdouble = true;
                $upthis = $upthis * 2;
            }
            if (($free['modifier'] === 4) && $free['expires'] > $dt) {
                $issilver = true;
                $downthis = $downthis / 2;
            }
        }
        if ($torrent['silver'] != 0 || $issilver) {
            $downthis = $downthis / 2;
        }
        $crazyhour_on = ($site_config['bonus']['crazy_hour'] ? crazyhour_announce() : false);
        if ($downthis > 0) {
            if (!($crazyhour_on || $isfree || $user['free_switch'] != 0 || $torrent['free'] != 0 || $torrent['vip'] != 0 || ($torrent['freeslot'] != 0))) {
                $user_updateset['downloaded'] = $user['downloaded'] + ($ratio_free ? 0 : $downthis);
            }
        }
        if ($upthis > 0) {
            if (!$crazyhour_on) {
                $user_updateset['uploaded'] = $user['uploaded'] + ($torrent['doubleslot'] != 0 || $isdouble ? ($upthis * 2) : $upthis);
            } else {
                $user_updateset['uploaded'] = $user['uploaded'] + ($upthis * 3);
            }
        }
    }
}
$snatches = $container->get(Snatched::class);
$snatched = $snatches->get_snatched($userid, $torrent['id']);
$snatched_values['torrentid'] = $torrent['id'];
$snatched_values['userid'] = $userid;
$snatched_values['uploaded'] = $uploaded;
$snatched_values['downloaded'] = $ratio_free ? 0 : $downloaded;
$snatched_values['real_uploaded'] = $real_uploaded;
$snatched_values['real_downloaded'] = $real_downloaded;
$snatched_values['to_go'] = $left;
$snatched_values['start_date'] = $dt;
$snatched_values['last_action'] = $dt;
$snatched_values['seeder'] = $seeder;
$snatched_values['timesann'] = 1;
if ($seeder === 'yes') {
    $snatched_values['complete_date'] = $dt;
    $snatched_values['finished'] = 'yes';
}
$peer_deleted = false;
if ($event === 'stopped') {
    if (!empty($this_user_torrent['id'])) {
        $peer_deleted = $peer_class->delete_by_id($this_user_torrent['id'], $torrent['id'], $info_hash);
        $cache->delete('peers_' . $userid);
    }
}
if (isset($self) && $event === 'stopped') {
    $seeder = 'no';
    $self['announcetime'] = $self['announcetime'] > 0 ? $self['announcetime'] : 1;
    if ($peer_deleted) {
        if ($self['seeder'] === 'yes') {
            $torrents_class->adjust_torrent_peers($torrent['id'], -1, 0, 0);
        } else {
            $torrents_class->adjust_torrent_peers($torrent['id'], 0, -1, 0);
        }
        if (!empty($snatched)) {
            $snatched_values['uploaded'] = $snatched['uploaded'] + $upthis;
            $snatched_values['downloaded'] = $snatched['downloaded'] + ($ratio_free ? 0 : $downthis);
            $snatched_values['to_go'] = $left;
            $snatched_values['upspeed'] = $upthis > 0 ? $upthis / $self['announcetime'] : 0;
            $snatched_values['downspeed'] = $downthis > 0 ? $downthis / $self['announcetime'] : 0;
            if ($self['seeder'] === 'yes') {
                $snatched_values['seedtime'] = $snatched['seedtime'] + $self['announcetime'];
            } else {
                $snatched_values['leechtime'] = $snatched['leechtime'] = $self['announcetime'];
            }
            $snatched_values['last_action'] = $dt;
            $snatched_values['seeder'] = $seeder;
        }
    }
} elseif (isset($self)) {
    $set = [];
    if ($event === 'completed') {
        if (!empty($snatched)) {
            $snatched_values['complete_date'] = $dt;
            $snatched_values['finished'] = 'yes';
        }
        $torrent_updateset['times_completed'] = $torrent['times_completed'] + 1;
        $set = [
            'finishedat' => $dt,
        ];
        $torrents_class->adjust_torrent_peers($torrent['id'], 0, 0, 1);
    }
    $prev_action = $self['ts'];
    $values = array_merge($set, [
        'connectable' => $connectable,
        'uploaded' => $uploaded,
        'to_go' => $left,
        'last_action' => $dt,
        'prev_action' => $prev_action,
        'seeder' => $seeder,
        'agent' => $agent,
        'downloaded' => $ratio_free ? 0 : $downloaded,
    ]);
    $update = $values;
    $values['torrent'] = $torrent['id'];
    $values['peer_id'] = $peer_id;
    $values['ip'] = $realip;
    $values['port'] = $port;
    $values['userid'] = $userid;
    $values['torrent_pass'] = $torrent_pass;
    $updated = $peer_class->insert_update($values, $update);
    unset($values, $update);
    $cache->delete('peers_' . $userid);
    if (!empty($updated)) {
        if ($seeder != $self['seeder']) {
            if ($seeder === 'yes') {
                $torrents_class->adjust_torrent_peers($torrent['id'], 1, -1, 0);
            } else {
                $torrents_class->adjust_torrent_peers($torrent['id'], -1, 1, 0);
            }
        }
        if (!empty($snatched)) {
            $snatched_values['uploaded'] = $snatched['uploaded'] + $upthis;
            $snatched_values['downloaded'] = $snatched['downloaded'] + ($ratio_free ? 0 : $downthis);
            $snatched_values['to_go'] = $left;
            $snatched_values['upspeed'] = $upthis > 0 ? $upthis / $self['announcetime'] : 0;
            $snatched_values['downspeed'] = $downthis > 0 && $self['announcetime'] > 0 ? $downthis / $self['announcetime'] : 0;
            if ($self['seeder'] === 'yes') {
                $snatched_values['seedtime'] = $snatched['seedtime'] + $self['announcetime'];
            } else {
                $snatched_values['leechtime'] = $snatched['leechtime'] + $self['announcetime'];
            }
            $snatched_values['last_action'] = $dt;
            $snatched_values['seeder'] = $seeder;
            $snatched_values['timesann'] = isset($snatched['timesann']) ? $snatched['timesann'] + 1 : 1;
        }
    }
} else {
    $values = [
        'torrent' => $torrent['id'],
        'peer_id' => $peer_id,
        'ip' => $realip,
        'userid' => $userid,
        'port' => $port,
        'connectable' => $connectable,
        'uploaded' => $uploaded,
        'downloaded' => $ratio_free ? 0 : $downloaded,
        'to_go' => $left,
        'started' => $dt,
        'last_action' => $dt,
        'seeder' => $seeder,
        'agent' => $agent,
        'downloadoffset' => $ratio_free ? 0 : $downloaded,
        'uploadoffset' => $uploaded,
        'torrent_pass' => $torrent_pass,
    ];

    $update = [
        'userid' => $userid,
        'connectable' => $connectable,
        'uploaded' => $uploaded,
        'downloaded' => $ratio_free ? 0 : $downloaded,
        'to_go' => $left,
        'last_action' => $dt,
        'seeder' => $seeder,
        'agent' => $agent,
    ];
    $update_id = $peer_class->insert_update($values, $update);
    if (empty($update_id)) {
        if ($seeder === 'yes') {
            $torrents_class->adjust_torrent_peers($torrent['id'], 1, 0, 0);
        } else {
            $torrents_class->adjust_torrent_peers($torrent['id'], 0, 1, 0);
        }
        if (!empty($snatched)) {
            $snatched_values['to_go'] = $left;
            $snatched_values['last_action'] = $dt;
            $snatched_values['seeder'] = $seeder;
            $snatched_values['timesann'] = isset($snatched['timesann']) ? $snatched['timesann'] + 1 : 1;
            $snatched_values['mark_of_cain'] = 'no';
        }
    }
    $cache->delete('peers_' . $userid);
}
if ($seeder === 'yes') {
    if ($torrent['banned'] != 'yes') {
        $torrent_updateset['visible'] = 'yes';
    }
    $torrent_updateset['last_action'] = $dt;
    $cache->update_row('torrent_details_' . $torrent['id'], [
        'visible' => 'yes',
    ], $site_config['expires']['torrent_details']);
    $cache->update_row('last_action_' . $torrent['id'], [
        'lastseed' => $dt,
    ], 1800);
}

if (!empty($torrent_updateset)) {
    $torrents_class->update($torrent_updateset, $torrent['id']);
}
if (!empty($snatched_values)) {
    $update = $snatched_values;
    $snatches->insert($snatched_values, $update);
}
if (!empty($user_updateset)) {
    $users_class->update($user_updateset, $userid, true);
}
benc_resp_raw($resp);
