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

    $count = count($query);
    $i = 0;
    if ($count >= 100) {
        $birthday['birthdayusers'] = format_comment($lang['index_blocks_too_many'], 0);
    } elseif ($count > 0) {
        foreach ($query as $row) {
            if (++$i != $count) {
                $list[] = format_username($row['id'], true, true, false, true);
            } else {
                $list[] = format_username($row['id']);
            }
        }
        $birthday['birthdayusers'] = implode('&nbsp;&nbsp;', $list);
    } elseif ($count === 0) {
        $birthday['birthdayusers'] = $lang['index_birthday_no'];
    }

    $birthday['count'] = number_format($count);
    $cache->set('birthdayusers_', $birthday, $site_config['expires']['birthdayusers']);
}

$birthday_users .= "
    <a id='birthday-hash'></a>
    <div id='birthday' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>
                <div class='bg-00 padding10 bottom10 has-text-centered round5 size_5'>{$lang['index_birthday']} ({$birthday['count']})</div>
                <div class='level-item is-wrapped padding20'>
                    {$birthday['birthdayusers']}
                </div>
            </div>
        </div>
    </div>";
