<?php
/**
 * @param $file
 *
 * @return string
 */
function get_file($file)
{
    global $site_config;
    $style = get_stylesheet();
    if ($style != 1) {
        return '';
    }
    if (!empty($file)) {
        if ($site_config['in_production']) {
            switch ($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/72ba2519dee3178f9a6080eb6ddf4bf7.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/5df7efd55633af695e59cb2aac02e923.min.js";
                case 'checkport_js':
                    return "{$site_config['baseurl']}/js/{$style}/4ba42503ffca4c65167590b15a03b842.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/4b7cbf5e86d3d9eb70ff7528523fcaab.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/9d1864f21ba3c43dfc84f4b9fc1a004c.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/1b65a2e4d308569d35d3baa36d2d8f51.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/53506f2a3f64e8334dda963048d45656.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/abfa0df75e42840eadd5153ba7384065.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/2a87242eb81a1876c98dc4e6af108f36.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/bca31b72a1d2cc4b193cbeda516078aa.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/235804f15af72aa795b25b30ba0e1f08.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/5d7606fe925ebf558dc656a91a03c767.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/ad403c0b871ca23f4cb71271f5885b1e.min.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/4bd2b11d16f9048a1f7318a216382353.min.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/4901c8acf2dbd75b2325da553afb7c12.min.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/26c3b79967b1fad03c4f21e9880b8410.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/d61dfbf2351bba26fe8a769392423113.min.js";
                default:
                    return '';
            }
        } else {
            switch ($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/88b7ad374be9cc202115337782f18157.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/3ee702eff7694e3393c4f1d0b86cae2c.js";
                case 'checkport_js':
                    return "{$site_config['baseurl']}/js/{$style}/4a99c7d4e3c8639af2775ef05d500598.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/3215eaf67c3e056b3d67b26a85d7080f.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/a3205d3fc5f4a9735f0f195417e59390.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/b96a319fae70ede9e502235b7231a569.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/7dccb3b61f08e1a49dc94844488faa32.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/a4c172a85fb36c2b00a6ef229205a674.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/59ced9883f674bfd099fb72bed746d63.js";
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
                    return "{$site_config['baseurl']}/js/{$style}/a069dd4b72e2605512d75dc6b1d8bb0a.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/44d555c268081133f47a9ab247ed95ca.js";
                default:
                    return '';
            }
        }
    }
}
