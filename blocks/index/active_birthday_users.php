<?php
$current_date = getdate();
$keys['birthdayusers'] = 'birthdayusers';
if (($birthday_users_cache = $mc1->get_value($keys['birthdayusers'])) === false) {
    $birthdayusers = '';
    $birthday_users_cache = [];
    $res = sql_query('SELECT id, username, perms FROM users WHERE MONTH(birthday) = ' . sqlesc($current_date['mon']) . ' AND DAYOFMONTH(birthday) = ' . sqlesc($current_date['mday']) . ' AND perms < ' . bt_options::PERMS_STEALTH . ' ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
    $actcount = mysqli_num_rows($res);
    while ($arr = mysqli_fetch_assoc($res)) {
        if ($birthdayusers) {
            $birthdayusers .= ",\n";
        }
        $birthdayusers .= format_username($arr['id']);
    }
    $birthday_users_cache['birthdayusers'] = $birthdayusers;
    $birthday_users_cache['actcount'] = $actcount;
    $mc1->cache_value($keys['birthdayusers'], $birthday_users_cache, $site_config['expires']['birthdayusers']);
}
if (!$birthday_users_cache['birthdayusers']) {
    $birthday_users_cache['birthdayusers'] = $lang['index_birthday_no'];
}
$HTMLOUT .= "
    <a id='birthday-hash'></a>
    <fieldset id='birthday' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_birthday']} ({$birthday_users_cache['actcount']})</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                {$birthday_users_cache['birthdayusers']}
            </div>
        </div>
    </fieldset>";

