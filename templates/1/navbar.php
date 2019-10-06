<?php

declare(strict_types = 1);

use Delight\Auth\Auth;
use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Roles;

/**
 * @throws Exception
 *
 * @return string
 */
function navbar()
{
    global $container, $CURUSER, $site_config, $BLOCKS;

    $auth = $container->get(Auth::class);
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
                        <li id='movies_links'>
                            <a href='#' class='has-text-weight-bold'>" . _('Movies & TV') . "</a>
                            <ul class='ddFade ddFadeFast'>" . ($BLOCKS['bluray_com_api_on'] ? "
                                <li><span class='left10 has-text-weight-bold'>" . _('Blu-Ray.com') . "</span></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=bluray'>" . _('Bluray Releases') . '</a></li>' : '') . ($BLOCKS['imdb_api_on'] ? "
                                <li><span class='left10 has-text-weight-bold'>" . _('IMDb') . "</span></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=imdb_top100'>" . _('Top 100') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=imdb_theaters'>" . _('In Theaters') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=upcoming'>" . _('Upcoming') . '</a></li>' : '') . ($BLOCKS['tmdb_api_on'] ? "
                                <li><span class='left10 has-text-weight-bold'>" . _('TMDb') . "</span></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=top100'>" . _('Top 100') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=theaters'>" . _('In Theaters') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=tv'>" . _('TV Airing') . '</a></li>' : '') . ($BLOCKS['tvmaze_api_on'] ? "
                                <li><span class='left10 has-text-weight-bold'>" . _('TVMaze') . "</span></li>
                                <li><a href='{$site_config['paths']['baseurl']}/movies.php?list=tvmaze'>" . _('TV Airing') . '</a></li>' : '') . '
                            </ul>
                        </li>' : '') . "
                        <li id='torrents_links'>
                            <a href='{$site_config['paths']['baseurl']}/browse.php'' class='has-text-weight-bold'>" . _('Torrent') . "</a>
                            <ul class='ddFade ddFadeFast'>
                                <li><a href='{$site_config['paths']['baseurl']}/browse.php'>" . _('Browse Torrents') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/catalog.php'>" . _('Catalog') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/upcoming.php'>" . _('Cooker') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/tmovies.php'>" . _('Movies') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/needseed.php?needed=seeders'><span class='has-text-weight-bold has-text-danger'>" . _('Needs Seeds') . "</span></a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/browse.php?today=1' class='has-text-weight-bold has-text-green'>" . _('New Torrents Today') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/offers.php'>" . _('Offers') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/requests.php'>" . _('Requests') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/subtitles.php'>" . _('Subtitles') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/tvshows.php'>" . _('TV Shows') . '</a></li>' . (!$auth->hasRole(Roles::UPLOADER) ? "
                                <li><a href='{$site_config['paths']['baseurl']}/uploadapp.php'>" . _('Uploader Application') . '</a></li>' : "
                                <li><a href='{$site_config['paths']['baseurl']}/upload.php'>" . _('Upload') . '</a></li>') . "
                            </ul>
                        </li>
                        <li id='general_links'>
                            <a href='#' class='has-text-weight-bold'>" . _('General') . "</a>
                            <ul class='ddFade ddFadeFast'>" . ($site_config['bucket']['allowed'] ? "
                                <li><a href='{$site_config['paths']['baseurl']}/bitbucket.php'>" . _('BitBucket') . '</a></li>' : '') . "
                                <li><a href='{$site_config['paths']['baseurl']}/bot_triggers.php'>" . _('Bot Triggers') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/faq.php'>" . _('FAQ') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/chat.php'>" . _('IRC') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/mybonus.php'>" . _('Karma Store') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/getrss.php'>" . _('Get RSS') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/rules.php'>" . _('Rules') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/announcement.php'>" . _('Site Announcements') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/topten.php'>" . _('Statistics') . '</a></li>' . ($BLOCKS['torrentfreak_on'] ? "
                                <li><a href='{$site_config['paths']['baseurl']}/rsstfreak.php'>" . _('Torrent Freak') . '</a></li>' : '') . "
                                <li><a href='{$site_config['paths']['baseurl']}/wiki.php'>" . _('Wiki') . "</a></li>
                            </ul>
                        </li>
                        <li id='games_links'>
                            <a href='#' class='has-text-weight-bold'>" . _('Games') . "</a>
                            <ul class='ddFade ddFadeFast'>
                                <li><a href='{$site_config['paths']['baseurl']}/arcade.php'>" . _('Arcade') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/games.php'>" . _('Games') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/lottery.php'>" . _('Lottery') . "</a></li>
                            </ul>
                        </li>
                        <li id='user_links'>
                            <a href='#' class='has-text-weight-bold'>" . _('Users') . "</a>
                            <ul class='ddFade ddFadeFast'>
                                <li><a href='{$site_config['paths']['baseurl']}/bookmarks.php'>" . _('Bookmarks') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/categoryids.php'>" . _("Category ID's") . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/friends.php'>" . _('Friends') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/hnrs.php'>" . _("Hit 'n Runs") . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/invite.php?do=view_page'>" . _('Invites') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/messages.php'>" . _('Messages') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/port_check.php'>" . _('Port Check') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/users.php'>" . _('Search Users') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/usercp.php?action=default' class='has-text-weight-bold'>" . _('User Control Panel') . "</a></li>
                            </ul>
                        </li>
                        <li>
                            <a href='{$site_config['paths']['baseurl']}/forums.php' class='has-text-weight-bold'>" . _('Forums') . '</a>
                        </li>' . (!has_access($CURUSER['class'], UC_STAFF, 'coder') ? "
                        <li id='staff_links'>
                            <a href='#' class='has-text-weight-bold'>" . _('Help') . "</a>
                            <ul class='ddFade ddFadeFast'>
                                <li><a href='{$site_config['paths']['baseurl']}/bugs.php?action=add'>" . _('Bug Report') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/contactstaff.php'>" . _('Contact Staff') . "</a></li>
                                <li><a href='{$site_config['paths']['baseurl']}/staff.php'>" . _('Staff List') . '</a></li>
                            </ul>
                        </li>' : '') . ($BLOCKS['global_staff_menu_on'] ? $staff_links : (has_access($CURUSER['class'], UC_STAFF, 'coder') ? "
                        <li>
                            <a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>
                        </li>' : '')) . "
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
                            <li>
                                <a href='{$site_config['paths']['baseurl']}/" . htmlsafechars($value['file_name']) . "'>" . _($value['page_name']) . '</a>
                            </li>';

    return $link;
}

/**
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function staff_panel()
{
    global $BLOCKS, $CURUSER, $container, $site_config;

    $cache = $container->get(Cache::class);
    $panel = '';
    $panels = [];
    if ($BLOCKS['global_staff_menu_on'] && has_access($CURUSER['class'], UC_STAFF, 'coder')) {
        $user_class = $CURUSER['class'] >= UC_STAFF ? $CURUSER['class'] : UC_MAX;
        $staff_panel = $cache->get('staff_panels_' . $user_class);
        if ($staff_panel === false || is_null($staff_panel)) {
            $fluent = $container->get(Database::class);
            $staff_panel = $fluent->from('staffpanel')
                                  ->where('navbar = 1')
                                  ->where('av_class <= ?', $user_class)
                                  ->orderBy('page_name')
                                  ->fetchAll();

            $cache->set('staff_panels_' . $user_class, $staff_panel, 0);
        }
        $staff_panel[] = [
            'id' => 0,
            'page_name' => _('Staff Messages'),
            'file_name' => 'staffbox.php',
            'description' => _('View Staff Messages'),
            'type' => 'user',
            'av_class' => UC_STAFF,
            'added_by' => 1,
            'added' => 1546167296,
            'navbar' => 1,
        ];
        if (in_array($CURUSER['id'], $site_config['adminer']['allowed_ids'])) {
            $staff_panel[] = [
                'page_name' => _('Adminer'),
                'file_name' => 'view_sql.php',
                'type' => 'other',
                'av_class' => UC_MAX,
                'navbar' => 1,
            ];
            $staff_panel = array_msort($staff_panel, ['page_name' => SORT_ASC]);
        }
        if ($staff_panel) {
            foreach ($staff_panel as $key => $value) {
                if ($value['av_class'] <= $user_class && $value['type'] === 'user') {
                    $panels['0' . _('Users')][] = make_link($value);
                } elseif ($value['av_class'] <= $user_class && $value['type'] === 'settings') {
                    $panels['1' . _('Settings')][] = make_link($value);
                } elseif ($value['av_class'] <= $user_class && $value['type'] === 'stats') {
                    $panels['2' . _('Stats')][] = make_link($value);
                } elseif ($value['av_class'] <= $user_class && $value['type'] === 'other') {
                    $panels['3' . _('Other')][] = make_link($value);
                }
            }
        }
        ksort($panels);
        foreach ($panels as $key => $value) {
            $panel .= "
                <li>
                    <a id='staff_" . strtolower(substr($key, 1)) . "' href='#' class='has-text-weight-bold'>[" . substr($key, 1) . "]</a>
                    <ul class='ddFade ddFadeFast'>" . make_link([
                'file_name' => 'staffpanel.php',
                'page_name' => _('Staff Panel'),
            ]) . implode('', $value) . '
                    </ul>
                </li>';
        }
    }

    return $panel;
}
