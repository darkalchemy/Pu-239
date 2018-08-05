<?php

/**
 * @return string
 *
 * @throws Exception
 */
function navbar()
{
    global $site_config, $CURUSER, $lang, $fluent, $cache, $BLOCKS;

    $navbar = $panel = $user_panel = $settings_panel = $stats_panel = $other_panel = '';

    if ($BLOCKS['global_staff_menu_on'] && $CURUSER['class'] >= UC_STAFF) {
        $staff_panel = $cache->get('staff_panels_' . $CURUSER['class']);
        if ($staff_panel === false || is_null($staff_panel)) {
            $staff_panel = $fluent->from('staffpanel')
                ->where('navbar = 1')
                ->where('av_class <= ?', $CURUSER['class'])
                ->orderBy('page_name')
                ->fetchAll();
            $cache->set('staff_panels_' . $CURUSER['class'], $staff_panel, 0);
        }

        if ($staff_panel) {
            foreach ($staff_panel as $key => $value) {
                if ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'user') {
                    $user_panel .= "
<li class='iss_hidden'>
    <a href='{$site_config['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a>
</li>';
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'settings') {
                    $settings_panel .= "
<li class='iss_hidden'>
    <a href='{$site_config['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a>
</li>';
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'stats') {
                    $stats_panel .= "
<li class='iss_hidden'>
    <a href='{$site_config['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a>
</li>';
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'other') {
                    $other_panel .= "
<li class='iss_hidden'>
    <a href='{$site_config['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a>
</li>';
                }
            }

            if (!empty($user_panel)) {
                $panel .= "
<li class='clickable'>
    <a id='staff_users' href='#'>[Users]</a>
    <ul class='ddFade ddFadeSlow'>
        <li class='iss_hidden'>
            <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
        </li>
        $user_panel
    </ul>
</li>";
            }
            if (!empty($settings_panel)) {
                $panel .= "
<li class='clickable'>
    <a id='staff_settings' href='#'>[Settings]</a>
    <ul class='ddFade ddFadeSlow'>
        <li class='iss_hidden'>
            <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
        </li>
        $settings_panel
    </ul>
</li>";
            }
            if (!empty($stats_panel)) {
                $panel .= "
<li class='clickable'>
    <a id='staff_stats' href='#'>[Stats]</a>
    <ul class='ddFade ddFadeSlow'>
        <li class='iss_hidden'>
            <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
        </li>
        $stats_panel
    </ul>
</li>";
            }
            if (!empty($other_panel)) {
                $panel .= "
<li class='clickable'>
    <a id='staff_other' href='#'>[Other]</a>
    <ul class='ddFade ddFadeSlow'>
        <li class='iss_hidden'>
            <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
        </li>";
                if ($CURUSER['class'] === UC_MAX) {
                    $panel .= "
        <li class='iss_hidden'>
            <a href='{$site_config['baseurl']}/view_sql.php'>Adminer</a>
        </li>";
                }
                $panel .= "
        $other_panel
    </ul>
</li>";
            }
        }
    }

    if ($CURUSER) {
        $navbar .= "
<div class='spacer'>
    <header id='navbar' class='container'>
        <div class='contained'>
            <div class='nav_container'>
                <div id='hamburger'><i class='icon-menu size_6 has-text-white' aria-hidden='true'></i></div>
                <div id='close' class='top10 right10'><i class='icon-cancel size_7 has-text-white' aria-hidden='true'></i></div>
                <div id='menuWrapper'>
                    <ul class='level'>
                        <li>
                            <a href='{$site_config['baseurl']}' class='is-flex'>
                            <i class='icon-home size_6'></i>
                            <span class='home'>{$site_config['site_name']}</span>
                            </a>
                        </li>" . ($BLOCKS['bluray_com_api_on'] || $BLOCKS['imdb_api_on'] || $BLOCKS['omdb_api_on'] || $BLOCKS['tvmaze_api_on'] ? "
                        <li id='movies_links' class='clickable'>
                            <a href='#'>{$lang['gl_movies_tv']}</a>
                            <ul class='ddFade ddFadeSlow'>" . ($BLOCKS['bluray_com_api_on'] ? "
                                <li class='iss_hidden'><span class='left10'>{$lang['gl_bluray']}</span></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/movies.php?list=bluray'>{$lang['gl_bluray_releases']}</a></li>" : '') . ($BLOCKS['imdb_api_on'] ? "
                                <li class='iss_hidden'><span class='left10'>{$lang['gl_imdb']}</span></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/movies.php?list=upcoming'>{$lang['gl_movies_upcoming']}</a></li>" : '') . ($BLOCKS['tmdb_api_on'] ? "
                                <li class='iss_hidden'><span class='left10'>{$lang['gl_tmdb']}</span></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/movies.php?list=top100'>{$lang['gl_movies_top_100']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/movies.php?list=theaters'>{$lang['gl_movies_theaters']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/movies.php?list=tv'>{$lang['gl_tv_today']}</a></li>" : '') . ($BLOCKS['tvmaze_api_on'] ? "
                                <li class='iss_hidden'><span class='left10'>{$lang['gl_tvmaze']}</span></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/movies.php?list=tvmaze'>{$lang['gl_tvmaze_today']}</a></li>" : '') . '
                            </ul>
                        </li>' : '') . " 
                        <li id='torrents_links' class='clickable'>
                            <a href='#'>{$lang['gl_torrent']}</a>
                            <ul class='ddFade ddFadeSlow'>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/browse.php'>Browse {$lang['gl_torrents']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/catalog.php'>Catalog</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/needseed.php?needed=seeders'><span class='is-danger'>{$lang['gl_nseeds']}</span></a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/browse.php?today=1'>{$lang['gl_newtor']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/offers.php'>{$lang['gl_offers']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/requests.php'>{$lang['gl_requests']}</a></li>
                                " . ($CURUSER['class'] <= UC_VIP ? "<li class='iss_hidden'><a href='{$site_config['baseurl']}/uploadapp.php'>{$lang['gl_uapp']}</a></li>" : "<li class='iss_hidden'><a href='{$site_config['baseurl']}/upload.php'>{$lang['gl_upload']}</a></li>") . "
                            </ul>
                        </li>
                        <li id='general_links' class='clickable'>
                            <a href='#'>{$lang['gl_general']}</a>
                            <ul class='ddFade ddFadeSlow'>";
        if ($site_config['bucket_allowed']) {
            $navbar .= "
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/bitbucket.php'>{$lang['gl_bitbucket']}</a></li>";
        }
        $navbar .= "
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/faq.php'>{$lang['gl_faq']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/chat.php'>{$lang['gl_irc']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/mybonus.php'>Karma Store</a></li>
                                <li class='iss_hidden'><a href='#' onclick='radio();'>{$lang['gl_radio']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/getrss.php'>RSS</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/rules.php'>{$lang['gl_rules']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/announcement.php'>{$lang['gl_announcements']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/staff.php'>{$lang['gl_staff']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/topten.php'>{$lang['gl_stats']}</a></li>" . ($BLOCKS['torrentfreak_on'] ? "
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/rsstfreak.php'>{$lang['gl_tfreak']}</a></li>" : '') . "
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/wiki.php'>{$lang['gl_wiki']}</a></li>
                            </ul>
                        </li>
                        <li id='games_links' class='clickable'>
                            <a href='#'>{$lang['gl_games']}</a>
                            <ul class='ddFade ddFadeSlow'>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/arcade.php'>{$lang['gl_arcade']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/games.php'>{$lang['gl_games']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/lottery.php'>{$lang['gl_lottery']}</a></li>
                            </ul>
                        </li>
                        <li id='user_links' class='clickable'>
                            <a href='#'>User</a>
                            <ul class='ddFade ddFadeSlow'>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/bookmarks.php'>{$lang['gl_bookmarks']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/friends.php'>{$lang['gl_friends']}</a></li>
                                <li class='iss_hidden'><a href='#' onclick='language_select();'>{$lang['gl_language_select']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/messages.php'>{$lang['gl_pms']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/users.php'>Search Users</a></li>
                                <li class='iss_hidden'><a href='#' onclick='themes();'>{$lang['gl_theme']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/usercp.php?action=default'>{$lang['gl_usercp']}</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href='#'>{$lang['gl_forums']}</a>
                            <ul class='ddFade ddFadeSlow'>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/forums.php'>{$lang['gl_forums']}</a></li>
                            </ul>
                        </li>
                        <li>" . ($CURUSER['class'] < UC_STAFF ? "<a href='{$site_config['baseurl']}/bugs.php?action=add'>{$lang['gl_breport']}</a>" : "<a href='{$site_config['baseurl']}/bugs.php?action=bugs'>[Bugs]</a>") . '</li>
                        <li>' . ($CURUSER['class'] < UC_STAFF ? "<a href='{$site_config['baseurl']}/contactstaff.php'>{$lang['gl_cstaff']}</a>" : "<a href='{$site_config['baseurl']}/staffbox.php'>[Messages]</a>") . '</li>' . ($BLOCKS['global_staff_menu_on'] ? $panel : ($CURUSER['class'] >= UC_STAFF ? "
                        <li class='iss_hidden'><a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a></li>" : '')) . "
                        <li>
                            <a href='{$site_config['baseurl']}/logout.php' class='is-flex'>
                            <i class='icon-logout size_6' aria-hidden='true'></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
</div>";
    }

    return $navbar;
}
