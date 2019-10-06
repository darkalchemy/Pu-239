<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config;

/**
 * @param $n
 *
 * @return string
 */
function is_s($n)
{
    if ($n == 1) {
        return '';
    } else {
        return _('s');
    }
}

/**
 * @return string
 */
function uptime()
{
    $filename = '/proc/uptime';
    $fd = fopen($filename, 'r');
    if ($fd === false) {
        $res = _('Could not retrieve uptime');
    } else {
        $uptime = fgets($fd, 64);
        fclose($fd);
        $mults = [
            4 => _('month'),
            7 => _('week'),
            24 => _('day'),
            60 => _('hour'),
            1 => _('minute'),
        ];
        $n = 2419200;
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
            $res .= _('less than one minute');
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
    $filename = '/proc/loadavg';
    $fd = fopen($filename, 'r');
    if ($fd === false) {
        $res = _('Could not retrieve load average');
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
    <h1 class='has-text-centered'>" . _('Server Load') . '</h1>';
$body = "
    <div id='load' class='padding20'>
        <div style='width: 100%; height: 15px; background: url({$site_config['paths']['images_baseurl']}/loadbarbg.gif) repeat-x;' class='bottom20 round5'>";
$percent = min(100, round(exec('ps ax | grep -c apache') / 256 * 100));
if ($percent <= 70) {
    $pic = 'loadbargreen.gif';
} elseif ($percent <= 90) {
    $pic = 'loadbaryellow.gif';
} else {
    $pic = 'loadbarred.gif';
}
$body .= "
            <img id='load_image' style='height: 15px; width: 1px;' src='{$site_config['paths']['images_baseurl']}{$pic}' alt='$percent&#37;' class='round5'>
        </div>
        <div class='padding20'>
            <span class='columns bg-02 round10'>
                <span class='column'>" . _('Currently') . ": </span><span class='has-text-success column has-text-right is-one-third'>{$percent}&#37; " . _('CPU usage.') . "</span>
            </span>
            <span class='columns'>
                <span class='column'>" . _('Uptime') . ": </span><span class='has-text-success column has-text-right is-one-third'>" . uptime() . '</span>
            </span>';

$loadinfo = loadavg(true);
$body .= "
            <span class='columns bg-02 round10'>
                <span class='column'>" . _('Load average for processes running for the past minute') . ": </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['last1'] . "</span>
            </span>
            <span class='columns'>
                <span class='column'>" . _('Load average for processes running for the past 5 minutes') . ": </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['last5'] . "</span>
            </span>
            <span class='columns bg-02 round10'>
                <span class='column'>" . _('Load average for processes running for the past 15 minutes') . ": </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['last15'] . "</span>
            </span>
            <span class='columns'>
                <span class='column'>" . _('Number of tasks currently running') . ": </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['tasks'] . "</span>
            </span>
            <span class='columns bg-02 round10'>
                <span class='column'>" . _('Number of processes currently running') . ": </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['processes'] . "</span>
            </span>
            <span class='columns'>
                <span class='column'>" . _('PID of last process executed') . ": </span><span class='has-text-success column has-text-right is-one-third'>" . $loadinfo['lastpid'] . '</span>
            </span>
        </div>
    </div>';

$HTMLOUT .= main_div($body) . "
    <script>
        var percent = {$percent};
        var width = document.getElementById('load').offsetWidth;
        width = Math.ceil(width / 100 * percent);
        document.getElementById('load_image').style.width = width + 'px';
        console.log(percent);
        console.log(width);
    </script>";
$title = _('Server Load');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
