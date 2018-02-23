<?php

global $site_config, $lang, $fluent, $cache;

$active = $cache->get('activeusers_');
if (false === $active || is_null($active)) {
    $list = [];
    $dt = TIME_NOW - 900;
    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('last_access > ?', $dt)
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->where('id != 2')
        ->orderBy('username ASC');

    foreach ($query as $row) {
        $list[] = format_username($row['id']);
    }
    $list[] = format_username(2);
    $active['activeusers'] = implode(',&nbsp;&nbsp;', $list);
    $active['actcount'] = count($list);
    if (0 === $active['actcount']) {
        $active['activeusers'] = $lang['index_active_users_no'];
    }
    $active_users_cache['au'] = number_format($active['actcount']);
    $cache->set('activeusers_', $active, $site_config['expires']['activeusers']);
}

$HTMLOUT .= "
        <a id='activeusers-hash'></a>
        <fieldset id='activeusers' class='header'>
            <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_active']} ({$active['actcount']})</legend>
            <div class='bordered'>
                <div class='alt_bordered bg-00 level-item is-wrapped'>
                    {$active['activeusers']}
                </div>
            </div>
        </fieldset>";
