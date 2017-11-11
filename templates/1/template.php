<?php
/**
 * @param string $title
 * @param bool   $msgalert
 * @param bool   $stdhead
 *
 * @return string
 */
function stdhead($title = '', $msgalert = true, $stdhead = false)
{
    global $CURUSER, $site_config, $lang, $free, $query_stat, $querytime, $mc1, $BLOCKS, $CURBLOCK, $mood;

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
    <link rel='manifest' href='/manifest.json' />
    <link rel='mask-icon' href='/safari-pinned-tab.svg' color='#5bbad5' />
    <meta name='theme-color' content='#ffffff'>
    <link rel='stylesheet' href='" . get_file('css') . "' />
    <link rel='stylesheet' href='//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' integrity='sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN' crossorigin='anonymous' />
    <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Acme|Baloo+Bhaijaan|Encode+Sans+Condensed|Lobster|Nova+Square|Open+Sans|Oswald|PT+Sans+Narrow' />
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
        <div class='spacer'></div>
        <div class='page-wrapper'>";
    $htmlout .= navbar();
    if ($CURUSER) {
        $htmlout .= "
            <div id='logo' class='logo columns level'>
                <div class='column'>
                    <h1>" . $site_config['variant'] . " Code</h1>
                    <p class='description left20'><i>Making progress, 1 day at a time...</i></p>
                </div>
            </div>";
        $htmlout .= platform_menu();
        $htmlout .= "
            <div id='base_globelmessage'>
                <div>
                    <ul class='level-right is-flex is-wrapped top5 bottom5'>";

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
                <div class='padding20 bg-00 round10'>
                    <nav class='breadcrumb' aria-label='breadcrumbs'>
                        <ul>
                            " . breadcrumbs() . "
                        </ul>
                    </nav>
                </div>";
    }

    foreach ($site_config['notifications'] as $notif) {
        if (($message = getSessionVar($notif)) != false) {
            $message = !is_array($message) ? $message : "<a href='{$message['link']}'>{$message['message']}</a>";
            $htmlout .= "
                <div class='notification $notif has-text-centered size_6'>
                    <button class='delete'></button>$message
                </div>";
        }
        unsetSessionVar($notif);
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
    global $CURUSER, $site_config, $start, $query_stat, $queries, $mc1, $querytime, $lang;
    $debug = (SQL_DEBUG && !empty($CURUSER['id']) && in_array($CURUSER['id'], $site_config['is_staff']['allowed']) ? 1 : 0);
    $cachetime = ($mc1->Time / 1000);
    $seconds = microtime(true) - $start;
    $r_seconds = round($seconds, 5);
    $phptime = $seconds - $querytime - $cachetime;
    $percentphp = number_format(($phptime / $seconds) * 100, 2);
    $percentmc = number_format(($cachetime / $seconds) * 100, 2);
    if (($MemStats = $mc1->get_value('mc_hits')) === false) {
        $MemStats = $mc1->getStats();
        $MemStats['Hits'] = (($MemStats['get_hits'] / $MemStats['cmd_get'] < 0.7) ? '' : number_format(($MemStats['get_hits'] / $MemStats['cmd_get']) * 100, 3));
        $mc1->cache_value('mc_hits', $MemStats, 10);
    }
    if ($debug) {
        if (($uptime = $mc1->get_value('uptime')) === false) {
            $uptime = `uptime`;
            $mc1->cache_value('uptime', $uptime, 25);
        }
        preg_match('/load average: (.*)$/i', $uptime, $load);
    }
    $header = '';
    if (!empty($MemStats['Hits']) && !empty($MemStats['curr_items']) && !empty($phptime) && !empty($percentmc) && !empty($cachetime)) {
        $header = '<b>' . $lang['gl_stdfoot_querys_mstat'] . '</b> ' . mksize(memory_get_peak_usage()) . ' ' . $lang['gl_stdfoot_querys_mstat1'] . ' ' . round($phptime, 2) . 's | ' . round($percentmc, 2) . '' . $lang['gl_stdfoot_querys_mstat2'] . '' . number_format($cachetime, 4) . 's ' . $lang['gl_stdfoot_querys_mstat3'] . '' . $MemStats['Hits'] . '' . $lang['gl_stdfoot_querys_mstat4'] . '' . number_format((100 - $MemStats['Hits']), 3) . '' . $lang['gl_stdfoot_querys_mstat5'] . '' . number_format($MemStats['curr_items']);
    }
    $htmlfoot = '';
    $querytime = 0;
    if ($CURUSER && $query_stat && $debug) {
        $htmlfoot .= "
                <div class='container is-fluid portlet'>
                    <a id='queries-hash'></a>
                    <fieldset id='queries' class='header'>
                        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['gl_stdfoot_querys']}</legend>
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
            $querytime += $value['seconds']; // query execution time
            $htmlfoot .= '
                                    <tr>
                                        <td>' . ($key + 1) . "</td>
                                        <td><b>" . ($value['seconds'] > 0.01 ? "<span class='is-danger' title='{$lang['gl_stdfoot_ysoq']}'>" . $value['seconds'] . '</span>' : "<span class='is-success' title='{$lang['gl_stdfoot_qg']}'>" . $value['seconds'] . '</span>') . "</b></td>
                                        <td><div class='text-justify'>" . htmlsafechars($value['query']) . '</div></td>
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
    if ($CURUSER) {
        $htmlfoot .= "
            <div class='container site-debug bg-05 round10 top20 bottom20'>
                <div class='level bordered bg-04'>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>" . $site_config['site_name'] . " {$lang['gl_stdfoot_querys_page']}" . $r_seconds . " {$lang['gl_stdfoot_querys_seconds']}</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_querys_server']}" . $queries . " {$lang['gl_stdfoot_querys_time']} " . ($queries != 1 ? "{$lang['gl_stdfoot_querys_times']}" : '') . '</p>
                        ' . ($debug ? '<p class="is-marginless"><b>' . $header . "</b></p><p class='is-marginless'><b>{$lang['gl_stdfoot_uptime']}</b> " . $uptime . '</p>' : '') . "
                    </div>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>{$lang['gl_stdfoot_powered']}" . $site_config['variant'] . "</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_using']}<b>{$lang['gl_stdfoot_using1']}</b></p>
                        " . ($debug ? "<p class='is-marginless'><a title='{$lang['gl_stdfoot_logview']}' class='tooltipper' rel='external' href='{$site_config['baseurl']}/staffpanel.php?tool=log_viewer'>{$lang['gl_stdfoot_logview']}</a> | <a title='{$lang['gl_stdfoot_sview']}' class='tooltipper' rel='external' href='/staffpanel.php?tool=system_view'>{$lang['gl_stdfoot_sview']}</a> | <a rel='external' title='OPCache' href='/staffpanel.php?tool=op' class='tooltipper'>{$lang['gl_stdfoot_opc']}</a> | <a rel='external' title='Memcache' href='/staffpanel.php?tool=memcache' class='tooltipper'>{$lang['gl_stdfoot_memcache']}</a></p>" : '');
        $htmlfoot .= "
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
        <i class='fa fa-arrow-circle-up' style='font-size:48px'></i>
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
                child.classList.add('fa-angle-down');
                child.classList.remove('fa-angle-up');
            } else if (id && localStorage[id] === 'open') {
                var nextSibling = x[i].nextSibling;
                while (nextSibling && nextSibling.nodeType != 1) {
                    nextSibling = nextSibling.nextSibling;
                }
                nextSibling.style.display = 'block';
                child = x[i].children[0];
                child.classList.add('fa-angle-up');
                child.classList.remove('fa-angle-down');
            }
        }
    </script>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>";

    if (!empty($stdfoot['js'])) {
        foreach ($stdfoot['js'] as $JS) {
            $htmlfoot .= "
    <script src='{$JS}'></script>";
        }
    }

    $htmlfoot .= "
    <script src='" . get_file('js') . "'></script>
</body>
</html>";

    return $htmlfoot;
}

