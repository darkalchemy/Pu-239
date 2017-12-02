<?php
global $site_config, $cache, $lang, $fpdo;

$latestuser = $cache->get('latestuser');
if ($latestuser === false || is_null($latestuser)) {
    $latestuser = $fpdo->from('users')
        ->select(null)
        ->select('id')
        ->where('status = ?', 'confirmed')
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->orderBy('id DESC')
        ->limit(1)
        ->fetchAll();

    $latestuser = format_username($latestuser['id']);
    $cache->set('latestuser', $latestuser, $site_config['expires']['latestuser']);
}

$HTMLOUT .= "
        <a id='latestuser-hash'></a>
        <fieldset id='latestuser' class='header'>
            <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_lmember']}</legend>
            <div class='bordered'>
                <div class='alt_bordered bg-00 has-text-centered'>
                    <span>{$lang['index_wmember']} " . format_username($latestuser['id']) . "!</span>
                </div>
            </div>
        </fieldset>";
