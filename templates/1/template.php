<?php

/**
 * @param string $title
 * @param null   $stdhead
 *
 * @return string
 */
function stdhead($title = '', $stdhead = null)
{
    require_once INCL_DIR . 'bbcode_functions.php';
    global $CURUSER, $site_config, $lang, $free, $querytime, $cache, $BLOCKS, $CURBLOCK, $mood;

    unsetSessionVar('Channel');
    if (!$site_config['site_online']) {
        die('Site is down for maintenance, please check back again later... thanks<br>');
    }
    if (empty($title)) {
        $title = $site_config['site_name'];
    } else {
        $title = $site_config['site_name'] . ' :: ' . htmlsafechars($title);
    }
    $css_incl = '';
    if (!empty($stdhead['css'])) {
        foreach ($stdhead['css'] as $CSS) {
            $css_incl .= "
    <link rel='stylesheet' href='{$CSS}' />";
        }
    }

    $body_class = 'background-16 h-style-9 text-9 skin-2';
    $htmlout = "<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <title>{$title}</title>
    <link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='{$site_config['baseurl']}/rss.php?torrent_pass={$CURUSER['torrent_pass']}' />
    <link rel='apple-touch-icon' sizes='180x180' href='{$site_config['baseurl']}/apple-touch-icon.png' />
    <link rel='icon' type='image/png' sizes='32x32' href='{$site_config['baseurl']}/favicon-32x32.png' />
    <link rel='icon' type='image/png' sizes='16x16' href='{$site_config['baseurl']}/favicon-16x16.png' />
    <link rel='manifest' href='{$site_config['baseurl']}/manifest.json' />
    <link rel='mask-icon' href='{$site_config['baseurl']}/safari-pinned-tab.svg' color='#5bbad5' />
    <meta name='theme-color' content='#fff'>
    <link rel='stylesheet' href='" . get_file_name('css') . "' />
    {$css_incl}
    <style>#mlike{cursor:pointer;}</style>
    <script>
        function resizeIframe(obj) {
            obj.style.height = 0;
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
        }
    </script>
</head>
<body class='{$body_class}'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
    </script>
    <div class='container'>
        <div class='page-wrapper'>";
    if ($CURUSER) {
        $htmlout .= navbar();
        $htmlout .= "
            <div id='logo' class='logo columns level is-marginless'>
                <div class='column'>
                    <h1>" . $site_config['variant'] . " Code</h1>
                    <p class='description left20'><i>Making progress, 1 day at a time...</i></p>
                </div>
            </div>";
        $htmlout .= platform_menu();
        $htmlout .= "
            <div id='base_globelmessage'>
                <div class='top5 bottom5'>
                    <ul class='level-center tags'>";

        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_REPORTS && $BLOCKS['global_staff_report_on']) {
            require_once BLOCK_DIR . 'global/report.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_UPLOADAPP && $BLOCKS['global_staff_uploadapp_on']) {
            require_once BLOCK_DIR . 'global/uploadapp.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR && $BLOCKS['global_happyhour_on'] && XBT_TRACKER == false) {
            require_once BLOCK_DIR . 'global/happyhour.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_STAFF_MESSAGE && $BLOCKS['global_staff_warn_on']) {
            require_once BLOCK_DIR . 'global/staffmessages.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_NEWPM && $BLOCKS['global_message_on']) {
            require_once BLOCK_DIR . 'global/message.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_DEMOTION && $BLOCKS['global_demotion_on']) {
            require_once BLOCK_DIR . 'global/demotion.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH && $BLOCKS['global_freeleech_on'] && XBT_TRACKER == false) {
            require_once BLOCK_DIR . 'global/freeleech.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR && $BLOCKS['global_crazyhour_on'] && XBT_TRACKER == false) {
            require_once BLOCK_DIR . 'global/crazyhour.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_BUG_MESSAGE && $BLOCKS['global_bug_message_on']) {
            require_once BLOCK_DIR . 'global/bugmessages.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION && $BLOCKS['global_freeleech_contribution_on']) {
            require_once BLOCK_DIR . 'global/freeleech_contribution.php';
        }
        require_once BLOCK_DIR . 'global/lottery.php';

        $htmlout .= '
                    </ul>
                </div>
            </div>';
    }

    $htmlout .= "
        <div id='base_content' class='bg-05'>
            <div class='inner-wrapper bg-04'>";

    $index_array = ['/', '/index.php', '/login.php'];
    if ($CURUSER && !in_array($_SERVER['REQUEST_URI'], $index_array)) {
        $htmlout .= "
                <div class='container is-fluid portlet padding20 bg-00 round10'>
                    <nav class='breadcrumb' aria-label='breadcrumbs'>
                        <ul>
                            " . breadcrumbs() . "
                        </ul>
                    </nav>
                </div>";
    }

    foreach ($site_config['notifications'] as $notif) {
        if (($messages = getSessionVar($notif)) != false) {
            foreach ($messages as $message) {
                $message = !is_array($message) ? format_comment($message) : "<a href='{$message['link']}'>" . format_comment($message['message']) . "</a>";
                $htmlout .= "
                <div class='notification $notif has-text-centered size_6'>
                    <button class='delete'></button>$message
                </div>";
            }
            unsetSessionVar($notif);
        }
    }
    return $htmlout;
}

