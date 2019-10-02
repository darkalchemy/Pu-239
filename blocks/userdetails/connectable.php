<?php

declare(strict_types = 1);

use Pu239\Database;

global $container, $CURUSER, $site_config;

if ($user['paranoia'] < 1 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    $What_Cache = 'port_data_';
    $Ident_Client = '';
    $port_data = $cache->get($What_Cache . $id);
    if ($port_data === false || is_null($port_data)) {
        $fluent = $container->get(Database::class);
        $port_data = $fluent->from('peers')
                            ->select(null)
                            ->select('connectable')
                            ->select('port')
                            ->select('agent')
                            ->where('userid = ?', $id)
                            ->limit(1)
                            ->fetch();
        $cache->set('port_data_' . $id, $port_data, $site_config['expires']['port_data']);
    }
    if (!empty($port_data) && isset($port_data[2])) {
        $connect = $port_data[0];
        $port = (int) $port_data[1];
        $Ident_Client = $port_data[2];
        if ($connect === 'yes') {
            $connectable = "
    <div class='has-text-success tooltipper' title='" . _('Sorted Yer connectable') . "'>
        <i class='icon-thumbs-up icon' aria-hidden='true'></i><b>" . _('Yes') . '</b>
    </div>';
        } else {
            $connectable = "
    <div class='has-text-danger tooltipper' title='" . _('Contact Site Staff') . "'>
        <i class='icon-thumbs-down icon' aria-hidden='true'></i><b>" . _('No') . '</b>
    </div>';
        }
    } else {
        $connectable = "<span style='color: orange;'><b>" . _('Unknown') . '</b></span>';
    }
    $table_data .= "
        <tr>
            <td class='rowhead'>" . _('Connectable') . '</td>
            <td>' . $connectable . '</td>
        </tr>';
    if (!empty($port)) {
        $table_data .= "
        <tr>
            <td class='rowhead'>" . _('Port') . "</td>
            <td class='tablea'>$port</td>
        </tr>
        <tr>
            <td class='rowhead'>" . _('Client') . "</td>
            <td class='tablea'>" . htmlsafechars($Ident_Client) . '</td>
        </tr>';
    }
}
