<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;
use Spatie\Image\Exceptions\InvalidManipulation;

/**
 * @param string $title
 * @param array  $stdhead
 * @param string $class
 * @param array  $breadcrumbs
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function stdhead(string $title, array $stdhead, string $class, array $breadcrumbs)
{
    global $container, $site_config;

    $curuser = check_user_status('login');

    $session = $container->get(Session::class);
    require_once INCL_DIR . 'function_bbcode.php';
    require_once INCL_DIR . 'function_breadcrumbs.php';
    require_once INCL_DIR . 'function_html.php';
    require_once 'navbar.php';

    if (empty($title)) {
        $title = $site_config['site']['name'];
    } else {
        $title = $site_config['site']['name'] . ' :: ' . format_comment($title);
    }
    $tmp = [
        'css' => [
            get_file_name('cookieconsent_css'),
        ],
    ];
    $stdhead = array_merge_recursive($stdhead, $tmp);
    $css_incl = '';
    if (!empty($stdhead['css'])) {
        foreach ($stdhead['css'] as $CSS) {
            $css_incl .= "
    <link rel='stylesheet' href='{$CSS}'>";
        }
    }
    $htmlout = doc_head($title) . "
    <link rel='apple-touch-icon' sizes='180x180' href='{$site_config['paths']['baseurl']}/apple-touch-icon.png'>
    <link rel='icon' type='image/png' sizes='32x32' href='{$site_config['paths']['baseurl']}/favicon-32x32.png'>
    <link rel='icon' type='image/png' sizes='16x16' href='{$site_config['paths']['baseurl']}/favicon-16x16.png'>
    <link rel='manifest' href='{$site_config['paths']['baseurl']}/manifest.json'>
    <link rel='mask-icon' href='{$site_config['paths']['baseurl']}/safari-pinned-tab.svg' color='#5bbad5'>
    <meta name='theme-color' content='#fff'>{$css_incl}
    <link rel='stylesheet' href='" . get_file_name('main_css') . "'>";
    $htmlout .= "
</head>
<body class='background-16 skin-2'>
    <div id='body-overlay'>
        <div class='$class'>";
    global $BLOCKS;

    if (!empty($curuser['id'])) {
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

        if (!empty($curuser['id'])) {
            $htmlout .= platform_menu();
            $htmlout .= "
            <div id='base_globelmessage'>
                <div class='top5 bottom5'>
                    <ul class='level-center tags'>";

            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_REPORTS && $BLOCKS['global_staff_report_on']) {
                require_once BLOCK_DIR . 'global/report.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_UPLOADAPP && $BLOCKS['global_staff_uploadapp_on']) {
                require_once BLOCK_DIR . 'global/uploadapp.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_HAPPYHOUR && $BLOCKS['global_happyhour_on']) {
                require_once BLOCK_DIR . 'global/happyhour.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_STAFF_MESSAGE && $BLOCKS['global_staff_warn_on']) {
                require_once BLOCK_DIR . 'global/staffmessages.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_NEWPM && $BLOCKS['global_message_on']) {
                require_once BLOCK_DIR . 'global/message.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_DEMOTION && $BLOCKS['global_demotion_on']) {
                require_once BLOCK_DIR . 'global/demotion.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH && $BLOCKS['global_freeleech_on']) {
                require_once BLOCK_DIR . 'global/freeleech.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_CRAZYHOUR && $BLOCKS['global_crazyhour_on']) {
                require_once BLOCK_DIR . 'global/crazyhour.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_BUG_MESSAGE && $BLOCKS['global_bug_message_on']) {
                require_once BLOCK_DIR . 'global/bugmessages.php';
            }
            if ($curuser['blocks']['global_stdhead'] & block_stdhead::STDHEAD_FREELEECH_CONTRIBUTION && $BLOCKS['global_freeleech_contribution_on']) {
                require_once BLOCK_DIR . 'global/freeleech_contribution.php';
            }
            require_once BLOCK_DIR . 'global/lottery.php';

            $htmlout .= '
                    </ul>
                </div>
            </div>';
        }
    }

    $htmlout .= "
        <div id='base_content' class='bg-05'>
            <div class='inner-wrapper bg-04'>";

    if (!empty($curuser['id'])) {
        $htmlout .= breadcrumbs($breadcrumbs);
    } else {
        //dd($curuser);
    }
    if ($BLOCKS['global_flash_messages_on']) {
        $htmlout .= "
                <div class='notification-wrapper'>";
        foreach ($site_config['site']['notifications'] as $notif) {
            $messages = $session->get($notif);
            if (!empty($messages)) {
                foreach ($messages as $message) {
                    $show[] = $message;
                    $message = !is_array($message) ? format_comment($message) : "<a href='{$message['link']}'>" . format_comment($message['message']) . '</a>';
                    $htmlout .= "
                    <div class='notification $notif has-text-centered size_5 is-marginless'>
                        <button class='delete'>&nbsp;</button>$message
                    </div>";
                }
            }
            $session->unset($notif);
        }
        $htmlout .= '
                </div>';
    }

    return $htmlout;
}

/**
 * @param array $stdfoot
 *
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 * @throws InvalidManipulation
 *
 * @return string
 */
