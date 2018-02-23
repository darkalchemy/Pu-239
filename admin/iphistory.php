<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'geoip.inc';
require_once INCL_DIR . 'geoipcity.inc';
require_once INCL_DIR . 'geoipregionvars.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $site_config, $lang;

$lang = array_merge($lang, load_language('ad_iphistory'));
//Clear the fields for use.
$id = $color = '';
$id = (int) $_GET['id'];
if (!is_valid_id($id)) {
    stderr("{$lang['stderr_error']}", "{$lang['stderr_badid']}");
}
/// Custom function....
if (isset($_GET['remove'])) {
    $remove    = htmlsafechars($_GET['remove']);
    $username2 = htmlsafechars($_GET['username2']);
    $deleteip  = htmlsafechars($_GET['deleteip']);
    //write_logs("<span style='color: #FA5858;'><b>{$lang['stderr_wipe']}</b></span> (<a href='{$site_config['baseurl']}/userdetails.php?id=$CURUSER[id]'><b>$CURUSER[username]</b></a>){$lang['stderr_justwipe']}(<b>$deleteip</b>) {$lang['stderr_from']}(<a href='{$site_config['baseurl']}/userdetails.php?id=$id'><b>$username2</b></a>)'s Ip History.", 'log');
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
$res      = sql_query('SELECT username FROM users WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
$user     = mysqli_fetch_array($res)                                        or stderr("{$lang['stderr_error']}", "{$lang['stderr_noid']}");
$username = htmlsafechars($user['username']);
$resip    = sql_query('SELECT * FROM ips WHERE userid = ' . sqlesc($id) . ' GROUP BY ip ORDER BY id DESC') or sqlerr(__FILE__, __LINE__);
$ipcount  = mysqli_num_rows($resip);
$HTMLOUT  = '';
$HTMLOUT .= "<table >
                <tr><td class='colhead'>{$lang['iphistory_usedby']}<a class='altlink_white' href='{$site_config['baseurl']}/userdetails.php?id=$id'><b>$username</b></a></td></tr>
                <tr>
                <td class='heading2'>{$lang['iphistory_total_unique']} <b>$username</b> {$lang['iphistory_total_logged']} <b><u>$ipcount</u></b>.</td></tr>
                <tr>
                <td class='heading2'><b><span style='color: blue'>{$lang['iphistory_single']}</font> - <span class='has-text-danger;'>{$lang['iphistory_banned']}</span> - <font color='black'>{$lang['iphistory_dupe']}</span></b></td>
                </tr>
                </table><br>

                <table >
                <tr>
                <td class='colhead'>{$lang['iphistory_last']}</td>
                <td class='colhead'>{$lang['iphistory_address']}</td>
                <td class='colhead'>{$lang['iphistory_isphost']}</td>
                <td class='colhead'>{$lang['iphistory_location']}</td>
                <td class='colhead'>{$lang['iphistory_type']}</td>
                <td class='colhead'>{$lang['iphistory_seedbox']}</td>
                <td class='colhead'>{$lang['iphistory_delete']}</td>
                <td class='colhead'>{$lang['iphistory_ban']}</td>
                </tr>";
while ($iphistory = mysqli_fetch_array($resip)) {
    if (!filter_var($iphistory['ip'], FILTER_VALIDATE_IP)) {
        continue;
    }
    $host   = gethostbyaddr($iphistory['ip']); //Hostname
    $userip = htmlsafechars($iphistory['ip']); //Users Ip
    $ipid   = (int) $iphistory['id']; // IP ID
    if ($host == $userip) {
        $host = "<span class='has-text-danger'><b>{$lang['iphistory_notfound']}</b></span>";
    }
    $seedboxdetected = ''; //Clear the field
    if (strpos($host, 'kimsufi.com')) {
        $seedboxdetected = 'yes';
    }
    if (strpos($host, 'leaseweb.com')) {
        $seedboxdetected = 'yes';
    }
    if (strpos($host, 'ovh.net')) {
        $seedboxdetected = 'yes';
    }
    if (strpos($host, 'powserv.com')) {
        $seedboxdetected = 'yes';
    }
    if (strpos($host, 'server.lu')) {
        $seedboxdetected = 'yes';
    }
    if (strpos($host, 'xirvik.com')) {
        $seedboxdetected = 'yes';
    }
    if (strpos($host, 'feralhosting.com')) {
        $seedboxdetected = 'yes';
    }
    if ('yes' == $seedboxdetected) {
        sql_query('UPDATE ips SET seedbox=1 WHERE id =' . sqlesc($ipid)) or sqlerr(__FILE__, __LINE__);
    }
    $lastbrowse   = (int) $iphistory['lastbrowse'];
    $lastlogin    = (int) $iphistory['lastlogin'];
    $lastannounce = (int) $iphistory['lastannounce'];
    $iptype       = htmlsafechars($iphistory['type']);
    $queryc       = 'SELECT COUNT(id) FROM (SELECT u.id FROM users AS u WHERE u.ip = ' . ipToStorageFormat($iphistory['ip']) . ' UNION SELECT u.id FROM users AS u RIGHT JOIN ips ON u.id= ips.userid WHERE ips.ip =' . ipToStorageFormat($iphistory['ip']) . ' GROUP BY u.id) AS ipsearch';
    $resip2       = sql_query($queryc) or sqlerr(__FILE__, __LINE__);
    $arrip2       = mysqli_fetch_row($resip2);
    $ipcount      = $arrip2[0];
    $banres       = sql_query('SELECT COUNT(*) FROM bans WHERE ' . ipToStorageFormat($iphistory['ip']) . ' >= first AND ' . ipToStorageFormat($iphistory['ip']) . ' <= last') or sqlerr(__FILE__, __LINE__);
    $banarr       = mysqli_fetch_row($banres);
    if (0 == $banarr[0]) {
        if ($ipcount > 1) {
            $ipshow = "<b><a class='altlink' href='{$site_config['baseurl']}/staffpanel . php ? tool = ipsearch & amp;action = ipsearch & amp;ip = " . htmlsafechars($iphistory['ip']) . "'><font color='black'>" . htmlsafechars($iphistory['ip']) . ' </font ></a ></b > ';
        } else {
            $ipshow = "<a class='altlink' href='{$site_config['baseurl']}/staffpanel . php ? tool = ipsearch & amp;action = ipsearch & amp;ip = " . htmlsafechars($iphistory['ip']) . "'><b><font color='blue'>" . htmlsafechars($iphistory['ip']) . ' </font ></b ></a > ';
        }
    } else {
        $ipshow = "<a class='altlink' href='{$site_config['baseurl']}/staffpanel . php ? tool = testip & amp;action = testip & amp;ip = " . htmlsafechars($iphistory['ip']) . "'><font color='red'><b>" . htmlsafechars($iphistory['ip']) . ' </b ></font ></a > ';
    }
    // User IP listed for GeoIP tracing
    $gi          = geoip_open(ROOT_DIR . 'GeoIP / GeoIP . dat', GEOIP_STANDARD);
    $countrybyip = geoip_country_name_by_addr($gi, $userip);
    $listcountry = $countrybyip;
    geoip_close($gi);
    // end fetch geoip code
    // User IP listed for GeoIP tracing
    $gi         = geoip_open(ROOT_DIR . 'GeoIP / GeoLiteCity . dat', GEOIP_STANDARD);
    $citybyip   = geoip_record_by_addr($gi, $userip);
    $listcity   = $citybyip->city;
    $listregion = $citybyip->region;
    geoip_close($gi);
    // end fetch geoip code
    //Is this a seedbox check
    $seedbox = htmlsafechars($iphistory['seedbox']);
    if ('0' == $seedbox) {
        $seedbox = "<a href='{$site_config['baseurl']}/staffpanel . php ? tool = iphistory & amp;action = iphistory & amp;id = $id & amp;setseedbox = " . (int) $iphistory['id'] . "'><span class='has-text-danger'><b>{$lang['iphistory_no']}</b></span></a>";
        $HTMLOUT .= "<tr>
                <td class='heading2'>{$lang['iphistory_browse']}" . get_date($lastbrowse, '') . "<br>{$lang['iphistory_login']}" . get_date($lastlogin, '') . "<br>{$lang['iphistory_ann']}" . get_date($lastannounce, '') . "</td>
                <td class='heading2'>$ipshow</td>
                <td class='heading2'>$host</td>
                <td class='heading2'>$listcity, $listregion<br>$listcountry</td>
                <td class='heading2'>$iptype</td>
                <td class='heading2'>$seedbox</td>
                <td class='heading2'><a href='{$site_config['baseurl']}/staffpanel . php ? tool = iphistory & amp;action = iphistory & amp;id = $id & amp;remove = $ipid & amp;deleteip = $userip & amp;username2 = $username'><b>{$lang['iphistory_delete']}</b></a></td>
                <td class='heading2'><a href='{$site_config['baseurl']}/staffpanel . php ? tool = iphistory & amp;action = bans & amp;banthisuser = $username & amp;banthisip = $userip'><b>{$lang['iphistory_ban']}</b></a></td>
                </tr>";
    } else {
        $seedbox = "<a class='altlink' href='{$site_config['baseurl']}/staffpanel . php ? tool = iphistory & amp;action = iphistory & amp;id = $id & amp;setseedbox2 = " . (int) $iphistory['id'] . "'><span style='color: Green;'><b>{$lang['iphistory_yes']}</b></span></a>";
        $color   = '#CCFFFF';
        $HTMLOUT .= "<tr>
                <td class='heading2' style='background-color:$color'>{$lang['iphistory_browse']}" . get_date($lastbrowse, '') . "<br>{$lang['iphistory_login']}" . get_date($lastlogin, '') . "<br>{$lang['iphistory_announce']}" . get_date($lastannounce, '') . "</td>
                <td class='heading2' style='background-color:$color'>$ipshow</td>
                <td class='heading2' style='background-color:$color'>$host</td>
                <td class='heading2' style='background-color:$color'>$listcity, $listregion<br>$listcountry</td>
                <td class='heading2' style='background-color:$color'>$iptype</td>
                <td class='heading2' style='background-color:$color'>$seedbox</td>
                <td class='heading2' style='background-color:$color'><a href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=$id&amp;remove=$ipid&amp;deleteip=$userip&amp;username2=$username'><b>{$lang['iphistory_delete']}</b></a></td>
                <td class='heading2' style='background-color:$color'><a href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=bans&amp;banthisuser=$username&amp;banthisip=$userip'><b>{$lang['iphistory_ban']}</b></a></td>
                </tr>";
    } // End Seedbox Check
}
$HTMLOUT .= '</table>';
echo stdhead("{$username}'s IP History") . $HTMLOUT . stdfoot();
