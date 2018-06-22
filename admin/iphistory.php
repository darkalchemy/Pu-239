<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'geoip.inc';
require_once INCL_DIR . 'geoipcity.inc';
require_once INCL_DIR . 'geoipregionvars.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang, $user_stuffs;

$lang = array_merge($lang, load_language('ad_iphistory'));
//Clear the fields for use.
$id = $color = '';
$id = (int) $_GET['id'];
if (!is_valid_id($id)) {
    stderr("{$lang['stderr_error']}", "{$lang['stderr_badid']}");
}
/// Custom function....
if (isset($_GET['remove'])) {
    $remove = htmlsafechars($_GET['remove']);
    $username2 = htmlsafechars($_GET['username2']);
    $deleteip = htmlsafechars($_GET['deleteip']);
    sql_query('DELETE FROM ips WHERE id = ' . sqlesc($remove)) or sqlerr(__FILE__, __LINE__);
}
if (isset($_GET['setseedbox'])) {
    $setseedbox = htmlsafechars($_GET['setseedbox']);
    if (is_valid_id($setseedbox)) {
        sql_query('UPDATE ips SET seedbox = 1 WHERE id =' . sqlesc($setseedbox)) or sqlerr(__FILE__, __LINE__);
    }
}
if (isset($_GET['setseedbox2'])) {
    $setseedbox2 = htmlsafechars($_GET['setseedbox2']);
    if (is_valid_id($setseedbox2)) {
        sql_query('UPDATE ips SET seedbox = 0 WHERE id =' . sqlesc($setseedbox2)) or sqlerr(__FILE__, __LINE__);
    }
}

$user = $user_stuffs->getUserFromId($id);
$username = htmlsafechars($user['username']);
$resip = sql_query('SELECT *, INET6_NTOA(ip) AS ip FROM ips WHERE userid = ' . sqlesc($id) . ' GROUP BY ip') or sqlerr(__FILE__, __LINE__);
$ipcount = mysqli_num_rows($resip);
$HTMLOUT = '';
$HTMLOUT .= "
        <h1 class='has-text-centered'>{$lang['iphistory_usedby']}" . format_username($id) . "</h1>
        <p class='has-text-centered'>{$lang['iphistory_total_unique']} <b>$username</b> {$lang['iphistory_total_logged']} <b><u>$ipcount</u></b>.</p>
        <p class='has-text-centered'>
            <span class='has-text-blue'>{$lang['iphistory_single']}</span> - <span class='has-text-danger'>{$lang['iphistory_banned']}</span> - <span class='has-text-lime'>{$lang['iphistory_dupe']}</span>
        </p>";

$heading = "
        <tr>
            <th>{$lang['iphistory_last']}</th>
            <th>{$lang['iphistory_address']}</th>
            <th>{$lang['iphistory_isphost']}</th>
            <th>{$lang['iphistory_location']}</th>
            <th>{$lang['iphistory_type']}</th>
            <th>{$lang['iphistory_seedbox']}</th>
            <th>{$lang['iphistory_delete']}</th>
            <th>{$lang['iphistory_ban']}</th>
        </tr>";

