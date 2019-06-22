<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $lang, $site_config;

$cache = $container->get(Cache::class);
$active24 = $cache->get('last24_users_');
if ($active24 === false || is_null($active24)) {
    $list = [];
    $fluent = $container->get(Database::class);
    $record = $fluent->from('avps')
                     ->where('arg = ?', 'last24')
                     ->fetch();

    $dt = TIME_NOW - 86400;
    $query = $fluent->from('users')
                    ->select(null)
                    ->select('id')
                    ->where('last_access > ?', $dt)
                    ->where('perms < ?', bt_options::PERMS_STEALTH)
                    ->where('id != 2')
                    ->orderBy('username')
                    ->fetchAll();

    $count = count($query);
    $i = 0;
    if ($count >= 100) {
        $active24['activeusers24'] = format_comment($lang['index_blocks_too_many']);
    } elseif ($count > 0) {
        foreach ($query as $row) {
            if (++$i != $count) {
                $list[] = format_username((int) $row['id'], true, true, false, true);
            } else {
                $list[] = format_username((int) $row['id']);
            }
        }
        $active24['activeusers24'] = implode('&nbsp;&nbsp;', $list);
    } elseif ($count === 0) {
        $active24['activeusers24'] = $lang['index_last24_nousers'];
    }
    $active24['totalonline24'] = number_format($count);
    $active24['last24'] = number_format($record['value_i']);
    $active24['ss24'] = $lang['gl_member'] . plural($count);
    $active24['record'] = get_date((int) $record['value_u'], '');
    if ($count > $record['value_i']) {
        $set = [
            'value_s' => 0,
            'value_i' => $count,
            'value_u' => TIME_NOW,
        ];
        $fluent->update('avps')
               ->set($set)
               ->where('arg = ?', 'last24')
               ->execute();
    }

    $cache->set('last24_users_', $active24, $site_config['expires']['last24']);
}

$active_users_24 .= "
        <a id='active24-hash'></a>
        <div id='active24' class='box'>
            <div class='bordered'>
                <div class='alt_bordered bg-00 has-text-centered'>
                    <div class='bg-00 padding10 bottom10 round5 size_5'>
                        {$active24['totalonline24']}{$active24['ss24']}{$lang['index_last24_during']}
                    </div>
                    <div class='top10 bottom10 level-item is-wrapped top10 bottom10 padding20'>
                        {$active24['activeusers24']}
                    </div>
                    <div class='bg-00 padding10 bottom10 has-text-centered round5 size_3'>
                        {$lang['index_last24_most']}{$active24['last24']}{$active24['ss24']}{$lang['index_last24_on']}{$active24['record']}
                    </div>
                </div>
            </div>
        </div>";
