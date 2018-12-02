<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once BIN_DIR . 'functions.php';
global $site_config, $BLOCKS;

if (empty($BLOCKS)) {
    die('BLOCKS are empty');
}

foreach ($argv as $arg) {
    if ($arg === 'update' || $arg === 'all') {
        passthru('composer self-update');
        passthru('sudo npm install -g npm');
        passthru('composer update');
        passthru('npm update');
    } elseif ($arg === 'classes') {
        echo "Creating classes\n";
        $styles = get_styles();
        $classes = get_classes($styles, true);
        die();
    }
}

$styles = get_styles();
$classes = get_classes($styles, false);

foreach ($styles as $style) {
    make_dir(CACHE_DIR . $style);
    make_dir(TEMPLATE_DIR . $style);
    make_dir(CHAT_DIR . 'css' . DIRECTORY_SEPARATOR . $style);
    write_class_files($style);
}

$purpose = '--beautify';
$short = 'Beautified';
$spurpose = '-O2 --skip-rebase --format beautify';
$css_ext = '.css';
$js_ext = '.js';
$jstmp = BIN_DIR . 'temp.js';
$csstmp = BIN_DIR . 'temp.css';

if ($site_config['in_production']) {
    $purpose = '--compress --mangle';
    $short = 'Minified';
    $spurpose = '--skip-rebase -O2';
    $css_ext = '.min.css';
    $js_ext = '.min.js';
}

exec('npx node-sass ' . BIN_DIR . 'pu239.scss ' . BIN_DIR . 'pu239.css');

