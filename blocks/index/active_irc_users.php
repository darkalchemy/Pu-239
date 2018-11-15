<?php

global $site_config, $lang, $fluent, $cache;

$irc = $cache->get('ircusers_');
if ($irc === false || is_null($irc)) {
    $irc = $list = [];
    $query = $fluent->from('users')
        ->select(null)
        ->select('id')
        ->where('onirc = ?', 'yes')
        ->where('perms < ?', bt_options::PERMS_STEALTH)
        ->where('id != 2')
        ->orderBy('username ASC');

    $irc['count'] = count($query);
    if ($irc['count'] >= 100) {
        $irc['ircusers'] = format_comment('Too many to list here :)');
    } elseif ($irc['count'] > 0) {
        foreach ($query as $row) {
            $list[] = format_username($row['id']);
        }
        $irc['ircusers'] = implode(',&nbsp;&nbsp;', $list);
    } elseif ($irc['count'] === 0) {
        $irc['ircusers'] = $lang['index_irc_nousers'];
    }

    $irc['count'] = number_format($irc['count']);
    $cache->set('ircusers_', $irc, $site_config['expires']['activeircusers']);
}

$active_users_irc .= "
    <a id='irc-hash'></a>
    <div id='irc' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00 level-item is-wrapped top10 bottom10'>
                {$irc['ircusers']}
            </div>
        </div>
    </div>";
