<?php
require_once 'emoticons.php';
function source_highlighter($source, $lang2geshi)
{
    require_once 'geshi/geshi.php';
    $source = str_replace([
        '&#039;',
        '&gt;',
        '&lt;',
        '&quot;',
        '&amp;',
    ], [
        "'",
        '>',
        '<',
        '"',
        '&',
    ], $source);
    $lang2geshi = ($lang2geshi == 'html' ? 'html4strict' : $lang2geshi);
    $geshi = new GeSHi($source, $lang2geshi);
    $geshi->set_header_type(GESHI_HEADER_PRE_VALID);
    $geshi->set_overall_style('font: normal normal 100% monospace; color: #000066;', false);
    $geshi->set_line_style('color: #003030;', 'font-weight: bold; color: #006060;', true);
    $geshi->set_code_style('color: #000020;font-family:monospace; font-size:12px;line-height:13px;', true);
    $geshi->enable_classes(false);
    $geshi->set_link_styles(GESHI_LINK, 'color: #000060;');
    $geshi->set_link_styles(GESHI_HOVER, 'background-color: #f0f000;');
    $return = "<div class=\"codetop\">Code</div><div class=\"codemain\">\n";
    $return .= $geshi->parse_code();
    $return .= "\n</div>\n";

    return $return;
}

//=== Inserts  smilies frame and smilie set: use by $use_this = smilies_frame($smilies,4,':thankyou:');
// $smilies_set > from emoticons
// 4 > number of columns
// $last_smilie_and_stop > blank to show all smilies, or smilie code to stop at say :thankyou: that is too big to display in the div
function smilies_frame($smilies_set)
{
    global $smilies, $customsmilies, $staff_smilies;
    $emoticons = '';

    foreach ($smilies_set as $code => $url) {
        $list .= "
            <div class='container-flex'>
                <a href='#' title='{$code}'>
                    <img border='0' src='./images/smilies/" . $url . "' alt='' />
                </a>
            </div>";
    }

    $emoticons = "
        <div class='flex-grid emoticons'>
            $list
        </div>";

    return $emoticons;
}

//=== BBcode function will add a BBcode markup text area with smilies frame and tags if Javascript is enabled if not, it will just make a text area
function BBcode($body)
{
    global $CURUSER, $smilies, $customsmilies, $staff_smilies, $INSTALLER09;
    $emoticons_normal = smilies_frame($smilies, 3, ':hslocked:');
    $emoticons_custom = smilies_frame($customsmilies, 3, ':wink_skull:');
    $emoticons_staff = smilies_frame($staff_smilies, 1, ':dabunnies:');
    $tags = '
            <tr>
                <td>not yet added</td>
            </tr>';

    $bbcode = '
        <table>
            <tr>
                <td id="tblDefects">
                    <textarea id="bbcode" name="body" cols="80" rows="20">' . $body . '</textarea>
                    <div id="outer-preview" class="outer-preview">
                        <div class="inner-preview">
                            <div id="preview-window" class="preview-window">
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="emos">
                        <ul class="flex-row">
                            <li>
                                <a href="#BBcode" id="smilies" class="altlink">Smilies</a>
                            </li>' . ($CURUSER['smile_until'] > 0 ? '
                            <li>
                                <a href="#BBcode" id="custom" class="altlink">Custom</a>
                            </li>' : '') . ($CURUSER['class'] < UC_STAFF ? '' : '
                            <li>
                                <a href="#BBcode" id="staff" class="altlink">Staff</a>
                            </li>') . '
                        </ul>
                        <div class="scroll" id="box_0" style="display:none">
                            <div class="smilies_frame">
                                <img src="./images/forums/updating.gif" alt="Loading..." />
                            </div>
                        </div>
                        <div class="scroll" id="box_1" style="display:none">
                            ' . $emoticons_normal . '
                        </div>
                        ' . ($CURUSER['smile_until'] > 0 ? '<div class="scroll" id="box_2" style="display:none">
                            ' . $emoticons_custom . '
                        </div>' : '') . ($CURUSER['class'] < UC_STAFF ? '' : '<div class="scroll" id="box_3" style="display:none">
                            ' . $emoticons_staff . '
                        </div>') . '
                    </div>
                </td>
            </tr>
        </table>
        ' . (($CURUSER['class'] < UC_UPLOADER && (isset($_GET['action']) && $_GET['action'] != 'new_topic')) ? '' : '<span style="text-align: right;">
            <a class="altlink"  title="More Options"  id="tool_open" style="font-weight:bold;cursor:pointer;"><img src="./images/forums/more.gif" alt="+" width="18" /> More Options</a>
            <a class="altlink"  title="Close More Options"  id="tool_close" style="font-weight:bold;cursor:pointer;display:none"><img src="./images/forums/less.gif" alt="-" width="18" /> Close More Options</a>
        </span>');

    return $bbcode;
}