function stdfoot(array $stdfoot = [])
{
    require_once INCL_DIR . 'function_bbcode.php';
    global $site_config, $starttime, $querytime, $container, $CURUSER;

    $cache = $container->get(Cache::class);
    $session_id = session_id();
    $query_stats = $cache->get('query_stats_' . $session_id);
    $use_12_hour = !empty($CURUSER['use_12_hour']) ? $CURUSER['use_12_hour'] : $site_config['site']['use_12_hour'];
    $header = $uptime = $htmlfoot = $now = '';
    $debug = $site_config['db']['debug'] && !empty($CURUSER['id']) && has_access($CURUSER['class'], UC_STAFF, 'coder') ? true : false;
    $queries = !empty($query_stats) ? count($query_stats) : 0;
    $seconds = microtime(true) - $starttime;
    $r_seconds = round($seconds, 5);
    if ($CURUSER['class'] >= UC_STAFF && $debug) {
        $querytime = $querytime === null ? 0 : $querytime;
        if ($site_config['cache']['driver'] === 'apcu' && extension_loaded('apcu')) {
            $stats = apcu_cache_info();
            if ($stats) {
                $stats['Hits'] = number_format($stats['num_hits'] / ($stats['num_hits'] + $stats['num_misses']) * 100, 3);
                $header = _fe('APC(u) Hits: {0}', $stats['Hits']);
                $header = _('APC(u) Hits: ') . "{$stats['Hits']}" . _('% Misses: ') . number_format((100 - $stats['Hits']), 3) . _('% Items: ') . number_format($stats['num_entries']) . _(' Memory: ') . mksize($stats['mem_size']);
            }
        } elseif ($site_config['cache']['driver'] === 'redis' && extension_loaded('redis')) {
            $client = $container->get(Redis::class);
            $stats = $client->info();
            if (!empty($stats)) {
                $stats['Hits'] = number_format($stats['keyspace_hits'] / ($stats['keyspace_hits'] + $stats['keyspace_misses']) * 100, 3);
                $db = 'db' . $site_config['redis']['database'];
                preg_match('/keys=(\d+)/', $stats[$db], $keys);
                $header = _('Redis Hits: ') . "{$stats['Hits']}" . _('% Misses: ') . number_format((100 - (float) $stats['Hits']), 3) . _('% Items: ') . number_format((float) $keys[1]) . _(' Memory: ') . "{$stats['used_memory_human']}";
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
                $header = _('Memcached Hits: ') . $stats['Hits'] . _('% Misses: ') . number_format((100 - $stats['Hits']), 3) . _('% Items: ') . number_format($stats['curr_items']) . _(' Memory: ') . mksize($stats['bytes']);
            }
        } elseif ($site_config['cache']['driver'] === 'file') {
            $files_info = GetDirectorySize($site_config['files']['path'], true, true);
            $header = _('Flysystem Cache: ') . "{$site_config['files']['path']} Count: {$files_info[1]} " . _('File size: ') . " {$files_info[0]}";
        } elseif ($site_config['cache']['driver'] === 'memory') {
            $header = _('Memory Cache: Nothing cached beyond the current request');
        } elseif ($site_config['cache']['driver'] === 'couchbase') {
            $header = _('Using Couchbase Cache');
        }
        if (!empty($query_stats)) {
            $htmlfoot .= "
                <div class='portlet top20'>
                    <a id='queries-hash'></a>
                    <div id='queries' class='box'>";
            $heading = "
                            <tr>
                                <th class='w-10'>" . _('ID') . "</th>
                                <th class='w-10'>" . _('Query Time') . "</th>
                                <th class='min-350'>" . _('Query String') . "</th>
                                <th class='min-150'>" . _('Parameters') . '</th>
                            </tr>';
            $body = '';
            foreach ($query_stats as $key => $value) {
                $params = implode("\n", $value['params']);
                $querytime += $value['seconds'];
                $body .= '
                            <tr>
                                <td>' . ($key + 1) . '</td>
                                <td>' . ($value['seconds'] > 0.01 ? "<span class='thas-text-danger tooltipper' title='" . _('You should optimize this query.') . "'>" . $value['seconds'] . '</span>' : "<span class='is-success tooltipper' title='" . _('Query does not appear to need optimizing.') . "'>" . $value['seconds'] . '</span>') . "</td>
                                <td>
                                    <div class='text-justify'>" . format_comment($value['query'], false, false, false) . '</div>
                                </td>
                                <td>' . format_comment($params, false, false, false) . '</td>
                            </tr>';
            }
            $htmlfoot .= main_table($body, $heading) . '
                    </div>
                </div>';
        }
    }
    $cache->delete('query_stats_' . $session_id);
    $uptime = $cache->get('uptime_');
    if ($uptime === false || is_null($uptime)) {
        $uptime = explode('up', `uptime`);
        $cache->set('uptime_', $uptime, 10);
    }
    $uptime = _fe('Uptime: {0}', str_replace('  ', ' ', $uptime[1]));
    if ($use_12_hour) {
        $now = time24to12(TIME_NOW, true);
    } else {
        $now = get_date((int) TIME_NOW, 'WITH_SEC', 1, 0);
    }
    $htmlfoot .= '
                </div>
            </div>';

    if ($CURUSER) {
        $sql_version = _('Database');
        $php_version = '';
        if (has_access($CURUSER['class'], UC_STAFF, 'coder')) {
            $sql_version = $cache->get('sql_version_');
            if ($sql_version === false || is_null($sql_version)) {
                $pdo = $container->get(PDO::class);
                $query = $pdo->query('SELECT VERSION() AS ver');
                $sql_version = $query->fetch(PDO::FETCH_COLUMN);
                if (!preg_match('/MariaDB/i', $sql_version)) {
                    $sql_version = _fe('MySQL {0}', $sql_version);
                }
                $cache->set('sql_version_', $sql_version, 3600);
            }
            $php_version = show_php_version();
        }
        $htmlfoot .= "
            <div class='site-debug bg-05 round10 top20 bottom20'>
                <div class='level bordered bg-04'>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>
                            " . _fe('PHP Peak Memory {0} in {1} seconds', mksize(memory_get_peak_usage()), $r_seconds) . "
                        </p>
                        <p class='is-marginless'>
                            " . $sql_version . ' ' . _pfe('was hit {0} time', 'was hit {0} times', $queries) . (has_access($CURUSER['class'], UC_STAFF, 'coder') ? ' ' . _pfe('in {0} second', 'in {0} seconds', $querytime) : '') . '
                        </p>
                        ' . ($debug ? "
                        <p class='is-marginless'>
                            $header
                        </p>
                        <p class='is-marginless'>
                            $uptime
                        </p>" : '') . "
                    </div>
                    <div class='size_4 top10 bottom10'>
                        <p class='is-marginless'>" . _fe('Server Time: {0}', $now) . "</p>
                        <p class='is-marginless'>" . _fe('Powered By: {0}', "<a href='" . url_proxy('https://github.com/darkalchemy/Pu-239', false) . "' target='_blank'>{$site_config['sourcecode']['name']}</a>") . "</p>
                        <p class='is-marginless'>" . _fe('Using Valid CSS3, HTML5 & PHP {0}', $php_version) . "</p>
                    </div>
                </div>
            </div>
            <div id='control_panel'>
                <a href='#' id='control_label'></a>
            </div>
        </div>";
    }
    $pages = [
        'details.php',
        'requests.php',
        'offers.php',
    ];
    $details = in_array(basename($_SERVER['PHP_SELF']), $pages);
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
            if (!empty($JS)) {
                $htmlfoot .= "
    <script src='{$JS}'></script>";
            }
        }
    }
    $font_size = !empty($CURUSER['font_size']) ? $CURUSER['font_size'] : 85;

    $htmlfoot .= "
    <script>document.body.style.fontSize = '{$font_size}%';</script>
    <link rel='stylesheet' href='" . get_file_name('last_css') . "'>
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
    $StatusBar .= "
                    <div id='base_usermenu' class='left10 level-item'>
                        <div class='tooltipper-ajax'>" . format_username($CURUSER['id'], true, false) . "</div>
                        <div id='clock' class='left10 right10 has-text-info tooltipper' onclick='hide_by_id()' title='" . _('Click to show the background image') . "'>{$clock}</div>
                    </div>";

    return $StatusBar;
}

