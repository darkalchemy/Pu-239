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

function tr($x, $y, $noesc = false)
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

function write_classes($data, $classes)
{
    $html = file_get_contents(CHAT_DIR . 'js/config.js');
    $classes = "bbCodeTags: [\n        'b',\n        'i',\n        'u',\n        'quote',\n        'code',\n        'color',\n        'url',\n        'img',\n        'chatbot',\n        'updown',\n        'video'," . "\n        '" . implode("',\n        '", $classes) . "'\n    ],";
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

function write_class_files()
{
    global $site_config, $fluent;

    $lang = load_language('ad_class_config');

    $t = 'define(';
    $configfile = '<' . $lang['classcfg_file_created'] . date('M d Y H:i:s') . $lang['classcfg_user_cfg'];
    $configfile = "<?php\n\n";
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
    write_css($data);
    $configfile .= get_cache_config_data($the_names, $the_colors, $the_images);
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

function comment_box($tid, $name)
{
    global $site_config, $lang;

    $comment_box = "
    <a name='startcomments'></a>
    <form name='comment' method='post' action='{$site_config['baseurl']}/comment.php?action=add&amp;tid=$tid'>
		<div class='has-text-centered'>
			<div class='size_6'>{$lang['details_comments']}:</div>
			<h1><a href='{$site_config['baseurl']}/details.php?id=$tid'> " . htmlsafechars($name, ENT_QUOTES) . " </a></h1>
		</div>
		<div class='bg-02 round10'>
			<div class='level-center'>
				<a href='{$site_config['baseurl']}/comment.php?action=add&amp;tid=$tid' class='button is-small tooltipper' title='Use the BBCode Editor'>BBcode Editor</a>
				<a href='{$site_config['baseurl']}/takethankyou.php?id=" . $tid . "'>
					<img src='{$site_config['pic_baseurl']}smilies/thankyou.gif' class='tooltipper' alt='Thank You' title='Give a quick \"Thank You\"' />
				</a>
			</div>
			<textarea name='body' class='w-100' rows='6'></textarea>
			<input type='hidden' name='tid' value='" . htmlsafechars($tid) . "' />
			<div class='has-text-centered'>
				<a href=\"javascript:SmileIT(':-)','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/smile1.gif' alt='Smile' class='tooltipper' title='Smile' /></a>
				<a href=\"javascript:SmileIT(':smile:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/smile2.gif' alt='Smiling' class='tooltipper' title='Smiling' /></a>
				<a href=\"javascript:SmileIT(':-D','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/grin.gif' alt='Grin' class='tooltipper' title='Grin' /></a>
				<a href=\"javascript:SmileIT(':lol:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/laugh.gif' alt='Laughing' class='tooltipper' title='Laughing' /></a>
				<a href=\"javascript:SmileIT(':w00t:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/w00t.gif' alt='W00t' class='tooltipper' title='W00t' /></a>
				<a href=\"javascript:SmileIT(':blum:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/blum.gif' alt='Rasp' class='tooltipper' title='Rasp' /></a>
				<a href=\"javascript:SmileIT(';-)','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/wink.gif' alt='Wink' class='tooltipper' title='Wink' /></a>
				<a href=\"javascript:SmileIT(':devil:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/devil.gif' alt='Devil' class='tooltipper' title='Devil' /></a>
				<a href=\"javascript:SmileIT(':yawn:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/yawn.gif' alt='Yawn' class='tooltipper' title='Yawn' /></a>
				<a href=\"javascript:SmileIT(':-/','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/confused.gif' alt='Confused' class='tooltipper' title='Confused' /></a>
				<a href=\"javascript:SmileIT(':o)','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/clown.gif' alt='Clown' class='tooltipper' title='Clown' /></a>
				<a href=\"javascript:SmileIT(':innocent:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/innocent.gif' alt='Innocent' class='tooltipper' title='innocent' /></a>
				<a href=\"javascript:SmileIT(':whistle:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/whistle.gif' alt='Whistle' class='tooltipper' title='Whistle' /></a>
				<a href=\"javascript:SmileIT(':unsure:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/unsure.gif' alt='Unsure' class='tooltipper' title='Unsure' /></a>
				<a href=\"javascript:SmileIT(':blush:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/blush.gif' alt='Blush' class='tooltipper' title='Blush' /></a>
				<a href=\"javascript:SmileIT(':hmm:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/hmm.gif' alt='Hmm' class='tooltipper' title='Hmm' /></a>
				<a href=\"javascript:SmileIT(':hmmm:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/hmmm.gif' alt='Hmmm' class='tooltipper' title='Hmmm' /></a>
				<a href=\"javascript:SmileIT(':huh:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/huh.gif' alt='Huh' class='tooltipper' title='Huh' /></a>
				<a href=\"javascript:SmileIT(':look:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/look.gif' alt='Look' class='tooltipper' title='Look' /></a>
				<a href=\"javascript:SmileIT(':rolleyes:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/rolleyes.gif' alt='Roll Eyes' class='tooltipper' title='Roll Eyes' /></a>
				<a href=\"javascript:SmileIT(':kiss:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/kiss.gif' alt='Kiss' class='tooltipper' title='Kiss' /></a>
				<a href=\"javascript:SmileIT(':blink:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/blink.gif' alt='Blink' class='tooltipper' title='Blink' /></a>
				<a href=\"javascript:SmileIT(':baby:','comment','body')\"><img src='{$site_config['pic_baseurl']}smilies/baby.gif' alt='Baby' class='tooltipper' title='Baby' /></a>
			</div>
			<div class='has-text-centered'>
				<input class='button is-small margin20' type='submit' value='Submit' />
			</div>
		</div>
    </form>";

    return $comment_box;
}
