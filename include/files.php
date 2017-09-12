<?php
function get_file($file) {
    global $site_config;
    $style = get_stylesheet();
    if (!empty($file)) {
        if ($site_config['production']) {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/f6f22429c2c4e848e285616f1d173fdb.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/fd23322a9aecdf552abb9dbf0594aa65.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/676a8765fd55d727153cb9e58a31b418.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/fabd1e485fe73b8413e5582e735711c4.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/d69f2c2146849a79ae370e0a223d908c.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/e4d826df88da85d81855fa53c02087af.min.css";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/5ca10c4e0ef96bb6445932cd0486a6ec.min.css";
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
                    return "{$site_config['baseurl']}/css/{$style}/888120ce1b3a23707e2d49c698850e32.min.css";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/936befd35c99dde2e1bd9bf7c95e14f2.min.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/7ac1fbcb7a786fe260eccbfecf8743d8.min.js";
                case 'userblocks_css':
                    return "{$site_config['baseurl']}/css/{$style}/20b7201df0dfb6d3f9a14a2e99e8215d.min.css";
                case 'globalblocks_css':
                    return "{$site_config['baseurl']}/css/{$style}/9dfd83b9e59499bf7716ea6a32d81ef1.min.css";
                case 'upload_css':
                    return "{$site_config['baseurl']}/css/{$style}/16ba74f6ef84e55ebc931e13ef46be28.min.css";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/859c6d8f53d8ba83a5a80596082ae411.min.js";
                case 'bj_css':
                    return "{$site_config['baseurl']}/css/{$style}/406c410f1cbc7c730ab8c51f124f1967.min.css";
                case 'requests_css':
                    return "{$site_config['baseurl']}/css/{$style}/cc234532f5e31d9abe9e8d10b8f5b277.min.css";
                default:
                    return '';
            }
        } else {
            switch($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/3cee26e12fdbe5f5e006c4377aeeab6c.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/8b14bfc7e5bba792911826621b20f540.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/fb37a1914d51f9bc487b54b121d6028d.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/47cafc8e674fc97c64e23cb6c35be5eb.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/23a52335b2c956344882320b3f1c19a8.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/4e661e82f1a3fd5ff5aab0a8175a0644.css";
                case 'trivia_css':
                    return "{$site_config['baseurl']}/css/{$style}/193c0f2e4f8153fbf274c43db806cd3b.css";
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
                    return "{$site_config['baseurl']}/css/{$style}/95ae0bb1845c0f63b295dbecc2ed72c5.css";
                case 'pm_js':
                    return "{$site_config['baseurl']}/js/{$style}/89fe72984f32ed9f02c73f05c4e6b8e9.js";
                case 'warn_js':
                    return "{$site_config['baseurl']}/js/{$style}/31840d666e0737044502f628d404d1df.js";
                case 'userblocks_css':
                    return "{$site_config['baseurl']}/css/{$style}/8c3798e8a7b49810b725354ed08103fc.css";
                case 'globalblocks_css':
                    return "{$site_config['baseurl']}/css/{$style}/aa8f21dbda6ea407379ed4953f48e8ff.css";
                case 'upload_css':
                    return "{$site_config['baseurl']}/css/{$style}/1e9240e16d69d901ccd63097da7b87e9.css";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/8fb0cafc24a1e92bd0a50caccfe26519.js";
                case 'bj_css':
                    return "{$site_config['baseurl']}/css/{$style}/5147de5531166b423051f36cd7f8a175.css";
                case 'requests_css':
                    return "{$site_config['baseurl']}/css/{$style}/669490a15a05f7cd24021af15c45ef35.css";
                default:
                    return '';
            }
        }
    }
}
