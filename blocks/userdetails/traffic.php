<?php

declare(strict_types = 1);

global $CURUSER, $lang, $user, $site_config;

if ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    $days = round((TIME_NOW - $user['registered']) / 86400);
    if ($site_config['site']['ratio_free']) {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_h_days']}</td>
            <td>{$lang['userdetails_rfree_effect']}</td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['userdetails_uploaded']}</td>
            <td>" . mksize($user['uploaded']) . " {$lang['userdetails_daily']}" . ($days > 1 ? mksize((int) floor($user['uploaded'] / $days)) : mksize($user['uploaded'])) . '</td>
        </tr>';
    } else {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_downloaded']}</td>
            <td>" . mksize($user['downloaded']) . " {$lang['userdetails_daily']}" . ($days > 1 ? mksize((int) floor($user['uploaded'] / $days)) : mksize($user['downloaded'])) . "</td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['userdetails_uploaded']}</td>
            <td>" . mksize($user['uploaded']) . " {$lang['userdetails_daily']}" . ($days > 1 ? mksize((int) floor($user['uploaded'] / $days)) : mksize($user['uploaded'])) . '</td>
        </tr>';
    }
}
