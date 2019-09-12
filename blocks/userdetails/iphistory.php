<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use Pu239\Cache;
use Pu239\Database;

global $container, $lang, $site_config, $CURUSER, $user;

$cache = $container->get(Cache::class);
$auth = $container->get(Auth::class);
$user['ip'] = $auth->getIpAddress();
if ($user['paranoia'] < 2 || $CURUSER['id'] == $id) {
    $cache->delete('ip_history_' . $id);
    $iphistory = $cache->get('ip_history_' . $id);
    if ($iphistory === false || is_null($iphistory)) {
        $fluent = $container->get(Database::class);
        $ipsinuse = $fluent->from('ips')
                           ->select(null)
                           ->select('COUNT(id) AS count')
                           ->where('INET6_NTOA(ip) = ?', $user['ip'])
                           ->fetch('count');
        if ($ipsinuse === 0) {
            $iphistory['use'] = '';
        } else {
            $ipcheck = $user['ip'];
            $iphistory['use'] = "
        <span class='has-text-danger'>{$lang['userdetails_ip_warn']}</span>
        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=usersearch&amp;action=usersearch&amp;ip=$ipcheck'>
            {$lang['userdetails_ip_used']}{$lang['userdetails_ip_users']}
        </a>";
        }
        $iphistory['ips'] = $fluent->from('ips')
                                   ->select(null)
                                   ->select('INET6_NTOA(ip) AS ip')
                                   ->where('userid = ?', $id)
                                   ->groupBy('ip')
                                   ->fetchAll();
        $cache->set('ip_history_' . $id, $iphistory, $site_config['expires']['iphistory']);
    }
    if (isset($addr)) {
        if ($CURUSER['id'] == $id || has_access($CURUSER['class'], UC_STAFF, '')) {
            $HTMLOUT .= "
            <tr>
                <td class='rowhead'>{$lang['userdetails_address']}</td>
                <td>
                    $addr<br>
                    {$iphistory['use']}<br>
                    <a class='button is-small top10' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$user['id']}'>{$lang['userdetails_ip_hist']}</a>
                    <a class='button is-small top10' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iplist&amp;id={$user['id']}'>{$lang['userdetails_ip_list']}</a>
                </td>
            </tr>";
        }
    }
    if (has_access($CURUSER['class'], UC_STAFF, '') && $iphistory['ips'] > 0) {
        $HTMLOUT .= "
            <tr>
                <td class='rowhead'>{$lang['userdetails_ip_history']}</td>
                <td>
                    {$lang['userdetails_ip_earlier']}<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$user['id']}'>{$ipsinuse} {$lang['userdetails_ip_different']}</a>
                </td>
            </tr>";
    }
}
