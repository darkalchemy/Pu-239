<?php

global $site_config, $lang, $fluent, $cache;

$birthday = $cache->get('birthdayusers_');
if (false === $birthday || is_null($birthday)) {
    $birthday = $list = [];
    $current_date = getdate();
    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('MONTH(birthday) = ?', $current_date['mon'])
        ->where('DAYOFMONTH(birthday) = ?', $current_date['mday'])
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->orderBy('username');

    foreach ($query as $row) {
        $list[] = format_username($row['id']);
    }
    $birthday['birthdayusers'] = implode(',&nbsp;&nbsp;', $list);
    $birthday['count'] = count($list);
    if (0 === $birthday['count']) {
        $birthday['birthdayusers'] = $lang['index_birthday_no'];
    }
    $cache->set('birthdayusers_', $birthday, $site_config['expires']['birthdayusers']);
}

$HTMLOUT .= "
    <a id='birthday-hash'></a>
    <fieldset id='birthday' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_birthday']} ({$birthday['count']})</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 level-item is-wrapped'>
                {$birthday['birthdayusers']}
            </div>
        </div>
    </fieldset>";
