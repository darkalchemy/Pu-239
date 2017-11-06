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
                    return "{$site_config['baseurl']}/css/{$style}/27a87bc80b39225109375acb245cee28.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/2337b97e2915911ced75ca707add45c2.min.js";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/c973a87cd5680c3aa1b7ab1656bf3d6d.min.css";
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
                    return "{$site_config['baseurl']}/js/{$style}/85aa624855439e3691bfbea38d44acc8.min.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/7ac1fbcb7a786fe260eccbfecf8743d8.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/eebeb32f4ddfdcccbfc94d13d1e5384a.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/ad403c0b871ca23f4cb71271f5885b1e.min.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/4bd2b11d16f9048a1f7318a216382353.min.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/ab146cfa6d3b58c82b4914a0ba02ddee.min.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/a19a35e544f07fe730c1bac6be4c6444.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/3da55a29a58bc753c671c5ec000bb925.min.js";
                default:
                    return '';
            }
        } else {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/2491a871b6e520318a203333340afd54.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/decd046c7b35303dcdfe2efce39cbb3e.js";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/f74772de6e1c0f294ed61731d58fcb4c.css";
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
                    return "{$site_config['baseurl']}/js/{$style}/d8ed260b6d143e2d4c13b5550dd5358c.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/31840d666e0737044502f628d404d1df.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/562e3b9f1b437cb1ad1b85b12f7eb260.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/ca4527c605ef9a28153794d83ac62d15.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/b507dbbb9dbc3fa55bae9d4fa752fbab.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/8f69172ed0680308cf8af14ab9855bc3.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/df64482a5151fb7175c4e1e8abd84ef9.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/2fa7d63bb8eeae1cde16ec932ebfc77c.js";
                default:
                    return '';
            }
        }
    }
}
