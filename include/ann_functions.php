<?php

/**
 * @return bool
 */
function crazyhour_announce()
{
    global $cache, $fluent;

    $crazy_hour = (TIME_NOW + 3600);
    $cz['crazyhour'] = $cache->get('crazyhour');
    if ($cz['crazyhour'] === false || is_null($cz['crazyhour'])) {
        $cz['crazyhour'] = $fluent->from('freeleech')
            ->select(null)
            ->select('var')
            ->select('amount')
            ->where('type = ?', 'crazyhour')
            ->fetch();

        if ($cz['crazyhour'] === false) {
            $cz['crazyhour']['var'] = random_int(TIME_NOW, (TIME_NOW + 86400));
            $cz['crazyhour']['amount'] = 0;
            $fluent->update('freeleech')
                ->set([
                    'var' => $cz['crazyhour']['var'],
                    'amount' => $cz['crazyhour']['amount']
                ])
                ->where('type = ?', 'crazyhour')
                ->execute();
        }
        $cache->set('crazyhour', $cz['crazyhour'], 0);
    }

    if ($cz['crazyhour']['var'] < TIME_NOW) {
        if (($cz_lock = $cache->add('crazyhour_lock', 1, 10)) !== false) {
            $cz['crazyhour_new'] = mktime(23, 59, 59, date('m'), date('d'), date('y'));
            $cz['crazyhour']['var'] = random_int($cz['crazyhour_new'], ($cz['crazyhour_new'] + 86400));
            $cz['crazyhour']['amount'] = 0;
            $cz['remaining'] = ($cz['crazyhour']['var'] - TIME_NOW);

            $set = ['var' => $cz['crazyhour']['var'], 'amount' => $cz['crazyhour']['amount']];
            $fluent->update('freeleech')
                ->set($set)
                ->where('type = ?', 'crazyhour')
                ->execute();

            $cache->set('crazyhour', $cz['crazyhour'], 0);

            $msg = 'Next [color=orange][b]Crazyhour[/b][/color] is at ' . date('F j, g:i a', $cz['crazyhour']['var']);
            autoshout($msg);

            $text = 'Next <span style="font-weight:bold;color:orange;">Crazyhour</span> is at ' . date('F j, g:i a', $cz['crazyhour']['var']);
            $values = ['added' => TIME_NOW, 'txt' => $text];
            $fluent->insertInto('sitelog')
                ->values($values)
                ->execute();
        }

        return false;
    } elseif (($cz['crazyhour']['var'] < $crazy_hour) && ($cz['crazyhour']['var'] >= TIME_NOW)) { // if crazyhour
        if ($cz['crazyhour']['amount'] !== 1) {
            $cz['crazyhour']['amount'] = 1;
            if (($cz_lock = $cache->add('crazyhour_lock', 1, 10)) !== false) {

                $set = ['amount' => $cz['crazyhour']['amount']];
                $fluent->update('freeleech')
                    ->set($set)
                    ->where('type = ?', 'crazyhour')
                    ->execute();

                $cache->set('crazyhour', $cz['crazyhour'], 0);

                $msg = 'w00t! It\'s [color=orange][b]Crazyhour[/b][/color] :w00t:';
                autoshout($msg);

                $text = 'w00t! It\'s <span style="font-weight:bold;color:orange;">Crazyhour</span> <img src="./images/smilies/w00t.gif" alt=":w00t:" />';
                $values = ['added' => TIME_NOW, 'txt' => $text];
                $fluent->insertInto('sitelog')
                    ->values($values)
                    ->execute();
            }
        }

        return true;
    } else {
        return false;
    }
}

/**
 * @param $torrent_pass
 *
 * @return array|bool|null|string
 */
function get_user_from_torrent_pass($torrent_pass)
{
    global $cache, $site_config, $fluent;
    if (strlen($torrent_pass) != 32) {
        return false;
    }
    $userid = $cache->get('torrent_pass_' . $torrent_pass);
    if ($userid === false || is_null($userid)) {
        $userid = $fluent->from('users')
            ->select(null)
            ->select('id')
            ->where('torrent_pass = ?', $torrent_pass)
            ->where("enabled = 'yes'")
            ->fetch();
        $userid = $userid['id'];
        $cache->set('torrent_pass_' . $torrent_pass, $userid, 3600);
    }
    if (empty($userid)) {
        return false;
    }
    $user = $cache->get('user' . $userid);
    if ($user === false || is_null($user)) {
        $user = $fluent->from('users')
            ->select('INET6_NTOA(ip) AS ip')
            ->where('id = ?', $userid)
            ->fetch();
        unset($user['hintanswer']);
        unset($user['passhash']);

        $cache->set('user' . $userid, $user, $site_config['expires']['user_cache']);
        if ($user['enabled'] != 'yes') {
            return false;
        }
    }
    if (!$user) {
        return false;
    }

    return $user;
}

