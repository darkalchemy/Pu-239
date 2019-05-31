<?php

declare(strict_types = 1);

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['paths']['baseurl']}/css/1/chat_trans_53889918.css";
        case 'chat_css_uranium':
            return "{$site_config['paths']['baseurl']}/css/1/chat_uranium_69a725eb.css";
        case 'css':
            return "{$site_config['paths']['baseurl']}/css/1/css_8dea8a5e.css";
        case 'vendor_css':
            return "{$site_config['paths']['baseurl']}/css/1/vendor_68ac7831.css";
        case 'cookieconsent_css':
            return "{$site_config['paths']['baseurl']}/css/1/cookieconsent_1b6e5918.css";
        case 'sceditor_css':
            return "{$site_config['paths']['baseurl']}/css/1/sceditor_fc466e60.css";
        case 'main_css':
            return "{$site_config['paths']['baseurl']}/css/1/main_07ebb4b8.css";
        case 'main_js':
            return "{$site_config['paths']['baseurl']}/js/1/main_a3d7bf35.js";
        case 'vendor_js':
            return "{$site_config['paths']['baseurl']}/js/1/vendor_dcc85e11.js";
        case 'jquery_js':
            return "{$site_config['paths']['baseurl']}/js/1/jquery_21c3cb84.js";
        case 'chat_main_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_main_d15a5c71.js";
        case 'chat_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_8feafabe.js";
        case 'chat_log_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_log_6680661c.js";
        case 'categories_js':
            return "{$site_config['paths']['baseurl']}/js/1/categories_5ad7e17e.js";
        case 'browse_js':
            return "{$site_config['paths']['baseurl']}/js/1/browse_a7fde3a7.js";
        case 'scroller_js':
            return "{$site_config['paths']['baseurl']}/js/1/scroller_c8d9f628.js";
        case 'glider_js':
            return "{$site_config['paths']['baseurl']}/js/1/glider_1e06ee07.js";
        case 'userdetails_js':
            return "{$site_config['paths']['baseurl']}/js/1/userdetails_15f714f6.js";
        case 'cookieconsent_js':
            return "{$site_config['paths']['baseurl']}/js/1/cookieconsent_a256d5f9.js";
        case 'bookmarks_js':
            return "{$site_config['paths']['baseurl']}/js/1/bookmarks_0673fe80.js";
        case 'iframe_js':
            return "{$site_config['paths']['baseurl']}/js/1/iframe_f74a311b.js";
        case 'navbar_show_js':
            return "{$site_config['paths']['baseurl']}/js/1/navbar_show_6a493036.js";
        case 'theme_js':
            return "{$site_config['paths']['baseurl']}/js/1/theme_ef7bc285.js";
        case 'sceditor_js':
            return "{$site_config['paths']['baseurl']}/js/1/sceditor_abd0371d.js";
        case 'cheaters_js':
            return "{$site_config['paths']['baseurl']}/js/1/cheaters_88e84984.js";
        case 'user_search_js':
            return "{$site_config['paths']['baseurl']}/js/1/user_search_79ba3068.js";
        case 'lightbox_js':
            return "{$site_config['paths']['baseurl']}/js/1/lightbox_1de13332.js";
        case 'tooltipster_js':
            return "{$site_config['paths']['baseurl']}/js/1/tooltipster_7883a611.js";
        case 'site_config_js':
            return "{$site_config['paths']['baseurl']}/js/1/site_config_07db2f4b.js";
        case 'checkport_js':
            return "{$site_config['paths']['baseurl']}/js/1/checkport_a540ce2b.js";
        case 'check_username_js':
            return "{$site_config['paths']['baseurl']}/js/1/check_username_14044cdf.js";
        case 'check_password_js':
            return "{$site_config['paths']['baseurl']}/js/1/check_password_a147515e.js";
        case 'upload_js':
            return "{$site_config['paths']['baseurl']}/js/1/upload_dabb0f82.js";
        case 'request_js':
            return "{$site_config['paths']['baseurl']}/js/1/request_3c1e75fc.js";
        case 'parallax_js':
            return "{$site_config['paths']['baseurl']}/js/1/parallax_068e1067.js";
        case 'acp_js':
            return "{$site_config['paths']['baseurl']}/js/1/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['paths']['baseurl']}/js/1/dragndrop_5647940a.js";
        case 'details_js':
            return "{$site_config['paths']['baseurl']}/js/1/details_43ed1838.js";
        case 'forums_js':
            return "{$site_config['paths']['baseurl']}/js/1/forums_b87eec63.js";
        case 'pollsmanager_js':
            return "{$site_config['paths']['baseurl']}/js/1/pollsmanager_5eaab6bb.js";
        case 'trivia_js':
            return "{$site_config['paths']['baseurl']}/js/1/trivia_28839400.js";
        default:
            return null;
    }
}
