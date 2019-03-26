<?php

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/2/chat_trans_5fcf2d2a.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/2/chat_uranium_2769ea27.css";
        case 'css':
            return "{$site_config['baseurl']}/css/2/css_c713ab41.css";
        case 'vendor_css':
            return "{$site_config['baseurl']}/css/2/vendor_0adb4a45.css";
        case 'sceditor_css':
            return "{$site_config['baseurl']}/css/2/sceditor_0ddbe82d.css";
        case 'main_css':
            return "{$site_config['baseurl']}/css/2/main_63adb4ae.css";
        case 'main_js':
            return "{$site_config['baseurl']}/js/2/main_0ee654d8.js";
        case 'vendor_js':
            return "{$site_config['baseurl']}/js/2/vendor_dcc85e11.js";
        case 'jquery_js':
            return "{$site_config['baseurl']}/js/2/jquery_acaf7162.js";
        case 'chat_main_js':
            return "{$site_config['baseurl']}/js/2/chat_main_a9bfdfd7.js";
        case 'chat_js':
            return "{$site_config['baseurl']}/js/2/chat_8497c973.js";
        case 'chat_log_js':
            return "{$site_config['baseurl']}/js/2/chat_log_e081be8f.js";
        case 'categories_js':
            return "{$site_config['baseurl']}/js/2/categories_5ad7e17e.js";
        case 'browse_js':
            return "{$site_config['baseurl']}/js/2/browse_de3939b1.js";
        case 'scroller_js':
            return "{$site_config['baseurl']}/js/2/scroller_4052fc2a.js";
        case 'slider_js':
            return "{$site_config['baseurl']}/js/2/slider_b7916982.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/2/userdetails_88ffe01b.js";
        case 'recaptcha_js':
            return "{$site_config['baseurl']}/js/2/recaptcha_8313f33d.js";
        case 'bookmarks_js':
            return "{$site_config['baseurl']}/js/2/bookmarks_0673fe80.js";
        case 'iframe_js':
            return "{$site_config['baseurl']}/js/2/iframe_f74a311b.js";
        case 'navbar_show_js':
            return "{$site_config['baseurl']}/js/2/navbar_show_6a493036.js";
        case '':
            return "{$site_config['baseurl']}/";
        case 'sceditor_js':
            return "{$site_config['baseurl']}/js/2/sceditor_12250a86.js";
        case 'cheaters_js':
            return "{$site_config['baseurl']}/js/2/cheaters_88e84984.js";
        case 'user_search_js':
            return "{$site_config['baseurl']}/js/2/user_search_79ba3068.js";
        case 'lightbox_js':
            return "{$site_config['baseurl']}/js/2/lightbox_b0c4a917.js";
        case 'tooltipster_js':
            return "{$site_config['baseurl']}/js/2/tooltipster_7883a611.js";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/2/checkport_a540ce2b.js";
        case 'check_username_js':
            return "{$site_config['baseurl']}/js/2/check_username_ce6ba54b.js";
        case 'pStrength_js':
            return "{$site_config['baseurl']}/js/2/pStrength_28866856.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/2/upload_229081f4.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/2/request_d5161ee2.js";
        case 'parallax_js':
            return "{$site_config['baseurl']}/js/2/parallax_eca2b578.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/2/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['baseurl']}/js/2/dragndrop_5647940a.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/2/details_f136860e.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/2/forums_b87eec63.js";
        case 'pollsmanager_js':
            return "{$site_config['baseurl']}/js/2/pollsmanager_801ab346.js";
        case 'trivia_js':
            return "{$site_config['baseurl']}/js/2/trivia_28839400.js";
        default:
            return null;
    }
}
