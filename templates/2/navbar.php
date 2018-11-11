<?php

/**
 * @return string
 *
 * @throws Exception
 */
function navbar()
{
    global $site_config, $CURUSER, $lang, $BLOCKS;

    $navbar = '';
    $staff_links = staff_panel();
    if ($CURUSER) {
        $navbar = "
<div class='spacer'>
    <header id='navbar'>
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
                            <ul class='ddFade ddFadeFast'>" . ($BLOCKS['bluray_com_api_on'] ? "
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
                            <ul class='ddFade ddFadeFast'>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/browse.php'>Browse {$lang['gl_torrents']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/catalog.php'>Catalog</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/needseed.php?needed=seeders'><span class='is-danger'>{$lang['gl_nseeds']}</span></a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/browse.php?today=1'>{$lang['gl_newtor']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/offers.php'>{$lang['gl_offers']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/requests.php'>{$lang['gl_requests']}</a></li>" . ($CURUSER['class'] < UC_UPLOADER ? "
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/uploadapp.php'>{$lang['gl_uapp']}</a></li>" : "
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/upload.php'>{$lang['gl_upload']}</a></li>") . "
                            </ul>
                        </li>
                        <li id='general_links' class='clickable'>
                            <a href='#'>{$lang['gl_general']}</a>
                            <ul class='ddFade ddFadeFast'>" . ($site_config['bucket_allowed'] ? "
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/bitbucket.php'>{$lang['gl_bitbucket']}</a></li>" : '') . "
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
                            <ul class='ddFade ddFadeFast'>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/arcade.php'>{$lang['gl_arcade']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/games.php'>{$lang['gl_games']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/lottery.php'>{$lang['gl_lottery']}</a></li>
                            </ul>
                        </li>
                        <li id='user_links' class='clickable'>
                            <a href='#'>User</a>
                            <ul class='ddFade ddFadeFast'>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/bookmarks.php'>{$lang['gl_bookmarks']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/friends.php'>{$lang['gl_friends']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/messages.php'>{$lang['gl_pms']}</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/users.php'>Search Users</a></li>
                                <li class='iss_hidden'><a href='{$site_config['baseurl']}/usercp.php?action=default'>{$lang['gl_usercp']}</a></li>
                            </ul>
                        </li>
                        <li class='iss_hidden'>
                            <a href='{$site_config['baseurl']}/forums.php'>{$lang['gl_forums']}</a>
                        </li>
                        <li>" . ($CURUSER['class'] < UC_STAFF ? "
                            <a href='{$site_config['baseurl']}/bugs.php?action=add'>{$lang['gl_breport']}</a>" : "
                            <a href='{$site_config['baseurl']}/bugs.php?action=bugs'>[Bugs]</a>") . '</li>
                        <li>' . ($CURUSER['class'] < UC_STAFF ? "
                            <a href='{$site_config['baseurl']}/contactstaff.php'>{$lang['gl_cstaff']}</a>" : "
                            <a href='{$site_config['baseurl']}/staffbox.php'>[Messages]</a>") . '</li>' . ($BLOCKS['global_staff_menu_on'] ? $staff_links : ($CURUSER['class'] >= UC_STAFF ? "
                        <li class='iss_hidden'>
                            <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
                        </li>" : '')) . "
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

function make_link($value)
{
    global $site_config;

    $link = "
                            <li class='iss_hidden'><a href='{$site_config['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a></li>';

    return $link;
}

function staff_panel()
{
    global $site_config, $CURUSER, $BLOCKS, $cache, $fluent;

    $panel = '';
    $panels = [];
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
        if (in_array($CURUSER['id'], $site_config['adminer_allowed_ids'])) {
            $staff_panel[] = [
                'page_name' => 'Adminer',
                'file_name' => 'view_sql.php',
                'type' => 'other',
                'av_class' => UC_MAX,
                'navbar' => 1,
            ];
            $staff_panel = array_msort($staff_panel, ['page_name' => SORT_ASC]);
        }
        if ($staff_panel) {
            foreach ($staff_panel as $key => $value) {
                if ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'user') {
                    $panels['0Users'][] = make_link($value);
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'settings') {
                    $panels['1Settings'][] = make_link($value);
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'stats') {
                    $panels['2Stats'][] = make_link($value);
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] === 'other') {
                    $panels['3Other'][] = make_link($value);
                }
            }
        }
        ksort($panels);
        foreach ($panels as $key => $value) {
            $panel .= "
                <li class='clickable'>
                    <a id='staff_other' href='#'>[" . substr($key, 1) . "]</a>
                        <ul class='ddFade ddFadeFast'>" . make_link([
                    'file_name' => 'staffpanel.php',
                    'page_name' => 'Staff Panel',
                ]) . implode('', $value) . '
                        </ul>
                    </a>
                </li>';
        }
    }

    return $panel;
}
