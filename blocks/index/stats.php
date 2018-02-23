<?php

global $site_config, $lang, $fluent, $cache;

$stats_cache = $cache->get('site_stats_');
if (false === $stats_cache || is_null($stats_cache)) {
    $stats_cache = $fluent->from('stats')
        ->select('seeders + leechers AS peers')
        ->select('seeders / leechers AS ratio')
        ->select('unconnectables / (seeders + leechers) AS ratiounconn')
        ->fetch();

    $cache->set('site_stats_', $stats_cache, $site_config['expires']['site_stats']);
}

$HTMLOUT .= "
    <a id='stats-hash'></a>
    <fieldset id='stats' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['index_stats_title']}</legend>
        <div class='w-100'>
            <div class='columns'>
                <div class='column'>
                    <div class='table-wrapper has-text-centered'>
                        <table class='table table-bordered table-striped'>
                            <tbody>
                                <tr>
                                    <td>{$lang['index_stats_regged']}</td><td class='w-15 has-text-centered'>{$stats_cache['regusers']}/{$site_config['maxusers']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_uncon']}</td><td class='w-15 has-text-centered'>{$stats_cache['unconusers']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_newtor_month']}</td><td class='w-15 has-text-centered'>{$stats_cache['torrentsmonth']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_gender_male']}</td><td class='w-15 has-text-centered'>{$stats_cache['gender_male']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_powerusers']}</td><td class='w-15 has-text-centered'>{$stats_cache['powerusers']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_uploaders']}</td><td class='w-15 has-text-centered'>{$stats_cache['uploaders']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_admin']}</td><td class='w-15 has-text-centered'>{$stats_cache['administrators']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_topics']}</td><td class='w-15 has-text-centered'>{$stats_cache['forumtopics']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_posts']}</td><td class='w-15 has-text-centered'>{$stats_cache['forumposts']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_peers']}</td><td class='w-15 has-text-centered'>{$stats_cache['peers']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_seeders']}</td><td class='w-15 has-text-centered'>{$stats_cache['seeders']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_leechers']}</td><td class='w-15 has-text-centered'>{$stats_cache['leechers']}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class='column'>
                    <div class='table-wrapper has-text-centered'>
                        <table class='table table-bordered table-striped'>
                            <tbody>
                                <tr>
                                    <td>{$lang['index_stats_online']}</td><td class='w-15 has-text-centered'>{$stats_cache['numactive']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_donor']}</td><td class='w-15 has-text-centered'>{$stats_cache['donors']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_gender_na']}</td><td class='w-15 has-text-centered'>{$stats_cache['gender_na']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_gender_female']}</td><td class='w-15 has-text-centered'>{$stats_cache['gender_female']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_banned']}</td><td class='w-15 has-text-centered'>{$stats_cache['disabled']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_moderators']}</td><td class='w-15 has-text-centered'>{$stats_cache['moderators']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_sysops']}</td><td class='w-15 has-text-centered'>{$stats_cache['sysops']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_torrents']}</td><td class='w-15 has-text-centered'>{$stats_cache['torrents']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_newtor']}</td><td class='w-15 has-text-centered'>{$stats_cache['torrentstoday']}</td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_unconpeer']}</td><td class='w-15 has-text-centered'>{$stats_cache['unconnectables']}</td>
                                </tr>
                                <tr>
                                    <td><b>{$lang['index_stats_unconratio']}</b></td><td class='w-15 has-text-centered'><b>" . round($stats_cache['ratiounconn'] * 100) . "</b></td>
                                </tr>
                                <tr>
                                    <td>{$lang['index_stats_slratio']}</td><td class='w-15 has-text-centered'>" . round($stats_cache['ratio'] * 100) . '</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>';
