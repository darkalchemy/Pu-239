<?php

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_index'));

/**
 * @param $n
 *
 * @return string
 */
function is_s($n)
{
    global $lang;
    if ($n == 1) {
        return '';
    } else {
        return $lang['index_load_s'];
    }
}

/**
 * @return string
 */
function uptime()
{
    global $lang;
    $res = '';
    $filename = '/proc/uptime';
    $fd = fopen($filename, 'r');
    if ($fd === false) {
        $res = $lang['index_load_uptime'];
    } else {
        $uptime = fgets($fd, 64);
        fclose($fd);
        $mults = [
            4 => $lang['index_load_month'],
            7 => $lang['index_load_week'],
            24 => $lang['index_load_day'],
            60 => $lang['index_load_hour'],
            1 => $lang['index_load_minute'],
        ];
        $n = 2419200;
        $periods = [];
        $shown = false;
        $uptime = substr($uptime, 0, strpos($uptime, ' '));
        $res = '';
        foreach ($mults as $k => $v) {
            $nmbr = floor($uptime / $n);
            $uptime -= ($nmbr * $n);
            $n = $n / $k;
            if ($nmbr) {
                if ($shown) {
                    $res .= ', ';
                }
                $res .= "$nmbr $v" . is_s($nmbr);
                $shown = true;
            }
        }
        if (!$shown) {
            $res .= 'less than one minute';
        }
    }

    return $res;
}

/**
 * @param bool $return_all
 *
 * @return array|string
 */
function loadavg($return_all = false)
{
    global $lang;

    $res = '';
    $filename = '/proc/loadavg';
    $fd = fopen($filename, 'r');
    if ($fd === false) {
        $res = $lang['index_load_average'];
    } else {
        $loadavg = fgets($fd, 64);
        fclose($fd);
        $loadavg = explode(' ', $loadavg);
        if ($return_all) {
            $active = explode('/', $loadavg[3]);
            $res = [
                'last1' => $loadavg[0],
                'last5' => $loadavg[1],
                'last15' => $loadavg[2],
                'tasks' => $active[0],
                'processes' => $active[1],
                'lastpid' => $loadavg[4],
            ];
        } else {
            $res = $loadavg[2];
        }
    }

    return $res;
}

$HTMLOUT = "
    <h1 class='has-text-centered'>{$lang['index_serverload']}</h1>";
$body = "
    <div id='load'>
        <div style='width: 100%; height: 15px; background: url({$site_config['pic_baseurl']}loadbarbg.gif) repeat-x;' class='bottom20 round5'>";
$percent = min(100, round(exec('ps ax | grep -c apache') / 256 * 100));
if ($percent <= 70) {
    $pic = 'loadbargreen.gif';
} elseif ($percent <= 90) {
    $pic = 'loadbaryellow.gif';
} else {
    $pic = 'loadbarred.gif';
}
$body .= "
            <img id='load_image' style='height: 15px; width: 1px;' src='{$site_config['pic_baseurl']}{$pic}' alt='$percent&#37;' class='round5'>
        </div>
        <div class='padding20'>
            <span class='columns'>
            <span class='column'>{$lang['index_load_curr']} </span><span class='has-text-success column has-text-right is-one-third'>{$percent}{$lang['index_load_cpu']}</span>
            </span>
            <span class='columns'>
                <span class='column'>{$lang['index_load_uptime1']} </span><span class='has-text-success column has-text-right is-one-third'>" . uptime() . '</span>
            </span>';

$loadinfo = loadavg(true);
$body .= "
            <span class='columns'>
                <span class='column'>{$lang['index_load_pastmin']} </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['last1'] . "</span>
            </span>
            <span class='columns'>
                <span class='column'>{$lang['index_load_pastmin5']} </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['last5'] . "</span>
            </span>
            <span class='columns'>
                <span class='column'>{$lang['index_load_pastmin15']} </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['last15'] . "</span>
            </span>
            <span class='columns'>
                <span class='column'>{$lang['index_load_numtsk']} </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['tasks'] . "</span>
            </span>
            <span class='columns'>
                <span class='column'>{$lang['index_load_numproc']} </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['processes'] . "</span>
            </span>
            <span class='columns'>
                <span class='column'>{$lang['index_load_pid']} </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['lastpid'] . '</span>
            </span>
        </div>
    </div>';

$HTMLOUT .= main_div($body) . "
    <script>
        var percent = $percent;
        var width = document.getElementById('load').offsetWidth;
        width = Math.ceil(width / 100 * percent);
        document.getElementById('load_image').style.width = width + 'px';
        console.log(percent);
        console.log(width);
    </script>";
echo stdhead($lang['index_serverload']) . wrapper($HTMLOUT) . stdfoot();
