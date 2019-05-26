<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$lang = array_merge($lang, load_language('ad_mysql_stats'));
class_check(UC_MAX);
$GLOBALS['byteUnits'] = [
    'Bytes',
    'KB',
    'MB',
    'GB',
    'TB',
    'PB',
    'EB',
];
$day_of_week = [
    'Sun',
    'Mon',
    'Tue',
    'Wed',
    'Thu',
    'Fri',
    'Sat',
];
$month = [
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'May',
    'Jun',
    'Jul',
    'Aug',
    'Sep',
    'Oct',
    'Nov',
    'Dec',
];
// See http://www.php.net/manual/en/function.strftime.php to define the
// variable below
$datefmt = '%B %d, %Y at %I:%M %p';
$timespanfmt = '%s days, %s hours, %s minutes and %s seconds';
////////////////// FUNCTION LIST /////////////////////////
/**
 * @param     $value
 * @param int $limes
 * @param int $comma
 *
 * @return array
 */
function byteformat($value, $limes = 2, $comma = 0)
{
    $dh = pow(10, $comma);
    $li = pow(10, $limes);
    $return_value = $value;
    $unit = $GLOBALS['byteUnits'][0];
    for ($d = 6, $ex = 15; $d >= 1; $d--, $ex -= 3) {
        if (isset($GLOBALS['byteUnits'][$d]) && $value >= $li * pow(10, $ex)) {
            $value = round($value / (pow(1024, $d) / $dh)) / $dh;
            $unit = $GLOBALS['byteUnits'][$d];
            break 1;
        } // end if
    } // end for
    if ($unit != $GLOBALS['byteUnits'][0]) {
        $return_value = number_format($value, $comma, '.', ',');
    } else {
        $return_value = number_format($value, 0, '.', ',');
    }

    return [
        $return_value,
        $unit,
    ];
} // end of the 'formatByteDown' function
/**
 * @param $seconds
 *
 * @return string
 */
function timespanFormat($seconds)
{
    global $lang;

    $return_string = '';
    $days = floor($seconds / 86400);
    if ($days > 0) {
        $seconds -= $days * 86400;
    }
    $hours = floor($seconds / 3600);
    if ($days > 0 || $hours > 0) {
        $seconds -= $hours * 3600;
    }
    $minutes = floor($seconds / 60);
    if ($days > 0 || $hours > 0 || $minutes > 0) {
        $seconds -= $minutes * 60;
    }

    return (string) $days . $lang['mysql_stats_days'] . (string) $hours . $lang['mysql_stats_hours'] . (string) $minutes . $lang['mysql_stats_minutes'] . (string) $seconds . $lang['mysql_stats_seconds'];
}

/**
 * @param int    $timestamp
 * @param string $format
 *
 * @return string
 */
function localisedDate($timestamp = -1, $format = '')
{
    global $datefmt, $month, $day_of_week;
    if ($format == '') {
        $format = $datefmt;
    }
    if ($timestamp == -1) {
        $timestamp = TIME_NOW;
    }
    $date = preg_replace('@%[aA]@', $day_of_week[(int) strftime('%w', $timestamp)], $format);
    $date = preg_replace('@%[bB]@', $month[(int) strftime('%m', $timestamp) - 1], $date);

    return strftime($date, $timestamp);
} // end of the 'localisedDate()' function
////////////////////// END FUNCTION LIST /////////////////////////////////////
$HTMLOUT = '';
$HTMLOUT .= "<h1 class='has-text-centered'>{$lang['mysql_stats_status']}</h1>";
$res = sql_query('SHOW GLOBAL STATUS') or sqlerr(__FILE__, __LINE__);
$serverStatus = [];
while ($row = mysqli_fetch_row($res)) {
    $serverStatus[$row[0]] = $row[1];
}
@((mysqli_free_result($res) || (is_object($res) && (get_class($res) === 'mysqli_result'))) ? true : false);
unset($res, $row);

