<?php
function get_file($file) {
    global $site_config;
    $style = get_stylesheet();
    if (!empty($file)) {
        if ($site_config['in_production']) {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/2d1c17433419a62dfb77d409aadce6bd.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/cbffb589442223e6beb466aaac0fa684.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/161d8f5152ad6d72261b8f3198cf1090.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/1233ee2cc70185d8c9c567f3f2a5eb49.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/f075c8243b64b99bc4a85f59688bbc23.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/cc1b6a652ad4472bcf91b8f47dd4527d.min.css";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/ca955ec83ff7e25ea0085c077860ad6f.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/abfa0df75e42840eadd5153ba7384065.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/2cb70b339b9b1c67cc067157097a5151.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/bca31b72a1d2cc4b193cbeda516078aa.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/8068bfa6d6adcaea9fa103c1183a9c4e.min.js";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/936befd35c99dde2e1bd9bf7c95e14f2.min.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/7ac1fbcb7a786fe260eccbfecf8743d8.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/376e97f149e9cc6a927eeaa6db98ee0d.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/c479fd45164cb9c7b5837e176d1b0b6a.min.js";
                case 'faq_js':
                    return "{$site_config['baseurl']}/css/{$style}/e0aa4449e1144d138ea6e11ea1a8e284.min.js";
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
                    return "{$site_config['baseurl']}/css/{$style}/0fe1e27b21dcec30dcf4280ac6863365.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/bdbb071b65c19c8e1f26c35ed669b7d3.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/1136229325e431a12bd5a911b88a9aef.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/d1710dabf911b4ae8ad6a28b668e0f9e.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/ec85df3db23e876d55eafb37c5c3ea0b.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/dc6566fcad3d13024438ca75dd32d2cc.css";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/805fd136cea125751045cf700dc93edc.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/a4c172a85fb36c2b00a6ef229205a674.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/ac8a13b75e3169b022149c2b50c7f42d.js";
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
                case 'faq_js':
                    return "{$site_config['baseurl']}/css/{$style}/ef6642c41fbf7cfe2835053ab39d4c50.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/b507dbbb9dbc3fa55bae9d4fa752fbab.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/418463ca939f7c566b454e009a828c65.js";
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
