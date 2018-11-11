<?php

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/1/chat_trans_8e1f35c7.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/1/chat_uranium_c927b560.css";
        case 'css':
            return "{$site_config['baseurl']}/css/1/css_c0a4997f.css";
        case 'vendor_css':
            return "{$site_config['baseurl']}/css/1/vendor_e11d3062.css";
        case 'sceditor_css':
            return "{$site_config['baseurl']}/css/1/sceditor_1970f6f8.css";
        case 'main_css':
            return "{$site_config['baseurl']}/css/1/main_587c58a8.css";
        case 'main_js':
            return "{$site_config['baseurl']}/js/1/main_deb9e2a7.js";
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
            return "{$site_config['baseurl']}/js/1/browse_922a31c0.js";
        case 'scroller_js':
            return "{$site_config['baseurl']}/js/1/scroller_13b47ab3.js";
        case 'slider_js':
            return "{$site_config['baseurl']}/js/1/slider_33a1861e.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/1/userdetails_88ffe01b.js";
        case 'recaptcha_js':
            return "{$site_config['baseurl']}/js/1/recaptcha_31b1d18c.js";
        case 'theme_js':
            return "{$site_config['baseurl']}/js/1/theme_ff3ca3dd.js";
        case 'sceditor_js':
            return "{$site_config['baseurl']}/js/1/sceditor_f6894a81.js";
        case 'cheaters_js':
            return "{$site_config['baseurl']}/js/1/cheaters_88e84984.js";
        case 'user_search_js':
            return "{$site_config['baseurl']}/js/1/user_search_79ba3068.js";
        case 'lightbox_js':
            return "{$site_config['baseurl']}/js/1/lightbox_b0c4a917.js";
        case 'tooltipster_js':
            return "{$site_config['baseurl']}/js/1/tooltipster_c8a04b79.js";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/1/checkport_a540ce2b.js";
        case 'pStrength_js':
            return "{$site_config['baseurl']}/js/1/pStrength_2d088c70.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/1/upload_d835f595.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/1/request_9103ebbd.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/1/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['baseurl']}/js/1/dragndrop_8018b488.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/1/details_aae059b4.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/1/forums_b87eec63.js";
        case 'pollsmanager_js':
            return "{$site_config['baseurl']}/js/1/pollsmanager_801ab346.js";
        default:
            return null;
    }
}
