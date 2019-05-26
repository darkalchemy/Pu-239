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
$lang = array_merge(load_language('global'), load_language('tags'));

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
    global $lang;

    $result = format_comment($example);
    if ($remarks != '') {
        $remarks = "
        <tr>
            <td>{$lang['tags_remarks']}</td>
            <td>$remarks</td>
        </tr>";
    }

    $htmlout = "
     <h2 class='top20 has-text-centered'>{$name}</h2>";
    $body = "
        <tr>
            <td class='w-25'>{$lang['tags_description']}</td>
            <td>{$description}</td>
        </tr>
        <tr>
            <td class='w-25'>{$lang['tags_systax']}</td>
            <td>{$syntax}</td>
        </tr>
        <tr>
            <td class='w-25'>{$lang['tags_example']}</td>
            <td>{$example}</td>
        </tr>
        <tr>
            <td class='w-25'>{$lang['tags_result']}</td>
            <td>{$result}</td>
        </tr>{$remarks}";

    $htmlout .= main_table($body);

    return $htmlout;
}

$test = isset($_POST['test']) ? $_POST['test'] : '';
$HTMLOUT = "<h1 class='has-text-centered'>BBcode Tags</h1>";
$HTMLOUT .= main_div("<div class='has-text-centered'>
    {$lang['tags_title']}<br><br>
    <form method='post' action='?' accept-charset='utf-8'>
    <textarea name='test' cols='60' rows='3'>" . ($test ? htmlsafechars($test) : '') . "</textarea>
    <input type='submit' value='{$lang['tags_test']}' style='height: 23px; margin-left: 5px;'>
    </form>
    </div>", '', 'padding20');
if ($test != '') {
    $HTMLOUT .= '<p><hr>' . format_comment($test) . "<hr></p>\n";
}
$HTMLOUT .= insert_tag($lang['tags_bold1'], $lang['tags_bold2'], $lang['tags_bold3'], $lang['tags_bold4'], '');
$HTMLOUT .= insert_tag($lang['tags_italic1'], $lang['tags_italic2'], $lang['tags_italic3'], $lang['tags_italic4'], '');
$HTMLOUT .= insert_tag($lang['tags_underline1'], $lang['tags_underline2'], $lang['tags_underline3'], $lang['tags_underline4'], '');
$HTMLOUT .= insert_tag($lang['tags_color1'], $lang['tags_color2'], $lang['tags_color3'], $lang['tags_color4'], $lang['tags_color5']);
$HTMLOUT .= insert_tag($lang['tags_color6'], $lang['tags_color7'], $lang['tags_color8'], $lang['tags_color9'], $lang['tags_color10']);
$HTMLOUT .= insert_tag($lang['tags_size1'], $lang['tags_size2'], $lang['tags_size3'], $lang['tags_size4'], $lang['tags_size5']);
$HTMLOUT .= insert_tag($lang['tags_fonts1'], $lang['tags_fonts2'], $lang['tags_fonts3'], $lang['tags_fonts4'], $lang['tags_fonts5']);
$HTMLOUT .= insert_tag($lang['tags_hyper1'], $lang['tags_hyper2'], $lang['tags_hyper3'], $lang['tags_hyper4'], $lang['tags_hyper5']);
$HTMLOUT .= insert_tag($lang['tags_hyper6'], $lang['tags_hyper7'], $lang['tags_hyper8'], $lang['tags_hyper9'], $lang['tags_hyper10']);
$HTMLOUT .= insert_tag($lang['tags_image1'], $lang['tags_image2'], $lang['tags_image3'], $lang['tags_image4'], $lang['tags_image5']);
$HTMLOUT .= insert_tag($lang['tags_image6'], $lang['tags_image7'], $lang['tags_image8'], $lang['tags_image9'], $lang['tags_image10']);
$HTMLOUT .= insert_tag($lang['tags_quote1'], $lang['tags_quote2'], $lang['tags_quote3'], $lang['tags_quote4'], '');
$HTMLOUT .= insert_tag($lang['tags_quote5'], $lang['tags_quote6'], $lang['tags_quote7'], $lang['tags_quote8'], '');
$HTMLOUT .= insert_tag($lang['tags_list1'], $lang['tags_list2'], $lang['tags_list3'], $lang['tags_list4'], '');
$HTMLOUT .= insert_tag($lang['tags_preformat1'], $lang['tags_preformat2'], $lang['tags_preformat3'], $lang['tags_preformat4'], '');

echo stdhead($lang['tags_tags']) . wrapper($HTMLOUT) . stdfoot();
