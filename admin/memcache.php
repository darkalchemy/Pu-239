<?php

declare(strict_types = 1);

/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2004 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:  Harun Yayli <harunyayli at gmail.com>                       |
  +----------------------------------------------------------------------+
 */

use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$VERSION = '$Id: memcache.php,v 1.1.2.3 2008/08/28 18:07:54 mikl Exp $';
define('DATE_FORMAT', 'Y/m/d H:i:s');
define('GRAPH_SIZE', 200);
define('MAX_ITEM_DUMP', 50);

if (extension_loaded('memcached')) {
    global $site_config;

    if (!$site_config['memcached']['use_socket']) {
        $MEMCACHE_SERVERS[] = "{$site_config['memcached']['host']}:{$site_config['memcached']['port']}";
    } else {
        $MEMCACHE_SERVERS[] = "unix://{$site_config['memcached']['socket']}";
    }
} else {
    die('<h1>Error</h1><p>php-memcached is not available</p>');
}

////////// END OF DEFAULT CONFIG AREA /////////////////////////////////////////////////////////////
/**
 * @param $server
 *
 * @return array
 */
function get_host_port_from_server($server)
{
    $values = explode(':', $server);
    if ($values[0] === 'unix' && !is_numeric($values[1])) {
        return [
            $server,
            0,
        ];
    } else {
        return $values;
    }
}

/**
 * @param $command
 *
 * @return array
 */
function sendMemcacheCommands($command)
{
    global $MEMCACHE_SERVERS;

    $result = [];
    foreach ($MEMCACHE_SERVERS as $server) {
        $strs = get_host_port_from_server($server);
        $host = $strs[0];
        $port = (int) $strs[1];
        $result[$server] = sendMemcacheCommand($host, $port, $command);
    }

    return $result;
}

/**
 * @param string $server
 * @param int    $port
 * @param string $command
 *
 * @return array
 */
function sendMemcacheCommand(string $server, int $port, string $command)
{
    $s = @fsockopen($server, $port);
    if (!$s) {
        die("Can't connect to: {$server}:{$port}");
    }
    fwrite($s, $command . "\r\n");
    $buf = '';
    while ((!feof($s))) {
        $buf .= fgets($s, 256);
        if (strpos($buf, "END\r\n") !== false) {
            break;
        }
        if (strpos($buf, "DELETED\r\n") !== false || strpos($buf, "NOT_FOUND\r\n") !== false) { // delete says these
            break;
        }
        if (strpos($buf, "OK\r\n") !== false) {
            break;
        }
    }
    fclose($s);

    return parseMemcacheResults($buf);
}

/**
 * @param $str
 *
 * @return array
 */
function parseMemcacheResults($str)
{
    $res = [];
    $lines = explode("\r\n", $str);
    $cnt = count($lines);
    for ($i = 0; $i < $cnt; ++$i) {
        $line = $lines[$i];
        $l = explode(' ', $line, 3);
        if (count($l) == 3) {
            $res[$l[0]][$l[1]] = $l[2];
            if ($l[0] === 'VALUE') {
                $res[$l[0]][$l[1]] = [];
                list($flag, $size) = explode(' ', $l[2]);
                $res[$l[0]][$l[1]]['stat'] = [
                    'flag' => $flag,
                    'size' => $size,
                ];
                $res[$l[0]][$l[1]]['value'] = $lines[++$i];
            }
        } elseif ($line === 'DELETED' || $line === 'NOT_FOUND' || $line === 'OK') {
            return [$line];
        }
    }

    return $res;
}

/**
 * @param $server
 * @param $slabId
 * @param $limit
 *
 * @return array
 */
function dumpCacheSlab($server, $slabId, $limit)
{
    list($host, $port) = explode(':', $server);
    $resp = sendMemcacheCommand($host, $port, 'stats cachedump ' . $slabId . ' ' . $limit);

    return $resp;
}

/**
 * @param $server
 *
 * @return array
 */
