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
 *
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
    $example = str_replace('[br]', '', $example);
    if ($remarks != '') {
        $remarks = "
        <tr class='no_hover'>
            <td>" . _('Remarks') . ":</td>
            <td>$remarks</td>
        </tr>";
    }

    $htmlout = "
     <h2 class='top20 has-text-centered'>{$name}</h2>";
    $body = "
        <tr class='no_hover'>
            <td class='w-25'>" . _('Description') . "</td>
            <td>{$description}</td>
        </tr>
        <tr class='no_hover'>
            <td class='w-25'>" . _('Syntax') . ":</td>
            <td>{$syntax}</td>
        </tr>
        <tr class='no_hover'>
            <td class='w-25'>" . _('Example') . ":</td>
            <td>{$example}</td>
        </tr>
        <tr class='no_hover'>
            <td class='w-25'>" . _('Result') . ":</td>
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

$HTMLOUT .= insert_tag(_('Bold'), _('Makes the enclosed text bold.'), '[b]<i>Text</i>[/b]', '[b]This is bold text.[/b]', '');
$HTMLOUT .= insert_tag(_('Italic'), _('Makes the enclosed text italic.'), '[i]<i>Text</i>[/i]', '[i]This is italic text.[/i]', '');
$HTMLOUT .= insert_tag(_('Underline'), _('Makes the enclosed text underlined.'), '[u]<i>Text</i>[/u]', '[u]This is underlined text.[/u]', '');
$HTMLOUT .= insert_tag(_('Strikethrough'), _('Makes the enclosed text strikethrough.'), '[s]<i>Text</i>[/s]', '[s]This is text is strikethrough.[/s]', '');
$HTMLOUT .= insert_tag(_('Subscript'), _('Makes the enclosed text subscript.'), '[sub]<i>Text</i>[/sub]', 'This is text is [sub]subscript.[/sub]', '');
$HTMLOUT .= insert_tag(_('Superscript'), _('Makes the enclosed text superscript.'), '[sup]<i>Text</i>[/sup]', 'This is text is [sup]superscript.[/sup]', '');
$HTMLOUT .= insert_tag(_('Align Right'), _('Makes the enclosed text align to the right.'), '[right]<i>Text</i>[/right]', '[right]This is text is right aligned.[/right]', '');
$HTMLOUT .= insert_tag(_('Align Left'), _('Makes the enclosed text align to the left.'), '[left]<i>Text</i>[/left]', '[left]This is text is left aligned.[/left]', '');
$HTMLOUT .= insert_tag(_('Centered'), _('Makes the enclosed text centered.'), '[center]<i>Text</i>[/center]', '[center]This is text is centered.[/center]', '');
$HTMLOUT .= insert_tag(_('Justified'), _('Makes the enclosed text justified.'), '[justify]<i>Text</i>[/justify]', '[justify]This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified. This is text is justified.[/justify]', '');
$HTMLOUT .= insert_tag(_('Color (alt. 1)'), _('Changes the color of the enclosed text.'), '[color=<i>Color</i>]<i>Text</i>[/color]', '[color=blue]This is blue text.[/color]', _('What colors are valid depends on the browser. If you use the basic colors (red, green, blue, yellow, pink etc) you should be safe.'));
$HTMLOUT .= insert_tag(_('Color (alt. 2)'), _('Changes the color of the enclosed text.'), '[color=#<i>RGB</i>]<i>Text</i>[/color]', '[color=#0000ff]This is blue text.[/color]', _('<i>RGB</i> must be a six digit hexadecimal number.'));
$HTMLOUT .= insert_tag(_('Size'), _('Sets the size of the enclosed text.'), '[size=<i>n</i>]<i>text</i>[/size]', '[size=4]This is size 4.[/size]', _('<i>n</i> must be an integer in the range 1 (smallest) to 7 (biggest). The default size is 2.'));
$HTMLOUT .= insert_tag(_('Font'), _('Sets the type-face (font) for the enclosed text.'), '[font=<i>Font</i>]<i>Text</i>[/font]', '[font=Impact]Hello world![/font]', _('You specify alternative fonts by separating them with a comma.'));
$HTMLOUT .= insert_tag(_('Hyperlink (alt. 1)'), _('Inserts a hyperlink.'), '[url]<i>URL</i>[/url]', '[url]http://Pu239.silly/[/url]', _('This tag is superfluous; all URLs are automatically hyperlinked.'));
$HTMLOUT .= insert_tag(_('Hyperlink (alt. 2)'), _('Inserts a hyperlink.'), '[url=<i>URL</i>]<i>Link text</i>[/url]', '[url=http://Pu239.silly/]Crafty[/url]', _('You do not have to use this tag unless you want to set the link text; all URLs are automatically hyperlinked.'));
$HTMLOUT .= insert_tag(_('Image (alt. 1)'), _('Inserts a picture.'), '[img=<i>URL</i>]', '[img=http://Pu239.silly/images/logo.png]', _('The URL must be an image of type gif, jpeg or png.'));
$HTMLOUT .= insert_tag(_('Image (alt. 2)'), _('Inserts a picture.'), '[img]<i>URL</i>[/img]<br>[img width=161 height=50]<i>URL</i>[/img]<br>[img width=150]<i>URL</i>[/img]<br>[img height=25]<i>URL</i>[/img]', '[img]http://Pu239.silly/images/logo.png[/img]<br>[br][img width=161 height=50]http://Pu239.silly/images/logo1.png[/img]<br>[br][img width=150]http://Pu239.silly/images/logo2.png[/img]<br>[br][img height=25]http://Pu239.silly/images/logo3.png[/img]', _('The URL must be an image of type gif, jpeg or png.'));
$HTMLOUT .= insert_tag(_('Quote (alt. 1)'), _('Inserts a quote.'), '[quote]<i>Quoted text</i>[/quote]', '[quote]The quick brown fox jumps over the lazy dog.[/quote]', '');
$HTMLOUT .= insert_tag(_('Quote (alt. 2)'), _('Inserts a quote.'), '[quote=<i>Author</i>]<i>Quoted text</i>[/quote]', '[quote=John Doe]The quick brown fox jumps over the lazy dog.[/quote]', '');
$HTMLOUT .= insert_tag(_('List'), _('Inserts a list item.'), '[*]<i>Text</i>', '[*] This is item 1
[*] This is item 2', '');
$HTMLOUT .= insert_tag(_('Table'), _('Inserts a formatted table.'), '[table][tr][td]<i>Text</i>[/td][[td]<i>Text</i>[/td][td]<i>Text</i>[/td][/tr][tr][td]<i>Text</i>[/td][[td]<i>Text</i>[/td][td]<i>Text</i>[/td][/tr][tr][td]<i>Text</i>[/td][[td]<i>Text</i>[/td][td]<i>Text</i>[/td][/tr][/table]', '[table][tr][td]Text[/td][td]Text[/td][td]Text[/td][/tr][tr][td]Text[/td][td]Text[/td][td]Text[/td][/tr][tr][td]Text[/td][td]Text[/td][td]Text[/td][/tr][/table]', '');
$HTMLOUT .= insert_tag(_('Preformat'), _('Preformatted (monospace) text. Does not wrap automatically.'), '[pre]<i>Text</i>[/pre]', '[pre]This is preformatted text.[/pre]', '');
$HTMLOUT .= insert_tag(_('Format Code'), _('Formatted text. Does wrap automatically.'), '[code]<i>Text</i>[/code]', '[code]This is code formatted text.[/code]', '');
$HTMLOUT .= insert_tag(_('Youtube (alt. 1)'), _('Display youtube video.'), '[youtube]<i>https://www.youtube.com/watch?v=u22BXhMu4tI</i>[/youtube]', '[youtube=https://www.youtube.com/watch?v=u22BXhMu4tI]', _('This format only works when using the bbcode editor as it strips all but the video id.'));
$HTMLOUT .= insert_tag(_('Youtube (alt. 2)'), _('Display youtube video.'), '[youtube]<i>https://www.youtube.com/watch?v=u22BXhMu4tI</i>[/youtube]', '[youtube=https://www.youtube.com/watch?v=u22BXhMu4tI]', _('This format works everywhere, but does not display correctly in BBcode editor.'));

$title = _('Tags');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
