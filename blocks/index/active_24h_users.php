<?php
$keys['last24'] = 'last24';
//if (($last24_cache = $mc1->get_value($keys['last24'])) === false) {
    $last24_cache = [];
    $time24 = $_SERVER['REQUEST_TIME'] - 86400;
    $activeusers24 = '';
    $arr = mysqli_fetch_assoc(sql_query('SELECT * FROM avps WHERE arg = "last24"'));
    $res = sql_query('SELECT id, username, perms FROM users WHERE last_access >= ' . $time24 . ' AND perms < ' . bt_options::PERMS_STEALTH . ' ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
    $totalonline24 = mysqli_num_rows($res);
    $_ss24 = $totalonline24;
    $last24record = get_date($arr['value_u'], '');
    $last24 = $arr['value_i'];
    if ($totalonline24 > $last24) {
        $last24 = $totalonline24;
        $period = $_SERVER['REQUEST_TIME'];
        sql_query('UPDATE avps SET value_s = 0, value_i = ' . sqlesc($last24) . ', value_u = ' . sqlesc($period) . ' WHERE arg = "last24"') or sqlerr(__FILE__, __LINE__);
    }
    while ($arr = mysqli_fetch_assoc($res)) {
        $list[] = format_username($arr['id']);
    }
    $activeusers24 = implode(', ', $list);
    $last24_cache['activeusers24'] = $activeusers24;
    $last24_cache['totalonline24'] = number_format($totalonline24);
    $last24_cache['last24record'] = $last24record;
    $last24_cache['last24'] = number_format($last24);
    $last24_cache['ss24'] = $_ss24;
    $mc1->cache_value($keys['last24'], $last24_cache, $site_config['expires']['last24']);
//}
if (!$last24_cache['activeusers24']) {
    $last24_cache['activeusers24'] = $lang['index_last24_nousers'];
}
if ($last24_cache['totalonline24'] != 1) {
    $last24_cache['ss24'] = $lang['gl_members'];
} else {
    $last24_cache['ss24'] = $lang['gl_member'];
}
$HTMLOUT .= "
        <a id='active24-hash'></a>
        <fieldset id='active24' class='header'>
            <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_active24']} <small>{$lang['index_last24_list']}</small></legend>
            <div class='bordered'>
                <div class='alt_bordered bg-00 has-text-centered'>
                    <div><b>{$last24_cache['totalonline24']}{$last24_cache['ss24']}{$lang['index_last24_during']}</b></div>
                    <div class='top20 bottom20'>{$last24_cache['activeusers24']}</div>
                    <div><b>{$lang['index_last24_most']}{$last24_cache['last24']}{$last24_cache['ss24']}{$lang['index_last24_on']}{$last24_cache['last24record']}</b></div>
                </div>
            </div>
        </fieldset>";
