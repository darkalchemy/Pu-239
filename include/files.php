<?php

function get_file_name($file)
{
    global $site_config;

    $style = get_stylesheet();
    switch ($file) {
        case 'css':
            return "{$site_config['baseurl']}/css/1/css_870ad0b5.min.css";
        case 'chat_css_trans':
            return "{$site_config['baseurl']}/css/1/chat_trans_8ed95a81.min.css";
        case 'chat_css_uranium':
            return "{$site_config['baseurl']}/css/1/chat_uranium_399ff408.min.css";
        case 'checkport_js':
            return "{$site_config['baseurl']}/js/1/checkport_8e2c55ff.min.js";
        case 'browse_js':
            return "{$site_config['baseurl']}/js/1/browse_1a80621c.min.js";
        case 'chat_js':
            return "{$site_config['baseurl']}/js/1/chat_b03969c6.min.js";
        case 'chat_log_js':
            return "{$site_config['baseurl']}/js/1/chat_log_5de5da99.min.js";
        case 'index_js':
            return "{$site_config['baseurl']}/js/1/index_830dbda9.min.js";
        case 'captcha2_js':
            return "{$site_config['baseurl']}/js/1/captcha2_fd963759.min.js";
        case 'upload_js':
            return "{$site_config['baseurl']}/js/1/upload_36544f45.min.js";
        case 'request_js':
            return "{$site_config['baseurl']}/js/1/request_b31f9c02.min.js";
        case 'acp_js':
            return "{$site_config['baseurl']}/js/1/acp_e14d81c8.min.js";
        case 'userdetails_js':
            return "{$site_config['baseurl']}/js/1/userdetails_929235f1.min.js";
        case 'details_js':
            return "{$site_config['baseurl']}/js/1/details_ab50371b.min.js";
        case 'forums_js':
            return "{$site_config['baseurl']}/js/1/forums_63bf2819.min.js";
        case 'staffpanel_js':
            return "{$site_config['baseurl']}/js/1/staffpanel_6f692c72.min.js";
        case 'js':
            return "{$site_config['baseurl']}/js/1/js_fe0ee2eb.min.js";
        default:
            return null;
    }
}