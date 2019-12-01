<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $site_config;

$cache = $container->get(Cache::class);
$active = $cache->get('activeusers_');
if ($active === false || is_null($active)) {
    $list = [];
    $dt = TIME_NOW - 900;
    $fluent = $container->get(Database::class);
    $query = $fluent->from('users')
                    ->select(null)
                    ->select('id')
                    ->where('last_access > ?', $dt)
                    ->where('perms < ?', PERMS_STEALTH)
                    ->where('anonymous_until < ?', TIME_NOW)
                    ->where('id != 2')
                    ->orderBy('username')
                    ->fetchAll();

    $count = count($query);
    $i = 0;
    if ($count >= 100) {
        $active['activeusers'] = format_comment(_('Too many to list here.'));
    } elseif ($count > 0) {
        foreach ($query as $row) {
            if (++$i != $count) {
                $list[] = format_username((int) $row['id'], true, true, false, true);
            } else {
                $list[] = format_username((int) $row['id']);
            }
        }
        $active['activeusers'] = implode('&nbsp;&nbsp;', $list);
    } elseif ($count === 0) {
        $active['activeusers'] = _('There have been no active users in the last 15 minutes.');
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
                        " . _pfe('{0} Active User', '{0} Active Users', $active['actcount']) . "
                    </div>
                    <div class='is-wrapped padding20'>
                        {$active['activeusers']}
                    </div>
                </div>
            </div>
        </div>";