$body = '';
while ($iphistory = mysqli_fetch_array($resip)) {
    if (!filter_var($iphistory['ip'], FILTER_VALIDATE_IP)) {
        continue;
    }
    $host = gethostbyaddr($iphistory['ip']); //Hostname
    $userip = htmlsafechars($iphistory['ip']); //Users Ip
    $ipid = (int) $iphistory['id']; // IP ID
    if ($host == $userip) {
        $host = "<span class='has-text-danger'><b>{$lang['iphistory_notfound']}</b></span>";
    }
    $seedboxdetected = 'no';
    $seedboxes = ['kimsufi.com', 'leaseweb.com', 'ovh.net', 'powserv.com', 'server.lu', 'xirvik.com', 'feralhosting.com'];
    foreach ($seedboxes as $seedbox) {
        if (stripos($host, $seedbox) !== false) {
            $seedboxdetected = 'yes';
        }
    }
    if ($seedboxdetected === 'yes') {
        sql_query('UPDATE ips SET seedbox = 1 WHERE id =' . sqlesc($ipid)) or sqlerr(__FILE__, __LINE__);
    }
    $lastbrowse = (int) $iphistory['lastbrowse'];
    $lastlogin = (int) $iphistory['lastlogin'];
    $lastannounce = (int) $iphistory['lastannounce'];
    $iptype = htmlsafechars($iphistory['type']);
    $queryc = 'SELECT COUNT(id) FROM (SELECT u.id FROM users AS u WHERE u.ip = ' . ipToStorageFormat($iphistory['ip']) . ' UNION SELECT u.id FROM users AS u RIGHT JOIN ips ON u.id= ips.userid WHERE ips.ip =' . ipToStorageFormat($iphistory['ip']) . ' GROUP BY u.id) AS ipsearch';
    $resip2 = sql_query($queryc) or sqlerr(__FILE__, __LINE__);
    $arrip2 = mysqli_fetch_row($resip2);
    $ipcount = $arrip2[0];
    $banres = sql_query('SELECT COUNT(*) FROM bans WHERE ' . ipToStorageFormat($iphistory['ip']) . ' >= first AND ' . ipToStorageFormat($iphistory['ip']) . ' <= last') or sqlerr(__FILE__, __LINE__);
    $banarr = mysqli_fetch_row($banres);
    if ($banarr[0] == 0) {
        if ($ipcount > 1) {
            $ipshow = "<b><a class='altlink' href='{$site_config['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><span class='has-text-lime'>" . htmlsafechars($iphistory['ip']) . ' </span></a ></b > ';
        } else {
            $ipshow = "<a class='altlink' href='{$site_config['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><b><span class='has-text-blue'>" . htmlsafechars($iphistory['ip']) . ' </span></b ></a > ';
        }
    } else {
        $ipshow = "<a class='altlink' href='{$site_config['baseurl']}/staffpanel.php?tool=testip&amp;action=testip&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><span class='has-text-red'><b>" . htmlsafechars($iphistory['ip']) . ' </b ></span></a > ';
    }
    // User IP listed for GeoIP tracing
    $gi = geoip_open(ROOT_DIR . 'GeoIP' . DIRECTORY_SEPARATOR . 'GeoIP.dat', GEOIP_STANDARD);
    $countrybyip = geoip_country_name_by_addr($gi, $userip);
    $listcountry = $countrybyip;
    geoip_close($gi);
    // end fetch geoip code
    // User IP listed for GeoIP tracing
    $gi = geoip_open(ROOT_DIR . 'GeoIP' . DIRECTORY_SEPARATOR . 'GeoLiteCity.dat', GEOIP_STANDARD);
    $citybyip = geoip_record_by_addr($gi, $userip);
    $listcity = @$citybyip->city;
    $listregion = @$citybyip->region;
    geoip_close($gi);
    // end fetch geoip code
    //Is this a seedbox check
    $seedbox = htmlsafechars($iphistory['seedbox']);

    if ($seedbox == '0') {
        $seedbox = "<a href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;setseedbox=" . (int) $iphistory['id'] . "'><span class='has-text-danger'><b>{$lang['iphistory_no']}</b></span></a>";
        $body .= "
        <tr>
            <td>{$lang['iphistory_browse']}" . get_date($lastbrowse, '') . "<br>{$lang['iphistory_login']}" . get_date($lastlogin, '') . "<br>{$lang['iphistory_announce']}" . get_date($lastannounce, '') . "</td>
            <td>$ipshow</td>
            <td>$host</td>
            <td>$listcity, $listregion<br>$listcountry</td>
            <td>$iptype</td>
            <td>$seedbox</td>
            <td><a href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;remove=$ipid&amp;deleteip=$userip&amp;username2=$username'><b>{$lang['iphistory_delete']}</b></a></td>
            <td><a href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=bans&amp;banthisuser=$username&amp;banthisip=$userip'><b>{$lang['iphistory_ban']}</b></a></td>
        </tr>";
    } else {
        $seedbox = "<a class='altlink' href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;setseedbox2=" . (int) $iphistory['id'] . "'><span class='has-text-green'><b>{$lang['iphistory_yes']}</b></span></a>";
        $body .= "
        <tr>
            <td>{$lang['iphistory_browse']}" . get_date($lastbrowse, '') . "<br>{$lang['iphistory_login']}" . get_date($lastlogin, '') . "<br>{$lang['iphistory_announce']}" . get_date($lastannounce, '') . "</td>
            <td>$ipshow</td>
            <td>$host</td>
            <td>$listcity, $listregion<br>$listcountry</td>
            <td>$iptype</td>
            <td>$seedbox</td>
            <td><a href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;remove=$ipid&amp;deleteip=$userip&amp;username2=$username'><b>{$lang['iphistory_delete']}</b></a></td>
            <td><a href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=bans&amp;banthisuser=$username&amp;banthisip=$userip'><b>{$lang['iphistory_ban']}</b></a></td>
        </tr>";
    }
}

if (!empty($body)) {
    $HTMLOUT .= main_table($body, $heading, 'top20');
} else {
    $HTMLOUT .= main_div('No IP Data Available');
}

echo stdhead("{$username}'s IP History") . wrapper($HTMLOUT) . stdfoot();
