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
    if ($style === 1 && !empty($file)) {
        if ($site_config['in_production']) {
            switch ($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/dfcca84290e6b17ab828c8c1ca87f060.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/b39cfe659667b801c122db91c1dc570e.min.js";
                case 'checkport_js':
                    return "{$site_config['baseurl']}/js/{$style}/4ba42503ffca4c65167590b15a03b842.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/7fb0d8c6215d903bbf16f43641d0a919.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/c2954cf71bde2ce5d7ce08497dccb06d.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/00cf9d7f34eac8566d88fe9d089c1a22.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/baeae3d3875d8e74f52c1be091e95132.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/abfa0df75e42840eadd5153ba7384065.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/734cb25df9f80f35967d30a9b371da37.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/baec24224b2573ea63563552bcaec948.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/a504f1a0ebd6bc7e9f01543c8c11c7bc.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/26711108b3fa8ca5fa6928f932a6d446.min.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/4bd2b11d16f9048a1f7318a216382353.min.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/bcd7ea6fc7f5952ae4b7accea3e3d5f4.min.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/4e83c183238c52403a21283ea3aa13ba.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/5d19c115cca0a2509afdf80bf9d6480a.min.js";
                case 'staffpanel_js':
                    return "{$site_config['baseurl']}/js/{$style}/8bfcb4772b1c260338b2229b276df026.min.js";
                case 'browse_js':
                    return "{$site_config['baseurl']}/js/{$style}/c0e8d1e5e323c7449617a762b2969198.min.js";
                default:
                    return null;
            }
        } else {
            switch ($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/9291e068e4c72af76fcb7766ea74e539.css";
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
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/895cc8b15a1b6299037181aa27d6e0b8.js";
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
                    return "{$site_config['baseurl']}/js/{$style}/12345.js";
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
