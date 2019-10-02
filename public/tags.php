<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();

$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('sceditor_js'),
    ],
];

/**
 * @param string $name
 * @param string $description
 * @param string $syntax
 * @param string $example
 * @param string $remarks
 *
 * @throws InvalidManipulation
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @return string
 */
function insert_tag(string $name, string $description, string $syntax, string $example, string $remarks)
{
    $result = format_comment($example);
    if ($remarks != '') {
        $remarks = '
        <tr>
            <td>' . _('Remarks:') . "</td>
            <td>$remarks</td>
        </tr>";
    }

    $htmlout = "
     <h2 class='top20 has-text-centered'>{$name}</h2>";
    $body = "
        <tr>
            <td class='w-25'>" . _('Description:') . "</td>
            <td>{$description}</td>
        </tr>
        <tr>
            <td class='w-25'>" . _('Syntax:') . "</td>
            <td>{$syntax}</td>
        </tr>
        <tr>
            <td class='w-25'>" . _('Example:') . "</td>
            <td>{$example}</td>
        </tr>
        <tr>
            <td class='w-25'>" . _('Result:') . "</td>
            <td>{$result}</td>
        </tr>{$remarks}";

    $htmlout .= main_table($body);

    return $htmlout;
}

$test = isset($_POST['test']) ? $_POST['test'] : '';
$HTMLOUT = "<h1 class='has-text-centered'>BBcode Tags</h1>";
$HTMLOUT .= main_div("
    <div class='has-text-centered'>
        <div class='padding20'>" . _('The Crafty forums supports a number of <i>BBcode tags</i> which you can embed to modify how your posts are displayed. The last button, from the left, will display your content.') . "</div>
        <div class='is-paddingless'>" . BBcode() . '</div>
    </div>', '', 'padding20');

$HTMLOUT .= insert_tag(_('Bold'), _('Makes the enclosed text bold.'), _('[b]<i>Text</i>[/b]'), _('[b]This is bold text.[/b]'), '');
$HTMLOUT .= insert_tag(_('Italic'), _('Makes the enclosed text italic.'), _('[i]<i>Text</i>[/i]'), _('[i]This is italic text.[/i]'), '');
$HTMLOUT .= insert_tag(_('Underline'), _('Makes the enclosed text underlined.'), _('[u]<i>Text</i>[/u]'), _('[u]This is underlined text.[/u]'), '');
$HTMLOUT .= insert_tag(_('Strikethrough'), _('Makes the enclosed text strikethrough.'), _('[s]<i>Text</i>[/s]'), _('[s]This is text is strikethrough.[/s]'), '');
$HTMLOUT .= insert_tag(_('Subscript'), _('Makes the enclosed text subscript.'), _('[sub]<i>Text</i>[/sub]'), _('This is text is [sub]subscript.[/sub]'), '');
$HTMLOUT .= insert_tag(_('Superscript'), _('Makes the enclosed text superscript.'), _('[sup]<i>Text</i>[/sup]'), _('This is text is [sup]superscript.[/sup]'), '');
$HTMLOUT .= insert_tag(_('Align Right'), _('Makes the enclosed text align to the right.'), _('[right]<i>Text</i>[/right]'), _('[right]This is text is right aligned.[/right]'), '');
$HTMLOUT .= insert_tag(_('Align Left'), _('Makes the enclosed text align to the left.'), _('[left]<i>Text</i>[/left]'), _('[left]This is text is left aligned.[/left]'), '');
$HTMLOUT .= insert_tag(_('Centered'), _('Makes the enclosed text centered.'), _('[center]<i>Text</i>[/center]'), _('[center]This is text is centered.[/center]'), '');
$HTMLOUT .= insert_tag(_('Justified'), _('Makes the enclosed text justified.'), _('[justify]<i>Text</i>[/justify]'), _('[justify]This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified.[/justify]'), '');
$HTMLOUT .= insert_tag(_('Color (alt. 1)'), _('Changes the color of the enclosed text.'), _('[color=<i>Color</i>]<i>Text</i>[/color]'), _('[color=blue]This is blue text.[/color]'), _('What colors are valid depends on the browser. If you use the basic colors (red, green, blue, yellow, pink etc) you should be safe.'));
$HTMLOUT .= insert_tag(_('Color (alt. 2)'), _('Changes the color of the enclosed text.'), _('[color=#<i>RGB</i>]<i>Text</i>[/color]'), _('[color=#0000ff]This is blue text.[/color]'), _('<i>RGB</i> must be a six digit hexadecimal number.'));
$HTMLOUT .= insert_tag(_('Size'), _('Sets the size of the enclosed text.'), _('[size=<i>n</i>]<i>text</i>[/size]'), _('[size=4]This is size 4.[/size]'), _('<i>n</i> must be an integer in the range 1 (smallest) to 7 (biggest). The default size is 2.'));
$HTMLOUT .= insert_tag(_('Font'), _('Sets the type-face (font) for the enclosed text.'), _('[font=<i>Font</i>]<i>Text</i>[/font]'), _('[font=Impact]Hello world![/font]'), _('You specify alternative fonts by separating them with a comma.'));
$HTMLOUT .= insert_tag(_('Hyperlink (alt. 1)'), _('Inserts a hyperlink.'), _('[url]<i>URL</i>[/url]'), _('[url]http://Pu239.silly/[/url]'), _('This tag is superfluous; all URLs are automatically hyperlinked.'));
$HTMLOUT .= insert_tag(_('Hyperlink (alt. 2)'), _('Inserts a hyperlink.'), _('[url=<i>URL</i>]<i>Link text</i>[/url]'), _('[url=http://Pu239.silly/]Crafty[/url]'), _('You do not have to use this tag unless you want to set the link text; all URLs are automatically hyperlinked.'));
$HTMLOUT .= insert_tag(_('Image (alt. 1)'), _('Inserts a picture.'), _('[img=<i>URL</i>]'), _('[img=http://Pu239.silly/images/logo.png]'), _('The URL must end with <b>.gif</b>, <b>.jpeg</b>, <b>.jpg</b> or <b>.png</b>.'));
$HTMLOUT .= insert_tag(_('Image (alt. 2)'), _('Inserts a picture.'), _('[img]<i>URL</i>[/img]'), _('[img]http://Pu239.silly/images/logo.png[/img]'), _('The URL must end with <b>.gif</b>, <b>.jepg</b>, <b>.jpg</b> or <b>.png</b>.'));
$HTMLOUT .= insert_tag(_('Quote (alt. 1)'), _('Inserts a quote.'), _('[quote]<i>Quoted text</i>[/quote]'), _('[quote]The quick brown fox jumps over the lazy dog.[/quote]'), '');
$HTMLOUT .= insert_tag(_('Quote (alt. 2)'), _('Inserts a quote.'), _('[quote=<i>Author</i>]<i>Quoted text</i>[/quote]'), _('[quote=John Doe]The quick brown fox jumps over the lazy dog.[/quote]'), '');
$HTMLOUT .= insert_tag(_('List'), _('Inserts a list item.'), _('[*]<i>Text</i>'), _('[*] This is item 1
[*] This is item 2'), '');
$HTMLOUT .= insert_tag(_('Table'), _('Inserts a formatted table.'), _('[table][tr][td]<i>Text</i>[/td][[td]<i>Text</i>[/td][td]<i>Text</i>[/td][/tr][tr][td]<i>Text</i>[/td][[td]<i>Text</i>[/td][td]<i>Text</i>[/td][/tr][tr][td]<i>Text</i>[/td][[td]<i>Text</i>[/td][td]<i>Text</i>[/td][/tr][/table]'), _('[table][tr][td]Text[/td][td]Text[/td][td]Text[/td][/tr][tr][td]Text[/td][td]Text[/td][td]Text[/td][/tr][tr][td]Text[/td][td]Text[/td][td]Text[/td][/tr][/table]'), '');
$HTMLOUT .= insert_tag(_('Preformat'), _('Preformatted (monospace) text. Does not wrap automatically.'), _('[pre]<i>Text</i>[/pre]'), _('[pre]This is preformatted text.[/pre]'), '');
$HTMLOUT .= insert_tag(_('Format Code'), _('Formatted text. Does wrap automatically.'), _('[code]<i>Text</i>[/code]'), _('[code]This is code formatted text.[/code]'), '');
$HTMLOUT .= insert_tag(_('Youtube (alt. 1)'), _('Display youtube video.'), _('[youtube]<i>https://www.youtube.com/watch?v=u22BXhMu4tI</i>[/youtube]'), _('[youtube=https://www.youtube.com/watch?v=u22BXhMu4tI]'), _('This format only works when using the bbcode editor as it strips all but the video id.'));
$HTMLOUT .= insert_tag(_('Youtube (alt. 2)'), _('Display youtube video.'), _('[youtube]<i>https://www.youtube.com/watch?v=u22BXhMu4tI</i>[/youtube]'), _('[youtube=https://www.youtube.com/watch?v=u22BXhMu4tI]'), _('This format works everywhere, but does not display correctly in BBcode editor.'));

$title = _('Tags');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
