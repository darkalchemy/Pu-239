<?php
global $CURUSER, $site_config, $cache, $lang, $user, $id;

if ($user['paranoia'] < 1 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    $What_Cache = (XBT_TRACKER == true ? 'port_data_xbt_' : 'port_data_');
    $port_data = $cache->get($What_Cache . $id);
    if ($port_data === false || is_null($port_data)) {
        if (XBT_TRACKER == true) {
            $q1 = sql_query('SELECT `connectable`, `peer_id` FROM `xbt_files_users` WHERE uid = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        } else {
            $q1 = sql_query('SELECT connectable, port,agent FROM peers WHERE userid = ' . sqlesc($id) . ' LIMIT 1') or sqlerr(__FILE__, __LINE__);
        }
        $port_data = mysqli_fetch_row($q1);
        $cache->set('port_data_' . $id, $port_data, $site_config['expires']['port_data']);
    }
    if ($port_data > 0) {
        $connect = $port_data[0];
        $port = (XBT_TRACKER == true ? '' : $port_data[1]);
        $Ident_Client = (XBT_TRACKER == true ? $port_data['1'] : $port_data[2]);
        $XBT_or_PHP = (XBT_TRACKER == true ? '1' : 'yes');
        if ($connect == $XBT_or_PHP) {
            $connectable = "<img src='{$site_config['pic_baseurl']}tick.png' alt='{$lang['userdetails_yes']}' title='{$lang['userdetails_conn_sort']}' style='border:none;padding:2px;' /><span style='color: green;'><b>{$lang['userdetails_yes']}</b></span>";
        } else {
            $connectable = "<img src='{$site_config['pic_baseurl']}cross.png' alt='{$lang['userdetails_no']}' title='{$lang['userdetails_conn_staff']}' style='border:none;padding:2px;' /><span class='has-text-danger'><b>{$lang['userdetails_no']}</b></span>";
        }
    } else {
        $connectable = "<span style='color: orange;'><b>{$lang['userdetails_unknown']}</b></span>";
    }
    $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_connectable']}</td><td>" . $connectable . '</td></tr>';
    if (!empty($port)) {
        $HTMLOUT .= "<tr><td class='rowhead'>{$lang['userdetails_port']}</td><td class='tablea'>" . htmlsafechars($port) . "</td></tr>
    <tr><td class='rowhead'>{$lang['userdetails_client']}</td><td class='tablea'>" . htmlsafechars($Ident_Client) . '</td></tr>';
    }
}
//==End
// End Class
// End File