$res = sql_query('SELECT UNIX_TIMESTAMP() - ' . $serverStatus['Uptime']) or sqlerr(__FILE__, __LINE__);
$row = mysqli_fetch_row($res);
$HTMLOUT .= "<p class='has-text-centered'>{$lang['mysql_stats_server']}" . timespanFormat($serverStatus['Uptime']) . $lang['mysql_stats_started'] . localisedDate((int) $row[0]) . '</p>';
((mysqli_free_result($res) || (is_object($res) && (get_class($res) === 'mysqli_result'))) ? true : false);
unset($res, $row);

//Get query statistics
$queryStats = [];
$tmp_array = $serverStatus;
foreach ($tmp_array as $name => $value) {
    if (substr($name, 0, 4) === 'Com_') {
        $queryStats[str_replace('_', ' ', substr($name, 4))] = $value;
        unset($serverStatus[$name]);
    }
}
unset($tmp_array);
$TRAFFIC_STATS = '';
$TRAFFIC_STATS_HEAD = "<p class='has-text-centered'>{$lang['mysql_stats_traffic_per_hour']}{$lang['mysql_stats_tables']}</p>";
$TRAFFIC_STATS .= main_table("
    <tr>
        <td colspan='3' class='bg-08'>{$lang['mysql_stats_traffic_per_hour']}</td>
    </tr>
    <tr>
        <td>{$lang['mysql_stats_received']}</td>
        <td>" . implode(' ', byteformat($serverStatus['Bytes_received'])) . '</td>
        <td>' . implode(' ', byteformat($serverStatus['Bytes_received'] * 3600 / $serverStatus['Uptime'])) . "</td>
    </tr>
    <tr>
        <td>{$lang['mysql_stats_sent']}</td>
        <td>" . implode(' ', byteformat($serverStatus['Bytes_sent'])) . '</td>
        <td>' . implode(' ', byteformat($serverStatus['Bytes_sent'] * 3600 / $serverStatus['Uptime'])) . "</td>
    </tr>
    <tr>
        <td class='bg-08'>&{$lang['mysql_stats_total']}</td>
        <td class='bg-08'>" . implode(' ', byteformat($serverStatus['Bytes_received'] + $serverStatus['Bytes_sent'])) . "</td>
        <td class='bg-08'>" . implode(' ', byteformat(($serverStatus['Bytes_received'] + $serverStatus['Bytes_sent']) * 3600 / $serverStatus['Uptime'])) . '</td>
    </tr>');
$TRAFFIC_STATS2 = main_table("
    <tr>
        <td colspan='4' class='bg-08'>{$lang['mysql_stats_connection_per_hour']}</td>
    </tr>
    <tr>
        <td>{$lang['mysql_stats_failed']}</td>
        <td>" . number_format((float) $serverStatus['Aborted_connects'], 0, '.', ',') . '</td>
        <td>' . number_format((float) ($serverStatus['Aborted_connects'] * 3600 / $serverStatus['Uptime']), 2, '.', ',') . '</td>
        <td>' . (($serverStatus['Connections'] > 0) ? number_format((float) ($serverStatus['Aborted_connects'] * 100 / $serverStatus['Connections']), 2, '.', ',') . '%' : '---' . '') . "</td>
    </tr>
    <tr>
        <td>{$lang['mysql_stats_aborted']}</td>
        <td>" . number_format((float) $serverStatus['Aborted_clients'], 0, '.', ',') . '</td>
        <td>' . number_format((float) ($serverStatus['Aborted_clients'] * 3600 / $serverStatus['Uptime']), 2, '.', ',') . '</td>
        <td>' . (($serverStatus['Connections'] > 0) ? number_format((float) ($serverStatus['Aborted_clients'] * 100 / $serverStatus['Connections']), 2, '.', ',') . '%' : '---') . "</td>
    </tr>
    <tr>
        <td class='bg-08'>{$lang['mysql_stats_total']}</td>
        <td class='bg-08'>" . number_format((float) $serverStatus['Connections'], 0, '.', ',') . "</td>
        <td class='bg-08'>" . number_format((float) ($serverStatus['Connections'] * 3600 / $serverStatus['Uptime']), 2, '.', ',') . "</td>
        <td class='bg-08'>" . number_format(100, 2, '.', ',') . '%</td>
    </tr>');
$QUERY_STATS = "
    <div class='has-text-centered'>
        <h1>{$lang['mysql_stats_query']}</h1>
        {$lang['mysql_stats_since']}" . number_format((float) $serverStatus['Questions'], 0, '.', ',') . "{$lang['mysql_stats_querys']}
    </div>";
$heading = "
    <tr>
        <th>{$lang['mysql_stats_total']}</th>
        <th>{$lang['mysql_stats_per_hour']}</th>
        <th>{$lang['mysql_stats_per_minute']}</th>
        <th>{$lang['mysql_stats_per_seconds']}</th>
    </tr>";
$body = '
    <tr>
        <td>' . number_format((float) $serverStatus['Questions'], 0, '.', ',') . '</td>
        <td>' . number_format((float) ($serverStatus['Questions'] * 3600 / $serverStatus['Uptime']), 2, '.', ',') . '</td>
        <td>' . number_format((float) ($serverStatus['Questions'] * 60 / $serverStatus['Uptime']), 2, '.', ',') . '</td>
        <td>' . number_format((float) ($serverStatus['Questions'] / $serverStatus['Uptime']), 2, '.', ',') . '</td>
    </tr>';
$QUERY_STATS .= main_table($body, $heading, 'bottom20');

$heading = "
        <tr>
            <th colspan='2'>{$lang['mysql_stats_query_type']}</th>
            <th>{$lang['mysql_stats_per_hour']};</th>
            <th>%</th>
        </tr>";
$body = '';
foreach ($queryStats as $name => $value) {
    $body .= '
        <tr>
            <td>' . htmlsafechars($name) . '</td>
            <td>' . number_format((float) $value, 0, '.', ',') . '</td>
            <td>' . number_format((float) ($value * 3600 / $serverStatus['Uptime']), 2, '.', ',') . '</td>
            <td>' . number_format((float) ($value * 100 / ($serverStatus['Questions'] - $serverStatus['Connections'])), 2, '.', ',') . '%</td>
        </tr>';
}

$QUERY_STATS .= main_table($body, $heading);
unset($serverStatus['Aborted_clients'], $serverStatus['Aborted_connects'], $serverStatus['Bytes_received'], $serverStatus['Bytes_sent'], $serverStatus['Connections'], $serverStatus['Questions'], $serverStatus['Uptime']);

$STATUS_TABLE = '';
if (!empty($serverStatus)) {
    $STATUS_TABLE .= "
          <h1 class='has-text-centered'>{$lang['mysql_stats_more']}</h1>";
    $heading = "
        <tr>
            <th>{$lang['mysql_stats_variable']}</th>
            <th>{$lang['mysql_stats_value']}</th>
        </tr>";
    $body = '';
    foreach ($serverStatus as $name => $value) {
        $body .= '
        <tr>
            <td>' . htmlsafechars(str_replace('_', ' ', $name)) . '</td>
            <td>' . htmlsafechars($value) . '</td>
        </tr>';
    }
    $STATUS_TABLE .= main_table($body, $heading);
}
/*
$HTMLOUT .= main_table("
    <tr>
        <td colspan='2'>$TRAFFIC_STATS_HEAD</td>
    </tr>
    <tr>
        <td>$TRAFFIC_STATS</td>
        <td>$TRAFFIC_STATS2</td>
    </tr>
    <tr>
        <td>$QUERY_STATS</td>
        <td>$STATUS_TABLE</td>
    </tr>");
*/
$HTMLOUT .= $TRAFFIC_STATS_HEAD . "
    <div class='columns'>
        <div class='column is-half'>$TRAFFIC_STATS</div>
        <div class='column'>$TRAFFIC_STATS2</div>
    </div>
    <div class='columns'>
        <div class='column is-half'>$QUERY_STATS</div>
        <div class='column'>$STATUS_TABLE</div>
    </div>";
echo stdhead($lang['mysql_stats_stdhead']) . wrapper($HTMLOUT) . stdfoot();
