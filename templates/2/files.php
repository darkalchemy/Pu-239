<?php

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/2/chat_trans_bc9ac054.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/2/chat_uranium_7253eb89.css";
        case 'css':
            return "{$site_config['baseurl']}/css/2/css_7401139d.css";
        case 'js':
            return "{$site_config['baseurl']}/js/2/js_bf9dc7b4.js";
        case 'index_js':
            return "{$site_config['baseurl']}/js/2/index_3479219a.js";
        case 'chat_js':
            return "{$site_config['baseurl']}/js/2/chat_d28a2667.js";
        case 'chat_log_js':
            return "{$site_config['baseurl']}/js/2/chat_log_fca523cf.js";
        case 'browse_js':
            return "{$site_config['baseurl']}/js/2/browse_74f9954e.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/2/userdetails_01fabb92.js";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/2/checkport_9f985589.js";
        case 'captcha2_js':
            return "{$site_config['baseurl']}/js/2/captcha2_2c3de5ae.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/2/upload_cbc14d14.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/2/request_f8d97b92.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/2/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['baseurl']}/js/2/dragndrop_8018b488.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/2/details_7864bc8d.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/2/forums_2ff1ac95.js";
        case 'staffpanel_js':
            return "{$site_config['baseurl']}/js/2/staffpanel_801ab346.js";
        default:
            return null;
    }
}
