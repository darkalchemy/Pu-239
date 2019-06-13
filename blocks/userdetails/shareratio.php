<?php

declare(strict_types = 1);
global $CURUSER, $user, $lang, $site_config;

if ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    if ($user['downloaded'] > 0) {
        $table_data .= '
        <tr>
            <td class="rowhead" style="vertical-align: middle">' . $lang['userdetails_share_ratio'] . '</td>
            <td>
                <div class="level-left">
                    ' . member_ratio((int) $user['uploaded'], (int) $user['downloaded']) . '
                    <span class="left10">' . get_user_ratio_image($user['uploaded'], $user['downloaded']) . '</span>
                </div>
            </td>
        </tr>';
    }
}
