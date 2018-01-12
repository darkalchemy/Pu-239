<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'ann_config.php';
require_once INCL_DIR . 'ann_functions.php';
global $site_config, $cache, $fluent;

if (isset($_SERVER['HTTP_COOKIE']) || isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
    die("It takes 46 muscles to frown but only 4 to flip 'em the bird.");
}
if (XBT_TRACKER == true) {
    err('Please redownload this torrent from the tracker');
}
$parts = [];
if (!isset($_GET['torrent_pass']) or !preg_match('/^[0-9a-fA-F]{32}$/i', $_GET['torrent_pass'], $parts)) {
    err('Invalid Torrent Pass');
} else {
    $GLOBALS['torrent_pass'] = $parts[0];
}

extract($_GET);
foreach (['torrent_pass', 'info_hash', 'peer_id', 'port', 'downloaded', 'uploaded', 'left', 'compact'] as $x) {
    if (!isset($$x)) {
        err("Missing key: $x");
    }
}
foreach (['info_hash', 'peer_id'] as $x) {
    if (strlen($$x) != 20) {
        err("Invalid $x (" . strlen($$x) . ' - ' . urlencode($$x) . ')');
    }
}
unset($x);
$realip = $ip = $_SERVER['REMOTE_ADDR'];
$port = (int)$port;
$downloaded = (int)$downloaded;
$uploaded = (int)$uploaded;
$left = (int)$left;
$rsize = 30;
foreach (['num want', 'numwant', 'num_want'] as $x) {
    if (isset($$x)) {
        $rsize = (int)$$x;
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
if (!isset($event)) {
    $event = '';
}
$seeder = ($left === 0) ? 'yes' : 'no';
$user = get_user_from_torrent_pass($torrent_pass);
if (!$user) {
    err('Invalid torrent_pass. Please redownload the torrent from ' . $site_config['baseurl']);
}
$userid = (int)$user['id'];
$user['perms'] = (int)$user['perms'];
if ($user['enabled'] == 'no') {
    err("Permission denied, you're account is disabled");
}

$connectable = 'yes';
$conn_ttl = 900;
if (portblacklisted($port)) {
    err("Port $port is blacklisted.");
} elseif ($site_config['connectable_check']) {
    $connkey = 'connectable::' . $realip . '::' . $port;
    $connectable = $cache->get($connkey);
    if ($connectable === false || is_null($connectable)) {
        $sockres = @fsockopen($ip, $port, $errno, $errstr, 5);
        if (!$sockres) {
            $connectable = 'no';
            $conn_ttl = 15;
        } else {
            @fclose($sockres);
        }
        $cache->set($connkey, $connectable, $conn_ttl);
    }
}

if ($connectable === 'no' && REQUIRE_CONNECTABLE) {
    $msg = "Your IP:PORT({$realip}:{$port}) does not appear to be open and/or properly forwarded. Please visit https://portforward.com/ and review their guides for port forwarding.";
    err($msg);
}

if (IP_LOGGING) {
    $no_log_ip = ($user['perms'] & bt_options::PERMS_NO_IP);
    if ($no_log_ip) {
        $connectable = 'no';
        $ip = '127.0.0.1';
    }
    if (!$no_log_ip) {
        $values = [
            'userid' => $userid,
            'ip' => inet_pton($ip),
            'lastannounce' => TIME_NOW,
            'type' => 'announce'
        ];
        $update_values = [
            'lastannounce' => TIME_NOW,
            'type' => 'announce'
        ];
        $fluent->insertInto('ips', $values)
            ->onDuplicateKeyUpdate($update_values)
            ->execute();
        $cache->delete('ip_history_' . $userid);
    }
}
$torrent = get_torrent_from_hash($info_hash, $userid);
if (!$torrent) {
    err('Torrent not found - contact site staff');
}

$torrentid = (int)$torrent['id'];
$torrent_modifier = get_slots($torrentid, $userid);
$torrent['freeslot'] = $torrent_modifier['freeslot'];
$torrent['doubleslot'] = $torrent_modifier['doubleslot'];
$happy_multiplier = ($site_config['happy_hour'] ? get_happy($torrentid, $userid) : 0);

$wantseeds = '';
if ($seeder == 'yes') {
    $wantseeds = 'seeder = "no" AND ';
}

$res = $fluent->from('peers')
    ->select(null)
    ->select('seeder')
    ->select('peer_id')
    ->select('INET6_NTOA(ip) AS ip')
    ->select('port')
    ->select('uploaded')
    ->select('downloaded')
    ->select('userid')
    ->select('last_action')
    ->select('(UNIX_TIMESTAMP(NOW()) - last_action) AS announcetime')
    ->select('last_action AS ts')
    ->select('UNIX_TIMESTAMP(NOW()) AS nowts')
    ->select('prev_action AS prevts')
    ->where($wantseeds . 'torrent = ?', $torrentid)
    ->orderBy('RAND()')
    ->limit($rsize)
    ->fetchAll();
unset($wantseeds);

if ($compact != 1) {
    $resp = 'd' . benc_str('interval') . 'i' . $site_config['announce_interval'] . 'e' . benc_str('private') . 'i1e' . benc_str('peers') . 'l';
} else {
    $resp = 'd' . benc_str('interval') . 'i' . $site_config['announce_interval'] . 'e' . benc_str('private') . 'i1e' . benc_str('min interval') . 'i' . 300 . 'e5:' . 'peers';
}

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
        if (!$_GET['no_peer_id']) {
            $resp .= benc_str('peer id') . benc_str($row['peer_id']);
        }
        $resp .= benc_str('port') . 'i' . $row['port'] . 'e' . 'e';
    } else {
        $peer_ip = explode('.', $row['ip']);
        $peer_ip = pack('C*', $peer_ip[0], $peer_ip[1], $peer_ip[2], $peer_ip[3]);
        $peer_port = pack('n*', (int)$row['port']);
        $time = intval((TIME_NOW % 7680) / 60);
        if ($_GET['left'] == 0) {
            $time += 128;
        }
        $time = pack('C', $time);
        $peer[] = $time . $peer_ip . $peer_port;
        $peer_num++;
    }
}

