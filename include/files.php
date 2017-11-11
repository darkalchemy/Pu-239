<?php
/**
 * @param $file
 *
 * @return string
 */
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
                    return "{$site_config['baseurl']}/css/{$style}/89ab6bb8f9fb7f4cd6655505b304255d.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/7a17c878ab17c6ecd0044290542477e7.min.js";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/c973a87cd5680c3aa1b7ab1656bf3d6d.min.css";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/5b1af6b3c5b43d3b626c2b17141827ac.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/06088dbcdc69a0586ed7cebe5b469168.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/b6616c56e035202172f35e9d47c734fb.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/a3f395d394a443e2af56b2492c693f70.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/abfa0df75e42840eadd5153ba7384065.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/2a87242eb81a1876c98dc4e6af108f36.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/bca31b72a1d2cc4b193cbeda516078aa.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/8068bfa6d6adcaea9fa103c1183a9c4e.min.js";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/7ac1fbcb7a786fe260eccbfecf8743d8.min.js";
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
                    return "{$site_config['baseurl']}/js/{$style}/ba6290f6c00d0999ef9c2f0bed8fbd3b.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/d61dfbf2351bba26fe8a769392423113.min.js";
                default:
                    return '';
            }
        } else {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/2c9fdef5eca363911c69a404210cc0cc.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/6de8c0e711d4bbefb270ce62cb3e8f8a.js";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/f74772de6e1c0f294ed61731d58fcb4c.css";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/2b7a7f823026fa5d8bc3ed3892b1f721.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/661d188734600d6381f8c7f8dfd8ef7e.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/ec39d717fe1d68fd68547f5e1c7f3d04.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/871aff9528c619cb559b62884a26895d.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/a4c172a85fb36c2b00a6ef229205a674.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/59ced9883f674bfd099fb72bed746d63.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/421d04585d4091db9268de6db0f2bc65.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/029d354c993a8b46d85d9cde58bb2f28.js";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/31840d666e0737044502f628d404d1df.js";
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
                    return "{$site_config['baseurl']}/js/{$style}/875dbeb9ab8e1be3e26af08c672ed2b7.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/44d555c268081133f47a9ab247ed95ca.js";
                default:
                    return '';
            }
        }
    }
}
