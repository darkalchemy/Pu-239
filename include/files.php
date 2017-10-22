<?php
function get_file($file) {
    global $site_config;
    $style = get_stylesheet();
    if (!empty($file)) {
        if ($site_config['in_production']) {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/a989f000aa998cd85b9c517dc8355880.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/fb66d2e1826bcd43b9dbd959e79f8a30.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/e1be3ab27b0f675bba0144fb3a8a52cd.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/aa522d932f08c78c32ff1fc1e28d6167.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/2df51636db9a28d5661a6e6dd846ca5a.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/3476f04f128c2a3a3ac05160b1954ca4.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/2d28f39c1474c0e07517d68f18e88974.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/46ada7fb8831525d44d3168dc3cec47f.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/18d0e43301d28b2090751e0210e4fff3.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/5a6ceecb5c88300a01cf08a64fac6b9b.min.js";
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
                    return "{$site_config['baseurl']}/js/{$style}/32dbd5455e43945a91592a3650a03d21.min.js";
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
                    return "{$site_config['baseurl']}/css/{$style}/79d5419288d8eb15f9317b1ba4713791.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/1b60f44c0383f30c0e247dbf4bfcfba9.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/1136229325e431a12bd5a911b88a9aef.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/d1710dabf911b4ae8ad6a28b668e0f9e.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/59f034b1d3416f97907434bc47ef1a89.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/0a8e462766346f32c0f4ec3256635748.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/a4c172a85fb36c2b00a6ef229205a674.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/7dbee50100d034706665eba73ead4720.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/1557552471b322c8660d996d7e9e39bf.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/f1cfcbaa990460b2ad1280997a1e9419.js";
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
                    return "{$site_config['baseurl']}/js/{$style}/418463ca939f7c566b454e009a828c65.js";
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
