<?php
global $site_config, $cache, $lang, $fpdo;

$irc = $cache->get('ircusers_');
if ($irc === false || is_null($irc)) {
    $irc = $list = [];
    $query = $fpdo->from('users')
        ->select(null)
        ->select('id')
        ->where('onirc = ?', 'yes')
        ->where('perms < ?',  bt_options::PERMS_STEALTH)
        ->orderBy('username ASC');

    foreach ($query as $row) {
        $list[] = format_username($row['id']);
    }
    $list[] = format_username(2);
    $irc['ircusers'] = implode(', ', $list);
    $irc['count'] = count($list);
    if ($irc['count'] === 0) {
        $irc['ircusers'] = $lang['index_irc_nousers'];
    }
    $cache->set('ircusers_', $irc, $site_config['expires']['activeircusers']);
}

$HTMLOUT .= "
    <a id='irc-hash'></a>
    <fieldset id='irc' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['index_active_irc']} ({$irc['count']})</legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                {$irc['ircusers']}
            </div>
        </div>
    </fieldset>";
