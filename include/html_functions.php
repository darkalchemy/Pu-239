<?php

/**
 * @return string
 */
function begin_main_frame()
{
    return "
            <table class='table table-bordered table-striped'>
                <tr>
                    <td class='embedded'>";
}

/**
 * @return string
 */
function end_main_frame()
{
    return '
                    </td>
                </tr>
            </table>';
}

/**
 * @param string $caption
 * @param bool   $center
 * @param int    $padding
 *
 * @return string
 */
function begin_frame($caption = '', $center = false, $padding = 10)
{
    $tdextra = '';
    $htmlout = '';
    $center = $center ? " class='has-text-centered'" : '';
    if ($caption) {
        $htmlout .= "<h1{$center}>$caption</h1>";
    }
    $htmlout .= "<table class='shit table table-bordered table-striped'><tr><td$tdextra>\n";

    return $htmlout;
}

/**
 * @param int $padding
 */
function attach_frame($padding = 10)
{
    echo "</td></tr><tr><td style='border-top: 0'>\n";
}

/**
 * @return string
 */
function end_frame()
{
    return "</td></tr></table>\n";
}

/**
 * @param bool $striped
 *
 * @return string
 */
function begin_table($striped = false)
{
    $htmlout = '';
    $stripe = $striped === true ? ' table-striped' : '';
    $htmlout .= "<table class='table table-bordered{$stripe}'>\n";

    return $htmlout;
}

/**
 * @return string
 */
function end_table()
{
    return "</table>\n";
}

function tr($x, $y, $noesc = false, $class = '')
{
    if ($noesc) {
        $a = $y;
    } else {
        $a = htmlsafechars($y);
        $a = str_replace("\n", "<br>\n", $a);
    }

    $class = !empty($class) ? " class='$class'" : '';

    return "
        <tr>
            <td class='rowhead'>
                $x
            </td>
            <td{$class}>
                $a
            </td>
        </tr>";
}

/**
 * @return string
 */
function insert_smilies_frame()
{
    global $smilies, $site_config;
    $htmlout = '';
    $htmlout .= begin_frame('Smilies', true);
    $htmlout .= begin_table(false);
    $htmlout .= "<tr><td class='colhead'>Type...</td><td class='colhead'>To make a...</td></tr>\n";
    foreach ($smilies as $code => $url) {
        $htmlout .= "<tr><td>$code</td><td><img src=\"{$site_config['pic_baseurl']}smilies/{$url}\" alt=''></td></tr>\n";
    }
    $htmlout .= end_table();
    $htmlout .= end_frame();

    return $htmlout;
}

/**
 * @param      $body
 * @param null $header
 * @param null $class
 * @param null $wrapper_class
 *
 * @return string
 */
function main_table($body, $header = null, $class = null, $wrapper_class = null, $striped = 'table-striped', $id = null)
{
    $id = !empty($id) ? " id='$id'" : '';
    $thead = $header != null ? "
                        <thead>
                            $header
                        </thead>" : '';

    return "
                <div class='table-wrapper $wrapper_class'>
                    <table{$id} class='table table-bordered $striped $class'>
                        $thead
                        <tbody>
                            $body
                        </tbody>
                    </table>
                </div>";
}

/**
 * @param $text
 *
 * @return string|void
 */
function main_div($text, $class = null)
{
    if ($text === '') {
        return;
    } else {
        return "
                <div class='bordered $class'>
                    <div class='alt_bordered bg-00'>
                        $text
                    </div>
                </div>";
    }
}

/**
 * @param        $text
 * @param string $class
 *
 * @return string|void
 */
function wrapper($text, $class = '')
{
    if ($text === '') {
        return;
    } else {
        return "
            <div class='portlet $class'>
                $text
            </div>";
    }
}

/**
 * @param $data
 */
function write_css($data, $template)
{
    $classdata = '';
    foreach ($data as $class) {
        $cname = str_replace(' ', '_', strtolower($class['className']));
        $ccolor = strtolower($class['classColor']);
        if (!empty($cname)) {
            //$classdata .= "#content .{$cname} {
            $classdata .= ".{$cname} {
    color: $ccolor;
}
";
        }
    }
    $classdata .= '#content .chatbot {
    color: #ff8b49;
    text-shadow: -1px 0 black, 0 1px black, 1px 0 black, 0 -1px black;
}
';
    foreach ($data as $class) {
        $cname = str_replace(' ', '_', strtolower($class['className']));
        if (!empty($cname)) {
            $classdata .= "#content #chatList span.{$cname} {
    font-weight: bold;
}
";
        }
    }
    $classdata .= '#content #chatList span.chatbot {
    font-weight: bold;
    font-style: italic;
}
';
    foreach ($data as $class) {
        $cname = str_replace(' ', '_', strtolower($class['className']));
        $ccolor = strtolower($class['classColor']);
        if (!empty($cname)) {
            $classdata .= ".{$cname}_bk {
    background-color: $ccolor;
}
";
        }
    }
    file_put_contents(ROOT_DIR . "chat/css/{$template}/classcolors.css", $classdata . PHP_EOL);
    file_put_contents(ROOT_DIR . "templates/{$template}/css/classcolors.css", $classdata . PHP_EOL);
}