if ($compact != 1) {
    $resp .= 'ee';
} else {
    $o = '';
    for ($i = 0; $i < $peer_num; ++$i) {
        $o .= substr($peer[ $i ], 1, 6);
    }
    $resp .= strlen($o) . ':' . $o . 'e';
}
if (!isset($self)) {
    $row = $fluent->from('peers')
        ->select(null)
        ->select('seeder')
        ->select('peer_id')
        ->select('INET6_NTOA(ip) AS ip')
        ->select('port')
        ->select('uploaded')
        ->select('downloaded')
        ->select('userid')
        ->select('last_action')
        ->select('(UNIX_TIMESTAMP(NOW()) - last_action) AS announcetime')
        ->select('last_action AS ts')
        ->select('UNIX_TIMESTAMP(NOW()) AS nowts')
        ->select('prev_action AS prevts')
        ->where('(peer_id = ? OR peer_id = ?)', $peer_id, preg_replace('/ *$/s', '', $peer_id))
        ->where('torrent = ?', $torrentid)
        ->where('(peer_id = ? OR peer_id = ?)', $peer_id, preg_replace('/ *$/s', '', $peer_id))
        ->fetch();

    if ($row) {
        $userid = (int)$row['userid'];
        $self = $row;
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
        err('This client is banned. Please use rTorrent, deluge, Transmission, uTorrent 2.2.1+ or any other modern torrent client.');
    }
}

$announce_wait = 30;

if (isset($self) && $self['prevts'] > ($self['nowts'] - $announce_wait)) {
    //err('There is a minimum announce time of ' . $announce_wait . ' seconds');
}
if ($left > 0 && $torrent['vip'] == 1 && $user['class'] < UC_VIP) {
    err('VIP Access Required, You must be a VIP In order to view details or download this torrent! You may become a Vip By Donating to our site. Donating ensures we stay online to provide you with more Excellent Torrents!');
}

