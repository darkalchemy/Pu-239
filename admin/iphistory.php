<?php

declare(strict_types = 1);

use Pu239\Ban;
use Pu239\IP;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'geoip.inc';
require_once INCL_DIR . 'geoipcity.inc';
require_once INCL_DIR . 'geoipregionvars.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_iphistory'));
global $container, $site_config;

$id = $color = '';
$id = (int) $_GET['id'];
if (!is_valid_id($id)) {
    stderr($lang['stderr_error'], $lang['stderr_badid']);
}
$ip_stuffs = $container->get(IP::class);
if (isset($_GET['remove'])) {
    $remove = (int) htmlsafechars($_GET['remove']);
    $username2 = htmlsafechars($_GET['username2']);
    $deleteip = htmlsafechars($_GET['deleteip']);
    $ip_stuffs->delete($remove);
}
if (isset($_GET['setseedbox'])) {
    $setseedbox = (int) htmlsafechars($_GET['setseedbox']);
    if (is_valid_id($setseedbox)) {
        $set = [
            'seedbox' => 1,
        ];
        $ip_stuffs->set($set, $setseedbox);
    }
}
if (isset($_GET['setseedbox2'])) {
    $setseedbox2 = (int) htmlsafechars($_GET['setseedbox2']);
    if (is_valid_id($setseedbox2)) {
        $set = [
            'seedbox' => 0,
        ];
        $ip_stuffs->set($set, $setseedbox2);
    }
}
$user_stuffs = $container->get(User::class);
$user = $user_stuffs->getUserFromId($id);
$username = htmlsafechars($user['username']);
$resip = $ip_stuffs->get($id);
$ipcount = count($resip);
$HTMLOUT = "
        <h1 class='has-text-centered'>{$lang['iphistory_usedby']}" . format_username((int) $id) . "</h1>
        <p class='has-text-centered'>{$lang['iphistory_total_unique']} <b>$username</b> {$lang['iphistory_total_logged']} <b><u>$ipcount</u></b>.</p>
        <p class='has-text-centered'>
            <span class='has-text-blue'>{$lang['iphistory_single']}</span> - <span class='has-text-danger'>{$lang['iphistory_banned']}</span> - <span class='has-text-success'>{$lang['iphistory_dupe']}</span>
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
foreach ($resip as $iphistory) {
    if (!validip($iphistory['ip'])) {
        continue;
    }
    $host = gethostbyaddr($iphistory['ip']); //Hostname
    $userip = htmlsafechars($iphistory['ip']); //Users Ip
    $ipid = (int) $iphistory['id']; // IP ID
    if ($host == $userip) {
        $host = "<span class='has-text-danger'><b>{$lang['iphistory_notfound']}</b></span>";
    }
    $seedboxdetected = 'no';
    $seedboxes = [
        'kimsufi.com',
        'leaseweb.com',
        'ovh.net',
        'powserv.com',
        'server.lu',
        'xirvik.com',
        'feralhosting.com',
    ];
    foreach ($seedboxes as $seedbox) {
        if (stripos($host, $seedbox) !== false) {
            $seedboxdetected = 'yes';
        }
    }
    if ($seedboxdetected === 'yes') {
        $set = [
            'seedbox' => 1,
        ];
        $ip_stuffs->set($set, $ipid);
    }
    $lastbrowse = (int) $iphistory['lastbrowse'];
    $lastlogin = (int) $iphistory['lastlogin'];
    $lastannounce = (int) $iphistory['lastannounce'];
    $iptype = htmlsafechars($iphistory['type']);
    $queryc = 'SELECT COUNT(id) FROM(SELECT u.id FROM users AS u WHERE INET6_NTOA(u.ip) = ' . sqlesc($iphistory['ip']) . ' UNION SELECT u.id FROM users AS u RIGHT JOIN ips ON u.id=ips.userid WHERE INET6_NTOA(ips.ip) = ' . sqlesc($iphistory['ip']) . ' GROUP BY u.id) AS ipsearch';
    $resip2 = sql_query($queryc) or sqlerr(__FILE__, __LINE__);
    $arrip2 = mysqli_fetch_row($resip2);
    $ipcount = $arrip2[0];
    $ban_stuffs = $container->get(Ban::class);
    $count = $ban_stuffs->get_count($iphistory['ip']);
    if ($count === 0) {
        if ($ipcount > 1) {
            $ipshow = "<b><a class='altlink' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><span class='has-text-success'>" . htmlsafechars($iphistory['ip']) . ' </span></a></b>';
        } else {
            $ipshow = "<a class='altlink' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><b><span class='has-text-blue'>" . htmlsafechars($iphistory['ip']) . ' </span></b></a>';
        }
    } else {
        $ipshow = "<a class='altlink' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=testip&amp;action=testip&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><span class='has-text-danger'><b>" . htmlsafechars($iphistory['ip']) . ' </b></span></a>';
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
        $seedbox = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$id}&amp;setseedbox=" . (int) $iphistory['id'] . "'><span class='has-text-danger'><b>{$lang['iphistory_no']}</b></span></a>";
        $body .= "
        <tr>
            <td>{$lang['iphistory_browse']}" . get_date((int) $lastbrowse, '') . "<br>{$lang['iphistory_login']}" . get_date((int) $lastlogin, '') . "<br>{$lang['iphistory_announce']}" . get_date((int) $lastannounce, '') . "</td>
            <td>$ipshow</td>
            <td>$host</td>
            <td>$listcity, $listregion<br>$listcountry</td>
            <td>$iptype</td>
            <td>$seedbox</td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;remove=$ipid&amp;deleteip=$userip&amp;username2=$username'><b>{$lang['iphistory_delete']}</b></a></td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=bans&amp;banthisuser=$username&amp;banthisip=$userip'><b>{$lang['iphistory_ban']}</b></a></td>
        </tr>";
    } else {
        $seedbox = "<a class='altlink' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$id}&amp;setseedbox2=" . (int) $iphistory['id'] . "'><span class='has-text-green'><b>{$lang['iphistory_yes']}</b></span></a>";
        $body .= "
        <tr>
            <td>{$lang['iphistory_browse']}" . get_date((int) $lastbrowse, '') . "<br>{$lang['iphistory_login']}" . get_date((int) $lastlogin, '') . "<br>{$lang['iphistory_announce']}" . get_date((int) $lastannounce, '') . "</td>
            <td>$ipshow</td>
            <td>$host</td>
            <td>$listcity, $listregion<br>$listcountry</td>
            <td>$iptype</td>
            <td>$seedbox</td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;remove=$ipid&amp;deleteip=$userip&amp;username2=$username'><b>{$lang['iphistory_delete']}</b></a></td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=bans&amp;banthisuser=$username&amp;banthisip=$userip'><b>{$lang['iphistory_ban']}</b></a></td>
        </tr>";
    }
}

if (!empty($body)) {
    $HTMLOUT .= main_table($body, $heading, 'top20');
} else {
    $HTMLOUT .= main_div('No IP Data Available');
}

echo stdhead("{$username}'s IP History") . wrapper($HTMLOUT) . stdfoot();
