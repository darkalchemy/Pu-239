<?php
function get_file($file) {
    global $site_config;
    $style = get_stylesheet();
    if ($style != 1) {
        return '';
    }
    if (!empty($file)) {
        if ($site_config['in_production']) {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/cf19fb2e1545b0ff42e0c397f171de4a.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/24931a60a8599b97c77ee4605c40ea2a.min.js";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/ca955ec83ff7e25ea0085c077860ad6f.min.css";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/6cc3084698ee1469d7b69c25b0872b38.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/816bfbda1c4e4b3db2b41280a3a8e43d.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/b6616c56e035202172f35e9d47c734fb.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/a3f395d394a443e2af56b2492c693f70.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/abfa0df75e42840eadd5153ba7384065.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/d72771265bf2860268f46c2330b43408.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/bca31b72a1d2cc4b193cbeda516078aa.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/8068bfa6d6adcaea9fa103c1183a9c4e.min.js";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/936befd35c99dde2e1bd9bf7c95e14f2.min.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/7ac1fbcb7a786fe260eccbfecf8743d8.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/943e67e27ce18f719adcab9dcb3fe779.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/c479fd45164cb9c7b5837e176d1b0b6a.min.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/4bd2b11d16f9048a1f7318a216382353.min.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/32dbd5455e43945a91592a3650a03d21.min.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/a19a35e544f07fe730c1bac6be4c6444.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/b4c8762dd9f5e7c0cd7d2147f16f7a00.min.js";
                default:
                    return '';
            }
        } else {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/80236cf0cb25da4588bda11a1edeb2a5.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/217069436e7586857c1d4468dc87b09f.js";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/4054f744412d7042f2fd91e143b0032c.css";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/1136229325e431a12bd5a911b88a9aef.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/d1710dabf911b4ae8ad6a28b668e0f9e.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/ec39d717fe1d68fd68547f5e1c7f3d04.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/871aff9528c619cb559b62884a26895d.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/a4c172a85fb36c2b00a6ef229205a674.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/311ce977a5e69efe1bfc14d34d013e04.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/421d04585d4091db9268de6db0f2bc65.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/029d354c993a8b46d85d9cde58bb2f28.js";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/89fe72984f32ed9f02c73f05c4e6b8e9.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/31840d666e0737044502f628d404d1df.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/d59fe8aa62f9b2318e3da76e4f043202.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/3e85fff3424b5e32cb6e7649bbe3073f.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/b507dbbb9dbc3fa55bae9d4fa752fbab.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/8f69172ed0680308cf8af14ab9855bc3.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/df64482a5151fb7175c4e1e8abd84ef9.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/a60c331ea10675d31582e269e7eb2d20.js";
                default:
                    return '';
            }
        }
    }
}
