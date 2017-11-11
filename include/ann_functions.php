<?php
//== Announce mysql error
/**
 * @param string $file
 * @param string $line
 */
function ann_sqlerr($file = '', $line = '')
{
    global $site_config, $CURUSER;
    $error = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_error($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false));
    $error_no = ((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false));
    if ($site_config['ann_sql_error_log'] and ANN_SQL_DEBUG == 1) {
        $_ann_sql_err = "\n===================================================";
        $_ann_sql_err .= "\n Date: " . date('r');
        $_ann_sql_err .= "\n Error Number: " . $error_no;
        $_ann_sql_err .= "\n Error: " . $error;
        $_ann_sql_err .= "\n IP Address: " . $_SERVER['REMOTE_ADDR'];
        $_ann_sql_err .= "\n in file " . $file . ' on line ' . $line;
        $_ann_sql_err .= "\n URL:" . $_SERVER['REQUEST_URI'];
        if ($FH = @fopen($site_config['ann_sql_error_log'], 'a')) {
            @fwrite($FH, $_ann_sql_err);
            @fclose($FH);
        }
    }
}

//==Announce Sql query logging
/**
 * @param $a_query
 *
 * @return bool|mysqli_result
 */
function ann_sql_query($a_query)
{
    global $a_query_stat, $site_config;
    $a_query_start_time = microtime(true); // Start time
    $result = mysqli_query($GLOBALS['___mysqli_ston'], $a_query);
    $a_query_end_time = microtime(true); // End time
    $a_querytime = ($a_query_end_time - $a_query_start_time);
    $a_query_stat[] = [
        'seconds' => number_format($a_query_end_time - $a_query_start_time, 6),
        'query'   => $a_query,
    ];
    if ((count($a_query_stat) > 0) && (ANN_SQL_LOGGING == 1)) {
        foreach ($a_query_stat as $key => $value) {
            $_ann_sql = "\n=============Announce query logging=========";
            $_ann_sql .= "\n Query no : " . $key . '+1';
            $_ann_sql .= "\n Executed in  : " . $value['seconds'];
            $_ann_sql .= "\n query ran : " . $value['query'];
        }
        if ($FH = @fopen($site_config['ann_sql_log'], 'a')) {
            @fwrite($FH, $_ann_sql);
            @fclose($FH);
        }
    }

    return $result;
}

/**
 * @return bool
 */
