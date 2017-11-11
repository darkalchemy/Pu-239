<?php
//-------- Begins a main frame
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

//-------- Ends a main frame
/**
 * @return string
 */
function end_main_frame()
{
    return "
                    </td>
                </tr>
            </table>";
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
        $tdextra .= "";
    }
    $htmlout .= "<table class='shit table table-bordered table-striped' cellpadding='$padding'><tr><td$tdextra>\n";

    return $htmlout;
}

/**
 * @param int $padding
 */
function attach_frame($padding = 10)
{
    echo "</td></tr><tr><td style='border-top: 0px'>\n";
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
            <td class='break_word'>
                $a
            </td>
        </tr>";
}

//-------- Inserts a smilies frame
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
        $htmlout .= "<tr><td>$code</td><td><img src=\"{$site_config['pic_base_url']}smilies/{$url}\" alt='' /></td></tr>\n";
    }
    $htmlout .= end_table();
    $htmlout .= end_frame();

    return $htmlout;
}

/**
 * @param      $body
 * @param null $header
 *
 * @return string
 */
function main_table($body, $header = null)
{
    $thead = $header != null ? "
                        <thead>
                            $header
                        </thead>" : '';
    return "
                <div class='table-wrapper'>
                    <table class='table table-bordered table-striped top20 bottom20'>
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
function main_div($text, $align = null)
{
    if ($text === '') {
        return;
    } else {
        return "
                <div class='bordered top20 $align'>
                    <div class='alt_bordered bg-00'>
                        $text
                    </div>
                </div>";
    }

}

/**
 * @param        $text
 * @param string $align
 * @param string $top
 * @param string $bottom
 * @param string $padding
 *
 * @return string|void
 */
function wrapper($text, $align = 'has-text-centered', $top = 'top20', $bottom = 'bottom20', $padding = '')
{
    if ($text === '') {
        return;
    } else {
        return "
            <div class='container is-fluid portlet $align $top $bottom $padding'>
                $text
            </div>";
    }
}
