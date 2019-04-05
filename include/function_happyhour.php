<?php

/**
 * @param $action
 *
 * @return false|float|int|string
 *
 * @throws \Envms\FluentPDO\Exception
 */
function happyHour($action)
{
    global $site_config, $fluent;

    if ($action === 'generate') {
        $nextDay = date('Y-m-d', TIME_NOW + 86400);
        $nextHoura = random_int(0, 2);
        if ($nextHoura == 2) {
            $nextHourb = random_int(0, 3);
        } else {
            $nextHourb = random_int(0, 9);
        }
        $nextHour = $nextHoura . $nextHourb;
        $nextMina = random_int(0, 5);
        $nextMinb = random_int(0, 9);
        $nextMin = $nextMina . $nextMinb;
        $happyHour = $nextDay . ' ' . $nextHour . ':' . $nextMin . '';

        return $happyHour;
    }
    $file = $site_config['paths']['happyhour'];
    $happy = unserialize(file_get_contents($file));
    $happyHour = strtotime($happy['time']);
    $happyDate = $happyHour;
    $curDate = TIME_NOW;
    $nextDate = $happyHour + 3600;
    if ($action === 'check') {
        if ($happyDate < $curDate && $nextDate >= $curDate) {
            return true;
        }
    }
    if ($action === 'time') {
        $timeLeft = mkprettytime(($happyHour + 3600) - TIME_NOW);
        $timeLeft = explode(':', $timeLeft);
        $time = ($timeLeft[0] . ' min : ' . $timeLeft[1] . ' sec');

        return $time;
    }
    if ($action === 'todo') {
        $act = random_int(1, 2);
        if ($act === 1) {
            $todo = 255;
        } else {
            $categories = $fluent->from('categories')
                ->select(null)
                ->select('id')
                ->fetchAll();

            shuffle($categories);
            $todo = $categories[0];
        }

        return $todo;
    }
    if ($action === 'multiplier') {
        $multiplier = random_int(11, 55) / 10;

        return $multiplier;
    }
}

/**
 * @param      $action
 * @param null $id
 *
 * @return bool
 */
function happyCheck($action, $id = null)
{
    global $site_config;

    $file = $site_config['paths']['happyhour'];
    $happy = unserialize(file_get_contents($file));
    $happycheck = $happy['catid'];
    if ($action === 'check') {
        return $happycheck['id'];
    }
    if ($action === 'checkid' && (($happycheck == '255') || $happycheck == $id)) {
        return true;
    }
}

/**
 * @param $act
 */
function happyFile($act)
{
    global $site_config;

    $file = $site_config['paths']['happyhour'];
    $happy = unserialize(file_get_contents($file));
    if ($act === 'set') {
        $array_happy = [
            'time' => happyHour('generate'),
            'status' => '1',
            'catid' => happyHour('todo'),
        ];
    } elseif ($act === 'reset') {
        $array_happy = [
            'time' => $happy['time'],
            'status' => '0',
            'catid' => $happy['catid'],
        ];
    }
    if (!empty($array_happy)) {
        $array_happy = serialize($array_happy);
        $file = $site_config['paths']['happyhour'];
        $file = fopen($file, 'w');
        ftruncate($file, 0);
        fwrite($file, $array_happy);
        fclose($file);
    }
}

/**
 * @param $userid
 * @param $torrentid
 * @param $multi
 */
function happyLog($userid, $torrentid, $multi)
{
    $time = sqlesc(TIME_NOW);
    sql_query('INSERT INTO happylog (userid, torrentid, multi, date) VALUES(' . sqlesc($userid) . ', ' . sqlesc($torrentid) . ', ' . sqlesc($multi) . ", $time)") or sqlerr(__FILE__, __LINE__);
}
