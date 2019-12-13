<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $site_config;

$cache = $container->get(Cache::class);
$birthday = $cache->get('birthdayusers_');
if ($birthday === false || is_null($birthday)) {
    $birthday = $list = [];
    $current_date = getdate();
    $fluent = $container->get(Database::class);
    $query = $fluent->from('users')
                    ->select(null)
                    ->select('id')
                    ->where('MONTH(birthday) = ?', $current_date['mon'])
                    ->where('DAYOFMONTH(birthday) = ?', $current_date['mday'])
                    ->where('perms < ?', PERMS_STEALTH)
                    ->where('anonymous_until < ?', TIME_NOW)
                    ->orderBy('username')
                    ->fetchAll();

    $count = count($query);
    $i = 0;
    if ($count >= 100) {
        $birthday['birthdayusers'] = format_comment(_('Too many to list here.'));
    } elseif ($count > 0) {
        foreach ($query as $row) {
            if (++$i != $count) {
                $list[] = format_username((int) $row['id'], true, true, false, true);
            } else {
                $list[] = format_username((int) $row['id']);
            }
        }
        $birthday['birthdayusers'] = implode('&nbsp;&nbsp;', $list);
    } elseif ($count === 0) {
        $birthday['birthdayusers'] = _('There are no members with birthdays today.');
    }

    $birthday['count'] = number_format($count);
    $cache->set('birthdayusers_', $birthday, $site_config['expires']['birthdayusers']);
}

$birthday_users .= "
    <a id='birthday-hash'></a>
    <div id='birthday' class='box'>
        <div class='bordered'>
            <div class='alt_bordered bg-00 has-text-centered'>
                <div class='bg-00 padding10 bottom10 has-text-centered round5 size_5'>" . _pfe('There is {0} Birthday Today', 'There are {0} Birthdays Today', $birthday['count']) . "</div>
                <div class='level-center-center is-wrapped padding20'>
                    {$birthday['birthdayusers']}
                </div>
            </div>
        </div>
    </div>";
