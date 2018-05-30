<?php

global $site_config, $lang, $fluent, $cache;

$irc = $cache->get('ircusers_');
if ($irc === false || is_null($irc)) {
    $irc   = $list   = [];
    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('onirc = ?', 'yes')
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->where('id != 2')
        ->orderBy('username ASC');

    foreach ($query as $row) {
        $list[] = format_username($row['id']);
    }
    $list[]          = format_username(2);
    $irc['ircusers'] = implode(',&nbsp;&nbsp;', $list);
    $irc['count']    = count($list);
    if ($irc['count'] === 0) {
        $irc['ircusers'] = $lang['index_irc_nousers'];
    }
    $cache->set('ircusers_', $irc, $site_config['expires']['activeircusers']);
}

$HTMLOUT .= "
    <a id='irc-hash'></a>
    <fieldset id='irc' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_active_irc']} ({$irc['count']})</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 level-item is-wrapped'>
                {$irc['ircusers']}
            </div>
        </div>
    </fieldset>";
