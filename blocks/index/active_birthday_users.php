<?php
global $site_config, $cache, $lang, $fpdo;

$birthday = $cache->get('birthdayusers_');
if ($birthday === false || is_null($birthday)) {
    $birthday = $list = [];
    $current_date = getdate();
    $query = $fpdo->from('users')
        ->select(null)
        ->select('id')
        ->where('MONTH(birthday) = ?', $current_date['mon'])
        ->where('DAYOFMONTH(birthday) = ?', $current_date['mday'])
        ->where('perms < ?',  bt_options::PERMS_STEALTH)
        ->orderBy('username');

    foreach ($query as $row) {
        $list[] = format_username($row['id']);
    }
    $birthday['birthdayusers'] = implode(', ', $list);
    $birthday['count'] = count($list);
    if ($birthday['count'] === 0) {
        $birthday['birthdayusers'] = $lang['index_birthday_no'];
    }
    $cache->set('birthdayusers_', $birthday, $site_config['expires']['birthdayusers']);
}

$HTMLOUT .= "
    <a id='birthday-hash'></a>
    <fieldset id='birthday' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_birthday']} ({$birthday['count']})</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                {$birthday['birthdayusers']}
            </div>
        </div>
    </fieldset>";
