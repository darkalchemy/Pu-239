<?php

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/1/chat_trans_bc9ac054.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/1/chat_uranium_7253eb89.css";
        case 'css':
            return "{$site_config['baseurl']}/css/1/css_705667c6.css";
        case 'js':
            return "{$site_config['baseurl']}/js/1/js_0842ef2c.js";
        case 'index_js':
            return "{$site_config['baseurl']}/js/1/index_3479219a.js";
        case 'chat_js':
            return "{$site_config['baseurl']}/js/1/chat_60a83936.js";
        case 'chat_log_js':
            return "{$site_config['baseurl']}/js/1/chat_log_cb64b1cc.js";
        case 'browse_js':
            return "{$site_config['baseurl']}/js/1/browse_74f9954e.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/1/userdetails_01fabb92.js";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/1/checkport_9f985589.js";
        case 'captcha2_js':
            return "{$site_config['baseurl']}/js/1/captcha2_5875867d.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/1/upload_cbc14d14.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/1/request_4f3792e6.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/1/acp_22d19c79.js";
        case 'dragndrop_js':
            return "{$site_config['baseurl']}/js/1/dragndrop_8018b488.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/1/details_7864bc8d.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/1/forums_2ff1ac95.js";
        case 'staffpanel_js':
            return "{$site_config['baseurl']}/js/1/staffpanel_801ab346.js";
        default:
            return null;
    }
}