function flushServer($server)
{
    list($host, $port) = explode(':', $server);
    $resp = sendMemcacheCommand($host, $port, 'flush_all');

    return $resp;
}

/**
 * @return array
 */
function getCacheItems()
{
    $items = sendMemcacheCommands('stats items');
    $serverItems = [];
    $totalItems = [];
    foreach ($items as $server => $itemlist) {
        $serverItems[$server] = [];
        $totalItems[$server] = 0;
        if (!isset($itemlist['STAT'])) {
            continue;
        }
        $iteminfo = $itemlist['STAT'];
        foreach ($iteminfo as $keyinfo => $value) {
            if (preg_match('/items\:(\d+?)\:(.+?)$/', $keyinfo, $matches)) {
                $serverItems[$server][$matches[1]][$matches[2]] = $value;
                if ($matches[2] === 'number') {
                    $totalItems[$server] += $value;
                }
            }
        }
    }

    return [
        'items' => $serverItems,
        'counts' => $totalItems,
    ];
}

/**
 * @param bool $total
 *
 * @return array
 */
function getMemcacheStats($total = true)
{
    $resp = sendMemcacheCommands('stats');
    if ($total) {
        $res = [];
        foreach ($resp as $server => $r) {
            foreach ($r['STAT'] as $key => $row) {
                if (!isset($res[$key])) {
                    $res[$key] = null;
                }
                switch ($key) {
                    case 'pid':
                        $res['pid'][$server] = $row;
                        break;

                    case 'uptime':
                        $res['uptime'][$server] = $row;
                        break;

                    case 'time':
                        $res['time'][$server] = $row;
                        break;

                    case 'version':
                        $res['version'][$server] = $row;
                        break;

                    case 'pointer_size':
                        $res['pointer_size'][$server] = $row;
                        break;

                    case 'rusage_user':
                        $res['rusage_user'][$server] = $row;
                        break;

                    case 'rusage_system':
                        $res['rusage_system'][$server] = $row;
                        break;

                    case 'curr_items':
                        $res['curr_items'] += $row;
                        break;

                    case 'total_items':
                        $res['total_items'] += $row;
                        break;

                    case 'bytes':
                        $res['bytes'] += $row;
                        break;

                    case 'curr_connections':
                        $res['curr_connections'] += $row;
                        break;

                    case 'total_connections':
                        $res['total_connections'] += $row;
                        break;

                    case 'connection_structures':
                        $res['connection_structures'] += $row;
                        break;

                    case 'cmd_get':
                        $res['cmd_get'] += $row;
                        break;

                    case 'cmd_set':
                        $res['cmd_set'] += $row;
                        break;

                    case 'get_hits':
                        $res['get_hits'] += $row;
                        break;

                    case 'get_misses':
                        $res['get_misses'] += $row;
                        break;

                    case 'evictions':
                        $res['evictions'] += $row;
                        break;

                    case 'bytes_read':
                        $res['bytes_read'] += $row;
                        break;

                    case 'bytes_written':
                        $res['bytes_written'] += $row;
                        break;

                    case 'limit_maxbytes':
                        $res['limit_maxbytes'] += $row;
                        break;

                    case 'threads':
                        $res['rusage_system'][$server] = $row;
                        break;
                }
            }
        }

        return $res;
    }

    return $resp;
}

/**
 * @param $ts
 *
 * @return string
 */
