<?php

global $site_config, $lang, $user_stuffs, $id, $CURUSER, $user;

if ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    $days = round((TIME_NOW - $user['added']) / 86400);
    if (RATIO_FREE) {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_h_days']}</td>
            <td>{$lang['userdetails_rfree_effect']}</td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['userdetails_uploaded']}</td>
            <td>" . mksize($user['uploaded']) . " {$lang['userdetails_daily']}" . ($days > 1 ? mksize($user['uploaded'] / $days) : mksize($user['uploaded'])) . '</td>
        </tr>';
    } else {
        $table_data .= "
        <tr>
            <td class='rowhead'>{$lang['userdetails_downloaded']}</td>
            <td>" . mksize($user['downloaded']) . " {$lang['userdetails_daily']}" . ($days > 1 ? mksize($user['downloaded'] / $days) : mksize($user['downloaded'])) . "</td>
        </tr>
        <tr>
            <td class='rowhead'>{$lang['userdetails_uploaded']}</td>
            <td>" . mksize($user['uploaded']) . " {$lang['userdetails_daily']}" . ($days > 1 ? mksize($user['uploaded'] / $days) : mksize($user['uploaded'])) . '</td>
        </tr>';
    }
}