//Finds last occurrence of needle in haystack
//in PHP5 use strripos() instead of this
function _strlastpos($haystack, $needle, $offset = 0)
{
    $addLen = strlen($needle);
    $endPos = $offset - $addLen;
    while (true) {
        if (($newPos = strpos($haystack, $needle, $endPos + $addLen)) === false) {
            break;
        }

        $endPos = $newPos;
    }

    return ($endPos >= 0) ? $endPos : false;
}

function validate_imgs($s)
{
    $start = '(http|https)://';
    $end = "+\.(?:jpe?g|png|gif)";
    preg_match_all('!' . $start . '(.*)' . $end . '!Ui', $s, $result);
    $array = $result[0];
    for ($i = 0; $i < count($array); ++$i) {
        $headers = @get_headers($array[$i]);
        if (strpos($headers[0], '200') === false) {
            $s = str_replace('[img]' . $array[$i] . '[/img]', '', $s);
            $s = str_replace('[img=' . $array[$i] . ']', '', $s);
        }
    }

    return $s;
}

//=== new test for BBcode errors from http://codesnippets.joyent.com/posts/show/959 by berto
function check_BBcode($html)
{
    preg_match_all('#<(?!img|br|hr\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];
    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];
    $len_opened = count($openedtags);
    if (count($closedtags) == $len_opened) {
        return $html;
    }
    $openedtags = array_reverse($openedtags);
    for ($i = 0; $i < $len_opened; ++$i) {
        if (!in_array($openedtags[$i], $closedtags)) {
            $html .= '</' . $openedtags[$i] . '>';
        } else {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }

    return $html;
}

//==format quotes by Retro
function format_quotes($s)
{
    preg_match_all('/\\[quote.*?\\]/', $s, $result, PREG_PATTERN_ORDER);
    $openquotecount = count($openquote = $result[0]);
    preg_match_all('/\\[\/quote\\]/', $s, $result, PREG_PATTERN_ORDER);
    $closequotecount = count($closequote = $result[0]);
    if ($openquotecount != $closequotecount) {
        return $s;
    } // quote mismatch. Return raw string...
    // Get position of opening quotes
    $openval = [];
    $pos = -1;
    foreach ($openquote as $val) {
        $openval[] = $pos = strpos($s, $val, $pos + 1);
    }
    // Get position of closing quotes
    $closeval = [];
    $pos = -1;
    foreach ($closequote as $val) {
        $closeval[] = $pos = strpos($s, $val, $pos + 1);
    }
    for ($i = 0; $i < count($openval); ++$i) {
        if ($openval[$i] > $closeval[$i]) {
            return $s;
        }
    } // Cannot close before opening. Return raw string...
    $s = str_replace('[quote]', "<b>Quote:</b><br><table class='main-left' border='1' cellspacing='0' cellpadding='10'><tr><td>", $s);
    $s = preg_replace('/\\[quote=(.+?)\\]/', "<b>\\1 wrote:</b><br><table class='main-left' border='1' cellspacing='0' cellpadding='10'><tr><td>", $s);
    $s = str_replace('[/quote]', '</td></tr></table><br>', $s);

    return $s;
}

function islocal($link)
{
    global $INSTALLER09;
    $flag = false;
    $limit = 600;
    $INSTALLER09['url'] = str_replace([
        'http://',
        'www',
        'http://www',
        'https://',
        'https://www',
    ], '', $INSTALLER09['baseurl']);
    if (false !== stristr($link[0], '[url=')) {
        $url = trim($link[1]);
        $title = trim($link[2]);
        if (false !== stristr($title, '[img]')) {
            $flag = true;
            $title = preg_replace("/\[img](https?:\/\/[^\s'\"<>]+(\.(jpeg|jpg|gif|png)))\[\/img\]/i", '<img class="img-responsive" src="\\1" alt="" border="0" />', $title);
        }
    } elseif (false !== stristr($link[0], '[url]')) {
        $url = $title = trim($link[1]);
    } else {
        $url = $title = trim($link[2]);
    }
    if (strlen($title) > $limit && $flag == false) {
        $l[0] = substr($title, 0, ($limit / 2));
        $l[1] = substr($title, strlen($title) - round($limit / 3));
        $lshort = $l[0] . '...' . $l[1];
    } else {
        $lshort = $title;
    }
    $url = htmlsafechars($url);

    return '<a href="' . ((stristr($url, $INSTALLER09['url']) !== false) ? '' : 'http://nullrefer.com/?') . $url . '" target="_blank">' . $lshort . '</a>';
}

function format_urls($s)
{
    return preg_replace_callback("/(\A|[^=\]'\"a-zA-Z0-9])((http|ftp|https|ftps|irc):\/\/[^<>\s]+)/i", 'islocal', $s);
}

function format_comment($text, $strip_html = true, $urls = true, $images = true)
{
    global $smilies, $staff_smilies, $customsmilies, $INSTALLER09, $CURUSER;
    $s = $text;
    unset($text);
    $s = validate_imgs($s);
    $INSTALLER09['url'] = str_replace(['http://', 'www', 'http://www', 'https://', 'https://www'], '', $INSTALLER09['baseurl']);
    if (isset($_SERVER['HTTPS']) && (bool)$_SERVER['HTTPS'] == true) {
        $s = preg_replace('/http:\/\/((?:www\.)?' . $INSTALLER09['url'] . ')/i', 'https://$1', $s);
    } else {
        $s = preg_replace('/https:\/\/((?:www\.)?' . $INSTALLER09['url'] . ')/i', 'http://$1', $s);
    }
    // This fixes the extraneous ;) smilies problem. When there was an html escaped
    // char before a closing bracket - like >), "), ... - this would be encoded
    // to &xxx;), hence all the extra smilies. I created a new :wink: label, removed
    // the ;) one, and replace all genuine ;) by :wink: before escaping the body.
    // (What took us so long? :blush:)- wyz
    $s = str_replace(';)', ':wink:', $s);
    // fix messed up links
    $s = str_replace('&amp;', '&', $s);
    if ($strip_html) {
        $s = htmlsafechars($s, ENT_QUOTES, charset());
    }
    if (preg_match("#function\s*\((.*?)\|\|#is", $s)) {
        $s = str_replace(':', '&#58;', $s);
        $s = str_replace('[', '&#91;', $s);
        $s = str_replace(']', '&#93;', $s);
        $s = str_replace(')', '&#41;', $s);
        $s = str_replace('(', '&#40;', $s);
        $s = str_replace('{', '&#123;', $s);
        $s = str_replace('}', '&#125;', $s);
        $s = str_replace('$', '&#36;', $s);
        $s = str_replace('&nbsp;', '&#160;', $s);
    }

    // BBCode to find...
    $bb_code_in = [
        '/\[table\](.*?)\[\/table\]/is',
        '/\[tr\](.*?)\[\/tr\]/is',
        '/\[td\](.*?)\[\/td\]/is',
        '/\[th\](.*?)\[\/th\]/is',
        '/\[sup\](.*?)\[\/sup\]/is',
        '/\[sub\](.*?)\[\/sub\]/is',
        '/\[b\](.*?)\[\/b\]/is',
        '/\[i\](.*?)\[\/i\]/is',
        '/\[u\](.*?)\[\/u\]/is',
        '/\[email\](.*?)\[\/email\]/is',
        '/\[align=([a-zA-Z]+)\](.+?)\[\/align\]/is',
        '/\[center\](.*?)\[\/center\]/is',
        '/\[left\](.*?)\[\/left\]/is',
        '/\[right\](.*?)\[\/right\]/is',
        '/\[blockquote\](.*?)\[\/blockquote\]/is',
        '/\[strike\](.*?)\[\/strike\]/is',
        '/\[s\](.*?)\[\/s\]/is',
        '/\[pre\](.*?)\[\/pre\]/is',
        '/\[marquee\](.*?)\[\/marquee\]/is',
        '/\[collapse=(.*?)\](.*?)\[\/collapse\]/is',
        '/\[size=([1-7])\](.*?)\[\/size\]/is',
        '/\[color=([a-zA-Z]+)\](.*?)\[\/color\]/is',
        '/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\](.*?)\[\/color\]/is',
        '/\[font=([a-zA-Z ,]+)\](.*?)\[\/font\]/is',
        '/\[spoiler\](.*?)\[\/spoiler\]/is',
        '/\[hide\](.*?)\[\/hide\]/is',
        '/\[youtube=[^\s\'"<>]*youtube.com.*v=([^\s\'"<>]+)\]/imsU',
        '/\[video=[^\s\'"<>]*youtube.com.*v=([^\s\'"<>]+)\]/imsU',
        "/\[video=[^\s'\"<>]*video.google.com.*docid=(-?[0-9]+).*\]/imsU",
        '/\[video=https?:\/\/i\.imgur\.com\/(.*)\.gifv\]/imsU',
        '/\[video=https?:(\/\/.*\.mp4)\]/imsU',
        '/\[video=https?:(\/\/.*\.ogg)\]/imsU',
        '/\[video=https?:(\/\/.*\.webm)\]/imsU',
        '/\[loop=https?:(\/\/.*\.mp4)\]/imsU',
        '/\[loop=https?:(\/\/.*\.webm)\]/imsU',
        '/\[loop=https?:(\/\/.*\.ogv)\]/imsU',
        '/\[audio\](http:\/\/[^\s\'"<>]+(\.(mp3|aiff|wav)))\[\/audio\]/is',
        '/\[list=([0-9]+)\](.*?)\[\/list\]/is',
        '/\[list\](.*?)\[\/list\]/is',
        '/\[\*\]\s?(.*?)\n/is',
        '/\[li\]\s?(.*?)\n/is',
        '/\[hr\]/',
    ];
    // And replace them by...
    $bb_code_out = [
        '<table class="table text-center">\1</table>',
        '<tr>\1</tr>',
        '<td>\1</td>',
        '<th>\1</th>',
        '<sup>\1</sup>',
        '<sub>\1</sub>',
        '<span style="font-weight: bold;">\1</span>',
        '<span style="font-style: italic;">\1</span>',
        '<span style="text-decoration: underline;">\1</span>',
        '<a class="altlink" href="mailto:\1">\1</a>',
        '<div style="text-align: \1;">\2</div>',
        '<div style="text-align: center;">\1</div>',
        '<div style="text-align: left;">\1</div>',
        '<div style="text-align: right;">\1</div>',
        '<blockquote class="style"><span>\1</span></blockquote>',
        '<span style="text-decoration: line-through;">\1</span>',
        '<span style="text-decoration: line-through;">\1</span>',
        '<span style="tab-size: 4; -moz-tab-size: 4;-o-tab-size: 4; white-space: pre-wrap; font-family: \'Courier New\', Courier, monospace;">\1</span>',
        '<marquee class="style">\1</marquee>',
        '<div style="padding-top: 2px; white-space: nowrap"><span style="cursor: hand; cursor: pointer; border-bottom: 1px dotted" onclick="if (document.getElementById(\'collapseobj\1\').style.display==\'block\') {document.getElementById(\'collapseobj\1\').style.display=\'none\' } else { document.getElementById(\'collapseobj\1\').style.display=\'block\' }">\1</span></div><div id="collapseobj\1" style="display:none; padding-top: 2px; padding-left: 14px; margin-bottom:10px; padding-bottom: 2px; background-color: #FEFEF4;">\2</div>',
        '<span class="size\1">\2</span>',
        '<span style="color:\1;">\2</span>',
        '<span style="color:\1;">\2</span>',
        '<span style="font-family:\'\1\';">\2</span>',
        "<div style='margin-bottom: 5px;'><span class='flip btn-clean'>Show Spoiler!</span><div class='panel spoiler' style='display:none;'>\\1</div></div><br>",
        "<div style='margin-bottom: 5px;'><span class='flip btn-clean'>Show Hide!</span><div class='panel spoiler' style='display:none;'>\\1</div></div><br>",
        "<div style='width: 500px; height: 281px;' class='text-center'><div class='youtube-embed rndcorners text-center' style='height: 100%; width: 100%;'><iframe width='1920px' height='1080px' src='//www.youtube.com/embed/\\1?vq=hd1080' autoplay='false' frameborder='0' allowfullscreen ></iframe></div></div>",
        "<div style='width: 500px; height: 281px;' class='text-center'><div class='youtube-embed rndcorners text-center' style='height: 100%; width: 100%;'><iframe width='1920px' height='1080px' src='//www.youtube.com/embed/\\1?vq=hd1080' autoplay='false' frameborder='0' allowfullscreen ></iframe></div></div>",
        '<embed style="width:500px; height:410px;" id="VideoPlayback" align="middle" type="application/x-shockwave-flash" src="//video.google.com/googleplayer.swf?docId=\\1" allowScriptAccess="sameDomain" quality="best" bgcolor="#ffffff" scale="noScale" wmode="window" salign="TL"  FlashVars="playerMode=embedded"> </embed>',
        '<span><video width="500" loop muted autoplay><source src="//i.imgur.com/\1.webm" type="video/webm" /><source src="//i.imgur.com/\1.mp4" type="video/mp4" />Your browser does not support the video tag.</video></span>',
        '<span><video width="500" controls><source src="\1" /><source src="\1" type="video/mp4" />Your browser does not support the video tag.</video></span>',
        '<span><video width="500" controls><source src="\1" /><source src="\1" type="video/ogg" />Your browser does not support the video tag.</video></span>',
        '<span><video width="500" controls><source src="\1" /><source src="\1" type="video/webm" />Your browser does not support the video tag.</video></span>',
        '<span><video width="500" loop muted autoplay><source src="\1" /><source src="\1" type="video/mp4" />Your browser does not support the video tag.</video></span>',
        '<span><video width="500" loop muted autoplay><source src="\1" /><source src="\1" type="video/webm" />Your browser does not support the video tag.</video></span>',
        '<span><video width="500" loop muted autoplay><source src="\1" /><source src="\1" type="video/ogv" />Your browser does not support the video tag.</video></span>',
        '<span style="text-align: center;"><p>Audio From: \1</p><embed type="application/x-shockwave-flash" src="http://www.google.com/reader/ui/3247397568-audio-player.swf?audioUrl=\\1" width="400" height="27" allowscriptaccess="never" quality="best" bgcolor="#ffffff" wmode="window" flashvars="playerMode=embedded" /></span>',
        '<ol class="style" start="\1">\2</ol>',
        '<ul class="style">\1</ul>',
        '<li>\1</li>',
        '<li>\1</li>',
        '<hr>',
    ];
    $s = preg_replace($bb_code_in, $bb_code_out, $s);

    // find timpstamps replace with dates
    preg_match_all('/key\s*=\s*(\d{10})/', $s, $match);
    foreach ($match[1] as $tmp) {
        $s = str_replace($tmp, get_date($tmp, ''), $s);
    }

    if ($urls) {
        $s = format_urls($s);
    }
    if (stripos($s, '[url') !== false && $urls) {
        $s = preg_replace_callback("/\[url=([^()<>\s]+?)\](.+?)\[\/url\]/is", 'islocal', $s);
        // [url]http://www.example.com[/url]
        $s = preg_replace_callback("/\[url\]([^()<>\s]+?)\[\/url\]/is", 'islocal', $s);
    }
    // Linebreaks
    //$s = nl2br($s);

    // Dynamic Vars
    $s = dynamic_user_vars($s);
    // [pre]Preformatted[/pre]
    if (stripos($s, '[pre]') !== false) {
        $s = preg_replace("/\[pre\]((\s|.)+?)\[\/pre\]/i", '<tt><span style="white-space: nowrap;">\\1</span></tt>', $s);
    }
    // [nfo]NFO-preformatted[/nfo]
    if (stripos($s, '[nfo]') !== false) {
        $s = preg_replace("/\[nfo\]((\s|.)+?)\[\/nfo\]/i", "<tt><span style=\"white-space: nowrap;\"><font face='MS Linedraw' size='2' style='font-size: 10pt; line-height:" . "10pt'>\\1</font></span></tt>", $s);
    }
    //==Media tag
    if (stripos($s, '[media=') !== false) {
        $s = preg_replace("#\[media=(youtube|liveleak|GameTrailers|vimeo|imdb)\](.+?)\[/media\]#ies", "_MediaTag('\\2','\\1')", $s);
    }
    if (stripos($s, '[img') !== false && $images) {
        // [img=http://www/image.gif]
        $s = preg_replace("/\[img\]((http|https):\/\/[^\s'\"<>]+(\.(jpeg|jpg|gif|png|bmp)))\[\/img\]/i", '<a href="\\1" rel="lightbox"><img src="\\1" border="0" alt="" class="img-responsive" /></a>', $s);
        // [img=http://www/image.gif]
        $s = preg_replace("/\[img=((http|https):\/\/[^\s'\"<>]+(\.(gif|jpeg|jpg|png|bmp)))\]/i", '<a href="\\1" rel="lightbox"><img src="\\1" border="0" alt="" class="img-responsive" /></a>', $s);
    }
    // [mcom]Text[/mcom]
    if (stripos($s, '[mcom]') !== false) {
        $s = preg_replace("/\[mcom\](.+?)\[\/mcom\]/is", '<div style="font-size: 18pt; line-height: 50%;">
   <div style="border-color: red; background-color: red; color: white; text-align: center; font-weight: bold; font-size: large;"><b>\\1</b></div></div>', $s);
    }
    // the [you] tag
    if (stripos($s, '[you]') !== false) {
        $s = preg_replace("/https?:\/\/[^\s'\"<>]*\[you\][^\s'\"<>]*/i", ' ', $s);
        $s = preg_replace("/\[you\]/i", $CURUSER['username'], $s);
    }

    // the [username] tag
    if (stripos($s, '[username]') !== false) {
        $s = preg_replace("/https?:\/\/[^\s'\"<>]*\[username\][^\s'\"<>]*/i", ' ', $s);
        $s = preg_replace("/\[username\]/i", $CURUSER['username'], $s);
    }

    // [php]code[/php]
    if (stripos($s, '[php]') !== false) {
        $s = preg_replace("#\[(php|sql|html)\](.+?)\[\/\\1\]#ise", "source_highlighter('\\2','\\1')", $s);
    }
    // Maintain spacing
    $s = str_replace('  ', ' &#160;', $s);
    if (isset($smilies)) {
        foreach ($smilies as $code => $url) {
            $s = str_replace($code, "<img border='0' src=\"{$INSTALLER09['pic_base_url']}smilies/{$url}\" alt=\"\" />", $s);
        }
    }
    if (isset($staff_smilies)) {
        foreach ($staff_smilies as $code => $url) {
            $s = str_replace($code, "<img border='0' src=\"{$INSTALLER09['pic_base_url']}smilies/{$url}\" alt=\"\" />", $s);
        }
    }
    if (isset($customsmilies)) {
        foreach ($customsmilies as $code => $url) {
            $s = str_replace($code, "<img border='0' src=\"{$INSTALLER09['pic_base_url']}smilies/{$url}\" alt=\"\" />", $s);
        }
    }
    $s = format_quotes($s);
    $s = check_BBcode($s);

    return $s;
}

//=== no bb code in post
function format_comment_no_bbcode($text, $strip_html = true)
{
    global $INSTALLER09;
    $s = $text;
    if ($strip_html) {
        //$s = htmlsafechars($s);
        $s = htmlsafechars($s, ENT_QUOTES, charset());
    }
    // BBCode to find...
    //=== basically will change this into a sort of strip tags but of bbcode shor of the code tag
    $bb_code_in = [
        '/\[b\]\s*((\s|.)+?)\s*\[\/b\]/i',
        '/\[i\]\s*((\s|.)+?)\s*\[\/i\]/i',
        '/\[u\]\s*((\s|.)+?)\s*\[\/u\]/i',
        '#\[img\](.+?)\[/img\]#i',
        '#\[img=(.+?)\]#i',
        '/\[email\](.*?)\[\/email\]/i',
        '/\[align=([a-zA-Z]+)\]((\s|.)+?)\[\/align\]/i',
        '/\[blockquote\]\s*((\s|.)+?)\s*\[\/blockquote\]/i',
        '/\[strike\]\s*((\s|.)+?)\s*\[\/strike\]/i',
        '/\[s\]\s*((\s|.)+?)\s*\[\/s\]/i',
        '/\[pre\]\s*((\s|.)+?)\s*\[\/pre\]/i',
        '/\[marquee\](.*?)\[\/marquee\]/i',
        '/\[url\="?(.*?)"?\]\s*((\s|.)+?)\s*\[\/url\]/i',
        '/\[url\]\s*((\s|.)+?)\s*\[\/url\]/i',
        '/\[collapse=(.*?)\]\s*((\s|.)+?)\s*\[\/collapse\]/i',
        '/\[size=([1-7])\]\s*((\s|.)+?)\s*\[\/size\]/i',
        '/\[color=([a-zA-Z]+)\]\s*((\s|.)+?)\s*\[\/color\]/i',
        '/\[color=(#[a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9][a-f0-9])\]\s*((\s|.)+?)\s*\[\/color\]/i',
        '/\[font=([a-zA-Z ,]+)\]((\s|.)+?)\[\/font\]/i',
        '/\[quote\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i',
        '/\[quote=(.+?)\]\s*((\s|.)+?)\s*\[\/quote\]\s*/i',
        '/\[spoiler\]\s*((\s|.)+?)\s*\[\/spoiler\]\s*/i',
        '/\[hide\]\s*((\s|.)+?)\s*\[\/hide\]\s*/i',
        '/\[video=[^\s\'"<>]*youtube.com.*v=([^\s\'"<>]+)\]/ims',
        "/\[video=[^\s'\"<>]*video.google.com.*docid=(-?[0-9]+).*\]/ims",
        '/\[video=https?:\/\/i\.imgur\.com\/(.*)\.gifv\]/ims',
        '/\[video=https?:(\/\/.*\.mp4)\]/ims',
        '/\[video=https?:(\/\/.*\.ogg)\]/ims',
        '/\[video=https?:(\/\/.*\.webm)\]/ims',
        '/\[loop=https?:(\/\/.*\.mp4)\]/ims',
        '/\[audio\](http:\/\/[^\s\'"<>]+(\.(mp3|aiff|wav)))\[\/audio\]/i',
        '/\[list=([0-9]+)\]((\s|.)+?)\[\/list\]/i',
        '/\[list\]((\s|.)+?)\[\/list\]/i',
        '/\[\*\]\s?(.*?)\n/i',
        '/\[hr\]\s?(.*?)\n/i',
    ];
    // And replace them by...

    $bb_code_out = [
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\2',
        '\2',
        '\2',
        '\2',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
        '\1',
    ];
    $s = preg_replace($bb_code_in, $bb_code_out, $s);

    // replace timestamps with dates
    preg_match_all('/key\s*=\s*(\d+)/', $s, $match);
    foreach ($match[1] as $tmp) {
        $s = str_replace($tmp, get_date($tmp, ''), $s);
    }

    // Linebreaks
    $s = nl2br($s);
    // Maintain spacing
    $s = str_replace('  ', '&#160;', $s);

    return $s;
}

function _MediaTag($content, $type)
{
    global $INSTALLER09;
    if ($content == '' or $type == '') {
        return;
    }
    $return = '';
    switch ($type) {
        case 'youtube':
            $return = preg_replace("#^http://(?:|www\.)youtube\.com/watch\?v=([a-zA-Z0-9\-]+)+?$#i", "<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.youtube.com/v/\\1'><param name='movie' value='http://www.youtube.com/v/\\1' /><param name='allowScriptAccess' value='sameDomain' /><param name='quality' value='best' /><param name='bgcolor' value='#FFFFFF' /><param name='scale' value='noScale' /><param name='salign' value='TL' /><param name='FlashVars' value='playerMode=embedded' /><param name='wmode' value='transparent' /></object>", $content);
            break;

        case 'liveleak':
            $return = preg_replace("#^http://(?:|www\.)liveleak\.com/view\?i=([_a-zA-Z0-9\-]+)+?$#i", "<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.liveleak.com/e/\\1'><param name='movie' value='http://www.liveleak.com/e/\\1' /><param name='allowScriptAccess' value='sameDomain' /><param name='quality' value='best' /><param name='bgcolor' value='#FFFFFF' /><param name='scale' value='noScale' /><param name='salign' value='TL' /><param name='FlashVars' value='playerMode=embedded' /><param name='wmode' value='transparent' /></object>", $content);
            break;

        case 'GameTrailers':
            $return = preg_replace("#^http://(?:|www\.)gametrailers\.com/video/([\-_a-zA-Z0-9\-]+)+?/([0-9]+)+?$#i", "<object type='application/x-shockwave-flash' height='355' width='425' data='http://www.gametrailers.com/remote_wrap.php?mid=\\2'><param name='movie' value='http://www.gametrailers.com/remote_wrap.php?mid=\\2' /><param name='allowScriptAccess' value='sameDomain' /> <param name='allowFullScreen' value='true' /><param name='quality' value='high' /></object>", $content);
            break;

        case 'imdb':
            $return = preg_replace("#^http://(?:|www\.)imdb\.com/video/screenplay/([_a-zA-Z0-9\-]+)+?$#i", "<div class='\\1'><div style=\"padding: 3px; background-color: transparent; border: none; width:690px;\"><div style=\"text-transform: uppercase; border-bottom: 1px solid #CCCCCC; margin-bottom: 3px; font-size: 0.8em; font-weight: bold; display: block;\"><span onclick=\"if (this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display != '') { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = ''; this.innerHTML = '<b>Imdb Trailer: </b><a href=\'#\' onclick=\'return false;\'>hide</a>'; } else { this.parentNode.parentNode.getElementsByTagName('div')[1].getElementsByTagName('div')[0].style.display = 'none'; this.innerHTML = '<b>Imdb Trailer: </b><a href=\'#\' onclick=\'return false;\'>show</a>'; }\" ><b>Imdb Trailer: </b><a href=\"#\" onclick=\"return false;\">show</a></span></div><div class=\"quotecontent\"><div style=\"display: none;\"><iframe style='vertical-align: middle;' src='http://www.imdb.com/video/screenplay/\\1/player' scrolling='no' width='660' height='490' frameborder='0'></iframe></div></div></div></div>", $content);
            break;

        case 'vimeo':
            $return = preg_replace("#^http://(?:|www\.)vimeo\.com/([0-9]+)+?$#i", "<object type='application/x-shockwave-flash' width='425' height='355' data='http://vimeo.com/moogaloop.swf?clip_id=\\1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1'>
    <param name='allowFullScreen' value='true' />
    <param name='allowScriptAccess' value='sameDomain' />
    <param name='movie' value='http://vimeo.com/moogaloop.swf?clip_id=\\1&amp;server=vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1' />
    <param name='quality' value='high' />
    </object>", $content);
            break;

        default:
            $return = 'not found';
    }

    return $return;
}

//=== smilie function
function get_smile()
{
    global $CURUSER;

    return $CURUSER['smile_until'];
}

function user_key_codes($key)
{
    return "/\[$key\]/i";
}

function dynamic_user_vars($text)
{
    global $CURUSER, $INSTALLER09;
    if (!isset($CURUSER)) {
        return;
    }
    $zone = 0; // GMT
    //$zone = 3600 * -5; // EST
    $tim = TIME_NOW + $zone;
    $cu = $CURUSER;
    // unset any variables ya dun want to display, or can't display
    unset($cu['passhash'], $cu['torrent_pass'], $cu['modcomment']);
    $bbkeys = array_keys($cu);
    $bbkeys[] = 'curdate';
    $bbkeys[] = 'curtime';
    $bbvals = array_values($cu);
    $bbvals[] = gmdate('F jS, Y', $tim);
    $bbvals[] = gmdate('g:i A', $tim);
    $bbkeys = array_map('user_key_codes', $bbkeys);

    return @preg_replace($bbkeys, $bbvals, $text);
}
