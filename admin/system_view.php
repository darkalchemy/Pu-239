<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
class_check(UC_MAX);
global $site_config;

if (isset($_GET['phpinfo']) && $_GET['phpinfo']) {
    ob_start();
    phpinfo();
    $parsed = ob_get_contents();
    ob_end_clean();
    preg_match('#<body>(.*)</body>#is', $parsed, $match1);
    $php_body = $match1[1];
    $php_body = str_replace('; ', ';<br>', $php_body);
    $php_body = str_replace('%3B', '<br>', $php_body);
    $php_body = str_replace(';i:', ';<br>i:', $php_body);
    $php_body = str_replace(':*.', '<br>:*.', $php_body);
    $php_body = str_replace('bin:/', 'bin<br>:/', $php_body);
    $php_body = str_replace('%2C', '%2C<br>', $php_body);
    $php_body = preg_replace("#,(\d+),#", ',<br>\\1,', $php_body);
    $php_style = "<style>
body {background-color: #fff; color: #222; font-family: sans-serif;}
pre {margin: 0; font-family: monospace;}
table {border-collapse: collapse; border: 0; width: 100%; box-shadow: 1px 2px 3px #ccc;}
.center {text-align: center;}
.center table {margin: 1em auto; text-align: left;}
.center th {text-align: center !important;}
td, th {border: 1px solid #666; font-size: 75%; vertical-align: baseline; padding: 4px 5px;}
h1 {font-size: 150%;}
h2 {font-size: 125%;}
.p {text-align: left;}
.e {background-color: #ccf; width: 300px; font-weight: bold;}
.h {background-color: #99c; font-weight: bold;}
.v {background-color: #ddd; max-width: 300px; overflow-x: auto; word-wrap: break-word;}
.v i {color: #999;}
img {float: right; border: 0;}
hr {width: 100%; background-color: #ccc; border: 0; height: 1px;}
</style>\n";
    $html = $php_style . $php_body;
    $title = _('PHP Info');
    $breadcrumbs = [
        "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
        "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
    ];
    echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($html) . stdfoot();
}
$html = [];

/**
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return string
 */
function sql_get_version()
{
    $query = sql_query('SELECT VERSION() AS version');
    if (!$row = mysqli_fetch_assoc($query)) {
        unset($row);
        $query = sql_query("SHOW VARIABLES LIKE 'version'");
        $row = mysqli_fetch_row($query);
        $row['version'] = $row[1];
    }
    $true_version = $row['version'];
    $tmp = explode('.', preg_replace("#[^\d\.]#", '\\1', $row['version']));
    $mysql_version = sprintf('%d%02d%02d', $tmp[0], $tmp[1], $tmp[2]);

    return $mysql_version . ' (' . $true_version . ')';
}

$php_version = phpversion() . ' (' . php_sapi_name() . ')';
$server_software = php_uname();
$load_limit = '--';
$server_load_found = 0;
$using_cache = 0;
$avp = sql_query("SELECT value_s FROM avps WHERE arg = 'loadlimit'") or sqlerr(__FILE__, __LINE__);
if (false !== $row = mysqli_fetch_assoc($avp)) {
    $loadinfo = explode('-', $row['value_s']);
    if (isset($loadinfo[1]) && intval($loadinfo[1]) > (time() - 20)) {
        $server_load_found = 1;
        $using_cache = 1;
        $load_limit = $loadinfo[0];
    }
}
if (!$server_load_found) {
    if (@file_exists('/proc/loadavg')) {
        if ($fh = @fopen('/proc/loadavg', 'r')) {
            $data = @fread($fh, 6);
            @fclose($fh);
            $load_avg = explode(' ', $data);
            $load_limit = trim($load_avg[0]);
        }
    } elseif (strstr(strtolower(PHP_OS), 'win')) {
        $serverstats = @shell_exec("typeperf \"Processor(_Total)\% Processor Time\" -sc 1");
        if ($serverstats) {
            $server_reply = explode("\n", str_replace("\r", '', $serverstats));
            $serverstats = array_slice($server_reply, 2, 1);
            $statline = explode(',', str_replace('"', '', $serverstats[0]));
            $load_limit = round($statline[1], 4);
        }
    } else {
        if ($serverstats = @exec('uptime')) {
            preg_match("/(?:averages)?\: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $serverstats, $load);
            $load_limit = $load[1];
        }
    }
    if ($load_limit) {
        sql_query("UPDATE avps SET value_s = '" . $load_limit . '-' . time() . "' WHERE arg = 'loadlimit'") or sqlerr(__FILE__, __LINE__);
    }
}
$total_memory = $avail_memory = '--';
if (strstr(strtolower(PHP_OS), 'win')) {
    $mem = @shell_exec('systeminfo');
    if ($mem) {
        $server_reply = explode("\n", str_replace("\r", '', $mem));
        if (!empty($server_reply) && count($server_reply)) {
            foreach ($server_reply as $info) {
                if (strstr($info, 'Total Physical Memory')) {
                    $total_memory = trim(str_replace(':', '', strrchr($info, ':')));
                }
                if (strstr($info, 'Available Physical Memory')) {
                    $avail_memory = trim(str_replace(':', '', strrchr($info, ':')));
                }
            }
        }
    }
} else {
    $mem = @shell_exec('free -m');
    $server_reply = explode("\n", str_replace("\r", '', $mem));
    $mem = array_slice($server_reply, 1, 1);
    $mem = preg_split("#\s+#", $mem[0]);
    $total_memory = $mem[1] . ' MB';
    $avail_memory = $mem[3] . ' MB';
}
$disabled_functions = @ini_get('disable_functions') ? str_replace(',', ', ', @ini_get('disable_functions')) : '<i>' . _('no information') . '</i>';
if (strstr(strtolower(PHP_OS), 'win')) {
    $tasks = @shell_exec('tasklist');
    $tasks = str_replace(' ', ' ', $tasks);
} else {
    $tasks = @shell_exec('top -b -n 1');
    $tasks = str_replace(' ', ' ', $tasks);
}
if (!$tasks) {
    $tasks = '<i>' . _('Unable to obtain process information') . '</i>';
} else {
    $tasks = '<pre>' . $tasks . '</pre>';
}
$load_limit = $load_limit . ' (' . _('From Cache: ') . '' . ($using_cache == 1 ? "<span style='color:green;font-weight:bold;'>" . _('True') . ')</span>' : "<span style='color:red;font-weight:bold;'>" . _('False') . ')</span>');
$html[] = [
    _('MySQL Version'),
    sql_get_version(),
];
$html[] = [
    _('PHP Version'),
    $php_version,
];
$html[] = [
    _('Safe Mode'),
    @ini_get('safe_mode') == 1 ? "<span style='color:red;font-weight:bold;'>" . _('ON') . '</span>' : "<span style='color:green;font-weight:bold;'>" . _('OFF') . '</span>',
];
$html[] = [
    _('Disabled PHP Functions'),
    $disabled_functions,
];
$html[] = [
    _('Server Software'),
    $server_software,
];
$html[] = [
    _('Current Server Load'),
    $load_limit,
];
$html[] = [
    _('Total Server Memory'),
    $total_memory,
];
$html[] = [
    _('Available Physical Memory'),
    $avail_memory,
];
$html[] = [
    _('System Processes'),
    $tasks,
];
$body = '';
foreach ($html as $key => $value) {
    $body .= "
        <tr>
            <td class='w-20'>{$value[0]}</td>
            <td>{$value[1]}</td>
        </tr>";
}
$htmlout = "
    <ul class='level-center bg-06 bottom10'>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=system_view&amp;phpinfo=1'>" . _('PHP INFO') . "</a>
        </li>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=memcache'>Memcache</a>
        </li>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=op'>OPcache</a>
        </li>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=mysql_stats'>MySQL Stats</a>
        </li>
        <li class='is-link margin10'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=mysql_overview'>MySQL Overview</a>
        </li>
    </ul>";
$htmlout .= main_table($body);
$title = _('System Overview');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($htmlout) . stdfoot();