if (!isset($self)) {
    $row = $fluent->from('peers')
        ->select(null)
        ->select('COUNT(*) AS count')
        ->where('torrent = ?', $torrentid)
        ->where('torrent_pass = ?', $torrent_pass)
        ->fetch();

    if ($row['count'] >= 3) {
        err('Connection limit exceeded!');
    }
} else {
    $upthis = max(0, $uploaded - $self['uploaded']);
    $downthis = max(0, $downloaded - $self['downloaded']);
    //== happyhour
    if ($happy_multiplier) {
        $upthis = $upthis * $happy_multiplier;
        $downthis = 0;
    }
    $contribution = $cache->get('freecontribution_');
    if ($contribution === false || is_null($contribution)) {
        $contribution_fields_ar_int = [
            'startTime',
            'endTime',
        ];
        $contribution_fields_ar_str = [
            'freeleechEnabled',
            'duploadEnabled',
            'hdownEnabled',
        ];

        $contribution = $fluent->from('events')
            ->select(null)
            ->select('startTime')
            ->select('endTime')
            ->select('freeleechEnabled')
            ->select('duploadEnabled')
            ->select('hdownEnabled')
            ->orderBy('startTime')
            ->limit(1)
            ->fetch();

        $cache->set('freecontribution_', $contribution, $site_config['expires']['contribution']);
    }
    if ($contribution['startTime'] < TIME_NOW && $contribution['endTime'] > TIME_NOW) {
        if ($contribution['freeleechEnabled'] == 1) {
            $downthis = 0;
        }
        if ($contribution['duploadEnabled'] == 1) {
            $upthis = $upthis * 2;
            $downthis = 0;
        }
        if ($contribution['hdownEnabled'] == 1) {
            $downthis = $downthis / 2;
        }
    }
    if ($upthis > 0 || $downthis > 0) {
        $isfree = $isdouble = $issilver = '';
        include CACHE_DIR . 'free_cache.php';
        if (isset($free)) {
            foreach ($free as $fl) {
                $isfree = ($fl['modifier'] == 1 || $fl['modifier'] == 3) && $fl['expires'] > TIME_NOW;
                $isdouble = ($fl['modifier'] == 2 || $fl['modifier'] == 3) && $fl['expires'] > TIME_NOW;
                $issilver = ($fl['modifier'] == 4) && $fl['expires'] > TIME_NOW;
            }
        }
        //== Silver torrents
        if ($torrent['silver'] != 0 || $issilver) {
            $downthis = $downthis / 2;
        }

        $RatioFreeCondition = ($site_config['ratio_free'] ? 'downloaded + 0' : "downloaded + $downthis");
        $crazyhour_on = ($site_config['crazy_hour'] ? crazyhour_announce() : false);
        if ($downthis > 0) {
            if (!($crazyhour_on || $isfree || $user['free_switch'] != 0 || $torrent['free'] != 0 || $torrent['vip'] != 0 || ($torrent['freeslot'] != 0))) {
                $user_updateset['downloaded'] = $RatioFreeCondition;
            }
        }
        if ($upthis > 0) {
            if (!$crazyhour_on) {
                $user_updateset['uploaded'] = 'uploaded + ' . (($torrent['doubleslot'] != 0 || $isdouble) ? ($upthis * 2) : $upthis);
            } else {
                $user_updateset['uploaded'] = "uploaded + ($upthis * 3)";
            }
        }
    }
    if ($user['highspeed'] == 'no' && $upthis > 103872) {
        $diff = (TIME_NOW - $self['ts']);
        $rate = ($upthis / ($diff + 1));
        $last_up = (int)$user['uploaded'];
        //=== about 5 MB/s
        if ($rate > 503872) {
            auto_enter_abnormal_upload($userid, $rate, $upthis, $diff, $torrentid, $agent, $realip, $last_up);
        }
    }
}

$a = $fluent->from('snatched')
    ->select(null)
    ->select('seedtime')
    ->select('uploaded')
    ->select('downloaded')
    ->select('finished')
    ->select('start_date AS start_snatch')
    ->where('torrentid = ?', $torrentid)
    ->where('userid = ?', $userid)
    ->where('userid = 37')
    ->fetch();

