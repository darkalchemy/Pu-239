<?php
/**
 * @param $stamp
 *
 * @return string
 */
function time_return($stamp)
{
    $ysecs  = 365 * 24 * 60 * 60;
    $mosecs = 31  * 24  * 60  * 60;
    $wsecs  = 7   * 24   * 60   * 60;
    $dsecs  = 24  * 60  * 60;
    $hsecs  = 60  * 60;
    $msecs  = 60;
    $years  = floor($stamp / $ysecs);
    $stamp %= $ysecs;
    $months = floor($stamp / $mosecs);
    $stamp %= $mosecs;
    $weeks = floor($stamp / $wsecs);
    $stamp %= $wsecs;
    $days = floor($stamp / $dsecs);
    $stamp %= $dsecs;
    $hours = floor($stamp / $hsecs);
    $stamp %= $hsecs;
    $minutes = floor($stamp / $msecs);
    $stamp %= $msecs;
    $seconds = $stamp;
    if (1 == $years) {
        $nicetime['years'] = '1 Year';
    } elseif ($years > 1) {
        $nicetime['years'] = $years . ' Years';
    }
    if (1 == $months) {
        $nicetime['months'] = '1 Month';
    } elseif ($months > 1) {
        $nicetime['months'] = $months . ' Months';
    }
    if (1 == $weeks) {
        $nicetime['weeks'] = '1 Week';
    } elseif ($weeks > 1) {
        $nicetime['weeks'] = $weeks . ' Weeks';
    }
    if (1 == $days) {
        $nicetime['days'] = '1 Day';
    } elseif ($days > 1) {
        $nicetime['days'] = $days . ' Day';
    }
    if (1 == $hours) {
        $nicetime['hours'] = '1 Hour';
    } elseif ($hours > 1) {
        $nicetime['hours'] = $hours . ' Hours';
    }
    if (1 == $minutes) {
        $nicetime['minutes'] = '1 minute';
    } elseif ($minutes > 1) {
        $nicetime['minutes'] = $minutes . ' Minutes';
    }
    if (1 == $seconds) {
        $nicetime['seconds'] = '1 second';
    } elseif ($seconds > 1) {
        $nicetime['seconds'] = $seconds . ' Seconds';
    }
    if (is_array($nicetime)) {
        return implode(', ', $nicetime);
    }
}
