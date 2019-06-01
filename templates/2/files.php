<?php

declare(strict_types = 1);

function get_file_name($file)
{
    global $site_config;

    switch ($file) {
        case 'chat_css_trans':
            return "{$site_config['paths']['baseurl']}/css/2/chat_trans_15ad1ce1.min.css";
        case 'chat_css_uranium':
            return "{$site_config['paths']['baseurl']}/css/2/chat_uranium_d97e6628.min.css";
        case 'css':
            return "{$site_config['paths']['baseurl']}/css/2/css_550a66a0.min.css";
        case 'vendor_css':
            return "{$site_config['paths']['baseurl']}/css/2/vendor_d8ba1b57.min.css";
        case 'cookieconsent_css':
            return "{$site_config['paths']['baseurl']}/css/2/cookieconsent_b78749ad.min.css";
        case 'sceditor_css':
            return "{$site_config['paths']['baseurl']}/css/2/sceditor_d1996b4a.min.css";
        case 'main_css':
            return "{$site_config['paths']['baseurl']}/css/2/main_84fb5dfc.min.css";
        case 'main_js':
            return "{$site_config['paths']['baseurl']}/js/2/main_945e8bff.min.js";
        case 'vendor_js':
            return "{$site_config['paths']['baseurl']}/js/2/vendor_bb9d6646.min.js";
        case 'jquery_js':
            return "{$site_config['paths']['baseurl']}/js/2/jquery_d3af6efb.min.js";
        case 'chat_main_js':
            return "{$site_config['paths']['baseurl']}/js/2/chat_main_8bbd44d8.min.js";
        case 'chat_js':
            return "{$site_config['paths']['baseurl']}/js/2/chat_26d080f1.min.js";
        case 'chat_log_js':
            return "{$site_config['paths']['baseurl']}/js/2/chat_log_164d1955.min.js";
        case 'categories_js':
            return "{$site_config['paths']['baseurl']}/js/2/categories_da296021.min.js";
        case 'browse_js':
            return "{$site_config['paths']['baseurl']}/js/2/browse_1d6f09b3.min.js";
        case 'scroller_js':
            return "{$site_config['paths']['baseurl']}/js/2/scroller_fbb72693.min.js";
        case 'glider_js':
            return "{$site_config['paths']['baseurl']}/js/2/glider_a7a15288.min.js";
        case 'userdetails_js':
            return "{$site_config['paths']['baseurl']}/js/2/userdetails_5cbf56c5.min.js";
        case 'cookieconsent_js':
            return "{$site_config['paths']['baseurl']}/js/2/cookieconsent_93489228.min.js";
        case 'bookmarks_js':
            return "{$site_config['paths']['baseurl']}/js/2/bookmarks_009f9dba.min.js";
        case 'iframe_js':
            return "{$site_config['paths']['baseurl']}/js/2/iframe_054ae778.min.js";
        case 'navbar_show_js':
            return "{$site_config['paths']['baseurl']}/js/2/navbar_show_c10c3d01.min.js";
        case '':
            return "{$site_config['paths']['baseurl']}/";
        case 'sceditor_js':
            return "{$site_config['paths']['baseurl']}/js/2/sceditor_5ae8d5e7.min.js";
        case 'cheaters_js':
            return "{$site_config['paths']['baseurl']}/js/2/cheaters_c149df09.min.js";
        case 'user_search_js':
            return "{$site_config['paths']['baseurl']}/js/2/user_search_079ee015.min.js";
        case 'lightbox_js':
            return "{$site_config['paths']['baseurl']}/js/2/lightbox_ba35af93.min.js";
        case 'tooltipster_js':
            return "{$site_config['paths']['baseurl']}/js/2/tooltipster_1b527836.min.js";
        case 'site_config_js':
            return "{$site_config['paths']['baseurl']}/js/2/site_config_fbfb8c9d.min.js";
        case 'checkport_js':
            return "{$site_config['paths']['baseurl']}/js/2/checkport_77478e3b.min.js";
        case 'check_username_js':
            return "{$site_config['paths']['baseurl']}/js/2/check_username_81437da7.min.js";
        case 'check_password_js':
            return "{$site_config['paths']['baseurl']}/js/2/check_password_b75a02b3.min.js";
        case 'upload_js':
            return "{$site_config['paths']['baseurl']}/js/2/upload_c83bf1b1.min.js";
        case 'request_js':
            return "{$site_config['paths']['baseurl']}/js/2/request_fbfd456d.min.js";
        case 'parallax_js':
            return "{$site_config['paths']['baseurl']}/js/2/parallax_edc6d1f1.min.js";
        case 'acp_js':
            return "{$site_config['paths']['baseurl']}/js/2/acp_e14d81c8.min.js";
        case 'dragndrop_js':
            return "{$site_config['paths']['baseurl']}/js/2/dragndrop_5b0409c4.min.js";
        case 'details_js':
            return "{$site_config['paths']['baseurl']}/js/2/details_0edf47dd.min.js";
        case 'forums_js':
            return "{$site_config['paths']['baseurl']}/js/2/forums_1813a0dd.min.js";
        case 'pollsmanager_js':
            return "{$site_config['paths']['baseurl']}/js/2/pollsmanager_31b7b408.min.js";
        case 'trivia_js':
            return "{$site_config['paths']['baseurl']}/js/2/trivia_99c57301.min.js";
        default:
            return null;
    }
}