/**
 * @param bool $stdfoot
 *
 * @return string
 */
function stdfoot($stdfoot = false)
{
    require_once INCL_DIR . 'bbcode_functions.php';
    global $CURUSER, $site_config, $start, $query_stat, $cache, $querytime, $lang;

    $header = $uptime = $htmlfoot = '';
    $debug = (SQL_DEBUG && !empty($CURUSER['id']) && in_array($CURUSER['id'], $site_config['is_staff']['allowed']) ? 1 : 0);
    $queries = count($query_stat);
    $cachetime = 0; //($cache->Time / 1000);
    $seconds = microtime(true) - $start;
    $r_seconds = round($seconds, 5);
    $querytime = $querytime === null ? 0 : $querytime;
    $phptime = $seconds - $querytime - $cachetime;
    $percentphp = number_format(($phptime / $seconds) * 100, 2);
    $percentmc = number_format(($cachetime / $seconds) * 100, 2);

    if ($CURUSER && $query_stat && $debug) {
        if (extension_loaded('memcached')) {
            $MemStats = $cache->get('mc_hits');
            if ($MemStats === false || is_null($MemStats)) {
                $MemStats = ''; //$cache->getStats();
                $MemStats['Hits'] = (($MemStats['get_hits'] / $MemStats['cmd_get'] < 0.7) ? '' : number_format(($MemStats['get_hits'] / $MemStats['cmd_get']) * 100, 3));
                $cache->set('mc_hits', $MemStats, 10);
            }
        }
        if (!empty($MemStats['Hits']) && !empty($MemStats['curr_items']) && !empty($phptime) && !empty($percentmc) && !empty($cachetime)) {
            $header = '<b>' . $lang['gl_stdfoot_querys_mstat'] . '</b> ' . mksize(memory_get_peak_usage()) . ' ' . $lang['gl_stdfoot_querys_mstat1'] . ' ' . round($phptime, 2) . 's | ' . round($percentmc, 2) . '' . $lang['gl_stdfoot_querys_mstat2'] . '' . number_format($cachetime, 4) . 's ' . $lang['gl_stdfoot_querys_mstat3'] . '' . $MemStats['Hits'] . '' . $lang['gl_stdfoot_querys_mstat4'] . '' . number_format((100 - $MemStats['Hits']), 3) . '' . $lang['gl_stdfoot_querys_mstat5'] . '' . number_format($MemStats['curr_items']);
        }

        $querytime = 0;

        $htmlfoot .= "
                <div class='container is-fluid portlet'>
                    <a id='queries-hash'></a>
                    <fieldset id='queries' class='header'>
                        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['gl_stdfoot_querys']}</legend>
                        <div class='has-text-centered'>
                            <table class='table table-bordered table-striped bottom10'>
                                <thead>
                                    <tr>
                                        <th>{$lang['gl_stdfoot_id']}</th>
                                        <th>{$lang['gl_stdfoot_qt']}</th>
                                        <th>{$lang['gl_stdfoot_qs']}</th>
                                    </tr>
                                </thead>
                                <tbody>";
        foreach ($query_stat as $key => $value) {
            $querytime += $value['seconds'];
            $htmlfoot .= '
                                    <tr>
                                        <td>' . ($key + 1) . "</td>
                                        <td>" . ($value['seconds'] > 0.01 ? "<span class='is-danger' title='{$lang['gl_stdfoot_ysoq']}'>" . $value['seconds'] . '</span>' : "<span class='is-success' title='{$lang['gl_stdfoot_qg']}'>" . $value['seconds'] . '</span>') . "</td>
                                        <td><div class='text-justify'>" . format_comment($value['query']) . '</div></td>
                                    </tr>';
        }
        $htmlfoot .= '
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                </div>';
    }
    $htmlfoot .= "
                </div>
            </div>";

    if ($CURUSER && $debug) {
        $uptime = $cache->get('uptime');
        if ($uptime === false || is_null($uptime)) {
            $uptime = `uptime`;
            $cache->set('uptime', $uptime, 25);
        }
    }

    if ($CURUSER) {
        $htmlfoot .= "
            <div class='container site-debug bg-05 round10 top20 bottom20'>
                <div class='level bordered bg-04'>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>{$lang['gl_stdfoot_querys_page']} $r_seconds {$lang['gl_stdfoot_querys_seconds']}</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_querys_server']} $queries {$lang['gl_stdfoot_querys_time']}" . plural($queries) . "</p>
                        " . ($debug ? "<p class='is-marginless'>$header</p><p class='is-marginless'>{$lang['gl_stdfoot_uptime']} $uptime</p>" : '') . "
                    </div>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>{$lang['gl_stdfoot_powered']}{$site_config['variant']}</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_using']}{$lang['gl_stdfoot_using1']}</p>
                    </div>
                </div>
            </div>
            <div id='control_panel'>
                <a href='#' id='control_label'></a>
            </div>
        </div>";
    }
    $htmlfoot .= "
    </div>
    <a href='#' class='back-to-top'>
        <i class='icon-angle-circled-up' style='font-size:48px'></i>
    </a>
    <script>
        var cookie_prefix   = '{$site_config['cookie_prefix']}';
        var cookie_path     = '{$site_config['cookie_path']}';
        var cookie_lifetime = '{$site_config['cookie_lifetime']}';
        var cookie_domain   = '{$site_config['cookie_domain']}';
        var cookie_secure   = '{$site_config['sessionCookieSecure']}';
        var csrf_token      = '" . getSessionVar('csrf_token') . "';
        var x = document.getElementsByClassName('flipper has-text-primary');
        var i;
        for (i = 0; i < x.length; i++) {
            var id = x[i].parentNode.id;
            if (id && localStorage[id] === 'closed') {
                var el = document.getElementById(id);
                el.classList.add('no-margin');
                el.classList.add('no-padding');
                var nextSibling = x[i].nextSibling;
                while (nextSibling && nextSibling.nodeType != 1) {
                    nextSibling = nextSibling.nextSibling;
                }
                nextSibling.style.display = 'none';
                child = x[i].children[0];
                child.classList.add('icon-down-open');
                child.classList.remove('icon-up-open');
            } else if (id && localStorage[id] === 'open') {
                var nextSibling = x[i].nextSibling;
                while (nextSibling && nextSibling.nodeType != 1) {
                    nextSibling = nextSibling.nextSibling;
                }
                nextSibling.style.display = 'block';
                child = x[i].children[0];
                child.classList.add('icon-up-open');
                child.classList.remove('icon-down-open');
            }
        }
    </script>";

    $htmlfoot .= "
    <script src='" . get_file_name('js') . "'></script>";

    if (!empty($stdfoot['js'])) {
        foreach ($stdfoot['js'] as $JS) {
            $htmlfoot .= "
    <script src='{$JS}'></script>";
        }
    }

    $htmlfoot .= "
