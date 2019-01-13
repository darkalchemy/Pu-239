<?php

global $CURUSER, $site_config, $lang, $user_stuffs, $id, $cache, $user;

if ($user['paranoia'] < 1 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    $What_Cache = 'port_data_';
    $Ident_Client = '';
    $port_data = $cache->get($What_Cache . $id);
    if ($port_data === false || is_null($port_data)) {
        $q1 = sql_query('SELECT connectable, port,agent FROM peers WHERE userid = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        $port_data = mysqli_fetch_row($q1);
        $cache->set('port_data_' . $id, $port_data, $site_config['expires']['port_data']);
    }
    if ($port_data > 0) {
        $connect = $port_data[0];
        $port = $port_data[1];
        $Ident_Client = $port_data[2];
        $XBT_or_PHP = 'yes';
        if ($connect == $XBT_or_PHP) {
            $connectable = "
    <div class='has-text-success tooltipper' title='{$lang['userdetails_conn_sort']}'>
        <i class='icon-thumbs-up icon' aria-hidden='true'></i><b>{$lang['userdetails_yes']}</b>
    </div>";
        } else {
            $connectable = "
    <div class='has-text-danger tooltipper' title='{$lang['userdetails_conn_staff']}'>
        <i class='icon-thumbs-down icon' aria-hidden='true'></i><b>{$lang['userdetails_no']}</b>
    </div>";
        }
    } else {
        $connectable = "<span style='color: orange;'><b>{$lang['userdetails_unknown']}</b></span>";
    }
    $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_connectable']}</td>
            <td>" . $connectable . '</td>
        </tr>';
    if (!empty($port)) {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_port']}</td>
            <td class='tablea'>" . htmlsafechars($port) . "</td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['userdetails_client']}</td>
            <td class='tablea'>" . htmlsafechars($Ident_Client) . '</td>
        </tr>';
    }
}
