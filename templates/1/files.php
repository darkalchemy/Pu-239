<?php

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/1/chat_trans_19e3cd7b.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/1/chat_uranium_e6ff1dde.css";
        case 'css':
            return "{$site_config['baseurl']}/css/1/css_5294c310.css";
        case 'vendor_css':
            return "{$site_config['baseurl']}/css/1/vendor_e11d3062.css";
        case 'sceditor_css':
            return "{$site_config['baseurl']}/css/1/sceditor_bef4ac18.css";
        case 'main_css':
            return "{$site_config['baseurl']}/css/1/main_41ec77fd.css";
        case 'main_js':
            return "{$site_config['baseurl']}/js/1/main_3a013c91.js";
        case 'vendor_js':
            return "{$site_config['baseurl']}/js/1/vendor_dcc85e11.js";
        case 'jquery_js':
            return "{$site_config['baseurl']}/js/1/jquery_acaf7162.js";
        case 'chat_main_js':
            return "{$site_config['baseurl']}/js/1/chat_main_0b4dc4c4.js";
        case 'chat_js':
            return "{$site_config['baseurl']}/js/1/chat_8497c973.js";
        case 'chat_log_js':
            return "{$site_config['baseurl']}/js/1/chat_log_e081be8f.js";
        case 'browse_js':
            return "{$site_config['baseurl']}/js/1/browse_d96450ea.js";
        case 'scroller_js':
            return "{$site_config['baseurl']}/js/1/scroller_13b47ab3.js";
        case 'slider_js':
            return "{$site_config['baseurl']}/js/1/slider_33a1861e.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/1/userdetails_88ffe01b.js";
        case 'recaptcha_js':
            return "{$site_config['baseurl']}/js/1/recaptcha_31b1d18c.js";
        case 'bookmarks_js':
            return "{$site_config['baseurl']}/js/1/bookmarks_8e719a09.js";
        case 'iframe_js':
            return "{$site_config['baseurl']}/js/1/iframe_f74a311b.js";
        case 'theme_js':
            return "{$site_config['baseurl']}/js/1/theme_ff3ca3dd.js";
        case 'sceditor_js':
            return "{$site_config['baseurl']}/js/1/sceditor_e4a73238.js";
        case 'cheaters_js':
            return "{$site_config['baseurl']}/js/1/cheaters_88e84984.js";
        case 'user_search_js':
            return "{$site_config['baseurl']}/js/1/user_search_79ba3068.js";
        case 'lightbox_js':
            return "{$site_config['baseurl']}/js/1/lightbox_b0c4a917.js";
        case 'tooltipster_js':
            return "{$site_config['baseurl']}/js/1/tooltipster_dc2a1682.js";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/1/checkport_a540ce2b.js";
        case 'check_username_js':
            return "{$site_config['baseurl']}/js/1/check_username_ce6ba54b.js";
        case 'pStrength_js':
            return "{$site_config['baseurl']}/js/1/pStrength_28866856.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/1/upload_dbe8405d.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/1/request_d5161ee2.js";
        case 'parallax_js':
            return "{$site_config['baseurl']}/js/1/parallax_eca2b578.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/1/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['baseurl']}/js/1/dragndrop_8018b488.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/1/details_fa26612c.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/1/forums_b87eec63.js";
        case 'pollsmanager_js':
            return "{$site_config['baseurl']}/js/1/pollsmanager_801ab346.js";
        case 'trivia_js':
            return "{$site_config['baseurl']}/js/1/trivia_6f40b5b8.js";
        default:
            return null;
    }
}
