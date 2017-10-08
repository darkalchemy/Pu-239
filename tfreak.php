<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();

function rsstfreakinfo()
{
    require_once INCL_DIR . 'html_functions.php';
    global $site_config;
    $html = '';
    $use_limit = true;
    $limit = 5;
    $xml = file_get_contents('http://feed.torrentfreak.com/Torrentfreak/');
    $html .= "
                <div class='text-left'>";
    $icount = 1;
    $doc = new DOMDocument();
    @$doc->loadXML($xml);
    $items = $doc->getElementsByTagName('item');
    foreach ($items as $item) {
        $html .= "
                    <div class='alt_bordered'>
                        <h3>
                            <u>" . $item->getElementsByTagName('title')->item(0)->nodeValue . "</u>
                        </h3>
                        <span class='size_2'>
                            by " . str_replace(['<![CDATA[', ']]>'], '', $item->getElementsByTagName('creator')->item(0)->nodeValue) . " on " . $item->getElementsByTagName('pubDate')->item(0)->nodeValue . "
                        </span>
                        <br>
                        <div>" .
                            str_replace(['<![CDATA[', ']]>', 'href="'], ['', '', 'href="' . $site_config['anonymizer_url']], $item->getElementsByTagName('description')->item(0)->nodeValue) . "
                            <a href='{$site_config['anonymizer_url']}" . $item->getElementsByTagName('link')->item(0)->nodeValue . "' target='_blank'>
                                <span class='size_2'>
                                    Read more
                                </span>
                            </a>
                        </div>
                    </div>";
        if ($use_limit && $icount == $limit) {
            break;
        }
        ++$icount;
    }
    $html = str_replace(['“', '”'], '"', $html);
    $html = str_replace(['’', '‘', '‘'], "'", $html);
    $html = str_replace('–', '-', $html);
    $html .= "
            </div>";

    return $html;
}
