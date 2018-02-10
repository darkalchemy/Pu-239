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
                    return "{$site_config['baseurl']}/css/{$style}/f918c661f79de731da51b830c09ceca4.min.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/3203b7c1c099fada671a613ea8ec273c.min.js";
                case 'checkport_js':
                    return "{$site_config['baseurl']}/js/{$style}/4ba42503ffca4c65167590b15a03b842.min.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/b4c941b67d96a70d5919fd45b5f1e01f.min.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/9ab803b88ab1b8246fcd2418c3ebc335.min.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/00cf9d7f34eac8566d88fe9d089c1a22.min.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/baeae3d3875d8e74f52c1be091e95132.min.css";
                case 'trivia_js':
                    return "{$site_config['baseurl']}/js/{$style}/abfa0df75e42840eadd5153ba7384065.min.js";
                case 'index_js':
                    return "{$site_config['baseurl']}/js/{$style}/fcc398901c832611804121c3e9f510fe.min.js";
                case 'captcha1_js':
                    return "{$site_config['baseurl']}/js/{$style}/bca31b72a1d2cc4b193cbeda516078aa.min.js";
                case 'captcha2_js':
                    return "{$site_config['baseurl']}/js/{$style}/235804f15af72aa795b25b30ba0e1f08.min.js";
                case 'upload_js':
                    return "{$site_config['baseurl']}/js/{$style}/061ca68280ae9330d9ba42f701f71e3c.min.js";
                case 'requests_js':
                    return "{$site_config['baseurl']}/js/{$style}/465f086ebcf7464e59c3324773392351.min.js";
                case 'acp_js':
                    return "{$site_config['baseurl']}/css/{$style}/4bd2b11d16f9048a1f7318a216382353.min.js";
                case 'userdetails_js':
                    return "{$site_config['baseurl']}/js/{$style}/4901c8acf2dbd75b2325da553afb7c12.min.js";
                case 'details_js':
                    return "{$site_config['baseurl']}/js/{$style}/21d2b2d5235e82f6402164d283f8dfec.min.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/d61dfbf2351bba26fe8a769392423113.min.js";
                case 'staffpanel_js':
                    return "{$site_config['baseurl']}/js/{$style}/660dd9dc9b08b420a482b7908c575a76.min.js";
                case 'browse_js':
                    return "{$site_config['baseurl']}/js/{$style}/c0e8d1e5e323c7449617a762b2969198.min.js";
                default:
                    return null;
            }
        } else {
            switch ($file) {
                case 'css':
                    return "{$site_config['baseurl']}/css/{$style}/bcffe300f11fe0e2b5f2588215ddf8b1.css";
                case 'js':
                    return "{$site_config['baseurl']}/js/{$style}/bf6e22d3c396436bc7cb135e1e59a8e8.js";
                case 'checkport_js':
                    return "{$site_config['baseurl']}/js/{$style}/4a99c7d4e3c8639af2775ef05d500598.js";
                case 'chatjs':
                    return "{$site_config['baseurl']}/js/{$style}/ab6f4d2a04b073ba47f8278c6908cef0.js";
                case 'chat_log_js':
                    return "{$site_config['baseurl']}/js/{$style}/d2f1a903c3701b7ce37afe7b48676c6e.js";
                case 'chat_css_trans':
                    return "{$site_config['baseurl']}/css/{$style}/ad0ffbdf45769fb84717a596d1a6ab64.css";
                case 'chat_css_uranium':
                    return "{$site_config['baseurl']}/css/{$style}/b7b8c9712d162b2afdec218bb902b0e0.css";
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
                    return "{$site_config['baseurl']}/js/{$style}/468b7944a319cc511992a6b74b99ae30.js";
                case 'forums_js':
                    return "{$site_config['baseurl']}/js/{$style}/44d555c268081133f47a9ab247ed95ca.js";
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
