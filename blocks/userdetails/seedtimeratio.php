<?php

global $CURUSER, $site_config, $lang, $user_stuffs, $cache, $user;

$What_Cache        = (XBT_TRACKER ? 'share_ratio_xbt_' : 'share_ratio_');
$What_Table        = (XBT_TRACKER ? 'xbt_files_users' : 'snatched');
$What_String       = (XBT_TRACKER ? 'fid' : 'id');
$What_User_String  = (XBT_TRACKER ? 'uid' : 'userid');
$What_Expire       = (XBT_TRACKER ? $site_config['expires']['share_ratio_xbt'] : $site_config['expires']['share_ratio']);
$cache_share_ratio = $cache->get($What_Cache . $id);
if ($cache_share_ratio === false || is_null($cache_share_ratio)) {
    $cache_share_ratio                    = mysqli_fetch_assoc(sql_query("SELECT SUM(seedtime) AS seed_time_total, COUNT($What_String) AS total_number FROM $What_Table WHERE seedtime > '0' AND $What_User_String =" . sqlesc($user['id'])));
    $cache_share_ratio['total_number']    = (int) $cache_share_ratio['total_number'];
    $cache_share_ratio['seed_time_total'] = (int) $cache_share_ratio['seed_time_total'];
    $cache->set($What_Cache . $id, $cache_share_ratio, $What_Expire);
}

switch (true) {
    case $user['class'] == UC_MIN:
        $days = 2;
        break;

    case $user['class'] < UC_VIP:
        $days = 1.5;
        break;

    case $user['class'] >= UC_VIP:
        $days = 0.5;
        break;
}
if ($cache_share_ratio['seed_time_total'] > 0 && $cache_share_ratio['total_number'] > 0) {
    $avg_time_ratio   = (($cache_share_ratio['seed_time_total'] / $cache_share_ratio['total_number']) / 86400 / $days);
    $avg_time_seeding = mkprettytime($cache_share_ratio['seed_time_total'] / $cache_share_ratio['total_number']);
    if ($user['id'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF) {
        $table_data .= '
        <tr>
            <td>' . $lang['userdetails_time_ratio'] . '</td>
            <td>
                <div class="level-left">' . (($user['downloaded'] > 0 || $user['uploaded'] > 2147483648) ? '
                    <span class="right10" style="color: ' . get_ratio_color(number_format($avg_time_ratio, 3)) . ';">' . number_format($avg_time_ratio, 3) . '</span>' . ratio_image_machine(number_format($avg_time_ratio, 3)) . '
                    <span class="left10">[</span><span style="color: ' . get_ratio_color(number_format($avg_time_ratio, 3)) . ';">&#160;' . $avg_time_seeding . '</span>&#160;' . $lang['userdetails_time_ratio_per'] : $lang['userdetails_inf']) . '
                </div>
            </td>
        </tr>';
    }
}
