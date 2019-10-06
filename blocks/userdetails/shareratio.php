<?php

declare(strict_types = 1);
global $CURUSER, $user, $site_config;

if ($user['paranoia'] < 2 || $CURUSER['id'] === $user['id'] || $CURUSER['class'] >= UC_STAFF) {
    if ($user['downloaded'] > 0) {
        $table_data .= '
        <tr>
            <td class="rowhead" style="vertical-align: middle">' . _('Share ratio') . '</td>
            <td>
                <div class="level-left">
                    ' . member_ratio((float) $user['uploaded'], (float) $user['downloaded']) . '
                    <span class="left10">' . get_user_ratio_image((float) $user['uploaded'], (float) $user['downloaded']) . '</span>
                </div>
            </td>
        </tr>';
    }
}
