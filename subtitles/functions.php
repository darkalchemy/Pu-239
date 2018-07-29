<?php

require_once 'xml2array.php';

/**
 * @param $name
 * @param $searchby
 * @param $lang
 * @param $cds
 * @param $format
 * @param $fps
 * @param $offset
 *
 * @return bool|string
 */
function requestXML($name, $searchby, $lang, $cds, $format, $fps, $offset)
{
    $optional = '';
    if (isset($lang)) {
        $optional .= '/sublanguageid-' . $lang;
    }
    if (isset($cds)) {
        $optional .= '/subsumcd-' . $cds;
    }
    if (isset($format)) {
        $optional .= '/subformat-' . $format;
    }
    if (isset($offset) && $offset > 0) {
        $optional .= '/offset-' . $offset;
    }
    if (isset($searchby) && $searchby === 'name') {
        $search = '/moviename-' . urlencode($name);
    }
    if (isset($searchby) && $searchby === 'imdb') {
        if (!ereg('[0-9]{7}', $name, $imdbid)) {
            die("Can't find imdb id");
        } else {
            $search = '/imdbid-' . $imdbid[0];
        }
    }
    $link = 'http://www.opensubtitles.org/en/search' . $search . $optional . '/simplexml';

    $xml = file_get_contents($link) or die("can't connect to host to get the xml data");

    return $xml;
}

/**
 * @param $source
 *
 * @return array
 */
function xmlconvert($source)
{
    $xml = new Xml2Array();
    $xml->setXml($source);
    $array = $xml->get_array();

    return $array;
}

/**
 * @param $array
 *
 * @return mixed
 */
function get_details($array)
{
    //check the array
    if (!is_array($array)) {
        die('no array passed');
    }
    foreach ($array as $key => $value) {
        foreach ($value as $key2 => $value2) {
            $details[$key] = $value2;
        }
    }

    return $details;
}

/**
 * @param $array
 *
 * @return mixed
 */
function get_base($array)
{
    return $array['#text'];
}

/**
 * @param $array
 *
 * @return array
 */
function get_results($array)
{
    return [
        'items' => $array['@items'],
        'itemsfound' => $array['@itemsfound'],
        'searchtime' => $array['@searchtime'],
    ];
}

/**
 * @param $itemsfound
 * @param $href
 *
 * @return string
 */
function pager($itemsfound, $href)
{
    $pager = '';
    if ($itemsfound > 40) {
        $links = ($itemsfound / 40);
    }
    if (intval($links) != $links) {
        $links = intval($links) + 1;
    }
    if ($links > 1) {
        $pager = '';
        for ($i = 0; $i < $links; ++$i) {
            if ($i % 15 == 0) {
                $pager .= '<br><br>';
            }
            if (($i * 40) == 1000) {
                break;
            }
            $pager .= '<a ' . ($_GET['offset'] == ($i * 40) ? 'class="sublink-active"' : 'class="sublink"') . ' href="' . $href . 'offset=' . ($i * 40) . '">' . ($i + 1) . '</a>&#160;';
        }
    }

    return $pager;
}

/**
 * @param $array
 * @param $pager
 */
function build_result($array, $pager)
{
    global $site_config;

    //define some vars
    $result = $array['search']['results']['subtitle'];
    $base = get_base($array['search']['base']);
    $time = get_results($array['search']['results']);
    //print the content
    if (count($result) == 0) {
        echo '<div><h2>No result found</h2></div>';
    } else {
        ?>
        <table width="55%" style="border-collapse:collapse;" border="1">
            <tr>
                <td colspan="9">Search took&#160;<font class="releasename">
                        <?php echo $time['searchtime']; ?>
                        s</font>, Items found <font class="releasename">
                        <?php echo $time['itemsfound']; ?>
                    </font></td>
            </tr>
            <tr>
                <td colspan="4" width="100%">Movie name</td>
                <td nowrap="nowrap">Added</td>
                <td nowrap="nowrap"><img src="' . $site_config['baseurl']. '/imgs/icon-files.gif" width="12" height="12"
                                         alt=" "
                                         title="CDs"/></td>
                <td nowrap="nowrap"><img src="' . $site_config['baseurl']. '/imgs/icon-format.gif" width="12"
                                         height="13" alt=" "
                                         title="Format"/></td>
                <td nowrap="nowrap"><img src="' . $site_config['baseurl']. '/imgs/icon-upper.gif" width="15" height="17"
                                         alt=" "
                                         title="Uploader"/></td>
            </tr>
            <?php

            $count = ($time['itemsfound'] == 1 ? 1 : count($result));
        for ($i = 0; $i < $count; ++$i) {
            $movie = ($count == 1 ? get_details($result) : get_details($result[$i])); ?>
                <tr>
                    <td nowrap="nowrap"><img
                                src="' . $site_config['baseurl']. '/flag/<?php echo $movie['iso639']; ?>.gif" width="18"
                                height="12" border="0"
                                alt="<?php echo $movie['language']; ?>"
                                title="<?php echo $movie['language']; ?>"/></td>
                    <td colspan="2" width="100%"><a href="<?php echo $base . $movie['detail']; ?>"
                                                    target="_blank">
                            <?php echo $movie['movie']; ?>
                        </a>
                        <?php echo $movie['releasename'] ? '<br><font class="releasename">' . $movie['releasename'] . '</font>' : ''; ?>
                    </td>
                    <td nowrap="nowrap"><a href="<?php echo $base . $movie['download']; ?>"
                                           target="blank"><img
                                    src="' . $site_config['baseurl']. '/imgs/icon-download.gif" width="12"
                                    height="12" border="0" alt=" "
                                    title="download"/></a></td>
                    <td nowrap="nowrap"
                    ><?php echo str_replace(' ', '<br>', $movie['subadddate']); ?></td>
                    <td nowrap="nowrap"><?php echo $movie['files']; ?></td>
                    <td nowrap="nowrap"><?php echo $movie['format']; ?></td>
                    <td nowrap="nowrap"
                    ><?php echo $movie['user'] == '' ? 'Unknown' : $movie['user']; ?></td>
                </tr>
                <?php
        } ?>
        </table>
        <?php echo $time['itemsfound'] > 40 ? '<br><div>' . pager($time['itemsfound'], $pager) . '</div>' : ''; ?>
        <?php
    }
}