if (empty($a)) {
    $values = [
        'torrentid'   => $torrentid,
        'userid'      => $userid,
        'peer_id'     => $peer_id,
        'ip'          => inet_pton($realip),
        'port'        => $port,
        'connectable' => $connectable,
        'uploaded'    => $uploaded,
        'downloaded'  => $site_config['ratio_free'] ? 0 : $downloaded,
        'to_go'       => $left,
        'start_date'  => TIME_NOW,
        'last_action' => TIME_NOW,
        'seeder'      => $seeder,
        'agent'       => $agent,
    ];

    if ($seeder == 'no') {
        $fluent->insertInto('snatched', $values)
            ->execute();
    } else {
        $values1 = [
            'seeder'        => 'yes',
            'complete_date' => TIME_NOW,
            'finished'      => 'yes',
        ];
        $values = array_merge($values, $values1);
        $fluent->insertInto('snatched', $values)
            ->execute();
    }
}
if (isset($self) && $event == 'stopped') {
    $seeder = 'no';
    $delete_count = $fluent->deleteFrom('peers')
        ->where('torrent = ?', $torrentid)
        ->where('(peer_id = ? OR peer_id = ?)', $peer_id, preg_replace('/ *$/s', '', $peer_id))
        ->execute();

    if (($a['uploaded'] + $upthis) < ($a['downloaded'] + $downthis) && $a['finished'] == 'yes') {
        $HnR_time_seeded = ($a['seedtime'] + $self['announcetime']);
        switch (true) {
            case $user['class'] <= $site_config['firstclass']:
                $days_3 = $site_config['_3day_first'] * 3600;
                $days_14 = $site_config['_14day_first'] * 3600;
                $days_over_14 = $site_config['_14day_over_first'] * 3600;
                break;

            case $user['class'] < $site_config['secondclass']:
                $days_3 = $site_config['_3day_second'] * 3600;
                $days_14 = $site_config['_14day_second'] * 3600;
                $days_over_14 = $site_config['_14day_over_second'] * 3600;
                break;

            case $user['class'] >= $site_config['thirdclass']:
                $days_3 = $site_config['_3day_third'] * 3600;
                $days_14 = $site_config['_14day_third'] * 3600;
                $days_over_14 = $site_config['_14day_over_third'] * 3600;
                break;

            default:
                $days_3 = 0;
                $days_14 = 0;
                $days_over_14 = 0;
        }
        switch (true) {
            case ($a['start_snatch'] - $torrent['ts']) < $site_config['torrentage1'] * 86400:
                $minus_ratio = ($days_3 - $HnR_time_seeded);
                break;

            case ($a['start_snatch'] - $torrent['ts']) < $site_config['torrentage2'] * 86400:
                $minus_ratio = ($days_14 - $HnR_time_seeded);
                break;

            case ($a['start_snatch'] - $torrent['ts']) >= $site_config['torrentage3'] * 86400:
                $minus_ratio = ($days_over_14 - $HnR_time_seeded);
                break;

            default:
                $minus_ratio = 0;
        }
        if ($site_config['hnr_online'] == 1 && $minus_ratio > 0 && ($a['uploaded'] + $upthis) < ($a['downloaded'] + $downthis)) {
            $hit_and_run = TIME_NOW;
            $seeder = 'no';
        } else {
            $hit_and_run = 0;
        }
    } else {
        $hit_and_run = 0;
    }
    if ($delete_count >= 1) {
        if ($self['seeder'] == 'yes') {
            adjust_torrent_peers($torrentid, -1, 0, 0);
        } else {
            adjust_torrent_peers($torrentid, 0, -1, 0);
        }
        if ($self['seeder'] == 'yes') {
            $torrent_updateset['seeders'] = new Envms\FluentPDO\Literal('seeders - 1');
        } else {
            $torrent_updateset['leechers'] = new Envms\FluentPDO\Literal('leechers - 1');
        }
        if ($a) {
            $snatch_updateset['ip'] = inet_pton($realip);
            $snatch_updateset['port'] = $port;
            $snatch_updateset['connectable'] = $connectable;
            $snatch_updateset['uploaded'] = "uploaded + $upthis";
            $snatch_updateset['downloaded'] = $site_config['ratio_free'] ? 'downloaded + 0' : "downloaded + $downthis";
            $snatch_updateset['to_go'] = $left;
            $snatch_updateset['upspeed'] = $upthis > 0 ? "$upthis / {$self['announcetime']}" : 0;
            $snatch_updateset['downspeed'] = $downthis > 0 ? "$downthis / {$self['announcetime']}" : 0;
            if ($self['seeder'] == 'yes') {
                $snatch_updateset['seedtime'] = "seedtime + {$self['announcetime']}";
            } else {
                $snatch_updateset['leechtime'] = "leechtime + {$self['announcetime']}";
            }
            $snatch_updateset['last_action'] = TIME_NOW;
            $snatch_updateset['seeder'] = $seeder;
            $snatch_updateset['agent'] = $agent;
            $snatch_updateset['hit_and_run'] = $hit_and_run;
        }
    }
} elseif (isset($self)) {
    $set = [];
    if ($event == 'completed') {
        if ($a) {
            $snatch_updateset['complete_date'] = TIME_NOW;
            $snatch_updateset['finished'] = 'yes';
        }
        $torrent_updateset['times_completed'] = new Envms\FluentPDO\Literal('times_completed + 1');
        $set = [
            'finishedat' => TIME_NOW,
        ];
        adjust_torrent_peers($torrentid, 0, 0, 1);
    }
    $prev_action = $self['ts'];
    $set = array_merge($set, [
        'connectable' => $connectable,
        'uploaded'    => $uploaded,
        'to_go'       => $left,
        'last_action' => TIME_NOW,
        'prev_action' => $prev_action,
        'seeder'      => $seeder,
        'agent'       => $agent,
        'downloaded'  => $site_config['ratio_free'] ? 0 : $downloaded,
    ]);
    $updated = $fluent->update('peers')
        ->set($set)
        ->where('torrent = ?', $torrentid)
        ->where('(peer_id = ? OR peer_id = ?)', $peer_id, preg_replace('/ *$/s', '', $peer_id))
        ->execute();

    if ($updated >= 1) {
        if ($seeder != $self['seeder']) {
            if ($seeder == 'yes') {
                adjust_torrent_peers($torrentid, 1, -1, 0);
            } else {
                adjust_torrent_peers($torrentid, -1, 1, 0);
            }
            if ($seeder == 'yes') {
                $torrent_updateset['seeders'] = new Envms\FluentPDO\Literal('seeders + 1');
                $torrent_updateset['leechers'] = new Envms\FluentPDO\Literal('leechers - 1');
            } else {
                $torrent_updateset['seeders'] = new Envms\FluentPDO\Literal('seeders - 1');
                $torrent_updateset['leechers'] = new Envms\FluentPDO\Literal('leechers + 1');
            }
        }
        if ($a) {
            $snatch_updateset['ip'] = inet_pton($realip);
            $snatch_updateset['port'] = $port;
            $snatch_updateset['connectable'] = $connectable;
            $snatch_updateset['uploaded'] = "uploaded + $upthis";
            $snatch_updateset['downloaded'] = $site_config['ratio_free'] ? 'downloaded + 0' : "downloaded + $downthis";
            $snatch_updateset['to_go'] = $left;
            $snatch_updateset['upspeed'] = $upthis > 0 ? "$upthis / {$self['announcetime']}" : 0;
            $snatch_updateset['downspeed'] = $downthis > 0 ? "$downthis / {$self['announcetime']}" : 0;
            if ($self['seeder'] == 'yes') {
                $snatch_updateset['seedtime'] = "seedtime + {$self['announcetime']}";
            } else {
                $snatch_updateset['leechtime'] = "leechtime + {$self['announcetime']}";
            }
            $snatch_updateset['last_action'] = TIME_NOW;
            $snatch_updateset['seeder'] = $seeder;
            $snatch_updateset['agent'] = $agent;
            $snatch_updateset['timesann'] = new Envms\FluentPDO\Literal('timesann + 1');
        }
    }
} else {
    if ($user['parked'] == 'yes') {
        err('Your account is parked! (Read the FAQ)');
    } elseif ($user['downloadpos'] != 1 || $user['hnrwarn'] == 'yes' and $seeder != 'yes') {
        err('Your downloading privileges have been disabled! (Read the rules)');
    }
    $values = [
        'torrent'        => $torrentid,
        'userid'         => $userid,
        'peer_id'        => $peer_id,
        'ip'             => inet_pton($realip),
        'port'           => $port,
        'connectable'    => $connectable,
        'uploaded'       => $uploaded,
        'downloaded'     => ($site_config['ratio_free'] ? 0 : $downloaded),
        'to_go'          => $left,
        'started'        => TIME_NOW,
        'last_action'    => TIME_NOW,
        'seeder'         => $seeder,
        'agent'          => $agent,
        'downloadoffset' => ($site_config['ratio_free'] ? 0 : $downloaded),
        'uploadoffset'   => $uploaded,
        'torrent_pass'   => $torrent_pass,
    ];

    $update_values = [
        'userid'      => $userid,
        'port'        => $port,
        'connectable' => $connectable,
        'uploaded'    => $uploaded,
        'downloaded'  => ($site_config['ratio_free'] ? 0 : $downloaded),
        'to_go'       => $left,
        'last_action' => TIME_NOW,
        'seeder'      => $seeder,
        'agent'       => $agent,
    ];


    $insert_peers = $fluent->insertInto('peers', $values)
        ->ignore()
        ->execute();
    echo $insert_peers;
    if ($insert_peers == 0) {
        $fluent->update('peers')
            ->set($update_values)
            ->execute();
    } else {
        if ($seeder == 'yes') {
            $torrent_updateset['seeders'] = new Envms\FluentPDO\Literal('seeders + 1');
        } else {
            $torrent_updateset['leechers'] = new Envms\FluentPDO\Literal('leechers + 1');
        }
        if ($seeder == 'yes') {
            adjust_torrent_peers($torrentid, 1, 0, 0);
        } else {
            adjust_torrent_peers($torrentid, 0, 1, 0);
        }
        if ($a) {
            $snatch_updateset['ip'] = inet_pton($realip);
            $snatch_updateset['port'] = $port;
            $snatch_updateset['connectable'] = $connectable;
            $snatch_updateset['to_go'] = $left;
            $snatch_updateset['last_action'] = TIME_NOW;
            $snatch_updateset['seeder'] = $seeder;
            $snatch_updateset['agent'] = $agent;
            $snatch_updateset['hit_and_run'] = 0;
            $snatch_updateset['timesann'] = new Envms\FluentPDO\Literal('timesann + 1');
            $snatch_updateset['mark_of_cain'] = 'no';
        }
    }
}

if ($seeder == 'yes') {
    if ($torrent['banned'] != 'yes') {
        $torrent_updateset['visible'] = 'yes';
    }
    $torrent_updateset['last_action'] = TIME_NOW;
    $cache->update_row('torrent_details_' . $torrentid, [
        'visible' => 'yes',
    ], $site_config['expires']['torrent_details']);
    $cache->update_row('last_action_' . $torrentid, [
        'lastseed' => TIME_NOW,
    ], 1800);
}
if (!empty($torrent_updateset)) {
    $fluent->update('torrents')
        ->set($torrent_updateset)
        ->where('id = ?', $torrentid)
        ->execute();
}
if (!empty($snatch_updateset)) {
    $fluent->update('snatched')
        ->set($snatch_updateset)
        ->where('torrentid = ?', $torrentid)
        ->where('userid = ?', $userid)
        ->execute();
}
if (!empty($user_updateset)) {
    $fluent->update('users')
        ->set($user_updateset)
        ->where('id = ?', $userid)
        ->execute();

    $cache->delete('userstats_' . $userid);
    $cache->delete('user_stats_' . $userid);
}
benc_resp_raw($resp);
