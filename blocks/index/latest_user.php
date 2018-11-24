<?php

global $site_config, $lang, $fluent, $cache;

$latestuser = $cache->get('latestuser');
if ($latestuser === false || is_null($latestuser)) {
    $latestuser = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('status = ?', 'confirmed')
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->orderBy('id DESC')
        ->limit(1)
        ->fetch();

    $latestuser = format_username($latestuser['id']);
    $cache->set('latestuser', $latestuser, $site_config['expires']['latestuser']);
}

$latest_user .= "
        <a id='latestuser-hash'></a>
        <div id='latestuser' class='box'>
            <div class='bordered'>
                <div class='alt_bordered bg-00 level-item is-wrapped top10 bottom10 padding20'>
                    {$lang['index_wmember']}&nbsp;{$latestuser}!
                </div>
            </div>
        </div>";