/**
 * @param $info_hash
 *
 * @return array|bool|null|string
 */
function get_torrent_from_hash($info_hash)
{
    global $cache, $fluent;
    $key = 'torrent::hash:::' . bin2hex($info_hash);
    $ttl = 21600;
    $torrent = $cache->get($key);
    if ($torrent === false || is_null($torrent)) {
        $torrent = $fluent->from('torrents')
            ->select(null)
            ->select('id')
            ->select('category')
            ->select('banned')
            ->select('free')
            ->select('silver')
            ->select('vip')
            ->select('seeders')
            ->select('leechers')
            ->select('times_completed')
            ->select('seeders + leechers AS numpeers')
            ->select('added AS ts')
            ->select('visible')
            ->where('info_hash = ?', $info_hash)
            ->fetch();
        if ($torrent !== false) {
            $cache->set($key, $torrent, $ttl);
            $seed_key = 'torrents::seeds:::' . $torrent['id'];
            $leech_key = 'torrents::leechs:::' . $torrent['id'];
            $comp_key = 'torrents::comps:::' . $torrent['id'];
            $cache->add($seed_key, $torrent['seeders'], $ttl);
            $cache->add($leech_key, $torrent['leechers'], $ttl);
            $cache->add($comp_key, $torrent['times_completed'], $ttl);
        } else {
            $cache->set($key, 0, 900);
 
           return false;
        }
    } else {
        $seed_key = 'torrents::seeds:::' . $torrent['id'];
        $leech_key = 'torrents::leechs:::' . $torrent['id'];
        $comp_key = 'torrents::comps:::' . $torrent['id'];
        $torrent['seeders'] = $cache->get($seed_key);
        $torrent['leechers'] = $cache->get($leech_key);
        $torrent['times_completed'] = $cache->get($comp_key);
        if (
                $torrent['seeders'] === false ||
                $torrent['leechers'] === false ||
                $torrent['times_completed'] === false ||
                is_null($torrent['seeders']) ||
                is_null($torrent['leechers']) ||
                is_null($torrent['times_completed'])
            ) {
            $res = $fluent->from('torrents')
                ->select(null)
                ->select('seeders')
                ->select('leechers')
                ->select('times_completed')
                ->where('id = ?', $torrent['id'])
                ->fetch();

            if ($res !== false) {
                $cache->add($seed_key, $res['seeders'], $ttl);
                $cache->add($leech_key, $res['leechers'], $ttl);
                $cache->add($comp_key, $res['times_completed'], $ttl);
                $torrent = array_merge($torrent, $res);
                $cache->set($key, $torrent, $ttl);
            } else {
                $cache->delete($key);

                return false;
            }
        }
    }

    return $torrent;
}

/**
 * @param     $id
 * @param int $seeds
 * @param int $leechers
 * @param int $completed
 *
 * @return bool
 */
function adjust_torrent_peers($id, $seeds = 0, $leechers = 0, $completed = 0)
{
    global $cache;
    if (!is_int($id) || $id < 1) {
        return false;
    }
    if (!$seeds && !$leechers && !$completed) {
        return false;
    }
    $adjust = 0;
    $seed_key = 'torrents::seeds:::' . $id;
    $leech_key = 'torrents::leechs:::' . $id;
    $comp_key = 'torrents::comps:::' . $id;
    if ($seeds > 0) {
        $adjust += (bool)$cache->increment($seed_key, $seeds);
    } elseif ($seeds < 0) {
        $adjust += (bool)$cache->decrement($seed_key, -$seeds);
    }
    if ($leechers > 0) {
        $adjust += (bool)$cache->increment($leech_key, $leechers);
    } elseif ($leechers < 0) {
        $adjust += (bool)$cache->decrement($leech_key, -$leechers);
    }
    if ($completed > 0) {
        $adjust += (bool)$cache->increment($comp_key, $completed);
    }

    return (bool)$adjust;
}

/**
 * @param $torrentid
 * @param $userid
 *
 * @return int|mixed
 */
