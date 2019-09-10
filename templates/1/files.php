<?php

declare(strict_types = 1);

/**
 * @param $file
 *
 * @return string|null
 */
function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['paths']['baseurl']}/css/1/chat_trans_1644374e.css";
        case 'chat_css_uranium':
            return "{$site_config['paths']['baseurl']}/css/1/chat_uranium_4c5c6845.css";
        case 'css':
            return "{$site_config['paths']['baseurl']}/css/1/css_c8b5cf39.css";
        case 'vendor_css':
            return "{$site_config['paths']['baseurl']}/css/1/vendor_620cdb5e.css";
        case 'cookieconsent_css':
            return "{$site_config['paths']['baseurl']}/css/1/cookieconsent_861434c7.css";
        case 'sceditor_css':
            return "{$site_config['paths']['baseurl']}/css/1/sceditor_3a62dd76.css";
        case 'main_css':
            return "{$site_config['paths']['baseurl']}/css/1/main_d9d63b86.css";
        case 'main_js':
            return "{$site_config['paths']['baseurl']}/js/1/main_5dd035d2.js";
        case 'vendor_js':
            return "{$site_config['paths']['baseurl']}/js/1/vendor_775593f4.js";
        case 'jquery_js':
            return "{$site_config['paths']['baseurl']}/js/1/jquery_21c3cb84.js";
        case 'chat_main_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_main_6f1fa437.js";
        case 'chat_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_8dfdde8e.js";
        case 'chat_log_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_log_399ae856.js";
        case 'categories_js':
            return "{$site_config['paths']['baseurl']}/js/1/categories_5ad7e17e.js";
        case 'browse_js':
            return "{$site_config['paths']['baseurl']}/js/1/browse_0995b15c.js";
        case 'scroller_js':
            return "{$site_config['paths']['baseurl']}/js/1/scroller_a736e94c.js";
        case 'glider_js':
            return "{$site_config['paths']['baseurl']}/js/1/glider_1e06ee07.js";
        case 'userdetails_js':
            return "{$site_config['paths']['baseurl']}/js/1/userdetails_fddd0a3e.js";
        case 'cookieconsent_js':
            return "{$site_config['paths']['baseurl']}/js/1/cookieconsent_657538f3.js";
        case 'invite_js':
            return "{$site_config['paths']['baseurl']}/js/1/invite_6f695552.js";
        case 'mass_bonus_js':
            return "{$site_config['paths']['baseurl']}/js/1/mass_bonus_5a93a3bc.js";
        case 'bookmarks_js':
            return "{$site_config['paths']['baseurl']}/js/1/bookmarks_d1a85bec.js";
        case 'iframe_js':
            return "{$site_config['paths']['baseurl']}/js/1/iframe_f74a311b.js";
        case 'navbar_show_js':
            return "{$site_config['paths']['baseurl']}/js/1/navbar_show_6a493036.js";
        case 'sceditor_js':
            return "{$site_config['paths']['baseurl']}/js/1/sceditor_14ab9de8.js";
        case 'cheaters_js':
            return "{$site_config['paths']['baseurl']}/js/1/cheaters_88e84984.js";
        case 'user_search_js':
            return "{$site_config['paths']['baseurl']}/js/1/user_search_79ba3068.js";
        case 'lightbox_js':
            return "{$site_config['paths']['baseurl']}/js/1/lightbox_dab5af27.js";
        case 'tooltipster_js':
            return "{$site_config['paths']['baseurl']}/js/1/tooltipster_602738b1.js";
        case 'site_config_js':
            return "{$site_config['paths']['baseurl']}/js/1/site_config_07db2f4b.js";
        case 'checkport_js':
            return "{$site_config['paths']['baseurl']}/js/1/checkport_a540ce2b.js";
        case 'check_username_js':
            return "{$site_config['paths']['baseurl']}/js/1/check_username_534a3b9e.js";
        case 'check_password_js':
            return "{$site_config['paths']['baseurl']}/js/1/check_password_a147515e.js";
        case 'upload_js':
            return "{$site_config['paths']['baseurl']}/js/1/upload_ef8fe24d.js";
        case 'request_js':
            return "{$site_config['paths']['baseurl']}/js/1/request_14f9d732.js";
        case 'scroll_to_poll_js':
            return "{$site_config['paths']['baseurl']}/js/1/scroll_to_poll_01088f95.js";
        case 'parallax_js':
            return "{$site_config['paths']['baseurl']}/js/1/parallax_a81ff2f8.js";
        case 'acp_js':
            return "{$site_config['paths']['baseurl']}/js/1/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['paths']['baseurl']}/js/1/dragndrop_1e9cee7f.js";
        case 'details_js':
            return "{$site_config['paths']['baseurl']}/js/1/details_f88bf6e8.js";
        case 'forums_js':
            return "{$site_config['paths']['baseurl']}/js/1/forums_b87eec63.js";
        case 'pollsmanager_js':
            return "{$site_config['paths']['baseurl']}/js/1/pollsmanager_5eaab6bb.js";
        case 'trivia_js':
            return "{$site_config['paths']['baseurl']}/js/1/trivia_b6a4ef84.js";
        default:
            return null;
    }
}
