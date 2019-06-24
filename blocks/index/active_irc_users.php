<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $lang, $site_config;

$cache = $container->get(Cache::class);
$irc = $cache->get('ircusers_');
if ($irc === false || is_null($irc)) {
    $irc = $list = [];
    $fluent = $container->get(Database::class);
    $query = $fluent->from('users')
                    ->select(null)
                    ->select('id')
                    ->where('onirc = ?', 'yes')
                    ->where('perms < ?', PERMS_STEALTH)
                    ->where('id != 2')
                    ->orderBy('username')
                    ->fetchAll();

    $count = count($query);
    $i = 0;
    if ($count >= 100) {
        $irc['ircusers'] = format_comment($lang['index_blocks_too_many']);
    } elseif ($count > 0) {
        foreach ($query as $row) {
            if (++$i != $count) {
                $list[] = format_username((int) $row['id'], true, true, false, true);
            } else {
                $list[] = format_username((int) $row['id']);
            }
        }
        $irc['ircusers'] = implode('&nbsp;&nbsp;', $list);
    } elseif ($count === 0) {
        $irc['ircusers'] = $lang['index_irc_nousers'];
    }

    $irc['count'] = number_format($count);
    $cache->set('ircusers_', $irc, $site_config['expires']['activeircusers']);
}

$active_users_irc .= "
    <a id='irc-hash'></a>
    <div id='irc' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00 level-item is-wrapped padding20'>
                {$irc['ircusers']}
            </div>
        </div>
    </div>";