function get_happy($torrentid, $userid)
{
    global $cache, $fluent;
    $keys['happyhour'] = $userid . '_happy';
    //$happy = $cache->get($keys['happyhour']);
    if ($happy === false || is_null($happy)) {
        $res = $fluent->from('happyhour')
            ->where('userid = ?', $userid)
            ->fetchAll();

        $happy = [];
        foreach ($res as $row) {
            $happy[ $row['torrentid'] ] = $row['multiplier'];
        }
        $cache->add($userid . '_happy', $happy, 0);
    }
    if (!empty($happy) && isset($happy[ $torrentid ])) {
        return $happy[ $torrentid ];
    }

    return 0;
}

/**
 * @param $torrentid
 * @param $userid
 *
 * @return mixed
 */
function get_slots($torrentid, $userid)
{
    global $cache, $fluent;

    $ttl_slot = 86400;
    $torrent['freeslot'] = $torrent['doubleslot'] = 0;
    $slot = $cache->get('fllslot_' . $userid);
    if ($slot === false || is_null($slot)) {
        $slot = $fluent->from('freeslots')
                ->where('userid = ?', $userid)
                ->fetchAll();
        $cache->add('fllslot_' . $userid, $slot, $ttl_slot);
    }
    if (!empty($slot)) {
        foreach ($slot as $sl) {
            if ($sl['torrentid'] == $torrentid && $sl['free'] == 'yes') {
                $torrent['freeslot'] = 1;
            }
            if ($sl['torrentid'] == $torrentid && $sl['doubleup'] == 'yes') {
                $torrent['doubleslot'] = 1;
            }
        }
    }

    return $torrent;
}

/**
 * @param $userid
 * @param $rate
 * @param $upthis
 * @param $diff
 * @param $torrentid
 * @param $client
 * @param $realip
 * @param $last_up
 */
function auto_enter_abnormal_upload($userid, $rate, $upthis, $diff, $torrentid, $client, $realip, $last_up)
{
    global $fluent;

    $values = [
            'added' => TIME_NOW, 'userid' => $userid, 'client' => $client, 'rate' => $rate,
            'beforeup' => $last_up, 'upthis' => $upthis, 'timediff' => $diff,
            'userip' => ipToStorageFormat($realip), 'torrentid' => $torrentid
    ];
    $fluent->insertInto('cheaters')
                ->values($values)
                ->execute();

}

/**
 * @param $msg
 */
function err($msg)
{
    benc_resp([
        'failure reason' => [
            'type'  => 'string',
            'value' => $msg,
        ],
    ]);
    exit();
}

/**
 * @param $d
 */
function benc_resp($d)
{
    benc_resp_raw(benc([
        'type'  => 'dictionary',
        'value' => $d,
    ]));
}

/**
 * @param $x
 */
function benc_resp_raw($x)
{
    header('Content-Type: text/plain');
    header('Pragma: no-cache');
    echo $x;
}

/**
 * @param $obj
 *
 * @return string|void
 */
function benc($obj)
{
    if (!is_array($obj) || !isset($obj['type']) || !isset($obj['value'])) {
        return;
    }
    $c = $obj['value'];
    switch ($obj['type']) {
        case 'string':
            return benc_str($c);
        case 'integer':
            return benc_int($c);
        case 'list':
            return benc_list($c);
        case 'dictionary':
            return benc_dict($c);
        default:
            return;
    }
}

/**
 * @param $s
 *
 * @return string
 */
function benc_str($s)
{
    return strlen($s) . ":$s";
}

/**
 * @param $i
 *
 * @return string
 */
function benc_int($i)
{
    return 'i' . $i . 'e';
}

/**
 * @param $a
 *
 * @return string
 */
function benc_list($a)
{
    $s = 'l';
    foreach ($a as $e) {
        $s .= benc($e);
    }
    $s .= 'e';

    return $s;
}

/**
 * @param $d
 *
 * @return string
 */
function benc_dict($d)
{
    $s = 'd';
    $keys = array_keys($d);
    sort($keys);
    foreach ($keys as $k) {
        $v = $d[ $k ];
        $s .= benc_str($k);
        $s .= benc($v);
    }
    $s .= 'e';

    return $s;
}

/**
 * @param $port
 *
 * @return bool
 */
function portblacklisted($port)
{
    //=== new portblacklisted ....... ==> direct connect 411 ot 413,  bittorrent 6881 to 6889, kazaa 1214, gnutella 6346 to 6347, emule 4662, winmx 6699, IRC bot based trojans 65535
    $portblacklisted = [
        411,
        412,
        413,
        6881,
        6882,
        6883,
        6884,
        6885,
        6886,
        6887,
        6889,
        1214,
        6346,
        6347,
        4662,
        6699,
        65535,
    ];
    if (in_array($port, $portblacklisted)) {
        return true;
    }

    return false;
}
