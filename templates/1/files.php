<?php

declare(strict_types = 1);

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['paths']['baseurl']}/css/1/chat_trans_375e6282.css";
        case 'chat_css_uranium':
            return "{$site_config['paths']['baseurl']}/css/1/chat_uranium_ff332dd3.css";
        case 'css':
            return "{$site_config['paths']['baseurl']}/css/1/css_3d6fdacc.css";
        case 'vendor_css':
            return "{$site_config['paths']['baseurl']}/css/1/vendor_34928329.css";
        case 'cookieconsent_css':
            return "{$site_config['paths']['baseurl']}/css/1/cookieconsent_861434c7.css";
        case 'sceditor_css':
            return "{$site_config['paths']['baseurl']}/css/1/sceditor_e26757ff.css";
        case 'main_css':
            return "{$site_config['paths']['baseurl']}/css/1/main_f2944378.css";
        case 'main_js':
            return "{$site_config['paths']['baseurl']}/js/1/main_39023546.js";
        case 'vendor_js':
            return "{$site_config['paths']['baseurl']}/js/1/vendor_775593f4.js";
        case 'jquery_js':
            return "{$site_config['paths']['baseurl']}/js/1/jquery_21c3cb84.js";
        case 'chat_main_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_main_d15a5c71.js";
        case 'chat_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_01f13cf7.js";
        case 'chat_log_js':
            return "{$site_config['paths']['baseurl']}/js/1/chat_log_6a6f6b6e.js";
        case 'categories_js':
            return "{$site_config['paths']['baseurl']}/js/1/categories_5ad7e17e.js";
        case 'browse_js':
            return "{$site_config['paths']['baseurl']}/js/1/browse_e16f4f2d.js";
        case 'scroller_js':
            return "{$site_config['paths']['baseurl']}/js/1/scroller_7313b84f.js";
        case 'glider_js':
            return "{$site_config['paths']['baseurl']}/js/1/glider_1e06ee07.js";
        case 'userdetails_js':
            return "{$site_config['paths']['baseurl']}/js/1/userdetails_15f714f6.js";
        case 'cookieconsent_js':
            return "{$site_config['paths']['baseurl']}/js/1/cookieconsent_a256d5f9.js";
        case 'invite_js':
            return "{$site_config['paths']['baseurl']}/js/1/invite_6f695552.js";
        case 'mass_bonus_js':
            return "{$site_config['paths']['baseurl']}/js/1/mass_bonus_5a93a3bc.js";
        case 'bookmarks_js':
            return "{$site_config['paths']['baseurl']}/js/1/bookmarks_0673fe80.js";
        case 'iframe_js':
            return "{$site_config['paths']['baseurl']}/js/1/iframe_f74a311b.js";
        case 'navbar_show_js':
            return "{$site_config['paths']['baseurl']}/js/1/navbar_show_6a493036.js";
        case 'sceditor_js':
            return "{$site_config['paths']['baseurl']}/js/1/sceditor_e99ede5d.js";
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
            return "{$site_config['paths']['baseurl']}/js/1/upload_e2bc9076.js";
        case 'request_js':
            return "{$site_config['paths']['baseurl']}/js/1/request_0762d325.js";
        case 'parallax_js':
            return "{$site_config['paths']['baseurl']}/js/1/parallax_068e1067.js";
        case 'acp_js':
            return "{$site_config['paths']['baseurl']}/js/1/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['paths']['baseurl']}/js/1/dragndrop_5647940a.js";
        case 'details_js':
            return "{$site_config['paths']['baseurl']}/js/1/details_9c8ee2c9.js";
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
