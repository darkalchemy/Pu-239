<?php
function get_file($file) {
    global $site_config;
    $style = get_stylesheet();
    if (!empty($file)) {
        if ($site_config['in_production']) {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/a2f14a532be43ae9cf3e75238970547b.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/fcc3b455d661f93b0e92b19744de6d39.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/31f6c0ee784f416ec29c17b7b3ef9b9c.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/c7f85c890ddcadde4cd3f3badacef0a6.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/094420ed943e8ce616d5c114005b92fb.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/5dee196e8ff424a3980fa0b672787d36.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/2d28f39c1474c0e07517d68f18e88974.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/46ada7fb8831525d44d3168dc3cec47f.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/592eda4935d1ef20432a018178f0a489.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/f8d4243e12418125f8714d55569c7bb4.min.js";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/936befd35c99dde2e1bd9bf7c95e14f2.min.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/7ac1fbcb7a786fe260eccbfecf8743d8.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/520a6d3f067ad4decc45615721814c61.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/1d8eccbf32215cd1bac64a2802e14fb9.min.js";
                case 'faq_js':
                    return "{$site_config['baseurl']}/css/{$style}/e0aa4449e1144d138ea6e11ea1a8e284.min.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/4bd2b11d16f9048a1f7318a216382353.min.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/de8c9a9e792812564093f25a33fe67fe.min.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/a19a35e544f07fe730c1bac6be4c6444.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/e58eb622d355484ec7d7273c4a218678.min.js";
                default:
                    return '';
            }
        } else {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/6f959a49da09c4c67529d89e248ccbbd.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/0ec865231e25fc6bfe082eeb0e5cd609.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/cca124c81bd7186e6d8eb1bb583d7703.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/76bd8489fd5a44bdf6cb872a6c960004.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/334a6d6af3a883642e387571605d5b1f.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/e37d1b00971ebe03d118032f02918e24.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/a4c172a85fb36c2b00a6ef229205a674.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/7dbee50100d034706665eba73ead4720.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/e5a7028ceed4a74506b9edf36aaaf53c.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/d66c90e1824cd1ebed3df8f54f4b7a92.js";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/89fe72984f32ed9f02c73f05c4e6b8e9.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/31840d666e0737044502f628d404d1df.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/33b5518bc661ededd9702d16faedcac0.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/9dcd5077cc72666deae1170dfe727683.js";
                case 'faq_js':
                    return "{$site_config['baseurl']}/css/{$style}/ef6642c41fbf7cfe2835053ab39d4c50.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/b507dbbb9dbc3fa55bae9d4fa752fbab.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/a549e8e2c1c915d869068fb27cbb5e16.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/df64482a5151fb7175c4e1e8abd84ef9.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/f2c5c3286f9f1b3ca4dbb67356eae96a.js";
                default:
                    return '';
            }
        }
    }
}