function duration($ts)
{
    global $time;

    $years = (int) ((($time - $ts) / (7 * 86400)) / 52.177457);
    $rem = (int) (($time - $ts) - ($years * 52.177457 * 7 * 86400));
    $weeks = (int) (($rem) / (7 * 86400));
    $days = (int) (($rem) / 86400) - $weeks * 7;
    $hours = (int) (($rem) / 3600) - $days * 24 - $weeks * 7 * 24;
    $mins = (int) (($rem) / 60) - $hours * 60 - $days * 24 * 60 - $weeks * 7 * 24 * 60;
    $str = '';
    if ($years == 1) {
        $str .= "$years year, ";
    }
    if ($years > 1) {
        $str .= "$years years, ";
    }
    if ($weeks == 1) {
        $str .= "$weeks week, ";
    }
    if ($weeks > 1) {
        $str .= "$weeks weeks, ";
    }
    if ($days == 1) {
        $str .= "$days day,";
    }
    if ($days > 1) {
        $str .= "$days days,";
    }
    if ($hours == 1) {
        $str .= " $hours hour and";
    }
    if ($hours > 1) {
        $str .= " $hours hours and";
    }
    if ($mins == 1) {
        $str .= ' 1 minute';
    } else {
        $str .= " $mins minutes";
    }

    return $str;
}

/**
 * @param $ob
 * @param $title
 *
 * @return string
 */
function menu_entry($ob, $title)
{
    global $site_config;

    if ($ob == $_GET['op']) {
        return "
            <li class='is-link margin10'>
                <a class='active' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;op=$ob'>$title</a>
            </li>";
    }

    return "
            <li class='is-link margin10'>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;op=$ob'>$title</a>
            </li>";
}

/**
 * @return string
 */
function getMenu()
{
    global $site_config;

    $menu = "
        <ul class='level-center bg-06'>";
    if ($_GET['op'] != 4) {
        $menu .= "
            <li class='is-link margin10'>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;op={$_GET['op']}'>Refresh Data</a>
            </li>";
    } else {
        $menu .= "
            <li class='is-link margin10'>
                <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;op=2'>Back</a>
            </li>";
    }
    $menu .= menu_entry(1, 'View Host Stats');
    $menu .= menu_entry(2, 'Variables');
    $menu .= '
        </ul>';

    return $menu;
}

// TODO, AUTH
$_GET['op'] = !isset($_GET['op']) ? '1' : $_GET['op'];
$PHP_SELF = isset($_SERVER['PHP_SELF']) ? htmlentities(strip_tags($_SERVER['PHP_SELF'], '')) : '';
$PHP_SELF = $PHP_SELF . '?';
$time = time();

foreach ($_GET as $key => $g) {
    $_GET[$key] = htmlentities($g);
}

if (isset($_GET['singleout']) && $_GET['singleout'] >= 0 && $_GET['singleout'] < count($MEMCACHE_SERVERS)) {
    $MEMCACHE_SERVERS = [
        $MEMCACHE_SERVERS[$_GET['singleout']],
    ];
}

