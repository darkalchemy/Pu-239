<?php
global $CURUSER, $site_config, $lang, $user, $user_stats, $id;

if ($user['paranoia'] < 2 || $CURUSER['id'] == $id || $CURUSER['class'] >= UC_STAFF) {
    if ($user_stats['downloaded'] > 0) {
        $table_data .= '
        <tr>
            <td class="rowhead" style="vertical-align: middle">' . $lang['userdetails_share_ratio'] . '</td>
            <td>
                <div class="level-left">
                    ' . member_ratio($user_stats['uploaded'], $site_config['ratio_free'] ? 0 : $user_stats['downloaded']) . '
                    <span class="left10">' . get_user_ratio_image($user_stats['uploaded'] / ($site_config['ratio_free'] ? 1 : $user_stats['downloaded'])) . '</span>
                </div>
            </td>
        </tr>';
    }
}
