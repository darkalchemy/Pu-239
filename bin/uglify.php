<?php

declare(strict_types = 1);

use Delight\Auth\AuthError;
use Delight\Auth\NotLoggedInException;
use DI\DependencyException;
use DI\NotFoundException;
use MatthiasMullie\Scrapbook\Exception\UnbegunTransaction;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once BIN_DIR . 'functions.php';

if (php_sapi_name() === 'cli') {
    toggle_site_status(true);
    run_uglify($argv);
    toggle_site_status(false);
}

/**
 * @param array $argv
 *
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 * @throws AuthError
 * @throws DependencyException
 *
 * @return bool|string
 */
function run_uglify($argv = [])
{
    global $site_config, $BLOCKS;

    if (empty($BLOCKS)) {
        return 'BLOCKS are empty';
    }
    if (php_sapi_name() === 'cli') {
        $site_config['cache']['driver'] = 'memory';
    }
    foreach ($argv as $arg) {
        if (!PRODUCTION && ($arg === 'update' || $arg === 'all')) {
            passthru('composer self-update');
            passthru('sudo npm install -g npm');
            passthru('composer update');
            passthru('npm update');
        } elseif ($arg === 'classes') {
            echo "Creating classes\n";
            $styles = get_styles();
            get_classes($styles, true);

            return true;
        }
    }

    $styles = get_styles();
    get_classes($styles, false);
    foreach ($styles as $style) {
        make_dir(CACHE_DIR . $style, 0774);
        make_dir(TEMPLATE_DIR . $style, 0774);
        make_dir(CHAT_DIR . 'css' . DIRECTORY_SEPARATOR . $style, 0774);
        write_class_files($style);
    }

    $purpose = '--beautify';
    $short = 'Beautified';
    $spurpose = "-O2 'all:on;mergeSemantically:off;removeUnusedAtRules:off' --format beautify";
    $css_ext = '.css';
    $js_ext = '.js';
    $jstmp = BIN_DIR . 'temp.js';
    $csstmp = BIN_DIR . 'temp.css';

    if (PRODUCTION) {
        $purpose = '--compress --mangle';
        $short = 'Minified';
        $spurpose = "-O2 'all:on;mergeSemantically:off;removeUnusedAtRules:off'";
        $css_ext = '.min.css';
        $js_ext = '.min.js';
    }

    foreach ($styles as $folder) {
        if (php_sapi_name() === 'cli') {
            echo "Processing Template: {$folder}\n";
        }
        get_default_border($folder);
        $update = TEMPLATE_DIR . "{$folder}/files.php";
        $dirs = [
            PUBLIC_DIR . "js/{$folder}/",
            PUBLIC_DIR . "css/{$folder}/",
        ];

        foreach ($dirs as $dir) {
            make_dir($dir, 0774);
            $files = glob($dir . '/*');
            foreach ($files as $file) {
                can_delete($file, true);
            }
        }

        copy(ROOT_DIR . 'node_modules/lightbox2/dist/css/lightbox.css', BIN_DIR . 'lightbox.css');
        if (can_delete(BIN_DIR . 'lightbox.css', false)) {
            passthru("sed -i 's#../images/#../../images/#g' " . BIN_DIR . 'lightbox.css');
        }
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
                    SCRIPTS_DIR . 'ajaxchat.js',
                    SCRIPTS_DIR . 'popup.js',
                ],
                'chat_log_js' => [
                    CHAT_DIR . 'js/logs.js',
                    CHAT_DIR . 'js/lang/en.js',
                    CHAT_DIR . 'js/config.js',
                ],
            ]);
        }

        $js_list['categories_js'] = [
            SCRIPTS_DIR . 'categories.js',
        ];

        $js_list['browse_js'] = [
            SCRIPTS_DIR . 'autocomplete.js',
            SCRIPTS_DIR . 'toggle.js',
        ];

        if ($BLOCKS['staff_picks_on']) {
            $js_list['browse_js'] = array_merge($js_list['browse_js'], [
                SCRIPTS_DIR . 'staff_picks.js',
            ]);
        }

        if ($BLOCKS['latest_torrents_scroll_on']) {
            $js_list['scroller_js'] = [
                ROOT_DIR . 'node_modules/raphael/raphael.js',
                SCRIPTS_DIR . 'icarousel.js',
            ];
        }

        if ($BLOCKS['latest_torrents_slider_on']) {
            $js_list['glider_js'] = [
                ROOT_DIR . 'node_modules/@glidejs/glide/dist/glide.js',
                SCRIPTS_DIR . 'glide.js',
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

        $js_list['cookieconsent_js'] = [
            ROOT_DIR . 'node_modules/cookieconsent/src/cookieconsent.js',
            SCRIPTS_DIR . 'cookieconsent.js',
        ];

        $js_list['invite_js'] = [
            SCRIPTS_DIR . 'invite.js',
        ];

        $js_list['mass_bonus_js'] = [
            SCRIPTS_DIR . 'mass_bonus.js',
        ];

        $js_list['bookmarks_js'] = [
            SCRIPTS_DIR . 'bookmarks.js',
        ];

        $js_list['iframe_js'] = [
            SCRIPTS_DIR . 'resize_iframe.js',
        ];

        $js_list['navbar_show_js'] = [
            SCRIPTS_DIR . 'navbar_show.js',
        ];

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

        $js_list['site_config_js'] = [
            SCRIPTS_DIR . 'site_config.js',
        ];

        $js_list['main_js'] = [
            SCRIPTS_DIR . 'copy_to_clipboard.js',
            SCRIPTS_DIR . 'flipper.js',
            SCRIPTS_DIR . 'replaced.js',
            SCRIPTS_DIR . 'hide_html.js',
            SCRIPTS_DIR . 'hide_navbar.js',
            SCRIPTS_DIR . 'cooker_notify.js',
            SCRIPTS_DIR . 'offer_notify.js',
            SCRIPTS_DIR . 'offer_vote.js',
            SCRIPTS_DIR . 'request_notify.js',
            SCRIPTS_DIR . 'request_vote.js',
            SCRIPTS_DIR . 'hide_menu_items.js',
        ];

        $js_list['offer_js'] = [
            SCRIPTS_DIR . 'offer_status.js',
        ];

        $js_list = array_merge($js_list, [
            'checkport_js' => [
                SCRIPTS_DIR . 'checkports.js',
            ],
            'check_username_js' => [
                SCRIPTS_DIR . 'check_username.js',
                SCRIPTS_DIR . 'check_email.js',
            ],
            'check_password_js' => [
                SCRIPTS_DIR . 'check_password.js',
            ],
            'upload_js' => [
                SCRIPTS_DIR . 'genres_show_hide.js',
                SCRIPTS_DIR . 'getname.js',
                SCRIPTS_DIR . 'imdb.js',
                SCRIPTS_DIR . 'isbn.js',
                SCRIPTS_DIR . 'upload.js',
            ],
            'imdb_js' => [
                SCRIPTS_DIR . 'imdb.js',
            ],
            'scroll_to_poll_js' => [
                SCRIPTS_DIR . 'scroll_to_poll.js',
            ],
            'parallax_js' => [
                SCRIPTS_DIR . 'parallax.js',
            ],
            'acp_js' => [
                SCRIPTS_DIR . 'acp.js',
            ],
            'dragndrop_js' => [
                SCRIPTS_DIR . 'dragndrop.js',
                SCRIPTS_DIR . 'upload_image_from_url.js',
            ],
            'details_js' => [
                SCRIPTS_DIR . 'descr.js',
                SCRIPTS_DIR . 'jquery.thanks.js',
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
        $css_list['index_css'] = [];
        $css_list['cookieconsent_css'] = [
            ROOT_DIR . 'node_modules/cookieconsent/src/styles/base.css',
            ROOT_DIR . 'node_modules/cookieconsent/src/styles/layout.css',
            ROOT_DIR . 'node_modules/cookieconsent/src/styles/media.css',
            ROOT_DIR . 'node_modules/cookieconsent/src/styles/animation.css',
            ROOT_DIR . 'node_modules/cookieconsent/src/styles/themes/classic.css',
        ];

        if ($BLOCKS['latest_torrents_scroll_on']) {
            $css_list['index_css'] = array_merge($css_list['index_css'], [
                TEMPLATE_DIR . "{$folder}/css/iCarousel.css",
            ]);
        }

        if ($BLOCKS['latest_torrents_slider_on']) {
            $css_list['index_css'] = array_merge($css_list['index_css'], [
                ROOT_DIR . 'node_modules/@glidejs/glide/dist/css/glide.core.css',
                ROOT_DIR . 'node_modules/@glidejs/glide/dist/css/glide.theme.css',
            ]);
        }

        $css_list['sceditor_css'] = [
            ROOT_DIR . 'node_modules/normalize.css/normalize.css',
            BIN_DIR . 'pu239.css',
            ROOT_DIR . 'node_modules/sceditor/minified/themes/modern.min.css',
            TEMPLATE_DIR . "{$folder}/css/sceditor.css",
            TEMPLATE_DIR . "{$folder}/variables.css",
            TEMPLATE_DIR . "{$folder}/css/default.css",
            TEMPLATE_DIR . "{$folder}/css/tables.css",
        ];

        $css_list['main_css'] = [
            ROOT_DIR . 'node_modules/normalize.css/normalize.css',
            BIN_DIR . 'pu239.css',
            TEMPLATE_DIR . "{$folder}/variables.css",
            TEMPLATE_DIR . "{$folder}/css/fonts.css",
            TEMPLATE_DIR . "{$folder}/css/fontello.css",
            TEMPLATE_DIR . "{$folder}/css/navbar.css",
            TEMPLATE_DIR . "{$folder}/css/skins.css",
            TEMPLATE_DIR . "{$folder}/css/tables.css",
            TEMPLATE_DIR . "{$folder}/css/cards.css",
            ROOT_DIR . 'node_modules/tooltipster/dist/css/tooltipster.bundle.css',
            ROOT_DIR . 'node_modules/tooltipster/dist/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-borderless.min.css',
            TEMPLATE_DIR . "{$folder}/css/tooltipster.css",
            TEMPLATE_DIR . "{$folder}/css/classcolors.css",
            BIN_DIR . 'lightbox.css',
            TEMPLATE_DIR . "{$folder}/css/default.css",
            TEMPLATE_DIR . "{$folder}/css/breadcrumbs.css",
            TEMPLATE_DIR . "{$folder}/custom.css",
        ];

        $css_list['last_css'] = [
            TEMPLATE_DIR . "{$folder}/css/show.css",
        ];

        if ($BLOCKS['ajaxchat_on']) {
            $css_list = array_merge([
                'chat_css_trans' => [
                    ROOT_DIR . 'node_modules/normalize.css/normalize.css',
                    TEMPLATE_DIR . "{$folder}/variables.css",
                    CHAT_DIR . "css/{$folder}/global.css",
                    CHAT_DIR . "css/{$folder}/fonts.css",
                    CHAT_DIR . "css/{$folder}/custom.css",
                    CHAT_DIR . "css/{$folder}/classcolors.css",
                    CHAT_DIR . "css/{$folder}/default.css",
                ],
                'chat_css_uranium' => [
                    ROOT_DIR . 'node_modules/normalize.css/normalize.css',
                    TEMPLATE_DIR . "{$folder}/variables.css",
                    CHAT_DIR . "css/{$folder}/global.css",
                    CHAT_DIR . "css/{$folder}/fonts.css",
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
            if (!empty($key) && !empty($css)) {
                $pages[] = process_css($key, $css, $spurpose, $csstmp, $folder, $css_ext);
            }
        }
        foreach ($js_list as $key => $js) {
            if (!empty($key) && !empty($js)) {
                $pages[] = process_js($key, $js, $purpose, $jstmp, $folder, $js_ext);
            }
        }

        can_delete($csstmp, true);
        can_delete($jstmp, true);
        can_delete(BIN_DIR . 'lightbox.css', true);
        write_file($update, $pages);
    }

    if (php_sapi_name() === 'cli') {
        echo "All CSS and Javascript files processed\n";
    }
    foreach ($argv as $arg) {
        if (!PRODUCTION && ($arg === 'fix' || $arg === 'all')) {
            passthru('vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --show-progress=dots -vvv');
        }
    }
    cleanup(get_webserver_user());

    return true;
}

/**
 * @param $key
 * @param $list
 * @param $purpose
 * @param $jstmp
 * @param $folder
 * @param $js_ext
 *
 * @return array|null
 */
function process_js($key, $list, $purpose, $jstmp, $folder, $js_ext)
{
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
        return null;
    }

    $list = implode(' ', $files);
    passthru(ROOT_DIR . "node_modules/uglify-es/bin/uglifyjs $list $purpose -o $jstmp");
    //passthru(ROOT_DIR . "node_modules/uglify-js/bin/uglifyjs $jstmp $purpose -o $jstmp");
    if (file_exists($jstmp)) {
        $lkey = str_replace('_js', '', $key);
        $hash = substr(hash_file('sha256', $jstmp), 0, 8);
        $data = file_get_contents($jstmp);
        $fp = gzopen(PUBLIC_DIR . "js/{$folder}/{$lkey}_{$hash}{$js_ext}.gz", 'w9');
        gzwrite($fp, $data);
        gzclose($fp);
        chmod(PUBLIC_DIR . "js/{$folder}/{$lkey}_{$hash}{$js_ext}.gz", 0664);
        //copy($jstmp, PUBLIC_DIR . "js/{$folder}/{$lkey}_{$hash}{$js_ext}");
        //chmod(PUBLIC_DIR . "js/{$folder}/{$lkey}_{$hash}{$js_ext}", 0664);

        return [
            $key,
            "js/{$folder}/{$lkey}_{$hash}{$js_ext}",
        ];
    }

    return null;
}

/**
 * @param $key
 * @param $list
 * @param $spurpose
 * @param $csstmp
 * @param $folder
 * @param $css_ext
 *
 * @return array|null
 */
function process_css($key, $list, $spurpose, $csstmp, $folder, $css_ext)
{
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
        return null;
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
        //copy($csstmp, PUBLIC_DIR . "css/{$folder}/{$lkey}_{$hash}{$css_ext}");
        //chmod(PUBLIC_DIR . "css/{$folder}/{$lkey}_{$hash}{$css_ext}", 0664);
        $lkey = str_replace('_css', '', $key);
        if ($key === 'sceditor_css') {
            copy(ROOT_DIR . 'node_modules/sceditor/minified/themes/famfamfam.png', PUBLIC_DIR . "css/{$folder}/famfamfam.png");
            $sceditor = file_get_contents(SCRIPTS_DIR . 'sceditor.js');
            make_dir(BIN_DIR . $folder, 0774);
            $sceditor = preg_replace("#/css/\d+/sceditor_.{8}\.css#", "/css/{$folder}/{$lkey}_{$hash}{$css_ext}", $sceditor);
            $sceditor = preg_replace("#/css/\d+/sceditor_.{8}\.min.css#", "/css/{$folder}/{$lkey}_{$hash}{$css_ext}", $sceditor);
            file_put_contents(BIN_DIR . "{$folder}/sceditor.js", $sceditor);
        }

        return [
            $key,
            "css/{$folder}/{$lkey}_{$hash}{$css_ext}",
        ];
    }

    return null;
}

/**
 * @param $update
 * @param $pages
 */
function write_file($update, $pages)
{
    $output = '<?php

declare(strict_types = 1);

/**
 * @param $file
 *
 * @return string|null
 */
function get_file_name($file)
{
    global $site_config;

    switch ($file) {';
    foreach ($pages as $page) {
        $output .= "
        case '{$page[0]}':
            return \"{\$site_config['paths']['baseurl']}/{$page[1]}\";";
    }
    $output .= '
        default:
            return null;
    }
}';

    file_put_contents($update, $output . PHP_EOL);
}

/**
 * @param $folder
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws UnbegunTransaction
 * @throws \Envms\FluentPDO\Exception
 */
function get_default_border($folder)
{
    $contents = file_get_contents(TEMPLATE_DIR . "{$folder}/variables.css");

    if (can_delete(SCRIPTS_DIR . 'replaced.js', false)) {
        preg_match('#--main-bdr-color: (.*);#', $contents, $match);
        if (!empty($match[1])) {
            $var = trim($match[1]);
            passthru("sed -i \"s/timerColor:.*$/timerColor: '{$var}',/g\" " . SCRIPTS_DIR . 'replaced.js');
            passthru("sed -i \"s/timerBarStrokeColor:.*$/timerBarStrokeColor: '{$var}',/g\" " . SCRIPTS_DIR . 'replaced.js');
        }
    }
    if (can_delete(TEMPLATE_DIR . "{$folder}/default.scss", false)) {
        preg_match('#--default-text-color: (.*);#', $contents, $match);
        if (!empty($match[1])) {
            $var = trim($match[1]);
            passthru("sed -i \"s/primary:.*$/primary: {$var};/g\" " . TEMPLATE_DIR . "{$folder}/default.scss");
        }
        preg_match('#--default-link-color: (.*);#', $contents, $match);
        if (!empty($match[1])) {
            $var = trim($match[1]);
            passthru("sed -i \"s/link:.*$/link: {$var};/g\" " . TEMPLATE_DIR . "{$folder}/default.scss");
        }

        preg_match('#--default-link-hover-color: (.*);#', $contents, $match);
        if (!empty($match[1])) {
            $var = trim($match[1]);
            passthru("sed -i \"s/link-hover:.*$/link-hover: {$var[1]};/g\" " . TEMPLATE_DIR . "{$folder}/default.scss");
        }
    }
    can_delete(BIN_DIR . 'pu239.css', true);
    passthru('npx node-sass ' . BIN_DIR . 'pu239.scss ' . BIN_DIR . 'pu239.css');
}

/**
 *
 * @param string $file
 * @param bool   $delete
 *
 * @throws AuthError
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws NotLoggedInException
 * @throws \Envms\FluentPDO\Exception
 * @throws UnbegunTransaction
 *
 * @return bool
 */
function can_delete(string $file, bool $delete)
{
    if (is_file($file)) {
        if (is_writable(dirname($file))) {
            if ($delete) {
                unlink($file);

                return true;
            } else {
                return true;
            }
        } else {
            $br = php_sapi_name() == 'cli' ? "\n" : '<br>';
            $user = get_username();
            $group = get_webserver_user();
            $user_group = php_sapi_name() == 'cli' ? "{$user}:{$group}" : "{$group}:{$group}";
            if ($delete) {
                $msg = _fe('{0}Unable to delete file:{1}.{2}Please check your permissions.{3}sudo chown -R {4}.{5}sudo php bin/set_perms.php', $br, $file, $br, $br, $user_group, $br);
            } else {
                $msg = _fe('{0}Unable to modify file:{1}.{2}Please check your permissions.{3}sudo chown -R {4}.{5}sudo php bin/set_perms.php', $br, $file, $br, $br, $user_group, $br);
            }
            if (php_sapi_name() === 'cli') {
                die($msg);
            } else {
                stderr(_('Error'), $msg);
            }
        }
    }

    return false;
}
