<?php
global $site_config, $cache, $lang;

$keys['activeusers'] = 'activeusers';
if (($active_users_cache = $cache->get($keys['activeusers'])) === false) {
    $dt = $_SERVER['REQUEST_TIME'] - 180;
    $activeusers = '';
    $active_users_cache = [];
    $res = sql_query('SELECT id, perms, username FROM users WHERE last_access >= ' . $dt . ' AND perms < ' . bt_options::PERMS_STEALTH . ' ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
    $actcount = mysqli_num_rows($res);
    $v = ($actcount != 1 ? 's' : '');
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($activeusers) {
            $activeusers .= ", ";
        }
        $activeusers .= format_username($arr['id'], true, true);
    }
    $active_users_cache['activeusers'] = $activeusers;
    $active_users_cache['actcount'] = $actcount;
    $active_users_cache['au'] = number_format($actcount);
    $last24_cache['v'] = $v;
    $cache->set($keys['activeusers'], $active_users_cache, $site_config['expires']['activeusers']);
}
if (!$active_users_cache['activeusers']) {
    $active_users_cache['activeusers'] = $lang['index_active_users_no'];
}
$HTMLOUT .= "
    <a id='activeusers-hash'></a>
    <fieldset id='activeusers' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_active']} ({$active_users_cache['actcount']})</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                {$active_users_cache['activeusers']}
            </div>
        </div>
    </fieldset>";
