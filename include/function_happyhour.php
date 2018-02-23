<?php
/**
 * @param $action
 *
 * @return false|float|int|string
 */
function happyHour($action)
{
    global $site_config;
    //generate happy hour
    if ('generate' == $action) {
        $nextDay   = date('Y-m-d', TIME_NOW + 86400);
        $nextHoura = random_int(0, 2);
        if (2 == $nextHoura) {
            $nextHourb = random_int(0, 3);
        } else {
            $nextHourb = random_int(0, 9);
        }
        $nextHour  = $nextHoura . $nextHourb;
        $nextMina  = random_int(0, 5);
        $nextMinb  = random_int(0, 9);
        $nextMin   = $nextMina . $nextMinb;
        $happyHour = $nextDay . ' ' . $nextHour . ':' . $nextMin . '';

        return $happyHour;
    }
    $file      = $site_config['happyhour'];
    $happy     = unserialize(file_get_contents($file));
    $happyHour = strtotime($happy['time']);
    $happyDate = $happyHour;
    $curDate   = TIME_NOW;
    $nextDate  = $happyHour + 3600;
    //action check
    if ('check' == $action) {
        if ($happyDate < $curDate && $nextDate >= $curDate) {
            return true;
        }
    }
    //action time left
    if ('time' == $action) {
        $timeLeft = mkprettytime(($happyHour + 3600) - TIME_NOW);
        $timeLeft = explode(':', $timeLeft);
        $time     = ($timeLeft[0] . ' min : ' . $timeLeft[1] . ' sec');

        return $time;
    }
    //this will set all torrent free or just one category
    if ('todo' == $action) {
        $act = random_int(1, 2);
        if (1 == $act) {
            $todo = 255;
        } // this will mean that all the torrent are free
        elseif (2 == $act) {
            $todo = random_int(1, 14);
        } // only one cat will be free || remember to change the number of categories i have 14 but you may have more

        return $todo;
    }
    //this will generate the multiplier so every torrent downloaded in the happy hour will have upload multiplied but this
    if ('multiplier' == $action) {
        $multiplier = random_int(11, 55) / 10; //max value of the multiplier will be 5,5 || you could change it to a higher or a lower value

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
    $file       = $site_config['happyhour'];
    $happy      = unserialize(file_get_contents($file));
    $happycheck = $happy['catid'];
    if ('check' == $action) {
        return $happycheck;
    }
    if ('checkid' == $action && (('255' == $happycheck) || $happycheck == $id)) {
        return true;
    }
}

/**
 * @param $act
 */
function happyFile($act)
{
    global $site_config;
    $file  = $site_config['happyhour'];
    $happy = unserialize(file_get_contents($file));
    if ('set' == $act) {
        $array_happy = [
            'time'   => happyHour('generate'),
            'status' => '1',
            'catid'  => happyHour('todo'),
        ];
    } elseif ('reset' == $act) {
        $array_happy = [
            'time'   => $happy['time'],
            'status' => '0',
            'catid'  => $happy['catid'],
        ];
    }
    $array_happy = serialize($array_happy);
    $file        = $site_config['happyhour'];
    $file        = fopen($file, 'w');
    ftruncate($file, 0);
    fwrite($file, $array_happy);
    fclose($file);
}

/**
 * @param $userid
 * @param $torrentid
 * @param $multi
 */
function happyLog($userid, $torrentid, $multi)
{
    $time = sqlesc(TIME_NOW);
    sql_query('INSERT INTO happylog (userid, torrentid,multi, date) VALUES(' . sqlesc($userid) . ', ' . sqlesc($torrentid) . ', ' . sqlesc($multi) . ", $time)") or sqlerr(__FILE__, __LINE__);
}
