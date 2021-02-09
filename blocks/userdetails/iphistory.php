<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\IP;

global $container, $site_config, $CURUSER, $user, $id, $HTMLOUT;

$cache = $container->get(Cache::class);
$user['ip'] = getip($id);
if (has_access($CURUSER['class'], UC_STAFF, '') || $CURUSER['id'] === $id) {
    $cache->delete('ip_history_' . $id);
    $iphistory = $cache->get('ip_history_' . $id);
    if ($iphistory === false || is_null($iphistory)) {
        $ip_class = $container->get(IP::class);
        $ipsinuse = $ip_class->get_user_count($user['ip']);
        $iphistory = [
            'in_use' => '',
            'count' => $ipsinuse - 1,
            'ips' => $ip_class->get($id),
        ];
        if ($ipsinuse > 1) {
            $iphistory['in_use'] = "
        <span class='has-text-danger'>" . _('Warning') . ": </span>
        <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=usersearch&amp;action=usersearch&amp;ip={$user['ip']}'>
            " . _('Used by other users!') . '
        </a>';
        }

        $cache->set('ip_history_' . $id, $iphistory, $site_config['expires']['iphistory']);
    }
    if (isset($addr)) {
        if ($CURUSER['id'] === $id || has_access($CURUSER['class'], UC_STAFF, '')) {
            $HTMLOUT .= "
            <tr>
                <td class='rowhead'>" . _('Address') . "</td>
                <td>
                    $addr<br>
                    {$iphistory['in_use']}<br>
                    <a class='button is-small top10' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$user['id']}'>" . _('History') . "</a>
                    <a class='button is-small top10' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iplist&amp;id={$user['id']}'>" . _('List') . '</a>
                </td>
            </tr>';
        }
    }
    if (has_access($CURUSER['class'], UC_STAFF, '') && $iphistory['count'] > 0) {
        $HTMLOUT .= "
            <tr>
                <td class='rowhead'>" . _('IP History') . '</td>
                <td>
                    ' . _pfe('This user has earlier used {1}{0}{2} different IP address', 'This user has earlier used {1}{0}{2} different IP addresses', $iphistory['count'], "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=iphistory&amp;action=iphistory&amp;id={$user['id']}'>", '</a>') . '
                </td>
            </tr>';
    }
}