function crazyhour_announce()
{
    global $mc1, $site_config;
    $crazy_hour = (TIME_NOW + 3600);
    if (($cz['crazyhour'] = $mc1->get_value('crazyhour')) === false) {
        $cz['sql'] = ann_sql_query('SELECT var, amount FROM freeleech WHERE type = "crazyhour"') or ann_sqlerr(__FILE__, __LINE__);
        $cz['crazyhour'] = [];
        if (mysqli_num_rows($cz['sql']) !== 0) {
            $cz['crazyhour'] = mysqli_fetch_assoc($cz['sql']);
        } else {
            $cz['crazyhour']['var'] = random_int(TIME_NOW, (TIME_NOW + 86400));
            $cz['crazyhour']['amount'] = 0;
            ann_sql_query('UPDATE freeleech SET var = ' . $cz['crazyhour']['var'] . ', amount = ' . $cz['crazyhour']['amount'] . ' 
         WHERE type = "crazyhour"') or ann_sqlerr(__FILE__, __LINE__);
        }
        $mc1->cache_value('crazyhour', $cz['crazyhour'], 0);
    }
    if ($cz['crazyhour']['var'] < TIME_NOW) { // if crazyhour over
        if (($cz_lock = $mc1->add_value('crazyhour_lock', 1, 10)) !== false) {
            $cz['crazyhour_new'] = mktime(23, 59, 59, date('m'), date('d'), date('y'));
            $cz['crazyhour']['var'] = random_int($cz['crazyhour_new'], ($cz['crazyhour_new'] + 86400));
            $cz['crazyhour']['amount'] = 0;
            $cz['remaining'] = ($cz['crazyhour']['var'] - TIME_NOW);
            ann_sql_query('UPDATE freeleech SET var = ' . $cz['crazyhour']['var'] . ', amount = ' . $cz['crazyhour']['amount'] . ' ' . 'WHERE type = "crazyhour"') or ann_sqlerr(__FILE__, __LINE__);
            $mc1->cache_value('crazyhour', $cz['crazyhour'], 0);

            $msg = 'Next [color=orange][b]Crazyhour[/b][/color] is at ' . date('F j, g:i a', $cz['crazyhour']['var']);
            autoshout($msg);

            $text = 'Next <span style="font-weight:bold;color:orange;">Crazyhour</span> is at ' . date('F j, g:i a', $cz['crazyhour']['var']);
            ann_sql_query('INSERT INTO sitelog (added, txt) ' . 'VALUES(' . TIME_NOW . ', ' . ann_sqlesc($text) . ')') or ann_sqlerr(__FILE__, __LINE__);
        }

        return false;
    } elseif (($cz['crazyhour']['var'] < $crazy_hour) && ($cz['crazyhour']['var'] >= TIME_NOW)) { // if crazyhour
        if ($cz['crazyhour']['amount'] !== 1) {
            $cz['crazyhour']['amount'] = 1;
            if (($cz_lock = $mc1->add_value('crazyhour_lock', 1, 10)) !== false) {
                ann_sql_query('UPDATE freeleech SET amount = ' . $cz['crazyhour']['amount'] . ' WHERE type = "crazyhour"') or ann_sqlerr(__FILE__, __LINE__);
                $mc1->cache_value('crazyhour', $cz['crazyhour'], 0);

                $msg = 'w00t! It\'s [color=orange][b]Crazyhour[/b][/color] :w00t:';
                autoshout($msg);

                $text = 'w00t! It\'s <span style="font-weight:bold;color:orange;">Crazyhour</span> <img src="./images/smilies/w00t.gif" alt=":w00t:" />';
                ann_sql_query('INSERT INTO sitelog (added, txt) VALUES(' . TIME_NOW . ', ' . ann_sqlesc($text) . ')') or ann_sqlerr(__FILE__, __LINE__);
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
    global $mc1, $site_config;
    if (strlen($torrent_pass) != 32 || !bin2hex($torrent_pass)) {
        return false;
    }
    $key = 'user::torrent_pass:::' . $torrent_pass;
    if (($user = $mc1->get_value($key)) === false) {
        $user_fields_ar_int = [
            'id',
            'uploaded',
            'downloaded',
            'class',
            'free_switch',
            'downloadpos',
            'perms',
        ];
        $user_fields_ar_str = [
            'ip',
            'enabled',
            'highspeed',
            'hnrwarn',
            'parked',
        ];
        $user_fields = implode(', ', array_merge($user_fields_ar_int, $user_fields_ar_str));
        $user_query = ann_sql_query('SELECT ' . $user_fields . ' FROM users WHERE torrent_pass=' . ann_sqlesc($torrent_pass) . " AND enabled = 'yes'") or ann_sqlerr(__FILE__, __LINE__);
        $user = mysqli_fetch_assoc($user_query);
        foreach ($user_fields_ar_int as $i) {
            $user[$i] = (int)$user[$i];
        }
        foreach ($user_fields_ar_str as $i) {
            $user[$i] = $user[$i];
        }
        $mc1->cache_value($key, $user, $site_config['expires']['user_passkey']);
    } elseif (!$user) {
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
    global $mc1, $site_config;
    $key = 'torrent::hash:::' . md5($info_hash);
    $ttll = 21600; // 21600;
    if (($torrent = $mc1->get_value($key)) === false) {
        $res = ann_sql_query('SELECT id, category, banned, free, silver, vip, seeders, leechers, times_completed, seeders + leechers AS numpeers, added AS ts, visible FROM torrents WHERE info_hash = ' . ann_sqlesc($info_hash)) or ann_sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res)) {
            $torrent = mysqli_fetch_assoc($res);
            $torrent['id'] = (int)$torrent['id'];
            $torrent['free'] = (int)$torrent['free'];
            $torrent['silver'] = (int)$torrent['silver'];
            $torrent['category'] = (int)$torrent['category'];
            $torrent['numpeers'] = (int)$torrent['numpeers'];
            $torrent['seeders'] = (int)$torrent['seeders'];
            $torrent['leechers'] = (int)$torrent['leechers'];
            $torrent['times_completed'] = (int)$torrent['times_completed'];
            $torrent['ts'] = (int)$torrent['ts'];
            $mc1->cache_value($key, $torrent, $ttll);
            $seed_key = 'torrents::seeds:::' . $torrent['id'];
            $leech_key = 'torrents::leechs:::' . $torrent['id'];
            $comp_key = 'torrents::comps:::' . $torrent['id'];
            $mc1->add_value($seed_key, $torrent['seeders'], $ttll);
            $mc1->add_value($leech_key, $torrent['leechers'], $ttll);
            $mc1->add_value($comp_key, $torrent['times_completed'], $ttll);
        } else {
            $mc1->cache_value($key, 0, 86400);

            return false;
        }
    } elseif (!$torrent) {
        return false;
    } else {
        $seed_key = 'torrents::seeds:::' . $torrent['id'];
        $leech_key = 'torrents::leechs:::' . $torrent['id'];
        $comp_key = 'torrents::comps:::' . $torrent['id'];
        $torrent['seeders'] = $mc1->get_value($seed_key);
        $torrent['leechers'] = $mc1->get_value($leech_key);
        $torrent['times_completed'] = $mc1->get_value($comp_key);
        if ($torrent['seeders'] === false || $torrent['leechers'] === false || $torrent['times_completed'] === false) {
            $res = ann_sql_query('SELECT seeders, leechers, times_completed FROM torrents WHERE id = ' . ann_sqlesc($torrent['id'])) or ann_sqlerr(__FILE__, __LINE__);
            if (mysqli_num_rows($res)) {
                $torrentq = mysqli_fetch_assoc($res);
                $torrent['seeders'] = (int)$torrentq['seeders'];
                $torrent['leechers'] = (int)$torrentq['leechers'];
                $torrent['times_completed'] = (int)$torrentq['times_completed'];
                $mc1->add_value($seed_key, $torrent['seeders'], $ttll);
                $mc1->add_value($leech_key, $torrent['leechers'], $ttll);
                $mc1->add_value($comp_key, $torrent['times_completed'], $ttll);
            } else {
                $mc1->delete_value($key);

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
    global $mc1;
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
        $adjust += (bool)$mc1->increment($seed_key, $seeds);
    } elseif ($seeds < 0) {
        $adjust += (bool)$mc1->decrement($seed_key, -$seeds);
    }
    if ($leechers > 0) {
        $adjust += (bool)$mc1->increment($leech_key, $leechers);
    } elseif ($leechers < 0) {
        $adjust += (bool)$mc1->decrement($leech_key, -$leechers);
    }
    if ($completed > 0) {
        $adjust += (bool)$mc1->increment($comp_key, $completed);
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
    global $mc1;
    $keys['happyhour'] = $userid . '_happy';
    if (($happy = $mc1->get_value($keys['happyhour'])) === false) {
        $res_happy = ann_sql_query('SELECT id, userid, torrentid, multiplier from happyhour where userid=' . ann_sqlesc($userid)) or ann_sqlerr(__FILE__, __LINE__);
        $happy = [];
        if (mysqli_num_rows($res_happy)) {
            while ($rowhappy = mysqli_fetch_assoc($res_happy)) {
                $happy[$rowhappy['torrentid']] = $rowhappy['multiplier'];
            }
        }
        $mc1->add_value($userid . '_happy', $happy, 0);
    }
    if (!empty($happy) && isset($happy[$torrentid])) {
        return $happy[$torrentid];
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
    global $mc1;
    $ttl_slot = 86400;
    $torrent['freeslot'] = $torrent['doubleslot'] = 0;
    if (($slot = $mc1->get_value('fllslot_' . $userid)) === false) {
        $res_slots = ann_sql_query('SELECT * FROM freeslots WHERE userid = ' . ann_sqlesc($userid)) or ann_sqlerr(__FILE__, __LINE__);
        $slot = [];
        if (mysqli_num_rows($res_slots)) {
            while ($rowslot = mysqli_fetch_assoc($res_slots)) {
                $slot[] = $rowslot;
            }
        }
        $mc1->add_value('fllslot_' . $userid, $slot, $ttl_slot);
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
    ann_sql_query('INSERT INTO cheaters (added, userid, client, rate, beforeup, upthis, timediff, userip, torrentid) VALUES(' . ann_sqlesc(TIME_NOW) . ', ' . ann_sqlesc($userid) . ', ' . ann_sqlesc($client) . ', ' . ann_sqlesc($rate) . ', ' . ann_sqlesc($last_up) . ', ' . ann_sqlesc($upthis) . ', ' . ann_sqlesc($diff) . ', ' . ann_sqlesc($realip) . ', ' . ann_sqlesc($torrentid) . ')') or ann_sqlerr(__FILE__, __LINE__);
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

function gzip()
{
    if (@extension_loaded('zlib') && @ini_get('zlib.output_compression') != '1' && @ini_get('output_handler') != 'ob_gzhandler') {
        @ob_start('ob_gzhandler');
    }
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
        $v = $d[$k];
        $s .= benc_str($k);
        $s .= benc($v);
    }
    $s .= 'e';

    return $s;
}

/**
 * @param $name
 * @param $hash
 *
 * @return string
 */
function hash_where($name, $hash)
{
    $shhash = preg_replace('/ *$/s', '', $hash);

    return "($name = " . ann_sqlesc($hash) . " OR $name = " . ann_sqlesc($shhash) . ')';
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

/**
 * @param $x
 *
 * @return int|string
 */
function ann_sqlesc($x)
{
    if (is_integer($x)) {
        return (int)$x;
    }

    return sprintf('\'%s\'', mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $x));
}

/**
 * @param $ip
 *
 * @return string
 */
function ipToStorageFormat($ip)
{
    $ip = empty($ip) ? '254.254.254.254' : $ip;
    return '0x' . bin2hex(inet_pton($ip));
}

