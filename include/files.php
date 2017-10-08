<?php
function get_file($file) {
    global $site_config;
    $style = get_stylesheet();
    if (!empty($file)) {
        if ($site_config['production']) {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/61fd38737739b0d08222379b51b76fc8.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/526185e700a2943070a9d1d62b4cfbb0.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/676a8765fd55d727153cb9e58a31b418.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/fabd1e485fe73b8413e5582e735711c4.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/efa78d44ba0d1afbd8b710cf279c76ca.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/c2f605d24b18ed355e6f0a680ce59421.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/2d28f39c1474c0e07517d68f18e88974.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/46ada7fb8831525d44d3168dc3cec47f.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/ec52af588beb0882cd49865b619c2326.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/7106b1289a96cca0556c128cd75cf02d.min.js";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/936befd35c99dde2e1bd9bf7c95e14f2.min.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/7ac1fbcb7a786fe260eccbfecf8743d8.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/f186bf829ef2ab9393c9f28fad62a689.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/f3ffa3fcf723fbed228b27dc0c1730ab.min.js";
                case 'faq_js':
                    return "{$site_config['baseurl']}/css/{$style}/e0aa4449e1144d138ea6e11ea1a8e284.min.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/4bd2b11d16f9048a1f7318a216382353.min.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/de8c9a9e792812564093f25a33fe67fe.min.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/045b89f840426f848f8ef22c19ca1088.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/38976eae7bc471e1913cc0830406b548.min.js";
                default:
                    return '';
            }
        } else {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/9a030725fc38af73cd80cbde29209ae7.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/a67b55bcb6974fa4b1012dd9ea864c9f.js";
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