if (isset($_GET['IMG'])) {
    global $container;

    $session = $container->get(Session::class);
    $memcacheStats = getMemcacheStats();
    $memcacheStatsSingle = getMemcacheStats(false);
    /**
     * @param        $im
     * @param        $x
     * @param        $y
     * @param        $w
     * @param        $h
     * @param        $color1
     * @param        $color2
     * @param string $text
     * @param string $placeindex
     */
    function fill_box($im, $x, $y, $w, $h, $color1, $color2, $text = '', $placeindex = '')
    {
        global $col_black;

        $x1 = $x + $w - 1;
        $y1 = $y + $h - 1;
        imagerectangle($im, $x, $y1, $x1 + 1, $y + 1, $col_black);
        if ($y1 > $y) {
            imagefilledrectangle($im, $x, $y, $x1, $y1, $color2);
        } else {
            imagefilledrectangle($im, $x, $y1, $x1, $y, $color2);
        }
        imagerectangle($im, $x, $y1, $x1, $y, $color1);
        if ($text) {
            if ($placeindex > 0) {
                if ($placeindex < 16) {
                    $px = 5;
                    $py = $placeindex * 12 + 6;
                    imagefilledrectangle($im, $px + 90, $py + 3, $px + 90 - 4, $py - 3, $color2);
                    imageline($im, $x, $y + $h / 2, $px + 90, $py, $color2);
                    imagestring($im, 2, $px, $py - 6, $text, $color1);
                } else {
                    if ($placeindex < 31) {
                        $px = $x + 40 * 2;
                        $py = ($placeindex - 15) * 12 + 6;
                    } else {
                        $px = $x + 40 * 2 + 100 * intval(($placeindex - 15) / 15);
                        $py = ($placeindex % 15) * 12 + 6;
                    }
                    imagefilledrectangle($im, $px, $py + 3, $px - 4, $py - 3, $color2);
                    imageline($im, $x + $w, $y + $h / 2, $px, $py, $color2);
                    imagestring($im, 2, $px + 2, $py - 6, $text, $color1);
                }
            } else {
                imagestring($im, 4, $x + 5, $y1 - 16, $text, $color1);
            }
        }
    }

    /**
     * @param        $im
     * @param        $centerX
     * @param        $centerY
     * @param        $diameter
     * @param        $start
     * @param        $end
     * @param        $color1
     * @param        $color2
     * @param string $text
     * @param int    $placeindex
     */
    function fill_arc($im, $centerX, $centerY, $diameter, $start, $end, $color1, $color2, $text = '', $placeindex = 0)
    {
        $r = $diameter / 2;
        $w = deg2rad((360 + $start + ($end - $start) / 2) % 360);
        if (function_exists('imagefilledarc')) {
            // exists only if GD 2.0.1 is avaliable
            imagefilledarc($im, $centerX + 1, $centerY + 1, $diameter, $diameter, $start, $end, $color1, IMG_ARC_PIE);
            imagefilledarc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color2, IMG_ARC_PIE);
            imagefilledarc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color1, IMG_ARC_NOFILL | IMG_ARC_EDGED);
        } else {
            imagearc($im, $centerX, $centerY, $diameter, $diameter, $start, $end, $color2);
            imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($start)) * $r, $centerY + sin(deg2rad($start)) * $r, $color2);
            imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($start + 1)) * $r, $centerY + sin(deg2rad($start)) * $r, $color2);
            imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($end - 1)) * $r, $centerY + sin(deg2rad($end)) * $r, $color2);
            imageline($im, $centerX, $centerY, $centerX + cos(deg2rad($end)) * $r, $centerY + sin(deg2rad($end)) * $r, $color2);
            imagefill($im, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $color2);
        }
        if ($text) {
            if ($placeindex > 0) {
                imageline($im, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $diameter, $placeindex * 12, $color1);
                imagestring($im, 4, $diameter, $placeindex * 12, $text, $color1);
            } else {
                imagestring($im, 4, $centerX + $r * cos($w) / 2, $centerY + $r * sin($w) / 2, $text, $color1);
            }
        }
    }

    $size = GRAPH_SIZE; // image size
    $image = imagecreate($size + 50, $size + 10);
    $col_white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
    $col_red = imagecolorallocate($image, 0xD0, 0x60, 0x30);
    $col_green = imagecolorallocate($image, 0x60, 0xF0, 0x60);
    $col_black = imagecolorallocate($image, 0, 0, 0);
    imagecolortransparent($image, $col_white);
    switch ($_GET['IMG']) {
        case 1: // pie chart
            $tsize = $memcacheStats['limit_maxbytes'];
            $avail = $tsize - $memcacheStats['bytes'];
            $x = $y = $size / 2;
            $angle_from = 0;
            $fuzz = 0.000001;
            foreach ($memcacheStatsSingle as $serv => $mcs) {
                $free = $mcs['STAT']['limit_maxbytes'] - $mcs['STAT']['bytes'];
                $used = $mcs['STAT']['bytes'];
                if ($free > 0) {
                    // draw free
                    $angle_to = ($free * 360) / $tsize;
                    $perc = sprintf('%.2f%%', ($free * 100) / $tsize);
                    fill_arc($image, $x, $y, $size, $angle_from, $angle_from + $angle_to, $col_black, $col_green, $perc);
                    $angle_from = $angle_from + $angle_to;
                }
                if ($used > 0) {
                    // draw used
                    $angle_to = ($used * 360) / $tsize;
                    $perc = sprintf('%.2f%%', ($used * 100) / $tsize);
                    fill_arc($image, $x, $y, $size, $angle_from, $angle_from + $angle_to, $col_black, $col_red, '(' . $perc . ')');
                    $angle_from = $angle_from + $angle_to;
                }
            }
            break;

        case 2: // hit miss
            $hits = ($memcacheStats['get_hits'] == 0) ? 1 : $memcacheStats['get_hits'];
            $misses = ($memcacheStats['get_misses'] == 0) ? 1 : $memcacheStats['get_misses'];
            $total = $hits + $misses;
            fill_box($image, 30, $size, 50, -$hits * ($size - 21) / $total, $col_black, $col_green, sprintf('%.1f%%', $hits * 100 / $total));
            fill_box($image, 130, $size, 50, -max(4, ($total - $hits) * ($size - 21) / $total), $col_black, $col_red, sprintf('%.1f%%', $misses * 100 / $total));
            break;
    }
    header('Content-type: image/png');
    imagepng($image);
    exit;
}
$HTMLOUT = getMenu();
switch ($_GET['op']) {
    case 1: // host stats
        $phpversion = phpversion();
        $memcacheStats = getMemcacheStats();
        $memcacheStatsSingle = getMemcacheStats(false);
        $mem_size = $memcacheStats['limit_maxbytes'];
        $mem_used = $memcacheStats['bytes'];
        $mem_avail = $mem_size - $mem_used;
        $startTime = time() - array_sum($memcacheStats['uptime']);
        $curr_items = $memcacheStats['curr_items'];
        $total_items = $memcacheStats['total_items'];
        $hits = ($memcacheStats['get_hits'] == 0) ? 1 : $memcacheStats['get_hits'];
        $misses = ($memcacheStats['get_misses'] == 0) ? 1 : $memcacheStats['get_misses'];
        $sets = $memcacheStats['cmd_set'];
        $req_rate = sprintf('%.2f', ($hits + $misses) / ($time - $startTime));
        $hit_rate = sprintf('%.2f', ($hits) / ($time - $startTime));
        $miss_rate = sprintf('%.2f', ($misses) / ($time - $startTime));
        $set_rate = sprintf('%.2f', ($sets) / ($time - $startTime));
        $HTMLOUT .= "
            <div class='info div1'>
                <h2 class='has-text-centered'>General Cache Information</h2>";
        $body = "
            <tr>
                <td>PHP Version</td>
                <td>$phpversion</td>
            </tr>
            <tr>
                <td>Memcached Host" . plural(count($MEMCACHE_SERVERS)) . '</td>
                <td>';
        $i = 0;
        if (!isset($_GET['singleout']) && count($MEMCACHE_SERVERS) > 1) {
            foreach ($MEMCACHE_SERVERS as $server) {
                ++$i;
                $body .= "$i : <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;singleout={$i}'>{$server}</a><br>";
            }
        } else {
            $body .= "1: {$MEMCACHE_SERVERS[0]}";
        }
        if (isset($_GET['singleout'])) {
            $body .= "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache'>(all servers)</a><br>";
        }
        $body .= '
                </td>
            </tr>
            <tr>
                <td>Total Memcache Cache</td>
                <td>' . mksize($memcacheStats['limit_maxbytes']) . '</td>
            </tr>';

        $HTMLOUT .= main_table($body) . "
        </div>
        <div>
            <h2 class='has-text-centered top20'>Memcache Server Information</h2>";

        foreach ($MEMCACHE_SERVERS as $server) {
            $body = "
            <tr>
                <td>$server</td>
                <td>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;server=" . array_search($server, $MEMCACHE_SERVERS) . "&amp;op=6'>Flush this server</a>
                </td>
            </tr>
            <tr>
                <td>Start Time</td>
                <td>" . date(DATE_FORMAT, $memcacheStatsSingle[$server]['STAT']['time'] - $memcacheStatsSingle[$server]['STAT']['uptime']) . '</td>
            </tr>
            <tr>
                <td>Uptime</td>
                <td>' . duration($memcacheStatsSingle[$server]['STAT']['time'] - $memcacheStatsSingle[$server]['STAT']['uptime']) . '</td>
            </tr>
            <tr>
                <td>Memcached Server Version</td>
                <td>' . $memcacheStatsSingle[$server]['STAT']['version'] . '</td>
            </tr>
            <tr>
                <td>Used Cache Size</td>
                <td>' . mksize($memcacheStatsSingle[$server]['STAT']['bytes']) . '</td>
            </tr>
            <tr>
                <td>Total Cache Size</td>
                <td>' . mksize($memcacheStatsSingle[$server]['STAT']['limit_maxbytes']) . '</td>
            </tr>';
        }
        $HTMLOUT .= main_table($body) . "
        </div>
        <div>
            <h2 class='has-text-centered top20'>Host Status Diagrams</h2>";
        $size = 'width=' . (GRAPH_SIZE + 50) . ' height=' . (GRAPH_SIZE + 10);
        $body = '
            <tr>
                <td>Cache Usage</td>
                <td>Hits &amp; Misses</td>
            </tr>';
        $body .= "
            <tr>
                <td>
                    <img alt='' $size src='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;IMG=1&amp;" . (isset($_GET['singleout']) ? "singleout={$_GET['singleout']}&amp;" : '') . "$time'>
                </td>
                <td>
                    <img alt='' $size src='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;IMG=2&amp;" . (isset($_GET['singleout']) ? "singleout={$_GET['singleout']}&amp;" : '') . "$time'>
                </td>
            </tr>
            <tr>
                <td>
                    <span class='has-text-success'>Free: </span>" . mksize($mem_avail) . sprintf(' (%.1f%%)', $mem_avail * 100 / $mem_size) . "
                </td>
                <td>
                    <span class='has-text-success'>Hits: </span>" . $hits . sprintf(' (%.1f%%)', $hits * 100 / ($hits + $misses)) . "
                </td>
            </tr>
            <tr>
                <td>
                    <span class='has-text-danger'>Used: </span>" . mksize($mem_used) . sprintf(' (%.1f%%)', $mem_used * 100 / $mem_size) . "
                </td>
                <td>
                    <span class='has-text-danger'>Misses: </span>" . $misses . sprintf(' (%.1f%%)', $misses * 100 / ($hits + $misses)) . '
                </td>
            </tr>';
        $HTMLOUT .= main_table($body) . "
        </div>
        <div>
            <h2 class='has-text-centered top20'>Cache Information</h2>";
        $body = "
            <tr>
                <td>Current Items(total)</td>
                <td>$curr_items ($total_items)</td>
            </tr>
            <tr>
                <td>Hits</td>
                <td>{$hits}</td>
            </tr>
            <tr>
                <td>Misses</td>
                <td>{$misses}</td>
            </tr>
            <tr>
                <td>Request Rate (hits, misses)</td>
                <td>$req_rate cache requests/second</td>
            </tr>
            <tr>
                <td>Hit Rate</td>
                <td>$hit_rate cache requests/second</td>
            </tr>
            <tr>
                <td>Miss Rate</td>
                <td>$miss_rate cache requests/second</td>
            </tr>
            <tr>
                <td>Set Rate</td>
                <td>$set_rate cache requests/second</td>
            </tr>";
        $HTMLOUT .= main_table($body) . '
        </div>';

        break;

    case 2:
        $m = 0;
        $cacheItems = getCacheItems();
        $items = $cacheItems['items'];
        $totals = $cacheItems['counts'];
        $maxDump = MAX_ITEM_DUMP;
        foreach ($items as $server => $entries) {
            $HTMLOUT .= '
        <div>';
            $heading = "
            <tr>
                <th colspan='2' class='has-text-centered size_6'>$server</th>
            </tr>
            <tr>
                <th>Slab Id</th>
                <th>Info</th>
            </tr>";
            $body = '';
            foreach ($entries as $slabId => $slab) {
                $dumpUrl = $site_config['paths']['baseurl'] . '/staffpanel.php?tool=memcache&amp;op=2&amp;server=' . (array_search($server, $MEMCACHE_SERVERS)) . '&amp;dumpslab=' . $slabId;
                $body .= "
            <tr>
                <td>
                    <a href='$dumpUrl'>$slabId</a>
                </td>
                <td>
                    Item count: {$slab['number']}<br>
                    Age: " . duration($time - $slab['age']) . '<br>
                    Evicted: ' . (isset($slab['evicted']) && $slab['evicted'] == 1 ? 'Yes' : 'No');
                if ((isset($_GET['dumpslab']) && $_GET['dumpslab'] == $slabId) && (isset($_GET['server']) && $_GET['server'] == array_search($server, $MEMCACHE_SERVERS))) {
                    $body .= '<br>
                    Items: item<br>';
                    $items = dumpCacheSlab($server, $slabId, $slab['number']);
                    $i = 1;
                    foreach ($items['ITEM'] as $itemKey => $itemInfo) {
                        $itemInfo = trim($itemInfo, '[ ]');
                        $body .= "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&amp;op=4&amp;server=" . (array_search($server, $MEMCACHE_SERVERS)) . '&amp;key=' . base64_encode($itemKey) . "'>$itemKey</a>";
                        if ($i++ % 10 == 0) {
                            $body .= '<br>';
                        } elseif ($i != $slab['number'] + 1) {
                            $body .= '.';
                        }
                    }
                }
                $body .= '
                </td>
            </tr>';
                $m = 1 - $m;
            }
            $HTMLOUT .= main_table($body, $heading) . '
        </div>';
        }
        break;

    case 4:
        if (!isset($_GET['key']) || !isset($_GET['server'])) {
            $crap = 'No key set!';
            break;
        }
        $theKey = htmlentities(base64_decode($_GET['key']));
        $theserver = $MEMCACHE_SERVERS[(int) $_GET['server']];
        list($h, $p) = explode(':', $theserver);
        $r = sendMemcacheCommand($h, $p, 'get ' . $theKey);
        $HTMLOUT .= '
        <div>';
        $heading = '
            <tr>
                <th>Server</th>
                <th>Key</th>
                <th>Value</th>
                <th>Delete</th>
            </tr>';
        $body = "
            <tr>
                <td>$theserver</td>
                <td>
                    $theKey<br>
                    flag: {$r['VALUE'][$theKey]['stat']['flag']}<br>
                    Size: " . mksize($r['VALUE'][$theKey]['stat']['size']) . '
                </td>
                <td>' . chunk_split($r['VALUE'][$theKey]['value'], 40) . "</td>
                <td>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache&op=5&server={$_GET['server']}&amp;key=" . base64_encode($theKey) . "'>Delete</a>
                </td>
            </tr>";
        $HTMLOUT .= main_table($body, $heading) . '
        </div>';
        break;

    case 5:
        if (!isset($_GET['key']) || !isset($_GET['server'])) {
            $crap = 'No key set!';
            break;
        }
        $theKey = htmlentities(base64_decode($_GET['key']));
        $theserver = $MEMCACHE_SERVERS[(int) $_GET['server']];
        list($h, $p) = explode(':', $theserver);
        $r = sendMemcacheCommand($h, $p, 'delete ' . $theKey);
        $session->set('is-success', "Deleting $theKey: $r");
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache");
        break;

    case 6:
        $theserver = $MEMCACHE_SERVERS[(int) $_GET['server']];
        $r = flushServer($theserver);
        $session->set('is-success', "Flushing $theserver: $r");
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache");
        break;
}

echo stdhead('Memcached') . wrapper($HTMLOUT) . stdfoot();
