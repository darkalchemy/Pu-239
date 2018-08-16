<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
global $site_config, $BLOCKS;

if (empty($BLOCKS)) {
    die('BLOCKS are empty');
}

write_class_files();

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

$templates = glob(TEMPLATE_DIR . '*', GLOB_ONLYDIR);
foreach ($templates as $template) {
    $folder = basename($template);
    if ($folder != 'themeChanger') {
        $folders[] = $folder;
    }
}

foreach ($folders as $folder) {
    $update = TEMPLATE_DIR . "{$folder}/files.php";
    $dirs = [
        PUBLIC_DIR . "js/{$folder}/*",
        PUBLIC_DIR . "css/{$folder}/*",
    ];

    foreach ($dirs as $dir) {
        $files = glob($dir);
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    copy(ROOT_DIR . 'node_modules/lightbox2/dist/css/lightbox.css', BIN_DIR . 'lightbox.css');
    passthru("sed -i 's#../images/#../../images/#g' " . BIN_DIR . 'lightbox.css');

    $js_list = [];
    $js_list['index_js'] = $js_list['js'] = [];
    if ($BLOCKS['ajaxchat_on']) {
        $js_list = array_merge($js_list, [
            'chat_js'     => [
                CHAT_DIR . 'js/chat.js',
                CHAT_DIR . 'js/custom.js',
                CHAT_DIR . 'js/classes.js',
                CHAT_DIR . 'js/lang/en.js',
                CHAT_DIR . 'js/config.js',
                CHAT_DIR . 'js/FABridge.js',
                SCRIPTS_DIR . 'ajaxchat.js',
            ],
            'chat_log_js' => [
                CHAT_DIR . 'js/chat.js',
                CHAT_DIR . 'js/logs.js',
                CHAT_DIR . 'js/custom.js',
                CHAT_DIR . 'js/classes.js',
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
        $js_list['index_js'] = array_merge($js_list['index_js'], [
            ROOT_DIR . 'node_modules/raphael/raphael.js',
            SCRIPTS_DIR . 'jquery.mousewheel.js',
            SCRIPTS_DIR . 'icarousel.js',
        ]);
    }
    if ($BLOCKS['latest_torrents_slider_on']) {
        $js_list['index_js'] = array_merge($js_list['index_js'], [
            SCRIPTS_DIR . 'jquery.flexslider.js',
            SCRIPTS_DIR . 'flexslider.js',
        ]);
    }

    $js_list['userdetails_js'] = [
        SCRIPTS_DIR . 'jquery.tabcontrol.js',
    ];
    if ($BLOCKS['userdetails_flush_on']) {
        $js_list['userdetails_js'] = array_merge($js_list['userdetails_js'], [
            SCRIPTS_DIR . 'flush_torrents.js',
        ]);
    }

    $js_list['js'] = array_merge($js_list['js'], [
        ROOT_DIR . 'node_modules/jquery/dist/jquery.js',
    ]);

    if ($BLOCKS['global_themechanger_on']) {
        $js_list['js'] = array_merge($js_list['js'], [
            TEMPLATE_DIR . 'themeChanger/js/colorpicker.js',
            TEMPLATE_DIR . 'themeChanger/js/themeChanger.js',
        ]);
    }

    $js_list['js'] = array_merge($js_list['js'], [
        SCRIPTS_DIR . 'yall.js',
        SCRIPTS_DIR . 'popup.js',
        SCRIPTS_DIR . 'markitup/jquery.markitup.js',
        SCRIPTS_DIR . 'markitup/sets/default/set.js',
        SCRIPTS_DIR . 'markitup.js',
        ROOT_DIR . 'node_modules/lightbox2/dist/js/lightbox.js',
        SCRIPTS_DIR . 'lightbox.js',
        SCRIPTS_DIR . 'tooltipster.bundle.js',
        SCRIPTS_DIR . 'tooltipster.js',
        SCRIPTS_DIR . 'copy_to_clipboard.js',
        SCRIPTS_DIR . 'flipper.js',
        SCRIPTS_DIR . 'replaced.js',
    ]);

    $js_list = array_merge($js_list, [
        'checkport_js'  => [
            SCRIPTS_DIR . 'checkports.js',
        ],
        'captcha2_js'   => [
            SCRIPTS_DIR . 'check.js',
            SCRIPTS_DIR . 'pStrength.jquery.js',
            SCRIPTS_DIR . 'pstrength.js',
        ],
        'upload_js'     => [
            SCRIPTS_DIR . 'FormManager.js',
            SCRIPTS_DIR . 'getname.js',
            SCRIPTS_DIR . 'imdb.js',
            SCRIPTS_DIR . 'isbn.js',
        ],
        'request_js'    => [
            SCRIPTS_DIR . 'jquery.validate.js',
            SCRIPTS_DIR . 'check_selected.js',
            SCRIPTS_DIR . 'imdb.js',
        ],
        'acp_js'        => [
            SCRIPTS_DIR . 'acp.js',
        ],
        'dragndrop_js'  => [
            SCRIPTS_DIR . 'dragndrop.js',
        ],
        'details_js'    => [
            SCRIPTS_DIR . 'jquery.thanks.js',
        ],
        'forums_js'     => [
            SCRIPTS_DIR . 'check_selected.js',
            SCRIPTS_DIR . 'jquery.trilemma.js',
            SCRIPTS_DIR . 'forums.js',
        ],
        'staffpanel_js' => [
            SCRIPTS_DIR . 'polls.js',
        ],
    ]);

    $css_list = [];
    $css_list['css'] = [
        ROOT_DIR . 'node_modules/normalize.css/normalize.css',
        ROOT_DIR . 'node_modules/bulma/css/bulma.css',
    ];

    if ($BLOCKS['global_themechanger_on']) {
        $css_list['css'] = array_merge([
            TEMPLATE_DIR . 'themeChanger/css/themeChanger.css',
            TEMPLATE_DIR . 'themeChanger/css/colorpicker.css',
        ], $css_list['css']);
    }

    if ($BLOCKS['latest_torrents_scroll_on']) {
        $css_list['css'] = array_merge($css_list['css'], [
            TEMPLATE_DIR . "{$folder}/css/iCarousel.css",
        ]);
    }

    if ($BLOCKS['latest_torrents_slider_on']) {
        $css_list['css'] = array_merge($css_list['css'], [
            TEMPLATE_DIR . "{$folder}/css/flexslider.css",
        ]);
    }

    $css_list['css'] = array_merge($css_list['css'], [
        TEMPLATE_DIR . "{$folder}/css/fonts.css",
        TEMPLATE_DIR . "{$folder}/css/fontello.css",
        TEMPLATE_DIR . "{$folder}/default.css",
        TEMPLATE_DIR . "{$folder}/css/navbar.css",
        TEMPLATE_DIR . "{$folder}/css/tables.css",
        TEMPLATE_DIR . "{$folder}/css/cards.css",
        TEMPLATE_DIR . "{$folder}/css/tooltipster.bundle.css",
        TEMPLATE_DIR . "{$folder}/css/tooltipster-sideTip-borderless.css",
        TEMPLATE_DIR . "{$folder}/css/classcolors.css",
        TEMPLATE_DIR . "{$folder}/css/skins.css",
        TEMPLATE_DIR . "{$folder}/css/markitup.css",
        BIN_DIR . 'lightbox.css',
        TEMPLATE_DIR . "{$folder}/custom.css/",
    ]);

    if ($BLOCKS['ajaxchat_on']) {
        $css_list = array_merge([
            'chat_css_trans'   => [
                ROOT_DIR . 'node_modules/normalize.css/normalize.css',
                CHAT_DIR . 'css/global.css',
                CHAT_DIR . 'css/fonts.css',
                CHAT_DIR . 'css/print.css',
                CHAT_DIR . 'css/custom.css',
                CHAT_DIR . 'css/classcolors.css',
                CHAT_DIR . 'css/transparent.css',
            ],
            'chat_css_uranium' => [
                ROOT_DIR . 'node_modules/normalize.css/normalize.css',
                CHAT_DIR . 'css/global.css',
                CHAT_DIR . 'css/fonts.css',
                CHAT_DIR . 'css/print.css',
                CHAT_DIR . 'css/custom.css',
                CHAT_DIR . 'css/classcolors.css',
                CHAT_DIR . 'css/Uranium.css',
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
        passthru('php-cs-fixer fix --show-progress=dots -vvv');
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
        die("$key array can not be empty\n");
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
        die("$key array can not be empty\n");
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

    return [
        $key,
        "css/{$folder}/{$lkey}_{$hash}{$css_ext}",
    ];
}
