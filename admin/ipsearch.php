<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_ipsearch'));
global $site_config;

$HTMLOUT = $ip = $mask = '';
$HTMLOUT .= begin_main_frame();
$ip = isset($_GET['ip']) ? htmlsafechars(trim($_GET['ip'])) : '';
if ($ip) {
    $regex = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))(\.\b|$)){4}$/";
    if (!preg_match($regex, $ip)) {
        $HTMLOUT .= stdmsg($lang['ipsearch_error'], $lang['ipsearch_invalid']);
        $HTMLOUT .= end_main_frame();
        echo stdhead('IP Search') . wrapper($HTMLOUT) . stdfoot();
        die();
    }
    $mask = isset($_GET['mask']) ? htmlsafechars(trim($_GET['mask'])) : '';
    if ($mask == '' || $mask === '255.255.255.255') {
        $where1 = "u.ip = '$ip'";
        $where2 = "ips.ip = '$ip'";
        $dom = @gethostbyaddr($ip);
        if ($dom == $ip || @gethostbyname($dom) != $ip) {
            $addr = '';
        } else {
            $addr = $dom;
        }
    } else {
        if (substr($mask, 0, 1) == '/') {
            $n = substr($mask, 1, strlen($mask) - 1);
            if (!is_numeric($n) || $n < 0 || $n > 32) {
                $HTMLOUT .= stdmsg($lang['ipsearch_error'], $lang['ipsearch_subnet']);
                $HTMLOUT .= end_main_frame();
                echo stdhead('IP Search') . wrapper($HTMLOUT) . stdfoot();
                die();
            } else {
                $mask = long2ip(pow(2, 32) - pow(2, 32 - $n));
            }
        } elseif (!preg_match($regex, $mask)) {
            $HTMLOUT .= stdmsg($lang['ipsearch_error'], $lang['ipsearch_subnet']);
            $HTMLOUT .= end_main_frame();
            echo stdhead('IP Search') . wrapper($HTMLOUT) . stdfoot();
            die();
        }
        $where1 = "INET_ATON(u.ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')";
        $where2 = "INET_ATON(ips.ip) & INET_ATON('$mask') = INET_ATON('$ip') & INET_ATON('$mask')";
        $addr = "{$lang['ipsearch_mask']} $mask";
    }
    $queryc = "SELECT COUNT(id) FROM
           (
             SELECT u.id FROM users AS u WHERE $where1
             UNION SELECT u.id FROM users AS u RIGHT JOIN ips ON u.id=ips.userid WHERE $where2
             GROUP BY u.id
           ) AS ipsearch";
    $res = sql_query($queryc) or sqlerr(__FILE__, __LINE__);
    $row = mysqli_fetch_array($res);
    $count = (int) $row[0];
    if ($count == 0) {
        $HTMLOUT .= "<br><b>No users found</b>\n";
        $HTMLOUT .= end_main_frame();
        echo stdhead('IP sEARCH') . wrapper($HTMLOUT) . stdfoot();
        die();
    }
    $order = isset($_GET['order']) && $_GET['order'];
    $page = isset($_GET['page']) && (int) $_GET['page'];
    $perpage = 20;
    $pager = pager($perpage, $count, "staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=$ip&amp;mask=$mask&amp;order=$order&amp;");
    if ($order === 'registered') {
        $orderby = 'registered DESC';
    } elseif ($order === 'username') {
        $orderby = 'UPPER(username) ASC';
    } elseif ($order === 'email') {
        $orderby = 'email ASC';
    } elseif ($order === 'last_ip') {
        $orderby = 'last_ip ASC';
    } elseif ($order === 'last_access') {
        $orderby = 'last_ip ASC';
    } else {
        $orderby = 'access DESC';
    }
    $query1 = "SELECT * FROM (
          SELECT u.id, u.username, INET6_NTOA(u.ip) AS ip, INET6_NTOA(u.ip) AS last_ip, u.last_access, u.last_access AS access, u.email, u.invitedby, u.registered, u.class, u.uploaded, u.downloaded, u.donor, u.status, u.warned, u.leechwarn, u.chatpost, u.pirate, u.king
          FROM users AS u
          WHERE $where1
          UNION SELECT u.id, u.username, INET6_NTOA(ips.ip) AS ip, INET6_NTOA(u.ip) as last_ip, u.last_access, max(ips.lastlogin) AS access, u.email, u.invitedby, u.registered, u.class, u.uploaded, u.downloaded, u.donor, u.status, u.warned, u.leechwarn, u.chatpost, u.pirate, u.king
          FROM users AS u
          RIGHT JOIN ips ON u.id=ips.userid
          WHERE $where2
          GROUP BY u.id ) as ipsearch
          GROUP BY id
          ORDER BY $orderby
          " . $pager['limit'] . '';
    $res = sql_query($query1) or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= begin_frame($count . " {$lang['ipsearch_have_used']}" . format_comment($ip) . ' (' . format_comment($addr) . ')', true);
    if ($count > $perpage) {
        $HTMLOUT .= $pager['pagertop'];
    }
    $HTMLOUT .= "<table>\n";
    $HTMLOUT .= "<tr>
      <td class='colhead'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=$ip&amp;mask=$mask&amp;order=username'>{$lang['ipsearch_username']}</a></td>" . "<td class='colhead'>{$lang['ipsearch_ratio']}</td>" . "<td class='colhead'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=$ip&amp;mask=$mask&amp;order=email'>{$lang['ipsearch_email']}</a></td>" . "<td class='colhead'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=$ip&amp;mask=$mask&amp;order=last_ip'>{$lang['ipsearch_ip']}</a></td>" . "<td class='colhead'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=$ip&amp;mask=$mask&amp;order=last_access'>{$lang['ipsearch_access']}</a></td>" . "<td class='colhead'>{$lang['ipsearch_num']}</td>" . "<td class='colhead'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=$ip&amp;mask=$mask'>{$lang['ipsearch_access']} on <br>" . format_comment($ip) . '</a></td>' . "<td class='colhead'><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=ipsearch&amp;action=ipsearch&amp;ip=$ip&amp;mask=$mask&amp;order=added'>{$lang['ipsearch_added']}</a></td>" . "<td class='colhead'>{$lang['ipsearch_invited']}</td></tr>";
    while ($user = mysqli_fetch_assoc($res)) {
        if ($user['registered'] == '0') {
            $user['registered'] = '---';
        }
        if ($user['last_access'] == '0') {
            $user['last_access'] = '---';
        }
        if ($user['last_ip']) {
            $count = $fluent->from('bans')
                            ->select(null)
                            ->select('COUNT(id) AS count')
                            ->where('INET6_NTOA(first) <= ?', $user['last_ip'])
                            ->where('INET6_NTOA(last)>= ?', $user['last_ip'])
                            ->fetch('count');

            if ($count == 0) {
                $ipstr = $user['last_ip'];
            } else {
                $ipstr = "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=testip&amp;action=testip&amp;ip=" . htmlsafechars($user['last_ip']) . "'><span style='color: #FF0000;'><b>" . format_comment($user['last_ip']) . '</b></span></a>';
            }
        } else {
            $ipstr = '---';
        }
        $resip = sql_query('SELECT INET6_NTOA(ip) FROM ips WHERE userid=' . sqlesc($user['id']) . ' GROUP BY ips.ip') or sqlerr(__FILE__, __LINE__);
        $iphistory = mysqli_num_rows($resip);
        if ($user['invitedby'] > 0) {
            $res2 = sql_query('SELECT username FROM users WHERE id=' . sqlesc($user['invitedby']));
            $array = mysqli_fetch_assoc($res2);
            $invitedby = $array['id'];
            if ($invitedby == '') {
                $invitedby = "<i>[{$lang['ipsearch_deleted']}]</i>";
            } else {
                $invitedby = format_username((int) $invitedby);
            }
        } else {
            $invitedby = '--';
        }
        $HTMLOUT .= '<tr>
           <td>' . format_username((int) $user['id']) . '</td>' . '<td>' . member_ratio($user['uploaded'], $user['downloaded']) . '</td>
          <td>' . $user['email'] . '</td><td>' . $ipstr . '</td>
          <td><div>' . get_date((int) $user['last_access'], 'DATE', 1, 0) . "</div></td>
          <td><div><b><a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id=" . (int) $user['id'] . "'>$iphistory</a></b></div></td>
          <td><div>" . get_date((int) $user['access'], 'DATE', 1, 0) . '</div></td>
          <td><div>' . get_date((int) $user['registered'], 'DATE', 1, 0) . '</div></td>
          <td><div>' . $invitedby . "</div></td>
          </tr>\n";
    }
    $HTMLOUT .= '</table>';
    if ($count > $perpage) {
        $pager['pagerbottom'];
    }
    $HTMLOUT .= end_frame();
}
$HTMLOUT .= end_main_frame();
echo stdhead($lang['ipsearch_stdhead']) . wrapper($HTMLOUT) . stdfoot();
die();
