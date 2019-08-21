<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

/**
 * @throws Exception
 *
 * @return bool
 */
function crazyhour_announce()
{
    global $container;

    $cache = $container->get(Cache::class);
    $crazy_hour = (TIME_NOW + 3600);
    $cz['crazyhour'] = $cache->get('crazyhour_');
    $fluent = $container->get(Database::class);
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
                       'amount' => $cz['crazyhour']['amount'],
                   ])
                   ->where('type = ?', 'crazyhour')
                   ->execute();
        }
        $cache->set('crazyhour_', $cz['crazyhour'], 0);
    }

    if ($cz['crazyhour']['var'] < TIME_NOW) {
        if (($cz_lock = $cache->set('crazyhour_lock_', 1, 10)) !== false) {
            $cz['crazyhour_new'] = mktime(23, 59, 59, (int) date('m'), (int) date('d'), (int) date('y'));
            $cz['crazyhour']['var'] = random_int($cz['crazyhour_new'], ($cz['crazyhour_new'] + 86400));
            $cz['crazyhour']['amount'] = 0;
            $cz['remaining'] = ($cz['crazyhour']['var'] - TIME_NOW);

            $set = [
                'var' => $cz['crazyhour']['var'],
                'amount' => $cz['crazyhour']['amount'],
            ];
            $fluent->update('freeleech')
                   ->set($set)
                   ->where('type = ?', 'crazyhour')
                   ->execute();

            $cache->set('crazyhour_', $cz['crazyhour'], 0);

            $msg = 'Next [color=orange][b]Crazyhour[/b][/color] is at ' . date('F j, g:i a', $cz['crazyhour']['var']);
            autoshout($msg);

            $text = 'Next <span style="font-weight:bold;color:orange;">Crazyhour</span> is at ' . date('F j, g:i a', $cz['crazyhour']['var']);
            $values = [
                'added' => TIME_NOW,
                'txt' => $text,
            ];
            $fluent->insertInto('sitelog')
                   ->values($values)
                   ->execute();
        }

        return false;
    } elseif (($cz['crazyhour']['var'] < $crazy_hour) && ($cz['crazyhour']['var'] >= TIME_NOW)) {
        if ($cz['crazyhour']['amount'] !== 1) {
            $cz['crazyhour']['amount'] = 1;
            if (($cz_lock = $cache->set('crazyhour_lock_', 1, 10)) !== false) {
                $set = ['amount' => $cz['crazyhour']['amount']];
                $fluent->update('freeleech')
                       ->set($set)
                       ->where('type = ?', 'crazyhour')
                       ->execute();

                $cache->set('crazyhour_', $cz['crazyhour'], 0);

                $msg = 'w00t! It\'s [color=orange][b]Crazyhour[/b][/color] :w00t:';
                autoshout($msg);

                $text = 'w00t! It\'s <span style="font-weight:bold;color:orange;">Crazyhour</span> <img src="' . IMAGES_DIR . 'smilies/w00t.gif" alt=":w00t:">';
                $values = [
                    'added' => TIME_NOW,
                    'txt' => $text,
                ];
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
 * @param int $torrentid
 * @param int $userid
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return int|mixed
 */
function get_happy(int $torrentid, int $userid)
{
    global $container;

    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);
    $keys['happyhour'] = $userid . '_happy';
    $happy = $cache->get($keys['happyhour']);
    if ($happy === false || is_null($happy)) {
        $res = $fluent->from('happyhour')
                      ->where('userid = ?', $userid)
                      ->fetchAll();

        $happy = [];
        foreach ($res as $row) {
            $happy[$row['torrentid']] = $row['multiplier'];
        }
        $cache->set($userid . '_happy', $happy, 0);
    }
    if (!empty($happy) && isset($happy[$torrentid])) {
        return $happy[$torrentid];
    }

    return 0;
}

/**
 * @param int $torrentid
 * @param int $userid
 *
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return mixed
 */
function get_slots(int $torrentid, int $userid)
{
    global $container;

    $cache = $container->get(Cache::class);
    $fluent = $container->get(Database::class);
    $ttl_slot = 86400;
    $torrent['freeslot'] = $torrent['doubleslot'] = 0;
    $slot = $cache->get('fllslot_' . $userid);
    if ($slot === false || is_null($slot)) {
        $slot = $fluent->from('freeslots')
                       ->where('userid=?', $userid)
                       ->fetchAll();
        $cache->set('fllslot_' . $userid, $slot, $ttl_slot);
    }
    if (!empty($slot)) {
        foreach ($slot as $sl) {
            if ($sl['torrentid'] === $torrentid && $sl['free'] === 'yes') {
                $torrent['freeslot'] = 1;
            }
            if ($sl['torrentid'] === $torrentid && $sl['doubleup'] === 'yes') {
                $torrent['doubleslot'] = 1;
            }
        }
    }

    return $torrent;
}

/**
 * @param $msg
 */
function err($msg)
{
    benc_resp([
        'failure reason' => [
            'type' => 'string',
            'value' => $msg,
        ],
    ]);
    die();
}

/**
 * @param $d
 */
function benc_resp($d)
{
    benc_resp_raw(benc([
        'type' => 'dictionary',
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
        $v = $d[$k];
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
    $portblacklisted = [
        80,
        411,
        412,
        413,
        443,
        1214,
        4662,
        6346,
        6347,
        6699,
        6881,
        6882,
        6883,
        6884,
        6885,
        6886,
        6887,
        6889,
        8080,
        65535,
    ];
    if (in_array($port, $portblacklisted)) {
        return true;
    }

    return false;
}
