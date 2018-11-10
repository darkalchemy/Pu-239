<?php

global $site_config, $lang, $fluent, $cache;

$birthday = $cache->get('birthdayusers_');
if ($birthday === false || is_null($birthday)) {
    $birthday = $list = [];
    $current_date = getdate();
    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('MONTH(birthday) = ?', $current_date['mon'])
        ->where('DAYOFMONTH(birthday) = ?', $current_date['mday'])
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->orderBy('username');

    $birthday['count'] = count($query);
    if ($birthday['count'] >= 100) {
        $birthday['birthdayusers'] = format_comment('Too many to list here :)');
    } elseif ($birthday['count'] > 0) {
        foreach ($query as $row) {
            $list[] = format_username($row['id']);
        }
        $birthday['birthdayusers'] = implode(',&nbsp;&nbsp;', $list);
    } elseif ($birthday['count'] === 0) {
        $birthday['birthdayusers'] = $lang['index_birthday_no'];
    }

    $birthday['count'] = number_format($birthday['count']);
    $cache->set('birthdayusers_', $birthday, $site_config['expires']['birthdayusers']);
}

$birthday_users .= "
    <a id='birthday-hash'></a>
    <fieldset id='birthday' class='header'>
        <legend class='flipper has-text-primary'><i class='icon-down-open size_2' aria-hidden='true'></i>{$lang['index_birthday']} ({$birthday['count']})</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 level-item is-wrapped top10 bottom10'>
                {$birthday['birthdayusers']}
            </div>
        </div>
    </fieldset>";