/**
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 * @throws DependencyException
 *
 * @return string
 */
function platform_menu()
{
    global $container, $CURUSER, $site_config;

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
    $buttons = "
                            <li class='tooltipper has-text-info' title='" . _('Movies') . "'>
                                <a href='{$site_config['paths']['baseurl']}/tmovies.php'>
                                    <i class='icon-video icon' aria-hidden='true'></i>
                                </a>
                            </li>
                            <li class='tooltipper has-text-info' title='" . _('TV Shows') . "'>
                                <a href='{$site_config['paths']['baseurl']}/tvshows.php'>
                                    <i class='icon-television icon' aria-hidden='true'></i>
                                </a>
                            </li>
                            <li class='tooltipper has-text-info' title='" . _('Forums') . "'>
                                <a href='{$site_config['paths']['baseurl']}/forums.php'>
                                    <i class='icon-chat-empty icon' aria-hidden='true'></i>
                                </a>
                            </li>
                            <li class='tooltipper has-text-info' title='" . _('Messages') . "'>
                                <a href='{$site_config['paths']['baseurl']}/messages.php'>
                                    <i class='icon-comment-empty icon' aria-hidden='true'></i>
                                </a>
                            </li>
                            <li class='tooltipper has-text-info' title='" . _('My Blocks') . "'>
                                <a href='{$site_config['paths']['baseurl']}/user_blocks.php'>
                                    <i class='icon-cubes icon' aria-hidden='true'></i>
                                </a>
                            </li>";
    $menu = "
        <div id='platform-menu' class='platform-menu'>
            <div class='platform-wrapper'>
                <div class='columns is-marginless searchbar'>
                    <div class='column is-paddingless middle user-buttons'>
                        <ul class='level-left size_3 left10'>" . (PRODUCTION ? $buttons : "
                            <li>
                                <a href='" . url_proxy('https://github.com/darkalchemy/Pu-239') . "'>
                                    Pu-239 v{$site_config['sourcecode']['version']}
                                </a>
                            </li>") . "
                        </ul>
                    </div>
                    <div class='column is-paddingless middle'>
                        <ul class='level-center'>
                            <li>
                                <form action='{$site_config['paths']['baseurl']}/browse.php'>
                                    <div class='search round5 middle bg-light has-text-centered'>
                                        <input type='text' name='sn' placeholder='" . _('Search') . "' class='bg-none has-text-black has-text-centered' onfocus=\"toggle_buttons('user-buttons')\" onblur=\"toggle_buttons('user-buttons')\" autocomplete='off'>
                                    </div>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <div class='column is-paddingless middle user-buttons'>
                        <ul class='level-right size_3 right10'>" . (!PRODUCTION ? $buttons : '') . StatusBar() . $styles . '
                        </ul>
                    </div>
                </div>
            </div>
        </div>';

    return $menu;
}
