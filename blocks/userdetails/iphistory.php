<?php

global $user_stuffs, $CURUSER, $fluent, $user;

if ($user['paranoia'] < 2 || $CURUSER['id'] == $id) {
    $iphistory = $cache->get('ip_history_' . $id);
    if ($iphistory === false || is_null($iphistory)) {
        $ipuse['yes'] = $ipuse['no'] = 0;
        $ipsinuse = $fluent->from('users')
            ->select(null)
            ->select('COUNT(*) AS count')
            ->select('enabled')
            ->where('INET6_NTOA(ip) = ?', $user['ip'])
            ->groupBy('enabled')
            ->fetchAll();
        if (!empty($ipsinuse[0])) {
            $ipuse[$ipsinuse[0]['enabled']] = $ipsinuse[0]['count'];
        }
        if (!empty($ipsinuse[1])) {
            $ipuse[$ipsinuse[1]['enabled']] = $ipsinuse[1]['count'];
        }

        if (($ipuse['yes'] == 1 && $ipuse['no'] == 0) || ($ipuse['no'] == 1 && $ipuse['yes'] == 0)) {
            $iphistory['use'] = '';
        } else {
            $ipcheck = $user['ip'];
            $enbl = $ipuse['yes'] ? $ipuse['yes'] . ' enabled ' : '';
            $dbl = $ipuse['no'] ? $ipuse['no'] . ' disabled ' : '';
            $mid = $enbl && $dbl ? ' and ' : '';
            $iphistory['use'] = "
        <span class='has-text-danger'>{$lang['userdetails_ip_warn']}</span>
        <a href='{$site_config['baseurl']}/staffpanel.php?tool=usersearch&amp;action=usersearch&amp;ip=$ipcheck'>
            {$lang['userdetails_ip_used']}{$enbl}{$mid}{$dbl}{$lang['userdetails_ip_users']}
        </a>";
        }
        $resip = sql_query('SELECT INET6_NTOA(ip) FROM ips WHERE userid = ' . sqlesc($id) . ' GROUP BY ip') or sqlerr(__FILE__, __LINE__);
        $iphistory['ips'] = mysqli_num_rows($resip);
        $cache->set('ip_history_' . $id, $iphistory, $site_config['expires']['iphistory']);
    }
    if (isset($addr)) {
        if ($CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
            $HTMLOUT .= "
            <tr>
                <td class='rowhead'>{$lang['userdetails_address']}</td>
                <td>
                    $addr<br>
                    {$iphistory['use']}<br>
                    <a class='button is-small top10' href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$user['id']}'>{$lang['userdetails_ip_hist']}</a>
                    <a class='button is-small top10' href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iplist&amp;id={$user['id']}'>{$lang['userdetails_ip_list']}</a>
                </td>
            </tr>";
        }
    }
    if ($CURUSER['class'] >= UC_STAFF && $iphistory['ips'] > 0) {
        $HTMLOUT .= "
            <tr>
                <td class='rowhead'>{$lang['userdetails_ip_history']}</td>
                <td>
                    {$lang['userdetails_ip_earlier']}<a href='{$site_config['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$user['id']}'>{$iphistory['ips']} {$lang['userdetails_ip_different']}</a>
                </td>
            </tr>";
    }
}
