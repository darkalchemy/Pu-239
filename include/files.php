<?php

function get_file_name($file)
{
    global $site_config;

    $style = get_stylesheet();
    switch ($file) {
        case 'css':
            return "{$site_config['baseurl']}/css/1/css_c13994f3.css";
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/1/chat_trans_cb71184e.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/1/chat_uranium_cc7e7299.css";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/1/checkport_9f985589.js";
        case 'browse_js':
            return "{$site_config['baseurl']}/js/1/browse_55efe0c1.js";
        case 'chat_js':
            return "{$site_config['baseurl']}/js/1/chat_af3d81c3.js";
        case 'chat_log_js':
            return "{$site_config['baseurl']}/js/1/chat_log_2078eeb0.js";
        case 'index_js':
            return "{$site_config['baseurl']}/js/1/index_c73226cb.js";
        case 'captcha2_js':
            return "{$site_config['baseurl']}/js/1/captcha2_2c3de5ae.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/1/upload_10ca99a2.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/1/request_4bbb71bf.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/1/acp_22d19c79.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/1/userdetails_9985f40a.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/1/details_7864bc8d.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/1/forums_2ff1ac95.js";
        case 'staffpanel_js':
            return "{$site_config['baseurl']}/js/1/staffpanel_801ab346.js";
        case 'js':
            return "{$site_config['baseurl']}/js/1/js_8ab33e82.js";
        default:
            return null;
    }
}