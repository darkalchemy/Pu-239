<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $lang, $site_config, $user, $CURUSER;

$cache = $container->get(Cache::class);
$cache_share_ratio = $cache->get('share_ratio_' . $user['id']);
if ($cache_share_ratio === false || is_null($cache_share_ratio)) {
    $fluent = $container->get(Database::class);
    $sql = $fluent->from('snatched')
                  ->select(null)
                  ->select('SUM(seedtime) AS seed_time_total')
                  ->select('COUNT(id) AS total_number')
                  ->where('seedtime > 0')
                  ->where('userid = ?', $user['id'])
                  ->fetch();

    $cache_share_ratio['total_number'] = (int) $sql['total_number'];
    $cache_share_ratio['seed_time_total'] = (int) $sql['seed_time_total'];
    $cache->set('share_ratio_' . $user['id'], $cache_share_ratio, $site_config['expires']['share_ratio']);
}

switch (true) {
    case $user['class'] === UC_MIN:
        $days = 3;
        break;

    case $user['class'] < UC_VIP:
        $days = 3;
        break;

    case $user['class'] >= UC_VIP:
        $days = 3;
        break;
}
if ($cache_share_ratio['seed_time_total'] > 0 && $cache_share_ratio['total_number'] > 0) {
    $avg_time_ratio = $cache_share_ratio['seed_time_total'] / $cache_share_ratio['total_number'] / 86400 / $days;
    $avg_time_seeding = mkprettytime($cache_share_ratio['seed_time_total'] / $cache_share_ratio['total_number']);
    if ($user['id'] == $CURUSER['id'] || $CURUSER['class'] >= UC_STAFF) {
        $table_data .= '
        <tr>
            <td>' . $lang['userdetails_time_ratio'] . '</td>
            <td>
                <div class="level-left">' . (($user['downloaded'] > 0 || $user['uploaded'] > 2147483648) ? '
                    <span class="right10" style="color: ' . get_ratio_color($avg_time_ratio) . ';">' . number_format($avg_time_ratio, 3) . '</span>' . ratio_image_machine($avg_time_ratio) . '
                    <span class="left10">[</span><span style="color: ' . get_ratio_color($avg_time_ratio) . ';">&#160;' . $avg_time_seeding . '</span>&#160;' . $lang['userdetails_time_ratio_per'] : $lang['userdetails_inf']) . '
                </div>
            </td>
        </tr>';
    }
}
