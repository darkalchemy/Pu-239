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
    if ($caption) {
        $htmlout .= "<h2>$caption</h2>\n";
    }
    if ($center) {
        $tdextra .= '';
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
    $htmlout .= "<table class='sucks table table-bordered{$stripe}'>\n";

    return $htmlout;
}

/**
 * @return string
 */
function end_table()
{
    return "</table>\n";
}

/**
 * @param     $x
 * @param     $y
 * @param int $noesc
 *
 * @return string
 */
function tr($x, $y, $noesc = 0)
{
    if ($noesc) {
        $a = $y;
    } else {
        $a = htmlsafechars($y);
        $a = str_replace("\n", "<br>\n", $a);
    }

    return "
        <tr>
            <td class='rowhead'>
                $x
            </td>
            <td>
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
        $htmlout .= "<tr><td>$code</td><td><img src=\"{$site_config['pic_baseurl']}smilies/{$url}\" alt='' /></td></tr>\n";
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
function main_table($body, $header = null, $class = null, $wrapper_class = null)
{
    $thead = $header != null ? "
                        <thead>
                            $header
                        </thead>" : '';

    return "
                <div class='table-wrapper $wrapper_class'>
                    <table class='table table-bordered table-striped $class'>
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
                <div class='bordered bg-00 $class'>
                    <div class='alt_bordered'>
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
            <div class='container is-fluid portlet $class'>
                $text
            </div>";
    }
}

/**
 * @param $data
 */
function write_css($data)
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
    file_put_contents(ROOT_DIR . 'chat/css/classcolors.css', $classdata . PHP_EOL);
    file_put_contents(ROOT_DIR . 'templates/1/css/classcolors.css', $classdata . PHP_EOL);
}

function write_classes($data)
{
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

function write_class_files()
{
    global $site_config, $fluent;

    $lang = load_language('ad_class_config');

    $t = 'define(';
    $configfile = '<' . $lang['classcfg_file_created'] . date('M d Y H:i:s') . $lang['classcfg_user_cfg'];
    $res = $fluent->from('class_config')
        ->orderBy('value ASC');
    $the_names = $the_colors = $the_images = '';
    foreach ($res as $arr) {
        $configfile .= '' . $t . "'{$arr['name']}', {$arr['value']});\n";
        if ($arr['name'] !== 'UC_STAFF' && $arr['name'] !== 'UC_MIN' && $arr['name'] !== 'UC_MAX') {
            $the_names .= "{$arr['name']} => '{$arr['classname']}',";
            $the_colors .= "{$arr['name']} => '{$arr['classcolor']}',";
            $the_images .= "{$arr['name']} => " . '$site_config[' . "'pic_baseurl'" . ']' . " . 'class/{$arr['classpic']}',";
            $js_classes[] = $arr['name'];
            $data[] = [
                'className' => $arr['classname'],
                'classColor' => '#' . $arr['classcolor'],
            ];
        }
        $classes[] = "var {$arr['name']} = {$arr['value']};";
    }

    file_put_contents(ROOT_DIR . 'chat/js/classes.js', implode("\n", $classes));
    write_classes($js_classes);
    write_css($data);
    $configfile .= get_cache_config_data($the_names, $the_colors, $the_images);
    file_put_contents(CACHE_DIR . 'class_config.php', $configfile);
}
