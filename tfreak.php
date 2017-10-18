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
    $doc = new DOMDocument();
    @$doc->loadXML($xml);
    $items = $doc->getElementsByTagName('item');
    $i = 0;
    foreach ($items as $item) {
        $top = $i++ >= 1 ? ' top20' : '';
        $html .= "
            <div class='bordered padleft10 padright10{$top}'>
                <div id='" . md5($item->getElementsByTagName('title')->item(0)->nodeValue) . "' class='header alt_bordered transparent text-left'>
                    <legend class='flipper'>
                        <i class='fa fa-angle-up right10' aria-hidden='true'></i><span>" . htmlsafechars($item->getElementsByTagName('title')->item(0)->nodeValue) . "</span>
                    </legend>
                    <div class='bg-window round5 padding10'>
                        <div class='bottom20 size_2'>
                            by " . str_replace(['<![CDATA[', ']]>'], '', htmlsafechars($item->getElementsByTagName('creator')->item(0)->nodeValue)) . " on " . htmlsafechars($item->getElementsByTagName('pubDate')->item(0)->nodeValue) . "
                        </div>
                        <div>
                            " . str_replace(['<![CDATA[', ']]>', 'href="'], ['', '', 'href="' . $site_config['anonymizer_url']], preg_replace('/<p>/', "<p class='text-white'>", $item->getElementsByTagName('description')->item(0)->nodeValue, 1)) . "
                            <a href='{$site_config['anonymizer_url']}" . $item->getElementsByTagName('link')->item(0)->nodeValue . "' target='_blank'>
                                <span class='size_2'>
                                    Read more
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>";
        if ($use_limit && $i == $limit) {
            break;
        }
    }
    $html = str_replace(['“', '”'], '"', $html);
    $html = str_replace(['’', '‘', '‘'], "'", $html);
    $html = str_replace('–', '-', $html);

    return $html;
}
