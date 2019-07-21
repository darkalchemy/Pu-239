<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;
use Pu239\User;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 * @param string|null $title
 * @param array       $stdhead
 * @param string      $class
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function stdhead(?string $title = null, array $stdhead = [], string $class = 'page-wrapper')
{
    global $container, $site_config, $CURUSER;

    $session = $container->get(Session::class);
    require_once INCL_DIR . 'function_bbcode.php';
    require_once INCL_DIR . 'function_breadcrumbs.php';
    require_once INCL_DIR . 'function_html.php';
    require_once 'navbar.php';
    if (!$site_config['site']['online']) {
        if (!empty($CURUSER) && $CURUSER['class'] < UC_STAFF) {
            die('Site is down for maintenance, please check back again later... thanks<br>');
        } elseif (!empty($CURUSER) && $CURUSER['class'] >= UC_STAFF) {
            $session->set('is-danger', 'Site is currently offline, only staff can access site.');
        }
    }
    //if (!empty($CURUSER) && $CURUSER['status'] > 0) {
    //    $user = $container->get(User::class);
    //    $user->logout($CURUSER['id'], true);
    //}
    if (empty($title)) {
        $title = $site_config['site']['name'];
    } else {
        $title = $site_config['site']['name'] . ' :: ' . htmlsafechars($title);
    }
    $css_incl = '';
    $tmp = [
        'css' => [
            get_file_name('cookieconsent_css'),
        ],
    ];
    $stdhead = array_merge_recursive($stdhead, $tmp);

    if (!empty($stdhead['css'])) {
        foreach ($stdhead['css'] as $CSS) {
            $css_incl .= "<link rel='stylesheet' href='{$CSS}'>";
        }
    }
    $htmlout = doc_head() . "
    <meta property='og:title' content='{$title}'>
    <title>{$title}</title>
    <link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='{$site_config['paths']['baseurl']}/rss.php?torrent_pass={$CURUSER['torrent_pass']}'>
    <link rel='apple-touch-icon' sizes='180x180' href='{$site_config['paths']['baseurl']}/apple-touch-icon.png'>
    <link rel='icon' type='image/png' sizes='32x32' href='{$site_config['paths']['baseurl']}/favicon-32x32.png'>
    <link rel='icon' type='image/png' sizes='16x16' href='{$site_config['paths']['baseurl']}/favicon-16x16.png'>
    <link rel='manifest' href='{$site_config['paths']['baseurl']}/manifest.json'>
    <link rel='mask-icon' href='{$site_config['paths']['baseurl']}/safari-pinned-tab.svg' color='#5bbad5'>
    <meta name='theme-color' content='#fff'>
    <link rel='stylesheet' href='" . get_file_name('vendor_css') . "'>
    <link rel='stylesheet' href='" . get_file_name('css') . "'>
    {$css_incl}
    <link rel='stylesheet' href='" . get_file_name('main_css') . "'>";
    $htmlout .= "
</head>
<body class='background-16 skin-2'>
    <div id='body-overlay'>
    <div id='container'></div>
        <div class='$class'>";
    global $BLOCKS;

    if ($CURUSER) {
        $htmlout .= navbar();
        $htmlout .= "
        <div id='inner-page-wrapper'>";
        if (empty($site_config['banners']['video'])) {
            if (empty($site_config['banners']['image'])) {
                $banner = "
                    <div class='left50'>
                        <h1>{$site_config['tagline']['banner']}</h1>
                        <p class='description text-shadow left20'><i>{$site_config['tagline']['tagline']}</i></p>
                    </div>";
            } else {
                $banner = "
                    <img src='" . $site_config['paths']['images_baseurl'] . $site_config['banners']['image'][array_rand($site_config['banners']['image'])] . "' class='w-100'>";
            }
            $htmlout .= "
            <div id='logo' class='logo columns level is-marginless bg-04'>
                <div class='column is-paddingless'>
                    $banner
                </div>
            </div>";
        } else {
            $banner = $site_config['banners']['video'][array_rand($site_config['banners']['video'])];
            $htmlout .= "
            <div id='base_contents_video'>
                <div class='base_header_video'>
                    <video class='object-fit-video' loop muted autoplay playsinline poster='{$site_config['paths']['images_baseurl']}banner.png'>
                        <source src='{$site_config['paths']['images_baseurl']}{$banner}.mp4' type='video/mp4'>
                        <source src='{$site_config['paths']['images_baseurl']}{$banner}.webm' type='video/webm'>
                        <img src='{$site_config['paths']['images_baseurl']}banner.png' title='Your browser does not support the <video> tag' alt='Logo'>
                    </video>
                </div>
            </div>";
        }

        $htmlout .= platform_menu();
        $htmlout .= "
            <div id='base_globelmessage'>
                <div class='top5 bottom5'>
                    <ul class='level-center tags'>";

        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_REPORTS && $BLOCKS['global_staff_report_on']) {
            require_once BLOCK_DIR . 'global/report.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_UPLOADAPP && $BLOCKS['global_staff_uploadapp_on']) {
            require_once BLOCK_DIR . 'global/uploadapp.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR && $BLOCKS['global_happyhour_on']) {
            require_once BLOCK_DIR . 'global/happyhour.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_STAFF_MESSAGE && $BLOCKS['global_staff_warn_on']) {
            require_once BLOCK_DIR . 'global/staffmessages.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_NEWPM && $BLOCKS['global_message_on']) {
            require_once BLOCK_DIR . 'global/message.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_DEMOTION && $BLOCKS['global_demotion_on']) {
            require_once BLOCK_DIR . 'global/demotion.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH && $BLOCKS['global_freeleech_on']) {
            require_once BLOCK_DIR . 'global/freeleech.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR && $BLOCKS['global_crazyhour_on']) {
            require_once BLOCK_DIR . 'global/crazyhour.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_BUG_MESSAGE && $BLOCKS['global_bug_message_on']) {
            require_once BLOCK_DIR . 'global/bugmessages.php';
        }
        if ($CURUSER['blocks']['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION && $BLOCKS['global_freeleech_contribution_on']) {
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

    if ($CURUSER) {
        $htmlout .= breadcrumbs();
    }
    if ($BLOCKS['global_flash_messages_on']) {
        foreach ($site_config['site']['notifications'] as $notif) {
            $messages = $session->get($notif);
            if (!empty($messages)) {
                foreach ($messages as $message) {
                    $show[] = $message;
                    $message = !is_array($message) ? format_comment($message) : "<a href='{$message['link']}'>" . format_comment($message['message']) . '</a>';
                    $htmlout .= "
                    <div class='notification $notif has-text-centered size_6'>
                        <button class='delete'>&nbsp;</button>$message
                    </div>";
                }
            }
            $session->unset($notif);
        }
    }

    return $htmlout;
}

/**
 * @param array $stdfoot
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function stdfoot(array $stdfoot = [])
{
    require_once INCL_DIR . 'function_bbcode.php';
    global $site_config, $starttime, $querytime, $lang, $container, $CURUSER;

    $cache = $container->get(Cache::class);
    $session_id = session_id();
    $query_stats = $cache->get('query_stats_' . $session_id);
    $use_12_hour = !empty($CURUSER['use_12_hour']) ? $CURUSER['use_12_hour'] : $site_config['site']['use_12_hour'];
    $header = $uptime = $htmlfoot = $now = '';
    $debug = $site_config['db']['debug'] && !empty($CURUSER['id']) && in_array($CURUSER['id'], $site_config['is_staff']) ? true : false;
    $queries = !empty($query_stats) ? count($query_stats) : 0;
    $seconds = microtime(true) - $starttime;
    $r_seconds = round($seconds, 5);
    if ($CURUSER['class'] >= UC_STAFF && $debug) {
        $querytime = $querytime === null ? 0 : $querytime;
        if ($site_config['cache']['driver'] === 'apcu' && extension_loaded('apcu')) {
            $stats = apcu_cache_info();
            if ($stats) {
                $stats['Hits'] = number_format($stats['num_hits'] / ($stats['num_hits'] + $stats['num_misses']) * 100, 3);
                $header = "{$lang['gl_stdfoot_querys_apcu1']}{$stats['Hits']}{$lang['gl_stdfoot_querys_mstat4']}" . number_format((100 - $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format($stats['num_entries']) . "{$lang['gl_stdfoot_querys_mstat6']}" . mksize($stats['mem_size']);
            }
        } elseif ($site_config['cache']['driver'] === 'redis' && extension_loaded('redis')) {
            $client = $container->get(Redis::class);
            $stats = $client->info();
            if (!empty($stats)) {
                $stats['Hits'] = number_format($stats['keyspace_hits'] / ($stats['keyspace_hits'] + $stats['keyspace_misses']) * 100, 3);
                $db = 'db' . $site_config['redis']['database'];
                preg_match('/keys=(\d+)/', $stats[$db], $keys);
                $header = "{$lang['gl_stdfoot_querys_redis1']}{$stats['Hits']}{$lang['gl_stdfoot_querys_mstat4']}" . number_format((100 - (float) $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format((float) $keys[1]) . "{$lang['gl_stdfoot_querys_mstat6']}{$stats['used_memory_human']}";
            }
        } elseif ($site_config['cache']['driver'] === 'memcached' && extension_loaded('memcached')) {
            $client = $container->get(Memcached::class);
            $stats = $client->getStats();
            if (!$site_config['memcached']['use_socket']) {
                $stats = !empty($stats["{$site_config['memcached']['host']}:{$site_config['memcached']['port']}"]) ? $stats["{$site_config['memcached']['host']}:{$site_config['memcached']['port']}"] : null;
            } else {
                $stats = !empty($stats["{$site_config['memcached']['socket']}:0"]) ? $stats["{$site_config['memcached']['socket']}:0"] : (!empty($stats["{$site_config['memcached']['socket']}:{$site_config['memcached']['port']}"]) ? $stats["{$site_config['memcached']['socket']}:{$site_config['memcached']['port']}"] : null);
            }
            if ($stats && !empty($stats['get_hits']) && !empty($stats['cmd_get'])) {
                $stats['Hits'] = number_format(($stats['get_hits'] / $stats['cmd_get']) * 100, 3);
                $header = $lang['gl_stdfoot_querys_mstat3'] . $stats['Hits'] . $lang['gl_stdfoot_querys_mstat4'] . number_format((100 - $stats['Hits']), 3) . $lang['gl_stdfoot_querys_mstat5'] . number_format($stats['curr_items']) . $lang['gl_stdfoot_querys_mstat6'] . mksize($stats['bytes']);
            }
        } elseif ($site_config['cache']['driver'] === 'file') {
            $files_info = GetDirectorySize($site_config['files']['path'], true, true);
            $header = "{$lang['gl_stdfoot_querys_fly1']}{$site_config['files']['path']} Count: {$files_info[1]} {$lang['gl_stdfoot_querys_fly2']} {$files_info[0]}";
        } elseif ($site_config['cache']['driver'] === 'memory') {
            $header = $lang['gl_stdfoot_querys_memory'];
        } elseif ($site_config['cache']['driver'] === 'couchbase') {
            $header = $lang['gl_stdfoot_querys_cbase'];
        }
        if (!empty($query_stats)) {
            $htmlfoot .= "
                <div class='portlet top20'>
                    <a id='queries-hash'></a>
                    <div id='queries' class='box'>";
            $heading = "
                            <tr>
                                <th class='w-10'>{$lang['gl_stdfoot_id']}</th>
                                <th class='w-10'>{$lang['gl_stdfoot_qt']}</th>
                                <th class='min-350'>{$lang['gl_stdfoot_qs']}</th>
                                <th class='min-150'>Parameters</th>
                            </tr>";
            $body = '';
            foreach ($query_stats as $key => $value) {
                $params = implode("\n", $value['params']);
                $querytime += $value['seconds'];
                $body .= '
                            <tr>
                                <td>' . ($key + 1) . '</td>
                                <td>' . ($value['seconds'] > 0.01 ? "<span class='thas-text-danger tooltipper' title='{$lang['gl_stdfoot_ysoq']}'>" . $value['seconds'] . '</span>' : "<span class='is-success tooltipper' title='{$lang['gl_stdfoot_qg']}'>" . $value['seconds'] . '</span>') . "</td>
                                <td>
                                    <div class='text-justify'>" . format_comment($value['query'], false) . '</div>
                                </td>
                                <td>' . format_comment($params) . '</td>
                            </tr>';
            }
            $cache->delete('query_stats_' . $session_id);
            $htmlfoot .= main_table($body, $heading) . '
                    </div>
                </div>';
        }
    }
    $uptime = $cache->get('uptime_');
    if ($uptime === false || is_null($uptime)) {
        $uptime = explode('up', `uptime`);
        $cache->set('uptime_', $uptime, 10);
    }
    if ($use_12_hour) {
        $uptime = $lang['gl_stdfoot_uptime'] . ' ' . str_replace('  ', ' ', $uptime[1]);
        $now = time24to12(TIME_NOW, true);
    } else {
        $uptime = $lang['gl_stdfoot_uptime'] . ' ' . str_replace('  ', ' ', $uptime[1]);
        $now = get_date((int) TIME_NOW, 'WITH_SEC', 1, 0);
    }
    $htmlfoot .= '
                </div>
            </div>';

    if ($CURUSER) {
        $sql_version = $lang['gl_database'];
        $php_version = '';
        if ($CURUSER['class'] >= UC_STAFF) {
            $sql_version = $cache->get('sql_version_');
            if ($sql_version === false || is_null($sql_version)) {
                $pdo = $container->get(PDO::class);
                $query = $pdo->query('SELECT VERSION() AS ver');
                $sql_version = $query->fetch(PDO::FETCH_COLUMN);
                if (!preg_match('/MariaDB/i', $sql_version)) {
                    $sql_version = 'MySQL ' . $sql_version;
                }
                $cache->set('sql_version_', $sql_version, 3600);
            }
            $php_version = show_php_version();
        }
        $htmlfoot .= "
            <div class='site-debug bg-05 round10 top20 bottom20'>
                <div class='level bordered bg-04'>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>{$lang['gl_stdfoot_querys_page']} " . mksize(memory_get_peak_usage()) . " in $r_seconds {$lang['gl_stdfoot_querys_seconds']}</p>
                        <p class='is-marginless'>{$sql_version} {$lang['gl_stdfoot_querys_server']} $queries {$lang['gl_stdfoot_querys_time']}" . plural($queries) . '</p>
                        ' . ($debug ? "<p class='is-marginless'>$header</p><p class='is-marginless'>$uptime</p>" : '') . "
                    </div>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>{$lang['gl_server_time']} {$now}</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_powered']}{$site_config['sourcecode']['name']}</p>
                        <p class='is-marginless'>{$lang['gl_stdfoot_using']}{$lang['gl_stdfoot_using1']} {$php_version}</p>
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
    if ($CURUSER && ($site_config['site']['backgrounds_on_all_pages'] || $details)) {
        $background = get_body_image($details);
        if (!empty($background)) {
            $bg_image = "var body_image = '" . url_proxy($background, true) . "'";
        }
    }
    $height = !empty($CURUSER['ajaxchat_height']) ? $CURUSER['ajaxchat_height'] . 'px' : '600px';
    $use_12_hour = $use_12_hour ? 'yes' : 'no';
    $htmlfoot .= "
    </div>
    <a href='#' class='back-to-top'>
        <i class='icon-angle-circled-up responsive-icon'></i>
    </a>
    <script>
        $bg_image;
        var is_12_hour = '{$use_12_hour}';
        var chat_height = '$height';
    </script>";

    $htmlfoot .= "
    <script src='" . get_file_name('jquery_js') . "'></script>
    <script src='" . get_file_name('lightbox_js') . "'></script>
    <script src='" . get_file_name('tooltipster_js') . "'></script>
    <script src='" . get_file_name('cookieconsent_js') . "'></script>
    <script src='" . get_file_name('vendor_js') . "'></script>
    <script src='" . get_file_name('main_js') . "'></script>";

    if (!empty($stdfoot['js'])) {
        foreach ($stdfoot['js'] as $JS) {
            $htmlfoot .= "
    <script src='{$JS}'></script>";
        }
    }

    $font_size = !empty($CURUSER['font_size']) ? $CURUSER['font_size'] : 85;
    $htmlfoot .= "
    <script>
        document.body.style.fontSize = '{$font_size}%';
    </script>
    </div>
</body>
</html>";

    return $htmlfoot;
}

/**
 * @param      $heading
 * @param      $text
 * @param null $outer_class
 * @param null $inner_class
 *
 * @return string
 */
function stdmsg($heading, $text, $outer_class = null, $inner_class = null)
{
    require_once INCL_DIR . 'function_html.php';

    $htmlout = '';
    if ($heading) {
        $htmlout .= "
                <h2>$heading</h2>";
    }
    $htmlout .= "
                <p>$text</p>";

    $htmlout = "<div class='padding20'>$htmlout</div>";

    return main_div($htmlout, $outer_class, $inner_class);
}

/**
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function StatusBar()
{
    global $CURUSER;

    if (!$CURUSER) {
        return '';
    }
    $StatusBar = $clock = '';
    $color = get_user_class_name((int) $CURUSER['class'], true);
    $StatusBar .= "
                    <div id='base_usermenu' class='right20 level-item'>
                        <div class='tooltipper-ajax'>" . format_username((int) $CURUSER['id'], true, false) . "</div>
                        <div id='clock' class='left20 {$color} tooltipper' onclick='hide_by_id()' title='Click to show the background image'>{$clock}</div>
                    </div>";

    return $StatusBar;
}

/**
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return string
 */
function platform_menu()
{
    global $container, $CURUSER, $site_config, $lang;

    $cache = $container->get(Cache::class);

    $templates = $cache->get('templates_' . $CURUSER['class']);
    if ($templates === false || is_null($templates)) {
        $fluent = $container->get(Database::class);
        $templates = $fluent->from('stylesheets')
                            ->orderBy('id')
                            ->where('min_class_to_view <= ?', $CURUSER['class'])
                            ->fetchAll();

        $cache->set('templates_' . $CURUSER['class'], $templates, 0);
    }

    $styles = '';
    if (!empty($templates) && count($templates) > 1) {
        $color = get_user_class_name((int) $CURUSER['class'], true);
        $styles .= "
            <span class='dt-tooltipper-links' data-tooltip-content='#styles_tooltip'>
                <span class='{$color} right10'>themes<i class='icon-down-open size_2'></i></span>
            </span>
            <div class='tooltip_templates'>
                <div id='styles_tooltip' class='has-text-left margin10'>
                    <ul>";

        foreach ($templates as $ar) {
            if ($ar['id'] === $CURUSER['stylesheet']) {
                $styles .= "
                        <li class='margin10'>
                            <span class='has-text-primary'>{$ar['name']}</span>
                        </li>";
            } else {
                $styles .= "
                        <li class='margin10'>
                            <a href='{$site_config['paths']['baseurl']}/take_theme.php?id={$ar['id']}'>{$ar['name']}</a>
                        </li>";
            }
        }
        $styles .= '
                    </ul>
                </div>
            </div>';
    }

    $menu = "
        <div id='platform-menu' class='platform-menu'>
            <div class='platform-wrapper'>
                <div class='columns is-marginless'>
                    <div class='column is-paddingless middle'>
                        <ul class='level-left size_3'>" . (!PRODUCTION ? "
                            <li class='left10 has-text-primary is-primary'>Pu-239 v{$site_config['sourcecode']['version']}</li>" : '') . "
                        </ul>
                    </div>
                    <div class='column is-paddingless middle searchbar'>
                        <ul class='level-center'>
                            <li>
                                <form action='{$site_config['paths']['baseurl']}/browse.php'>
                                    <div class='search round5 middle bg-light'>
                                        <i class='icon-search has-text-black' aria-hidden='true'></i>
                                        <input type='text' name='sn' placeholder='{$lang['gl_search']}' class='bg-none has-text-black'>
                                        <button type='submit' class='button is-small round5'>{$lang['gl_go']}</button>
                                    </div>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div class='column is-paddingless middle'>
                        <div class='level-right size_3'>" . StatusBar() . $styles . '</div>
                    </div>
                </div>
            </div>
        </div>';

    return $menu;
}
