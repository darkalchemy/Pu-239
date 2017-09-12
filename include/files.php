<?php
function get_file($file) {
    global $site_config;
    $style = get_stylesheet();
    if (!empty($file)) {
        if ($site_config['production']) {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/cad04d222e1d8a14cac97a58fe371585.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/fd23322a9aecdf552abb9dbf0594aa65.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/34f020afe4846d81cb78cd8a88bb2a64.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/c802e2633a390de7a06f38aeb8a1eba8.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/fa4698eaed7ce222182a40fa5a7ecda3.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/8fedd740cb2907c109403a13185045c2.min.css";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/6e687d65e9a4bca44ee411965da1d339.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/2d28f39c1474c0e07517d68f18e88974.min.js";
                case 'index_css':
                    return "{$site_config['baseurl']}/css/{$style}/12ee27333be9fe5ec379b4ad2663f3d2.min.css";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/46387be23f7a064e322957a121165120.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/ec52af588beb0882cd49865b619c2326.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/7106b1289a96cca0556c128cd75cf02d.min.js";
                case 'pm_css':
                    return "{$site_config['baseurl']}/css/{$style}/fef0e0a0a817772cfb73c7a918560f01.min.css";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/936befd35c99dde2e1bd9bf7c95e14f2.min.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/7ac1fbcb7a786fe260eccbfecf8743d8.min.js";
                default:
                    return '';
            }
        } else {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/a95b4bb26e940e6e734d8482e1023a6c.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/8b14bfc7e5bba792911826621b20f540.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/fb37a1914d51f9bc487b54b121d6028d.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/47cafc8e674fc97c64e23cb6c35be5eb.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/aa54a3e6c824c69739e0304da3165d5f.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/731cc817c6c2652e8205f1ff4f5b2eab.css";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/e8a2074dc237293861adc504a00138c8.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/a4c172a85fb36c2b00a6ef229205a674.js";
                case 'index_css':
                    return "{$site_config['baseurl']}/css/{$style}/5f2e2c7a8730d0910368c158695eed0f.css";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/1eaee3837b719499d7967a7f957ab5af.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/80396c584bddf0c78f6c1824f91b5408.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/e1a68e07f5e57869f03affbd8d218d68.js";
                case 'pm_css':
                    return "{$site_config['baseurl']}/css/{$style}/d072ab8ddd6309a73b8243ad21eba43f.css";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/89fe72984f32ed9f02c73f05c4e6b8e9.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/31840d666e0737044502f628d404d1df.js";
                default:
                    return '';
            }
        }
    }
}