/**
 * @param $heading
 * @param $text
 *
 * @return string
 */
function stdmsg($heading, $text)
{
    $htmlout = '';
    if ($heading) {
        $htmlout .= "
                <h2>$heading</h2>";
    }
    $htmlout .= "
                <span>$text</span>";

    return main_div($htmlout);
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
                    <div id='base_usermenu' class='tooltipper-ajax right10'>
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
    global $site_config, $CURUSER, $lang, $mc1;
    $navbar = $panel = $user_panel = $settings_panel = $stats_panel = $other_panel = '';

    if ($CURUSER['class'] >= UC_STAFF) {
        if (($staff_panel = $mc1->get_value('staff_panels_' . $CURUSER['class'])) === false) {
            $res = sql_query('SELECT * FROM staffpanel
                            WHERE navbar = 1 AND av_class <= ' . sqlesc($CURUSER['class']) . '
                            ORDER BY page_name ASC') or sqlerr(__FILE__, __LINE__);
            while ($arr = mysqli_fetch_assoc($res)) $staff_panel[] = $arr;
            $mc1->cache_value('staff_panels_' . $CURUSER['class'], $staff_panel, 0);
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
                            </li>
                            $other_panel
                        </ul>
                   </li>";
            }
        }
    }

    if ($CURUSER) {
        $salty = salty($CURUSER['username']);
        $navbar .= "
        <header id='navbar' class='container'>
            <div class='contained'>
                <div class='nav_container'>
                    <div id='hamburger'><i class='fa fa-bars has-text-white fa-2x' aria-hidden='true'></i></div>
                    <div id='close'><i class='fa fa-times has-text-white fa-2x' aria-hidden='true'></i></div>
                    <div id='menuWrapper'>
                        <ul class='level'>
                            <li>
                                <a href='{$site_config['baseurl']}' class='is-flex'>
                                    <i class='fa fa-home fa-2x'></i>
                                    <span class='home'>Pu-239</span>
                                </a>
                            </li>
                            <li>
                                <a href='#'>{$lang['gl_torrent']}</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li><a href='{$site_config['baseurl']}/browse.php'>{$lang['gl_torrents']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/requests.php'>{$lang['gl_requests']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/offers.php'>{$lang['gl_offers']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/needseed.php?needed=seeders'><span class='is-danger'>{$lang['gl_nseeds']}</span></a></li>
                                    <li><a href='{$site_config['baseurl']}/torrents-today.php'>{$lang['gl_newtor']}</a></li>
                                    " . ($CURUSER['class'] <= UC_VIP ? "<li><a href='{$site_config['baseurl']}/uploadapp.php'>{$lang['gl_uapp']}</a></li>" : "<li><a href='{$site_config['baseurl']}/upload.php'>{$lang['gl_upload']}</a></li>") . "
                                    <li><a href='{$site_config['baseurl']}/bookmarks.php'>{$lang['gl_bookmarks']}</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href='#'>{$lang['gl_general']}</a>
                                <ul class='ddFade ddFadeSlow'>";
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
                                </ul>
                            </li>
                            <li>
                                <a href='#'>{$lang['gl_forums']}</a>
                                <ul class='ddFade ddFadeSlow'>
                                    <li><a href='http://pu-239.pw:8080'>{$lang['gl_tforums']}</a></li>
                                    <li><a href='{$site_config['baseurl']}/forums.php'>{$lang['gl_forums']}</a></li>
                                </ul>
                            </li>
                            <li>" . ($CURUSER['class'] < UC_STAFF ? "<a href='{$site_config['baseurl']}/bugs.php?action=add'>{$lang['gl_breport']}</a>" : "<a href='{$site_config['baseurl']}/bugs.php?action=bugs'>[Bugs]</a>") . "</li>
                            <li>" . ($CURUSER['class'] < UC_STAFF ? "<a href='{$site_config['baseurl']}/contactstaff.php'>{$lang['gl_cstaff']}</a>" : "<a href='{$site_config['baseurl']}/staffbox.php'>[Messages]</a>") . "</li>
                            $panel
                            <li>
                                <a href='{$site_config['baseurl']}/logout.php?hash_please={$salty}' class='is-flex'>
                                    <i class='fa fa-sign-out fa-2x' aria-hidden='true'></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>";
    }
    return $navbar;
}

/**
 * @return string
 */
function platform_menu()
{
    $menu = "
        <div id='platform-menu' class='container platform-menu'>
            <div class='platform-wrapper level'>
                <ul class='level-left'>
                </ul>
                <ul class='level-right'>" .
        StatusBar() . "
                </ul>
            </div>
        </div>";
    return $menu;
}
