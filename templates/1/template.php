<?php

/**
 * @param string $title
 * @param null   $stdhead
 *
 * @return string
 *
 * @throws Exception
 */
function stdhead($title = '', $stdhead = null)
{
    require_once INCL_DIR . 'bbcode_functions.php';
    require_once INCL_DIR . 'function_breadcrumbs.php';
    require_once 'navbar.php';
    global $CURUSER, $site_config, $BLOCKS, $session;

    if (!$site_config['site_online']) {
        if (!empty($CURUSER) && $CURUSER['class'] < UC_STAFF) {
            die('Site is down for maintenance, please check back again later... thanks<br>');
        } elseif (!empty($CURUSER) && $CURUSER['class'] >= UC_STAFF) {
            $session->set('is-danger', 'Site is currently offline, only staff can access site.');
        }
    }
    if (!empty($CURUSER) && $CURUSER['enabled'] !== 'yes') {
        $session->destroy();
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
    <!--[if lt IE 9]><script language='javascript' type='text/javascript' src='//html5shim.googlecode.com/svn/trunk/html5.js'></script><![endif]-->
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
    {$css_incl}";

    if ($CURUSER) {
        $htmlout .= "
    <style>#mlike{cursor:pointer;}</style>
    <script>
        function resizeIframe(obj) {
            obj.style.height = 0;
            obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
        }
    </script>";
    }

    $captcha = [
        'login.php',
        'takelogin.php',
        'signup.php',
        'takesignup.php',
        'invite_signup.php',
        'take_invite_signup.php',
        'resetpw.php',
        'recover.php',
    ];
    if (in_array(basename($_SERVER['PHP_SELF']), $captcha) && !empty($_ENV['RECAPTCHA_SITE_KEY'])) {
        $htmlout .= "
        <script src='//www.google.com/recaptcha/api.js'></script>";
    }
    $htmlout .= "
</head>
<body class='{$body_class}'>
    <div id='body-overlay'>
    <script>
        var theme = localStorage.getItem('theme');
        if (theme) {
            document.body.className = theme;
        }
    </script>
    <div id='container' class='container'>
        <div class='page-wrapper'>";
    if ($CURUSER) {
        $htmlout .= navbar();
        if (empty($site_config['video_banners'])) {
            if (empty($site_config['banners'])) {
                $banner = "
                    <div class='left50'>
                        <h1>" . $site_config['variant'] . " Code</h1>
                        <p class='description left20'><i>Making progress, 1 day at a time...</i></p>
                    </div>";
            } else {
                $banner = "
                    <img src='" . $site_config['pic_baseurl'] . '/' . $site_config['banners'][array_rand($site_config['banners'])] . "' class='w-100' />";
            }
            $htmlout .= "
            <div id='logo' class='logo columns level is-marginless'>
                <div class='column is-paddingless'>
                    $banner
                </div>
            </div>";
        } else {
            $banner = $site_config['video_banners'][array_rand($site_config['video_banners'])];
            $htmlout .= "
            <div id='base_contents_video'>
                <div class='base_header_video'>
                    <video class='object-fit-video' loop muted autoplay playsinline poster='{$site_config['pic_baseurl']}banner.png'>
                        <source src='{$site_config['pic_baseurl']}{$banner}.mp4' type='video/mp4'>
                        <source src='{$site_config['pic_baseurl']}{$banner}.webm' type='video/webm'>
                        <img src='{$site_config['pic_baseurl']}banner.png' title='Your browser does not support the <video> tag' alt='Logo' />
                    </video>
                </div>
            </div>";
        }

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
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR && $BLOCKS['global_happyhour_on'] && !XBT_TRACKER) {
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
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH && $BLOCKS['global_freeleech_on'] && !XBT_TRACKER) {
            require_once BLOCK_DIR . 'global/freeleech.php';
        }
        if (curuser::$blocks['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR && $BLOCKS['global_crazyhour_on'] && !XBT_TRACKER) {
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

    $index_array = [
        '/',
        '/index.php',
        '/login.php',
    ];

    if ($CURUSER && !in_array($_SERVER['REQUEST_URI'], $index_array)) {
        $htmlout .= "
                <div class='container is-fluid portlet padding20 bg-00 round10'>
                    <nav class='breadcrumb' aria-label='breadcrumbs'>
                        <ul>
                            " . breadcrumbs() . '
                        </ul>
                    </nav>
                </div>';
    }

    foreach ($site_config['notifications'] as $notif) {
        if (($messages = $session->get($notif)) != false) {
            foreach ($messages as $message) {
                if ($CURUSER && $BLOCKS['global_flash_messages_on']) {
                    $message = !is_array($message) ? format_comment($message) : "<a href='{$message['link']}'>" . format_comment($message['message']) . '</a>';
                    $htmlout .= "
                <div class='notification $notif has-text-centered size_6'>
                    <button class='delete'></button>$message
                </div>";
                }
                $session->unset($notif);
            }
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
    global $CURUSER, $site_config, $starttime, $query_stat, $querytime, $lang, $cache, $session;

    $use_12_hour = !empty($CURUSER['12_hour']) ? $CURUSER['12_hour'] === 'yes' ? 1 : 0 : $site_config['12_hour'];
    $header = $uptime = $htmlfoot = '';
    $debug = SQL_DEBUG && !empty($CURUSER['id']) && in_array($CURUSER['id'], $site_config['is_staff']['allowed']) ? 1 : 0;
    $queries = !empty($query_stat) ? count($query_stat) : 0;
    $seconds = microtime(true) - $starttime;
    $r_seconds = round($seconds, 5);

    if ($CURUSER['class'] >= UC_STAFF && $debug) {
        $querytime = $querytime === null ? 0 : $querytime;

        if ($_ENV['CACHE_DRIVER'] === 'apcu' && extension_loaded('apcu')) {
            $stats = apcu_cache_info();
            if ($stats) {
                $stats['Hits'] = number_format($stats['num_hits'] / ($stats['num_hits'] + $stats['num_misses']) * 100, 3);
                $header = "{$lang['gl_stdfoot_querys_apcu1']}{$stats['Hits']}{$lang['gl_stdfoot_querys_mstat4']}" . number_format((100 - $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format($stats['num_entries']) . "{$lang['gl_stdfoot_querys_mstat6']}" . human_filesize($stats['mem_size']);
            }
        } elseif ($_ENV['CACHE_DRIVER'] === 'redis' && extension_loaded('redis')) {
            $client = new \Redis();
            if (!SOCKET) {
                $client->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);
            } else {
                $client->connect($_ENV['REDIS_SOCKET']);
            }
            $client->select($_ENV['REDIS_DATABASE']);
            $stats = $client->info();
            if ($stats) {
                $stats['Hits'] = number_format($stats['keyspace_hits'] / ($stats['keyspace_hits'] + $stats['keyspace_misses']) * 100, 3);
                preg_match('/keys=(\d+)/', $stats['db' . $_ENV['REDIS_DATABASE']], $keys);
                $header = "{$lang['gl_stdfoot_querys_redis1']}{$stats['Hits']}{$lang['gl_stdfoot_querys_mstat4']}" . number_format((100 - $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format($keys[1]) . "{$lang['gl_stdfoot_querys_mstat6']}{$stats['used_memory_human']}";
            }
        } elseif ($_ENV['CACHE_DRIVER'] === 'memcached' && extension_loaded('memcached')) {
            $client = new \Memcached();
            if (!count($client->getServerList())) {
                $client->addServer($_ENV['MEMCACHED_HOST'], $_ENV['MEMCACHED_PORT']);
            }
            $stats = $client->getStats();
            $stats = !empty($stats["{$_ENV['MEMCACHED_HOST']}:{$_ENV['MEMCACHED_PORT']}"]) ? $stats["{$_ENV['MEMCACHED_HOST']}:{$_ENV['MEMCACHED_PORT']}"] : null;
            if ($stats && !empty($stats['get_hits']) && !empty($stats['cmd_get'])) {
                $stats['Hits'] = number_format(($stats['get_hits'] / $stats['cmd_get']) * 100, 3);
                $header = $lang['gl_stdfoot_querys_mstat3'] . $stats['Hits'] . $lang['gl_stdfoot_querys_mstat4'] . number_format((100 - $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format($stats['curr_items']) . "{$lang['gl_stdfoot_querys_mstat6']}" . human_filesize($stats['bytes']);
            }
        } elseif ($_ENV['CACHE_DRIVER'] === 'files') {
            $header = "{$lang['gl_stdfoot_querys_fly1']}{$_ENV['FILES_PATH']} {$lang['gl_stdfoot_querys_fly2']}" . GetDirectorySize($_ENV['FILES_PATH']);
        } elseif ($_ENV['CACHE_DRIVER'] === 'couchbase') {
            $header = $lang['gl_stdfoot_querys_cbase'];
        }

        if (!empty($query_stat)) {
            $htmlfoot .= "
                <div class='container is-fluid portlet'>
                    <a id='queries-hash'></a>
                    <fieldset id='queries' class='header'>
                        <legend class='flipper has-text-primary'><i class='icon-down-open size_3' aria-hidden='true'></i>{$lang['gl_stdfoot_querys']}</legend>
                        <div class='has-text-centered'>
                            <table class='table table-bordered table-striped bottom10'>
                                <thead>
                                    <tr>
                                        <th class='w-10'>{$lang['gl_stdfoot_id']}</th>
                                        <th class='w-10'>{$lang['gl_stdfoot_qt']}</th>
                                        <th>{$lang['gl_stdfoot_qs']}</th>
                                    </tr>
                                </thead>
                                <tbody>";
            foreach ($query_stat as $key => $value) {
                $querytime += $value['seconds'];
                $htmlfoot .= '
                                    <tr>
                                        <td>' . ($key + 1) . '</td>
                                        <td>' . ($value['seconds'] > 0.01 ? "<span class='has-text-red' title='{$lang['gl_stdfoot_ysoq']}'>" . $value['seconds'] . '</span>' : "<span class='has-text-green' title='{$lang['gl_stdfoot_qg']}'>" . $value['seconds'] . '</span>') . "</td>
                                        <td>
                                            <div class='text-justify'>" . format_comment($value['query']) . '</div>
                                        </td>
                                    </tr>';
            }

            $htmlfoot .= '
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                </div>';
        }
        $uptime = $cache->get('uptime');
        if ($uptime === false || is_null($uptime)) {
            $uptime = explode('up', `uptime`);
            $cache->set('uptime', $uptime, 10);
        }
        if ($use_12_hour) {
            $uptime = time24to12(TIME_NOW, true) . "<br>{$lang['gl_stdfoot_uptime']} " . str_replace('  ', ' ', $uptime[1]);
        } else {
            $uptime = get_date(TIME_NOW, 'WITH_SEC', 1, 1) . "<br>{$lang['gl_stdfoot_uptime']} " . str_replace('  ', ' ', $uptime[1]);
        }
    }
    $htmlfoot .= '
                </div>
            </div>';

    if ($CURUSER) {
        $htmlfoot .= "
            <div class='container site-debug bg-05 round10 top20 bottom20'>
                <div class='level bordered bg-04'>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>{$lang['gl_stdfoot_querys_page']} " . mksize(memory_get_peak_usage()) . " in $r_seconds {$lang['gl_stdfoot_querys_seconds']}</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_querys_server']} $queries {$lang['gl_stdfoot_querys_time']}" . plural($queries) . '</p>
                        ' . ($debug ? "<p class='is-marginless'>$header</p><p class='is-marginless'>$uptime</p>" : '') . "
                    </div>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>{$lang['gl_stdfoot_powered']}{$site_config['variant']}</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_using']}{$lang['gl_stdfoot_using1']} " . show_php_version() . "</p>
                    </div>
                </div>
            </div>
            <div id='control_panel'>
                <a href='#' id='control_label'></a>
            </div>
        </div>";
    }
    $details = basename($_SERVER['PHP_SELF']) === 'details.php';
    $bg_image = '';
    if ($CURUSER && ($site_config['backgrounds_on_all_pages'] || $details)) {
        $background = get_body_image($details);
        if (!empty($background['background'])) {
            $bg_image = "var body_image = '" . url_proxy($background['background'], true) . "'";
        }
    }
    $htmlfoot .= "
    </div>
    <a href='#' class='back-to-top'>
        <i class='icon-angle-circled-up' style='font-size:48px'></i>
    </a>
    <script>
        $bg_image
        var is_12_hour = {$use_12_hour};
    </script>";

    $htmlfoot .= "
    <script src='" . get_file_name('js') . "'></script>";

    if (!empty($stdfoot['js'])) {
        foreach ($stdfoot['js'] as $JS) {
            $htmlfoot .= "
    <script src='{$JS}'></script>";
        }
    }

    $htmlfoot .= '
    </div>
</body>
</html>';

    $session->close();

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
    global $CURUSER, $session;
    if (!$CURUSER) {
        return '';
    }
    $StatusBar = $clock = '';
    $StatusBar .= "
                    <div id='base_usermenu' class='tooltipper-ajax right10 level-item' data-csrf='" . $session->get('csrf_token') . "'>
                        <span id='clock' class='has-text-white right10'>{$clock}</span>
                        " . format_username($CURUSER['id'], true, false) . '
                    </div>';

    return $StatusBar;
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
                <ul class='level-right'>" . StatusBar() . '
                </ul>
            </div>
        </div>';

    return $menu;
}
