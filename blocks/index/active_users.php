<?php

global $site_config, $lang, $fluent, $cache;

$active = $cache->get('activeusers_');
if ($active === false || is_null($active)) {
    $list = [];
    $dt = TIME_NOW - 900;
    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('last_access > ?', $dt)
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->where('id != 2')
        ->orderBy('username ASC');

    $active['actcount'] = count($query);
    if ($active['actcount'] >= 100) {
        $active['activeusers'] = format_comment('Too many to list here :)');
    } elseif ($active['actcount'] > 0) {
        foreach ($query as $row) {
            $list[] = format_username($row['id']);
        }
        $active['activeusers'] = implode(',&nbsp;&nbsp;', $list);
    } elseif ($active['actcount'] === 0) {
        $active['activeusers'] = $lang['index_active_users_no'];
    }

    $active['actcount'] = number_format($active['actcount']);
    $cache->set('activeusers_', $active, $site_config['expires']['activeusers']);
}

$HTMLOUT .= "
        <a id='activeusers-hash'></a>
        <fieldset id='activeusers' class='header'>
            <legend class='flipper has-text-primary'><i class='icon-down-open size_2' aria-hidden='true'></i>{$lang['index_active']} ({$active['actcount']})</legend>
            <div class='bordered'>
                <div class='alt_bordered bg-00 level-item is-wrapped top10 bottom10'>
                    {$active['activeusers']}
                </div>
            </div>
        </fieldset>";
