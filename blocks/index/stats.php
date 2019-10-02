<?php

declare(strict_types = 1);

use Pu239\Cache;

global $container, $site_config;

$cache = $container->get(Cache::class);
$stats_cache = $cache->get('site_stats_');
if ($stats_cache === false || is_null($stats_cache)) {
    return;
}
$users = [
    '<tr><td>' . _('Users Online') . "</td><td class='w-15 has-text-centered'>{$stats_cache['numactive']}</td></tr>",
    '<tr><td>' . _('Users') . "</td><td class='w-15 has-text-centered'>{$stats_cache['regusers']}</td></tr>",
    '<tr><td>' . _('Power Users') . "</td><td class='w-15 has-text-centered'>{$stats_cache['powerusers']}</td></tr>",
    '<tr><td>' . _('Super Users') . "</td><td class='w-15 has-text-centered'>{$stats_cache['superusers']}</td></tr>",
    '<tr><td>' . _('Uploaders') . "</td><td class='w-15 has-text-centered'>{$stats_cache['uploaders']}</td></tr>",
    '<tr><td>' . _('VIPs') . "</td><td class='w-15 has-text-centered'>{$stats_cache['vips']}</td></tr>",
    '<tr><td>' . _('Moderators') . "</td><td class='w-15 has-text-centered'>{$stats_cache['moderators']}</td></tr>",
    '<tr><td>' . _('Administrators') . "</td><td class='w-15 has-text-centered'>{$stats_cache['administrators']}</td></tr>",
    '<tr><td>' . _('Sysops') . "</td><td class='w-15 has-text-centered'>{$stats_cache['sysops']}</td></tr>",
];

$gender = [
    '<tr><td>' . _('Unconfirmed Users') . "</td><td class='w-15 has-text-centered'>{$stats_cache['unconusers']}</td></tr>",
    '<tr><td>' . _('Disabled') . "</td><td class='w-15 has-text-centered'>{$stats_cache['disabled']}</td></tr>",
    '<tr><td>' . _('Anonymous Users') . "</td><td class='w-15 has-text-centered'>{$stats_cache['numanonymous']}</td></tr>",
    '<tr><td>' . _('Donors') . "</td><td class='w-15 has-text-centered'>{$stats_cache['donors']}</td></tr>",
    '<tr><td>' . _('Gender Neutral') . "</td><td class='w-15 has-text-centered'>{$stats_cache['gender_na']}</td></tr>",
    '<tr><td>' . _('Ladies') . "</td><td class='w-15 has-text-centered'>{$stats_cache['gender_female']}</td></tr>",
    '<tr><td>' . _('Gents') . "</td><td class='w-15 has-text-centered'>{$stats_cache['gender_male']}</td></tr>",
];

$forums = [
    '<tr><td>' . _('Topics') . "</td><td class='w-15 has-text-centered'>{$stats_cache['forumtopics']}</td></tr>",
    '<tr><td>' . _('New Topics Today') . "</td><td class='w-15 has-text-centered'>{$stats_cache['topicstoday']}</td></tr>",
    '<tr><td>' . _('New Topics This Month') . "</td><td class='w-15 has-text-centered'>{$stats_cache['topicsmonth']}</td></tr>",
    '<tr><td>' . _('Posts') . "</td><td class='w-15 has-text-centered'>{$stats_cache['forumposts']}</td></tr>",
    '<tr><td>' . _('New Posts Today') . "</td><td class='w-15 has-text-centered'>{$stats_cache['poststoday']}</td></tr>",
    '<tr><td>' . _('New Topics This Month') . "</td><td class='w-15 has-text-centered'>{$stats_cache['postsmonth']}</td></tr>",
];

$torrents = [
    '<tr><td>' . _('Torrents') . "</td><td class='w-15 has-text-centered'>{$stats_cache['torrents']}</td></tr>",
    '<tr><td>' . _('New Torrents Today') . "</td><td class='w-15 has-text-centered'>{$stats_cache['torrentstoday']}</td></tr>",
    '<tr><td>' . _('New Torrents This Month') . "</td><td class='w-15 has-text-centered'>{$stats_cache['torrentsmonth']}</td></tr>",
    '<tr><td>' . _('Peers') . "</td><td class='w-15 has-text-centered'>{$stats_cache['peers']}</td></tr>",
    '<tr><td>' . _('Seeders') . "</td><td class='w-15 has-text-centered'>{$stats_cache['seeders']}</td></tr>",
    '<tr><td>' . _('Leechers') . "</td><td class='w-15 has-text-centered'>{$stats_cache['leechers']}</td></tr>",
    '<tr><td>' . _('Unconnectables Peers') . "</td><td class='w-15 has-text-centered'>{$stats_cache['unconnectables']}</td></tr>",
    '<tr><td>' . _('Unconnectables Ratio (%%)') . "</td><td class='w-15 has-text-centered'>" . round($stats_cache['ratiounconn'] * 100) . '</td></tr>',
    '<tr><td>' . _('Seeder/Leecher Rratio (%%)') . "</td><td class='w-15 has-text-centered'>" . round($stats_cache['ratio'] * 100) . '</td></tr>',
];

$site_stats .= "
    <a id='stats-hash'></a>
    <div class='flex-grid'>
        <div class='col'>" . wrap_this($users) . "</div>
        <div class='col'>" . wrap_this($torrents) . "</div>
        <div class='col'>" . wrap_this($gender) . "</div>
        <div class='col'>" . wrap_this($forums) . '</div>
    </div>';

/**
 * @param array $values
 *
 * @return string
 */
function wrap_this(array $values)
{
    return main_table(implode("\n", $values));
}
