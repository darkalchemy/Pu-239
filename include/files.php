<?php
/**
 * @param $file
 *
 * @return string
 */
function get_file_name($file)
{
    global $site_config;

    $style = get_stylesheet();
    if (1 === $style && !empty($file)) {
        if ($site_config['in_production']) {
            switch ($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/8313b1e2e296c4f9f9a36183215dc939.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/ab55312e6ea1893c9a69353b69353626.min.js";
                case 'checkport_js':
                    return "{$site_config['baseurl']}/js/{$style}/4ba42503ffca4c65167590b15a03b842.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/df3227b4763454c4e378fb98f07ac3cb.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/728a623ec11809a591a03ea6daacce6a.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/00cf9d7f34eac8566d88fe9d089c1a22.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/baeae3d3875d8e74f52c1be091e95132.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/abfa0df75e42840eadd5153ba7384065.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/f72af936295090f3ca5fa3585a46f1be.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/bca31b72a1d2cc4b193cbeda516078aa.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/235804f15af72aa795b25b30ba0e1f08.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/a504f1a0ebd6bc7e9f01543c8c11c7bc.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/ea75f09f160393c00ea3b2e05a969ed0.min.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/4bd2b11d16f9048a1f7318a216382353.min.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/4901c8acf2dbd75b2325da553afb7c12.min.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/b5299c256b189f2dfbe6ed8e4f6bd88b.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/2f78b8ec930bb709bb8114c062f998fe.min.js";
                case 'staffpanel_js':
                    return "{$site_config['baseurl']}/js/{$style}/4851e0a230999f53a46119c110157989.min.js";
                case 'browse_js':
                    return "{$site_config['baseurl']}/js/{$style}/c0e8d1e5e323c7449617a762b2969198.min.js";
                default:
                    return null;
            }
        } else {
            switch ($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/cd2410488bbd6d074837c0ad0c5790a9.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/4e6c1b1f4f324181e81b53c6da23f542.js";
                case 'checkport_js':
                    return "{$site_config['baseurl']}/js/{$style}/4a99c7d4e3c8639af2775ef05d500598.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/5195fa11423331ec9d72a4fc947b09d0.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/9a59ab678ab62cf32c65704450bc6c25.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/ad0ffbdf45769fb84717a596d1a6ab64.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/b7b8c9712d162b2afdec218bb902b0e0.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/a4c172a85fb36c2b00a6ef229205a674.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/f9567fbf7012cbd82aa2ff70c4c4226a.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/421d04585d4091db9268de6db0f2bc65.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/becc3f1a23ee07159e177a21b2d9dd9e.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/562e3b9f1b437cb1ad1b85b12f7eb260.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/ca4527c605ef9a28153794d83ac62d15.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/b507dbbb9dbc3fa55bae9d4fa752fbab.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/2032e11580aaee0e87464cbfbacfc277.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/f0a5a109311c2c6a392a75be553ff6d8.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/89f1dedae45a25f8e0165758411321ce.js";
                case 'staffpanel_js':
                    return "{$site_config['baseurl']}/js/{$style}/0e6c0a3138d3efe7fdd4ff7e1e669f3a.js";
                case 'browse_js':
                    return "{$site_config['baseurl']}/js/{$style}/eb2fe8334478d94a4a294acec2d8cf09.js";
                default:
                    return null;
            }
        }
    }

    return null;
}