foreach ($styles as $folder) {
    $update = TEMPLATE_DIR . "{$folder}/files.php";
    $dirs = [
        PUBLIC_DIR . "js/{$folder}/",
        PUBLIC_DIR . "css/{$folder}/",
    ];

    foreach ($dirs as $dir) {
        make_dir($dir);
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    copy(ROOT_DIR . 'node_modules/lightbox2/dist/css/lightbox.css', BIN_DIR . 'lightbox.css');
    passthru("sed -i 's#../images/#../../images/#g' " . BIN_DIR . 'lightbox.css');

    $js_list = [];
    $js_list['jquery_js'] = $js_list['vendor_js'] = $js_list['main_js'] = [];
    if ($BLOCKS['ajaxchat_on']) {
        $js_list = array_merge($js_list, [
            'chat_main_js' => [
                CHAT_DIR . 'js/chat.js',
                CHAT_DIR . 'js/custom.js',
                CHAT_DIR . 'js/classes.js',
            ],
            'chat_js' => [
                CHAT_DIR . 'js/lang/en.js',
                CHAT_DIR . 'js/config.js',
                CHAT_DIR . 'js/FABridge.js',
                SCRIPTS_DIR . 'ajaxchat.js',
                SCRIPTS_DIR . 'popup.js',
            ],
            'chat_log_js' => [
                CHAT_DIR . 'js/logs.js',
                CHAT_DIR . 'js/lang/en.js',
                CHAT_DIR . 'js/config.js',
                CHAT_DIR . 'js/FABridge.js',
            ],
        ]);
    }
    if ($BLOCKS['staff_picks_on']) {
        $js_list['browse_js'] = [
            SCRIPTS_DIR . 'autocomplete.js',
            SCRIPTS_DIR . 'staff_picks.js',
        ];
    } else {
        $js_list['browse_js'] = [
            SCRIPTS_DIR . 'autocomplete.js',
        ];
    }

    if ($BLOCKS['latest_torrents_scroll_on']) {
        $js_list['scroller_js'] = [
            ROOT_DIR . 'node_modules/raphael/raphael.js',
            SCRIPTS_DIR . 'icarousel.js',
        ];
    }

    if ($BLOCKS['latest_torrents_slider_on']) {
        $js_list['slider_js'] = [
            ROOT_DIR . 'node_modules/flexslider/jquery.flexslider.js',
            SCRIPTS_DIR . 'flexslider.js',
        ];
    }
    $js_list['userdetails_js'] = [
        SCRIPTS_DIR . 'jquery.tabcontrol.js',
        SCRIPTS_DIR . 'flip_box.js',
        SCRIPTS_DIR . 'user_torrents.js',
    ];

    if ($BLOCKS['userdetails_flush_on']) {
        $js_list['userdetails_js'] = array_merge($js_list['userdetails_js'], [
            SCRIPTS_DIR . 'flush_torrents.js',
        ]);
    }

    $js_list['jquery_js'] = [
        ROOT_DIR . 'node_modules/jquery/dist/jquery.js',
    ];

    $js_list['recaptcha_js'] = [
        SCRIPTS_DIR . 'recaptcha.js',
    ];

    $js_list['bookmarks_js'] = [
        SCRIPTS_DIR . 'bookmarks.js',
    ];

    $js_list['iframe_js'] = [
        SCRIPTS_DIR . 'resize_iframe.js',
    ];

    if ($BLOCKS['global_themechanger_on']) {
        $js_list['theme_js'] = [
            TEMPLATE_DIR . "{$folder}/themeChanger/js/colorpicker.js",
            TEMPLATE_DIR . "{$folder}/themeChanger/js/themeChanger.js",
        ];
    }

    $js_list['sceditor_js'] = [
        ROOT_DIR . 'node_modules/sceditor/minified/jquery.sceditor.bbcode.min.js',
        ROOT_DIR . 'node_modules/sceditor/src/icons/material.js',
        ROOT_DIR . 'node_modules/sceditor/src/plugins/autoyoutube.js',
        BIN_DIR . "{$folder}/sceditor.js",
    ];

    $js_list['cheaters_js'] = [
        SCRIPTS_DIR . 'cheaters.js',
    ];

    $js_list['user_search_js'] = [
        SCRIPTS_DIR . 'usersearch.js',
    ];

    $js_list['lightbox_js'] = [
        ROOT_DIR . 'node_modules/lightbox2/dist/js/lightbox.js',
        SCRIPTS_DIR . 'lightbox.js',
    ];

    $js_list['tooltipster_js'] = [
        ROOT_DIR . 'node_modules/tooltipster/dist/js/tooltipster.bundle.js',
        SCRIPTS_DIR . 'tooltipster.js',
    ];
    $js_list['vendor_js'] = [
        SCRIPTS_DIR . 'yall.js',
        SCRIPTS_DIR . 'popup.js',
    ];

    $js_list['main_js'] = [
        SCRIPTS_DIR . 'copy_to_clipboard.js',
        SCRIPTS_DIR . 'flipper.js',
        SCRIPTS_DIR . 'replaced.js',
    ];

    $js_list = array_merge($js_list, [
        'checkport_js' => [
            SCRIPTS_DIR . 'checkports.js',
        ],
        'check_username_js' => [
            SCRIPTS_DIR . 'check.js',
        ],
        'pStrength_js' => [
            SCRIPTS_DIR . 'pStrength.jquery.js',
            SCRIPTS_DIR . 'pstrength.js',
        ],
        'upload_js' => [
            SCRIPTS_DIR . 'genres_show_hide.js',
            SCRIPTS_DIR . 'getname.js',
            SCRIPTS_DIR . 'imdb.js',
            SCRIPTS_DIR . 'isbn.js',
            SCRIPTS_DIR . 'upload.js',
        ],
        'request_js' => [
            SCRIPTS_DIR . 'imdb.js',
        ],
        'parallax_js' => [
            SCRIPTS_DIR . 'parallax.js',
        ],
        'acp_js' => [
            SCRIPTS_DIR . 'acp.js',
        ],
        'dragndrop_js' => [
            SCRIPTS_DIR . 'dragndrop.js',
        ],
        'details_js' => [
            SCRIPTS_DIR . 'descr.js',
            SCRIPTS_DIR . 'jquery.thanks.js',
            SCRIPTS_DIR . 'imdb.js',
            SCRIPTS_DIR . 'isbn.js',
            SCRIPTS_DIR . 'tvmaze.js',
        ],
        'forums_js' => [
            SCRIPTS_DIR . 'jquery.trilemma.js',
            SCRIPTS_DIR . 'forums.js',
        ],
        'pollsmanager_js' => [
            SCRIPTS_DIR . 'polls.js',
        ],
        'trivia_js' => [
            SCRIPTS_DIR . 'trivia.js',
        ],
    ]);

    $css_list = [];
    $css_list['css'] = [];
    $css_list['vendor_css'] = [
        ROOT_DIR . 'node_modules/normalize.css/normalize.css',
        BIN_DIR . 'pu239.css',
    ];

    if ($BLOCKS['global_themechanger_on']) {
        $css_list['css'] = array_merge([
            TEMPLATE_DIR . "{$folder}/themeChanger/css/themeChanger.css",
            TEMPLATE_DIR . "{$folder}/themeChanger/css/colorpicker.css",
        ], $css_list['css']);
    }

    if ($BLOCKS['latest_torrents_scroll_on']) {
        $css_list['css'] = array_merge($css_list['css'], [
            TEMPLATE_DIR . "{$folder}/css/iCarousel.css",
        ]);
    }

    if ($BLOCKS['latest_torrents_slider_on']) {
        $css_list['css'] = array_merge($css_list['css'], [
            ROOT_DIR . 'node_modules/flexslider/flexslider.css',
            TEMPLATE_DIR . "{$folder}/css/flexslider.css",
        ]);
    }

    $css_list['css'] = array_merge($css_list['css'], [
        TEMPLATE_DIR . "{$folder}/css/fonts.css",
        TEMPLATE_DIR . "{$folder}/css/fontello.css",
        TEMPLATE_DIR . "{$folder}/css/navbar.css",
        TEMPLATE_DIR . "{$folder}/css/tables.css",
        TEMPLATE_DIR . "{$folder}/css/cards.css",
        ROOT_DIR . 'node_modules/tooltipster/dist/css/tooltipster.bundle.css',
        ROOT_DIR . 'node_modules/tooltipster/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-borderless.min.css',
        TEMPLATE_DIR . "{$folder}/css/tooltipster.css",
        TEMPLATE_DIR . "{$folder}/css/classcolors.css",
        TEMPLATE_DIR . "{$folder}/css/skins.css",
        BIN_DIR . 'lightbox.css',
    ]);

    $css_list['sceditor_css'] = [
        ROOT_DIR . 'node_modules/normalize.css/normalize.css',
        BIN_DIR . 'pu239.css',
        ROOT_DIR . 'node_modules/sceditor/minified/themes/modern.min.css',
        TEMPLATE_DIR . "{$folder}/css/sceditor.css",
        TEMPLATE_DIR . "{$folder}/default.css",
        TEMPLATE_DIR . "{$folder}/css/tables.css",
    ];

    $css_list['main_css'] = [
        TEMPLATE_DIR . "{$folder}/default.css",
        TEMPLATE_DIR . "{$folder}/css/breadcrumbs.css",
        TEMPLATE_DIR . "{$folder}/custom.css/",
    ];

    if ($BLOCKS['ajaxchat_on']) {
        $css_list = array_merge([
            'chat_css_trans' => [
                ROOT_DIR . 'node_modules/normalize.css/normalize.css',
                CHAT_DIR . "css/{$folder}/global.css",
                CHAT_DIR . "css/{$folder}/fonts.css",
                CHAT_DIR . "css/{$folder}/print.css",
                CHAT_DIR . "css/{$folder}/custom.css",
                CHAT_DIR . "css/{$folder}/classcolors.css",
                CHAT_DIR . "css/{$folder}/transparent.css",
            ],
            'chat_css_uranium' => [
                ROOT_DIR . 'node_modules/normalize.css/normalize.css',
                CHAT_DIR . "css/{$folder}/global.css",
                CHAT_DIR . "css/{$folder}/fonts.css",
                CHAT_DIR . "css/{$folder}/print.css",
                CHAT_DIR . "css/{$folder}/custom.css",
                CHAT_DIR . "css/{$folder}/classcolors.css",
                CHAT_DIR . "css/{$folder}/Uranium.css",
            ],
        ], $css_list);
    }

    $css_files = [];
    foreach ($css_list as $key => $css) {
        foreach ($css as $file) {
            if (!in_array($file, $css_files)) {
                $css_files[] = $file;
            }
        }
    }

    $pages = [];
    foreach ($css_list as $key => $css) {
        if (!empty($css)) {
            $pages[] = process_css($key, $css);
        }
    }

    foreach ($js_list as $key => $js) {
        if (!empty($js)) {
            $pages[] = process_js($key, $js);
        }
    }

    unlink($csstmp);
    unlink($jstmp);
    unlink(BIN_DIR . 'lightbox.css');
    write_file($update, $pages);
}

echo "All CSS and Javascript files processed\n";
foreach ($argv as $arg) {
    if ($arg === 'fix' || $arg === 'all') {
        passthru('vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --show-progress=dots -vvv');
    }
}

/**
 * @param $key
 * @param $list
 *
 * @return array
 */
function process_js($key, $list)
{
    global $jstmp, $purpose, $js_ext, $folder;

    if (empty($list)) {
        die("$key array can not be empty\n");
    }

    $files = [];
    foreach ($list as $file) {
        if (file_exists($file)) {
            $files[] = $file;
        }
    }
    if (empty($files)) {
        return [];
    }

    $list = implode(' ', $files);
    $cmd = ROOT_DIR . "node_modules/uglify-js/bin/uglifyjs $list $purpose -o $jstmp";
    passthru($cmd);
    if (file_exists($jstmp)) {
        $lkey = str_replace('_js', '', $key);
        $hash = substr(hash_file('sha256', $jstmp), 0, 8);
        $data = file_get_contents($jstmp);
        $fp = gzopen(PUBLIC_DIR . "js/{$folder}/{$lkey}_{$hash}{$js_ext}.gz", 'w9');
        gzwrite($fp, $data);
        gzclose($fp);
        chmod(PUBLIC_DIR . "js/{$folder}/{$lkey}_{$hash}{$js_ext}.gz", 0664);
        copy($jstmp, PUBLIC_DIR . "js/{$folder}/{$lkey}_{$hash}{$js_ext}");
        chmod(PUBLIC_DIR . "js/{$folder}/{$lkey}_{$hash}{$js_ext}", 0664);
    }

    return [
        $key,
        "js/{$folder}/{$lkey}_{$hash}{$js_ext}",
    ];
}

/**
 * @param $key
 * @param $list
 *
 * @return array
 */
function process_css($key, $list)
{
    global $csstmp, $spurpose, $css_ext, $folder;

    if (empty($list)) {
        die("$key array can not be empty\n");
    }
    $files = [];
    foreach ($list as $file) {
        if (file_exists($file)) {
            $files[] = $file;
        }
    }
    if (empty($files)) {
        return [];
    }

    $list = implode(' ', $files);
    $cmd = ROOT_DIR . "node_modules/clean-css-cli/bin/cleancss $spurpose -o $csstmp $list";
    passthru($cmd);
    if (file_exists($csstmp)) {
        passthru("sudo npx postcss $csstmp --no-map --replace");
        $lkey = str_replace('_css', '', $key);
        $hash = substr(hash_file('sha256', $csstmp), 0, 8);
        $data = file_get_contents($csstmp);
        $fp = gzopen(PUBLIC_DIR . "css/{$folder}/{$lkey}_{$hash}{$css_ext}.gz", 'w9');
        gzwrite($fp, $data);
        gzclose($fp);
        chmod(PUBLIC_DIR . "css/{$folder}/{$lkey}_{$hash}{$css_ext}.gz", 0664);
        copy($csstmp, PUBLIC_DIR . "css/{$folder}/{$lkey}_{$hash}{$css_ext}");
        chmod(PUBLIC_DIR . "css/{$folder}/{$lkey}_{$hash}{$css_ext}", 0664);
    }

    if ($key === 'sceditor_css') {
        $lkey = str_replace('_css', '', $key);
        copy(ROOT_DIR . 'node_modules/sceditor/minified/themes/famfamfam.png', PUBLIC_DIR . "css/{$folder}/famfamfam.png");
        $sceditor = file_get_contents(SCRIPTS_DIR . 'sceditor.js');
        make_dir(BIN_DIR . $folder);
        $sceditor = preg_replace("#/css/\d+/sceditor_.{8}\.css#", "/css/{$folder}/{$lkey}_{$hash}{$css_ext}", $sceditor);
        $sceditor = preg_replace("#/css/\d+/sceditor_.{8}\.min.css#", "/css/{$folder}/{$lkey}_{$hash}{$css_ext}", $sceditor);
        file_put_contents(BIN_DIR . "{$folder}/sceditor.js", $sceditor);
    }

    return [
        $key,
        "css/{$folder}/{$lkey}_{$hash}{$css_ext}",
    ];
}

/**
 * @param $dir
 */
function make_dir($dir)
{
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

/**
 * @param $update
 * @param $pages
 */
function write_file($update, $pages)
{
    $output = '<?php

function get_file_name($file)
{
    global $site_config;

    switch ($file) {';

    foreach ($pages as $page) {
        $output .= "
        case '{$page[0]}':
            return \"{\$site_config['baseurl']}/{$page[1]}\";";
    }
    $output .= '
        default:
            return null;
    }
}';

    file_put_contents($update, $output . PHP_EOL);
}
