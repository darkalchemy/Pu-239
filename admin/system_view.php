<?php

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
class_check(UC_MAX);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_systemview'));
if (isset($_GET['phpinfo']) && $_GET['phpinfo']) {
    @ob_start();
    phpinfo();
    $parsed = @ob_get_contents();
    @ob_end_clean();
    preg_match('#<body>(.*)</body>#is', $parsed, $match1);
    $php_body = $match1[1];
    // PREVENT WRAP: Most cookies
    $php_body = str_replace('; ', ';<br>', $php_body);
    // PREVENT WRAP: Very long string cookies
    $php_body = str_replace('%3B', '<br>', $php_body);
    // PREVENT WRAP: Serialized array string cookies
    $php_body = str_replace(';i:', ';<br>i:', $php_body);
    // PREVENT WRAP: LS_COLORS env
    $php_body = str_replace(':*.', '<br>:*.', $php_body);
    // PREVENT WRAP: PATH env
    $php_body = str_replace('bin:/', 'bin<br>:/', $php_body);
    // PREVENT WRAP: Cookie %2C split
    $php_body = str_replace('%2C', '%2C<br>', $php_body);
    // PREVENT WRAP: Cookie , split
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
    echo stdhead('PHP Info') . wrapper($html) . stdfoot();
    die();
}
$html = [];

/**
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

$php_version = phpversion() . ' (' . @php_sapi_name() . ')';
$server_software = php_uname();
// print $php_version ." ".$server_software;
$load_limit = '--';
$server_load_found = 0;
$using_cache = 0;
$avp = sql_query("SELECT value_s FROM avps WHERE arg = 'loadlimit'") or sqlerr(__FILE__, __LINE__);
if (false !== $row = mysqli_fetch_assoc($avp)) {
    $loadinfo = explode('-', $row['value_s']);
    if (intval($loadinfo[1]) > (time() - 20)) {
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
$disabled_functions = @ini_get('disable_functions') ? str_replace(',', ', ', @ini_get('disable_functions')) : "<i>{$lang['system_noinf']}</i>";
if (strstr(strtolower(PHP_OS), 'win')) {
    $tasks = @shell_exec('tasklist');
    $tasks = str_replace(' ', ' ', $tasks);
} else {
    $tasks = @shell_exec('top -b -n 1');
    $tasks = str_replace(' ', ' ', $tasks);
}
if (!$tasks) {
    $tasks = "<i>{$lang['system_unable']}</i>";
} else {
    $tasks = '<pre>' . $tasks . '</pre>';
}
$load_limit = $load_limit . " ({$lang['system_fromcache']}" . ($using_cache == 1 ? "<span style='color:green;font-weight:bold;'>{$lang['system_true']})</span>" : "<span style='color:red;font-weight:bold;'>{$lang['system_false']})</span>");
$html[] = [
    $lang['system_mysql'],
    sql_get_version(),
];
$html[] = [
    $lang['system_php'],
    $php_version,
];
$html[] = [
    $lang['system_safe'],
    @ini_get('safe_mode') == 1 ? "<span style='color:red;font-weight:bold;'>{$lang['system_on']}</span>" : "<span style='color:green;font-weight:bold;'>{$lang['system_off']}</span>",
];
$html[] = [
    $lang['system_disabled'],
    $disabled_functions,
];
$html[] = [
    $lang['system_server_soft'],
    $server_software,
];
$html[] = [
    $lang['system_server_load'],
    $load_limit,
];
$html[] = [
    $lang['system_server_memory'],
    $total_memory,
];
$html[] = [
    $lang['system_server_avail'],
    $avail_memory,
];
$html[] = [
    $lang['system_sys_proc'],
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
        <li class='altlink margin10'>
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=system_view&amp;phpinfo=1'>{$lang['system_phpinfo']}</a>
        </li>
        <li class='altlink margin10'>
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=memcache'>Memcache</a>
        </li>
        <li class='altlink margin10'>
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=op'>OPcache</a>
        </li>
        <li class='altlink margin10'>
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=mysql_stats'>MySQL Stats</a>
        </li>
        <li class='altlink margin10'>
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=mysql_overview'>MySQL Overview</a>
        </li>
    </ul>";
$htmlout .= main_table($body);
echo stdhead($lang['system_stdhead']) . wrapper($htmlout) . stdfoot();
