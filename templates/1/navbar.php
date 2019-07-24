<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;

/**
 * @throws Exception
 *
 * @return string
 */
function navbar()
{
    global $CURUSER, $site_config, $BLOCKS, $lang;

    $navbar = '';
    $staff_links = staff_panel();
    if ($CURUSER) {
        $navbar = "
<div class='spacer'>
    <header id='navbar'>
        <div class='contained'>
            <div class='nav_container'>
                <div id='pm_count' class='has-text-centered vertical_center'></div>
                <div id='hamburger'><i class='icon-menu size_6 has-text-link' aria-hidden='true'></i></div>
                <div id='close' class='top10 right10'><i class='icon-cancel icon size_7 has-text-link' aria-hidden='true'></i></div>
                <div id='menuWrapper'>
                    <ul class='level'>
                        <li>
                            <a href='{$site_config['paths']['baseurl']}' class='is-flex'>
                                <i class='icon-home size_6'></i>
                                <span class='home'>{$site_config['site']['name']}</span>
                            </a>
                        </li>" . ($BLOCKS['bluray_com_api_on'] || $BLOCKS['imdb_api_on'] || $BLOCKS['tvmaze_api_on'] ? "
                        <li id='movies_links' class='clickable'>
                            <a href='#' class='has-text-weight-bold'>{$lang['gl_movies_tv']}</a>
                            <ul class='ddFade ddFadeFast'>" . ($BLOCKS['bluray_com_api_on'] ? "
                                <li><span class='left10 has-text-weight-bold'>{$lang['gl_bluray']}</span></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=bluray'>{$lang['gl_bluray_releases']}</a></li>" : '') . ($BLOCKS['imdb_api_on'] ? "
                                <li><span class='left10 has-text-weight-bold'>{$lang['gl_imdb']}</span></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=upcoming'>{$lang['gl_movies_upcoming']}</a></li>" : '') . ($BLOCKS['tmdb_api_on'] ? "
                                <li><span class='left10 has-text-weight-bold'>{$lang['gl_tmdb']}</span></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=top100'>{$lang['gl_movies_top_100']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=theaters'>{$lang['gl_movies_theaters']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=tv'>{$lang['gl_tv_today']}</a></li>" : '') . ($BLOCKS['tvmaze_api_on'] ? "
                                <li><span class='left10 has-text-weight-bold'>{$lang['gl_tvmaze']}</span></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=tvmaze'>{$lang['gl_tvmaze_today']}</a></li>" : '') . '
                            </ul>
                        </li>' : '') . "
                        <li id='torrents_links' class='clickable'>
                            <a href='#' class='has-text-weight-bold'>{$lang['gl_torrent']}</a>
                            <ul class='ddFade ddFadeFast'>
                                <li><a href='{$site_config['paths']['baseurl']}/browse.php'>{$lang['gl_browse']} {$lang['gl_torrents']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/tmovies.php'>{$lang['gl_movies']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/catalog.php'>{$lang['gl_catalogue']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/needseed.php?needed=seeders'><span class='has-text-weight-bold has-text-danger'>{$lang['gl_nseeds']}</span></a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/browse.php?today=1' class='has-text-weight-bold has-text-green'>{$lang['gl_newtor']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/offers.php'>{$lang['gl_offers']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/requests.php'>{$lang['gl_requests']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/subtitles.php'>{$lang['gl_subtitles']}</a></li>" . ($CURUSER['class'] < $site_config['allowed']['upload'] ? "
                                <li><a href='{$site_config['paths']['baseurl']}/uploadapp.php'>{$lang['gl_uapp']}</a></li>" : "
                                <li><a href='{$site_config['paths']['baseurl']}/upload.php'>{$lang['gl_upload']}</a></li>") . "
                            </ul>
                        </li>
                        <li id='general_links' class='clickable'>
                            <a href='#' class='has-text-weight-bold'>{$lang['gl_general']}</a>
                            <ul class='ddFade ddFadeFast'>" . ($site_config['bucket']['allowed'] ? "
                                <li><a href='{$site_config['paths']['baseurl']}/bitbucket.php'>{$lang['gl_bitbucket']}</a></li>" : '') . "
                                <li><a href='{$site_config['paths']['baseurl']}/faq.php'>{$lang['gl_faq']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/chat.php'>{$lang['gl_irc']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/mybonus.php'>{$lang['gl_karma']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/getrss.php'>{$lang['gl_getrss']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/rules.php'>{$lang['gl_rules']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/announcement.php'>{$lang['gl_announcements']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/promo.php'>{$lang['gl_promo']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/topten.php'>{$lang['gl_stats']}</a></li>" . ($BLOCKS['torrentfreak_on'] ? "
                                <li><a href='{$site_config['paths']['baseurl']}/rsstfreak.php'>{$lang['gl_tfreak']}</a></li>" : '') . "
                                <li><a href='{$site_config['paths']['baseurl']}/wiki.php'>{$lang['gl_wiki']}</a></li>
                            </ul>
                        </li>
                        <li id='games_links' class='clickable'>
                            <a href='#' class='has-text-weight-bold'>{$lang['gl_games']}</a>
                            <ul class='ddFade ddFadeFast'>
                                <li><a href='{$site_config['paths']['baseurl']}/arcade.php'>{$lang['gl_arcade']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/games.php'>{$lang['gl_games']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/lottery.php'>{$lang['gl_lottery']}</a></li>
                            </ul>
                        </li>
                        <li id='user_links' class='clickable'>
                            <a href='#' class='has-text-weight-bold'>{$lang['gl_users']}</a>
                            <ul class='ddFade ddFadeFast'>
                                <li><a href='{$site_config['paths']['baseurl']}/bookmarks.php'>{$lang['gl_bookmarks']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/categoryids.php'>{$lang['gl_catids']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/friends.php'>{$lang['gl_friends']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/hnrs.php'>{$lang['gl_hnrs']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/invite.php?do=view_page'>{$lang['gl_invites']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/messages.php'>{$lang['gl_pms']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/users.php'>{$lang['gl_search_users']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/usercp.php?action=default' class='has-text-weight-bold'>{$lang['gl_usercp']}</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href='{$site_config['paths']['baseurl']}/forums.php' class='has-text-weight-bold'>{$lang['gl_forums']}</a>
                        </li>" . ($CURUSER['class'] < UC_STAFF ? "
                        <li id='staff_links' class='clickable'>
                            <a href='#' class='has-text-weight-bold'>{$lang['gl_help']}</a>
                            <ul class='ddFade ddFadeFast'>
                                <li><a href='{$site_config['paths']['baseurl']}/bugs.php?action=add'>{$lang['gl_breport']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/contactstaff.php'>{$lang['gl_cstaff']}</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/staff.php'>{$lang['gl_staff_list']}</a></li>
                            </ul>
                        </li>" : '') . ($BLOCKS['global_staff_menu_on'] ? $staff_links : ($CURUSER['class'] >= UC_STAFF ? "
                        <li>
                            <a href='{$site_config['paths']['baseurl']}/staffpanel.php'>{$lang['gl_staffpanel']}</a>
                        </li>" : '')) . "
                        <li>
                            <a href='{$site_config['paths']['baseurl']}/logout.php' class='is-flex'>
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

/**
 * @param $value
 *
 * @return string
 */
function make_link($value)
{
    global $site_config;

    $link = "
                            <li><a href='{$site_config['paths']['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . '</a></li>';

    return $link;
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return string
 */
function staff_panel()
{
    global $BLOCKS, $CURUSER, $container, $site_config;

    $cache = $container->get(Cache::class);
    $panel = '';
    $panels = [];
    if ($BLOCKS['global_staff_menu_on'] && $CURUSER['class'] >= UC_STAFF) {
        $staff_panel = $cache->get('staff_panels_' . $CURUSER['class']);
        if ($staff_panel === false || is_null($staff_panel)) {
            $fluent = $container->get(Database::class);
            $staff_panel = $fluent->from('staffpanel')
                                  ->where('navbar = 1')
                                  ->where('av_class <= ?', $CURUSER['class'])
                                  ->orderBy('page_name')
                                  ->fetchAll();

            $cache->set('staff_panels_' . $CURUSER['class'], $staff_panel, 0);
        }
        $staff_panel[] = [
            'id' => 0,
            'page_name' => 'Staff Messages',
            'file_name' => 'staffbox.php',
            'description' => 'View Staff Messages',
            'type' => 'user',
            'av_class' => UC_STAFF,
            'added_by' => 1,
            'added' => 1546167296,
            'navbar' => 1,
        ];
        if (in_array($CURUSER['id'], $site_config['adminer']['allowed_ids'])) {
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
                    <a id='staff_" . strtolower(substr($key, 1)) . "' href='#' class='has-text-weight-bold'>[" . substr($key, 1) . "]</a>
                    <ul class='ddFade ddFadeFast'>" . make_link([
                'file_name' => 'staffpanel.php',
                'page_name' => 'Staff Panel',
            ]) . implode('', $value) . '
                    </ul>
                </li>';
        }
    }

    return $panel;
}