</body>
</html>";

    return $htmlfoot;
}

/**
 * @param      $heading
 * @param      $text
 * @param null $class
 *
 * @return string
 */
function stdmsg($heading, $text, $class = null)
{
    require_once INCL_DIR . 'html_functions.php';

    $htmlout = '';
    if ($heading) {
        $htmlout .= "
                <h2>$heading</h2>";
    }
    $htmlout .= "
                <p>$text</p>";

    return main_div($htmlout, "$class bottom20");
}

/**
 * @return string
 */
function StatusBar()
{
    global $CURUSER;
    if (!$CURUSER) {
        return '';
    }
    $StatusBar = $clock = '';
    $StatusBar .= "
                    <div id='base_usermenu' class='tooltipper-ajax right10 level-item'>
                        <span id='clock' class='has-text-white right10'>{$clock}</span>
                        " . format_username($CURUSER['id'], true, false) . "
                    </div>";

    return $StatusBar;
}

/**
 * @return string
 */
function navbar()
{
    global $site_config, $CURUSER, $lang, $cache, $fluent;
    $navbar = $panel = $user_panel = $settings_panel = $stats_panel = $other_panel = '';

    if ($CURUSER['class'] >= UC_STAFF) {
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
                if ($value['av_class'] <= $CURUSER['class'] && $value['type'] == 'user') {
                    $user_panel .= "
                        <li>
                            <a href='" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . "</a>
                        </li>";
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] == 'settings') {
                    $settings_panel .= "
                        <li>
                            <a href='" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . "</a>
                        </li>";
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] == 'stats') {
                    $stats_panel .= "
                        <li>
                            <a href='" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . "</a>
                        </li>";
                } elseif ($value['av_class'] <= $CURUSER['class'] && $value['type'] == 'other') {
                    $other_panel .= "
                        <li>
                            <a href='" . htmlsafechars($value['file_name']) . "'>" . htmlsafechars($value['page_name']) . "</a>
                        </li>";
                }
            }

            if (!empty($user_panel)) {
                $panel .= "
                    <li>
                        <a href='#'>[Users]</a>
                        <ul class='ddFade ddFadeSlow'>
                            <li>
                                <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
                            </li>
                            $user_panel
                        </ul>
                   </li>";
            }
            if (!empty($settings_panel)) {
                $panel .= "
                   <li>
                        <a href='#'>[Settings]</a>
                        <ul class='ddFade ddFadeSlow'>
                            <li>
                                <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
                            </li>
                            $settings_panel
                        </ul>
                    </li>";
            }
            if (!empty($stats_panel)) {
                $panel .= "
                    <li>
                        <a href='#'>[Stats]</a>
                        <ul class='ddFade ddFadeSlow'>
                            <li>
                                <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
                            </li>
                            $stats_panel
                        </ul>
                   </li>";
            }
            if (!empty($other_panel)) {
                $panel .= "
                    <li>
                        <a href='#'>[Other]</a>
                        <ul class='ddFade ddFadeSlow'>
                            <li>
                                <a href='{$site_config['baseurl']}/staffpanel.php'>Staff Panel</a>
                            </li>";
                if ($CURUSER['class'] === UC_MAX) {
                    $panel .= "
                            <li>
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
        $salty = salty();
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
                            </li>
                            <li>
                                <a href='#'>{$lang['gl_torrent']}</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li><a href='{$site_config['baseurl']}/browse.php'>{$lang['gl_torrents']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/requests.php'>{$lang['gl_requests']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/offers.php'>{$lang['gl_offers']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/needseed.php?needed=seeders'><span class='is-danger'>{$lang['gl_nseeds']}</span></a></li>
                                    <li><a href='{$site_config['baseurl']}/browse.php?today=1'>{$lang['gl_newtor']}</a></li>
                                    " . ($CURUSER['class'] <= UC_VIP ? "<li><a href='{$site_config['baseurl']}/uploadapp.php'>{$lang['gl_uapp']}</a></li>" : "<li><a href='{$site_config['baseurl']}/upload.php'>{$lang['gl_upload']}</a></li>") . "
                                    <li><a href='{$site_config['baseurl']}/bookmarks.php'>{$lang['gl_bookmarks']}</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href='#'>{$lang['gl_general']}</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li><a href='{$site_config['baseurl']}/mybonus.php'>Karma Store</a></li>";
        if ($site_config['bucket_allowed'] === 1) {
            $navbar .= "
                                    <li><a href='{$site_config['baseurl']}/bitbucket.php'>{$lang['gl_bitbucket']}</a></li>";
        }
        $navbar .= "
                                    <li><a href='{$site_config['baseurl']}/getrss.php'>RSS</a></li>
                                    <li><a href='{$site_config['baseurl']}/announcement.php'>{$lang['gl_announcements']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/topten.php'>{$lang['gl_stats']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/faq.php'>{$lang['gl_faq']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/rules.php'>{$lang['gl_rules']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/chat.php'>{$lang['gl_irc']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/staff.php'>{$lang['gl_staff']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/wiki.php'>{$lang['gl_wiki']}</a></li>
                                    <li><a href='#' onclick='radio();'>{$lang['gl_radio']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/rsstfreak.php'>{$lang['gl_tfreak']}</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href='#'>{$lang['gl_games']}</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li><a href='{$site_config['baseurl']}/games.php'>{$lang['gl_games']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/arcade.php'>{$lang['gl_arcade']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/lottery.php'>{$lang['gl_lottery']}</a></li>
                                </ul>
                            </li>
                            <li><a href='{$site_config['baseurl']}/donate.php'>{$lang['gl_donate']}</a></li>
                            <li>
                                <a href='#'>User</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li><a href='{$site_config['baseurl']}/pm_system.php'>{$lang['gl_pms']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/usercp.php?action=default'>{$lang['gl_usercp']}</a></li>
                                    <li><a href='#' onclick='themes();'>{$lang['gl_theme']}</a></li>
                                    <li><a href='#' onclick='language_select();'>{$lang['gl_language_select']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/friends.php'>{$lang['gl_friends']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/users.php'>Search Users</a></li>
                                </ul>
                            </li>
<!--
                            <li>
                                <a href='#'>{$lang['gl_forums']}</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li><a href='http://pu-239.pw'>{$lang['gl_tforums']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/forums.php'>{$lang['gl_forums']}</a></li>
                                </ul>
-->
                            </li>
                            <li>" . ($CURUSER['class'] < UC_STAFF ? "<a href='{$site_config['baseurl']}/bugs.php?action=add'>{$lang['gl_breport']}</a>" : "<a href='{$site_config['baseurl']}/bugs.php?action=bugs'>[Bugs]</a>") . "</li>
                            <li>" . ($CURUSER['class'] < UC_STAFF ? "<a href='{$site_config['baseurl']}/contactstaff.php'>{$lang['gl_cstaff']}</a>" : "<a href='{$site_config['baseurl']}/staffbox.php'>[Messages]</a>") . "</li>
                            $panel
                            <li>
                                <a href='{$site_config['baseurl']}/logout.php?hash_please={$salty}' class='is-flex'>
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
 * @return string
 */
function platform_menu()
{
    global $site_config;

    $menu = "
        <div id='platform-menu' class='container platform-menu'>
            <div class='platform-wrapper level'>
                <ul class='level-left'>" . (!$site_config['in_production'] ? "
                    <li class='left10 has-text-primary'>Pu-239 v{$site_config['version']}</li>" : '') . "
                </ul>
                <ul class='level-right'>" .
        StatusBar() . "
                </ul>
            </div>
        </div>";
    return $menu;
}
