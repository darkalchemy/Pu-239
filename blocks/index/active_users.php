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

    $count = count($query);
    $i = 0;
    if ($count >= 100) {
        $active['activeusers'] = format_comment($lang['index_blocks_too_many'], 0);
    } elseif ($count > 0) {
        foreach ($query as $row) {
            if (++$i != $count) {
                $list[] = format_username($row['id'], true, true, false, true);
            } else {
                $list[] = format_username($row['id']);
            }
        }
        $active['activeusers'] = implode('&nbsp;&nbsp;', $list);
    } elseif ($count === 0) {
        $active['activeusers'] = $lang['index_active_users_no'];
    }

    $active['actcount'] = number_format($count);
    $cache->set('activeusers_', $active, $site_config['expires']['activeusers']);
}

$active_users .= "
        <a id='activeusers-hash'></a>
        <div id='activeusers' class='box'>
            <div class='bordered'>
                <div class='alt_bordered bg-00'>
                    <div class='bg-00 padding10 bottom10 has-text-centered round5 size_5'>
                        {$lang['index_active']} ({$active['actcount']})
                    </div>
                    <div class='level-item is-wrapped padding20'>
                        {$active['activeusers']}
                    </div>
                </div>
            </div>
        </div>";
