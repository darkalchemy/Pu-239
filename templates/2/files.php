<?php

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/2/chat_trans_e0c56401.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/2/chat_uranium_4603c5b9.css";
        case 'css':
            return "{$site_config['baseurl']}/css/2/css_6e4e1498.css";
        case 'vendor_css':
            return "{$site_config['baseurl']}/css/2/vendor_e892b672.css";
        case 'sceditor_css':
            return "{$site_config['baseurl']}/css/2/sceditor_6eb75160.css";
        case 'main_css':
            return "{$site_config['baseurl']}/css/2/main_325b8b55.css";
        case 'main_js':
            return "{$site_config['baseurl']}/js/2/main_f153e2cb.js";
        case 'vendor_js':
            return "{$site_config['baseurl']}/js/2/vendor_dcc85e11.js";
        case 'jquery_js':
            return "{$site_config['baseurl']}/js/2/jquery_acaf7162.js";
        case 'chat_main_js':
            return "{$site_config['baseurl']}/js/2/chat_main_0b4dc4c4.js";
        case 'chat_js':
            return "{$site_config['baseurl']}/js/2/chat_8497c973.js";
        case 'chat_log_js':
            return "{$site_config['baseurl']}/js/2/chat_log_e081be8f.js";
        case 'browse_js':
            return "{$site_config['baseurl']}/js/2/browse_922a31c0.js";
        case 'scroller_js':
            return "{$site_config['baseurl']}/js/2/scroller_13b47ab3.js";
        case 'slider_js':
            return "{$site_config['baseurl']}/js/2/slider_33a1861e.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/2/userdetails_6ea0d103.js";
        case 'recaptcha_js':
            return "{$site_config['baseurl']}/js/2/recaptcha_31b1d18c.js";
        case '':
            return "{$site_config['baseurl']}/";
        case 'sceditor_js':
            return "{$site_config['baseurl']}/js/2/sceditor_3942ad35.js";
        case 'user_search_js':
            return "{$site_config['baseurl']}/js/2/user_search_79ba3068.js";
        case 'opcache_js':
            return "{$site_config['baseurl']}/js/2/opcache_885a7399.js";
        case 'lightbox_js':
            return "{$site_config['baseurl']}/js/2/lightbox_b0c4a917.js";
        case 'tooltipster_js':
            return "{$site_config['baseurl']}/js/2/tooltipster_c8a04b79.js";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/2/checkport_a540ce2b.js";
        case 'pStrength_js':
            return "{$site_config['baseurl']}/js/2/pStrength_2d088c70.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/2/upload_d3a06ad6.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/2/request_b205f15b.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/2/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['baseurl']}/js/2/dragndrop_8018b488.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/2/details_5a1433e5.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/2/forums_b87eec63.js";
        case 'pollsmanager_js':
            return "{$site_config['baseurl']}/js/2/pollsmanager_801ab346.js";
        default:
            return null;
    }
}
