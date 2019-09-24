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
            return "{$site_config['paths']['baseurl']}/css/1/chat_trans_e2ce56e7.css";
        case 'chat_css_uranium':
            return "{$site_config['paths']['baseurl']}/css/1/chat_uranium_9d8737c4.css";
        case 'index_css':
            return "{$site_config['paths']['baseurl']}/css/1/index_64c2c5b0.css";
        case 'cookieconsent_css':
            return "{$site_config['paths']['baseurl']}/css/1/cookieconsent_2f4ef440.css";
        case 'sceditor_css':
            return "{$site_config['paths']['baseurl']}/css/1/sceditor_b3d8a457.css";
        case 'main_css':
            return "{$site_config['paths']['baseurl']}/css/1/main_16c8e4ac.css";
        case 'last_css':
            return "{$site_config['paths']['baseurl']}/css/1/last_33d8c190.css";
        case 'main_js':
            return "{$site_config['paths']['baseurl']}/js/1/main_21c757e5.js";
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
            return "{$site_config['paths']['baseurl']}/js/1/browse_be345bd4.js";
        case 'scroller_js':
            return "{$site_config['paths']['baseurl']}/js/1/scroller_a736e94c.js";
        case 'glider_js':
            return "{$site_config['paths']['baseurl']}/js/1/glider_35ad5028.js";
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
            return "{$site_config['paths']['baseurl']}/js/1/sceditor_d3b2e805.js";
        case 'cheaters_js':
            return "{$site_config['paths']['baseurl']}/js/1/cheaters_88e84984.js";
        case 'user_search_js':
            return "{$site_config['paths']['baseurl']}/js/1/user_search_79ba3068.js";
        case 'lightbox_js':
            return "{$site_config['paths']['baseurl']}/js/1/lightbox_dab5af27.js";
        case 'tooltipster_js':
            return "{$site_config['paths']['baseurl']}/js/1/tooltipster_f0def356.js";
        case 'site_config_js':
            return "{$site_config['paths']['baseurl']}/js/1/site_config_07db2f4b.js";
        case 'offer_js':
            return "{$site_config['paths']['baseurl']}/js/1/offer_0947814a.js";
        case 'checkport_js':
            return "{$site_config['paths']['baseurl']}/js/1/checkport_a540ce2b.js";
        case 'check_username_js':
            return "{$site_config['paths']['baseurl']}/js/1/check_username_534a3b9e.js";
        case 'check_password_js':
            return "{$site_config['paths']['baseurl']}/js/1/check_password_a147515e.js";
        case 'upload_js':
            return "{$site_config['paths']['baseurl']}/js/1/upload_219237a1.js";
        case 'imdb_js':
            return "{$site_config['paths']['baseurl']}/js/1/imdb_16b88402.js";
        case 'scroll_to_poll_js':
            return "{$site_config['paths']['baseurl']}/js/1/scroll_to_poll_01088f95.js";
        case 'parallax_js':
            return "{$site_config['paths']['baseurl']}/js/1/parallax_a81ff2f8.js";
        case 'acp_js':
            return "{$site_config['paths']['baseurl']}/js/1/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['paths']['baseurl']}/js/1/dragndrop_c06ac504.js";
        case 'details_js':
            return "{$site_config['paths']['baseurl']}/js/1/details_c57f59ed.js";
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
