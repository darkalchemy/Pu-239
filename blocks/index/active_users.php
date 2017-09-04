<?php
$keys['activeusers'] = 'activeusers';
if (($active_users_cache = $mc1->get_value($keys['activeusers'])) === false) {
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
        $activeusers .= format_username($arr['id']);
    }
    $active_users_cache['activeusers'] = $activeusers;
    $active_users_cache['actcount'] = $actcount;
    $active_users_cache['au'] = number_format($actcount);
    $last24_cache['v'] = $v;
    $mc1->cache_value($keys['activeusers'], $active_users_cache, $INSTALLER09['expires']['activeusers']);
}
if (!$active_users_cache['activeusers']) {
    $active_users_cache['activeusers'] = $lang['index_active_users_no'];
}
$HTMLOUT .= "
    <a id='activeusers-hash'></a>
    <fieldset id='activeusers' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up' aria-hidden='true'></i>{$lang['index_active']} ({$active_users_cache['actcount']})</legend>
            <div class='text-center'>
                {$active_users_cache['activeusers']}
            </div>
    </fieldset>";
