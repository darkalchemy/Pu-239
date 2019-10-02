<?php

declare(strict_types = 1);

use Pu239\Ban;
use Pu239\Database;
use Pu239\IP;
use Pu239\User;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'geoip.inc';
require_once INCL_DIR . 'geoipcity.inc';
require_once INCL_DIR . 'geoipregionvars.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config;

$fluent = $container->get(Database::class);
$id = $color = '';
$id = (int) $_GET['id'];
if (!is_valid_id($id)) {
    stderr(_('Error'), _('Invalid ID.'));
}
$ips_class = $container->get(IP::class);
if (isset($_GET['remove'])) {
    $remove = (int) htmlsafechars($_GET['remove']);
    $username2 = htmlsafechars($_GET['username2']);
    $deleteip = htmlsafechars($_GET['deleteip']);
    $ips_class->delete($remove);
}
if (isset($_GET['setseedbox'])) {
    $setseedbox = intval($_GET['setseedbox']);
    if (is_valid_id($setseedbox)) {
        $set = [
            'seedbox' => 1,
        ];
        $ips_class->set($set, $setseedbox);
    }
}
if (isset($_GET['setseedbox2'])) {
    $setseedbox2 = intval($_GET['setseedbox2']);
    if (is_valid_id($setseedbox2)) {
        $set = [
            'seedbox' => 0,
        ];
        $ips_class->set($set, $setseedbox2);
    }
}
$users_class = $container->get(User::class);
$user = $users_class->getUserFromId($id);
$username = htmlsafechars($user['username']);
$resip = $ips_class->get($id);
$ipcount = count($resip);
$HTMLOUT = "
        <h1 class='has-text-centered'>" . _('IP addresses used by ') . '' . format_username((int) $id) . "</h1>
        <p class='has-text-centered'>" . _('Total Unique IP Addresses') . " <b>$username</b> " . _('Has Logged In With') . " <b><u>$ipcount</u></b>.</p>
        <p class='has-text-centered'>
            <span class='is-blue'>" . _('Single') . "</span> - <span class='has-text-danger'>" . _('Banned') . "</span> - <span class='has-text-success'>" . _('Dupe Used') . '</span>
        </p>';

$heading = '
        <tr>
            <th>' . _('Last') . '</th>
            <th>' . _('Address') . '</th>
            <th>' . _('ISP/Host Name') . '</th>
            <th>' . _('Location') . '</th>
            <th>' . _('Type') . '</th>
            <th>' . _('SeedBox') . '</th>
            <th>' . _('Delete') . '</th>
            <th>' . _('Ban') . '</th>
        </tr>';

$body = '';
foreach ($resip as $iphistory) {
    if (!validip($iphistory['ip'])) {
        continue;
    }
    $host = gethostbyaddr($iphistory['ip']); //Hostname
    $userip = htmlsafechars($iphistory['ip']); //Users Ip
    $ipid = (int) $iphistory['id']; // IP ID
    if ($host == $userip) {
        $host = "<span class='has-text-danger'><b>" . _('Not Found') . '</b></span>';
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
        $ips_class->set($set, $ipid);
    }
    $lastannounce = $iphistory['type'] === 'announce' ? $iphistory['last_access'] : 0;
    $lastbrowse = $iphistory['type'] === 'browse' ? $iphistory['last_access'] : 0;
    $lastlogin = $iphistory['type'] === 'login' ? $iphistory['last_access'] : 0;
    $iptype = htmlsafechars($iphistory['type']);
    $queryc = 'SELECT COUNT(id) FROM(SELECT u.id FROM users AS u WHERE INET6_NTOA(u.ip) = ' . sqlesc($iphistory['ip']) . ' UNION SELECT u.id FROM users AS u RIGHT JOIN ips ON u.id=ips.userid WHERE INET6_NTOA(ips.ip) = ' . sqlesc($iphistory['ip']) . ' GROUP BY u.id) AS ipsearch';
    $resip2 = sql_query($queryc) or sqlerr(__FILE__, __LINE__);
    $arrip2 = mysqli_fetch_row($resip2);
    $ipcount = $arrip2[0];
    $bans_class = $container->get(Ban::class);
    $count = $bans_class->get_count($iphistory['ip']);
    if ($count === 0) {
        if ($ipcount > 1) {
            $ipshow = "<b><a class='is-link' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><span class='has-text-success'>" . htmlsafechars($iphistory['ip']) . ' </span></a></b>';
        } else {
            $ipshow = "<a class='is-link' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><b><span class='is-blue'>" . htmlsafechars($iphistory['ip']) . ' </span></b></a>';
        }
    } else {
        $ipshow = "<a class='is-link' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=testip&amp;action=testip&amp;ip=" . htmlsafechars($iphistory['ip']) . "'><span class='has-text-danger'><b>" . htmlsafechars($iphistory['ip']) . ' </b></span></a>';
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
        $seedbox = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$id}&amp;setseedbox=" . (int) $iphistory['id'] . "'><span class='has-text-danger'><b>" . _('No') . '</b></span></a>';
        $body .= '
        <tr>
            <td>' . _('Browse: ') . '' . get_date((int) $lastbrowse, '') . '<br>' . _('Login: ') . '' . get_date((int) $lastlogin, '') . '<br>' . _('Announce: ') . '' . get_date((int) $lastannounce, '') . "</td>
            <td>$ipshow</td>
            <td>$host</td>
            <td>$listcity, $listregion<br>$listcountry</td>
            <td>$iptype</td>
            <td>$seedbox</td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;remove=$ipid&amp;deleteip=$userip&amp;username2=$username'><b>" . _('Delete') . "</b></a></td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=bans&amp;banthisuser=$username&amp;banthisip=$userip'><b>" . _('Ban') . '</b></a></td>
        </tr>';
    } else {
        $seedbox = "<a class='is-link' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$id}&amp;setseedbox2=" . (int) $iphistory['id'] . "'><span class='is-success'><b>" . _('yes') . '</b></span></a>';
        $body .= '
        <tr>
            <td>' . _('Browse: ') . '' . get_date((int) $lastbrowse, '') . '<br>' . _('Login: ') . '' . get_date((int) $lastlogin, '') . '<br>' . _('Announce: ') . '' . get_date((int) $lastannounce, '') . "</td>
            <td>$ipshow</td>
            <td>$host</td>
            <td>$listcity, $listregion<br>$listcountry</td>
            <td>$iptype</td>
            <td>$seedbox</td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;remove=$ipid&amp;deleteip=$userip&amp;username2=$username'><b>" . _('Delete') . "</b></a></td>
            <td><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=bans&amp;banthisuser=$username&amp;banthisip=$userip'><b>" . _('Ban') . '</b></a></td>
        </tr>';
    }
}

if (!empty($body)) {
    $HTMLOUT .= main_table($body, $heading, 'top20');
} else {
    $HTMLOUT .= main_div('No IP Data Available');
}
$title = _('IP History');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
