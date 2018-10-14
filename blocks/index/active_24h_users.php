<?php

global $site_config, $lang, $fluent, $cache;

$active24 = $cache->get('last24_users_');
if ($active24 === false || is_null($active24)) {
    $list = [];
    $record = $fluent->from('avps')
        ->where('arg = ?', 'last24')
        ->fetch();

    $dt = TIME_NOW - 86400;
    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('last_access > ?', $dt)
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->where('id != 2')
        ->orderBy('username ASC')
        ->fetchAll();

    $count = count($query);
    if ($count >= 100) {
        $active24['activeusers24'] = format_comment('Too many to list here :)');
    } elseif ($count > 0) {
        foreach ($query as $row) {
            $list[] = format_username($row['id']);
        }
        $active24['activeusers24'] = implode(',&nbsp;&nbsp;', $list);
    } elseif ($count === 0) {
        $active24['activeusers24'] = $lang['index_last24_nousers'];
    }
    $active24['totalonline24'] = number_format($count);
    $active24['last24'] = number_format($record['value_i']);
    $active24['ss24'] = $lang['gl_member'] . plural($count);
    $active24['record'] = get_date($record['value_u'], '');
    if ($count > $record['value_i']) {
        $set = [
            'value_s' => 0,
            'value_i' => $count,
            'value_u' => TIME_NOW,
        ];
        $fluent->update('avps')
            ->set($set)
            ->where('arg = ?', 'last24')
            ->execute();
    }

    $cache->set('last24_users_', $active24, $site_config['expires']['last24']);
}

$HTMLOUT .= "
        <a id='active24-hash'></a>
        <fieldset id='active24' class='header'>
            <legend class='flipper has-text-primary'><i class='icon-down-open size_2' aria-hidden='true'></i>{$lang['index_active24']} <small>{$lang['index_last24_list']}</small></legend>
            <div class='bordered'>
                <div class='alt_bordered bg-00 has-text-centered'>
                    <div>{$active24['totalonline24']}{$active24['ss24']}{$lang['index_last24_during']}</div>
                    <div class='top10 bottom10 level-item is-wrapped top10 bottom10'>
                        {$active24['activeusers24']}
                    </div>
                    <div>{$lang['index_last24_most']}{$active24['last24']}{$active24['ss24']}{$lang['index_last24_on']}{$active24['record']}</div>
                </div>
            </div>
        </fieldset>";