function write_classes($data, $classes)
{
    $html = file_get_contents(CHAT_DIR . 'js/config.js');
    $classes = "bbCodeTags: [\n        'b',\n        'i',\n        'u',\n        'quote',\n        'code',\n        'color',\n        'url',\n        'img',\n        'chatbot',\n        'level',\n        'updown',\n        'video'," . "\n        '" . implode("',\n        '", $classes) . "'\n    ],";
    $html = preg_replace('/(bbCodeTags:\s+\[.*?\],)/s', $classes, $html);
    file_put_contents(CHAT_DIR . 'js/config.js', $html);

    $text = '

ajaxChat.getRoleClass = function(roleID) {
    switch (parseInt(roleID)) {';
    foreach ($data as $class) {
        $text .= "
        case parseInt($class):
            return '" . strtolower(str_replace('UC_', '', $class)) . "';";
    }
    $text .= "
        case parseInt(ajaxChat.chatBotRole):
            return 'chatbot';
        default:
            return 'user';
    }
};";

    file_put_contents(ROOT_DIR . 'chat/js/classes.js', $text, FILE_APPEND);
}

function write_class_files($template)
{
    global $site_config, $fluent;

    $t = 'define(';
    $configfile = "<?php\n\n";
    $res = $fluent->from('class_config')
        ->orderBy('value ASC')
        ->where('template = ?', $template);
    $the_names = $the_colors = $the_images = '';
    foreach ($res as $arr) {
        $configfile .= $t . "'{$arr['name']}', {$arr['value']});\n";
        if ($arr['name'] !== 'UC_STAFF' && $arr['name'] !== 'UC_MIN' && $arr['name'] !== 'UC_MAX') {
            $js_classes[] = $arr['name'];
            $config_classes[] = strtolower(str_replace(' ', '_', $arr['classname']));
            $data[] = [
                'className' => $arr['classname'],
                'classColor' => '#' . $arr['classcolor'],
            ];
        }
        $classes[] = "var {$arr['name']} = {$arr['value']};";
    }

    file_put_contents(ROOT_DIR . 'chat/js/classes.js', implode("\n", $classes) . PHP_EOL);
    write_classes($js_classes, $config_classes);
    write_css($data, $template);
    file_put_contents(CACHE_DIR . 'class_config.php', $configfile . PHP_EOL);
}

function clear_image_cache()
{
    global $cache;

    $cache->deleteMulti([
        'lastest_tor_',
        'last5_tor_',
        'top5_tor_',
        'scroll_tor_',
        'slider_tor_',
        'torrent_poster_count_',
        'torrent_banner_count_',
        'backgrounds_',
        'posters_',
    ]);
}

function placeholder_image($size = 10)
{
    global $cache;

    $image = $cache->get('placeholder_image_' . $size);
    if ($image === false || is_null($image)) {
        $image_proxy = new DarkAlchemy\Pu239\ImageProxy();
        $image = $image_proxy->create_image($size, $size, '#7d7e7d');
        $image = 'data:image/jpeg;base64, ' . base64_encode($image);
        $cache->set('placeholder_image_' . $size, $image, 0);
    }

    return $image;
}

function validate_url($url)
{
    require_once INCL_DIR . 'bbcode_functions.php';

    $url = format_comment_no_bbcode($url);
    $url = filter_var($url, FILTER_SANITIZE_URL);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }

    if (preg_match("/^https?:\/\/$/i", $url) || preg_match('/[&;]/', $url) || preg_match('#javascript:#is', $url) || !preg_match("#^https?://(?:[^<>*\"]+|[a-z0-9/\._\-!]+)$#iU", $url)) {
        return null;
    }

    return $url;
}

function doc_head()
{
    global $site_config;

    return "<!doctype html>
<html>
<head>
    <!--[if lt IE 9]><script src='//html5shim.googlecode.com/svn/trunk/html5.js'></script><![endif]-->
    <meta charset='{$site_config['char_set']}'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta property='og:url' content='{$site_config['baseurl']}'>
    <meta property='og:type' content='website'>
    <meta property='og:description' content='{$site_config['domain']} - {$site_config['site_name']}'>";
}
