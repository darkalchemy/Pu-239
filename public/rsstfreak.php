<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'user_functions.php';
check_user_status();
$html = '';
$lang = load_language('global');
$use_limit = true;
$limit = 15;
$xml = file_get_contents('http://feed.torrentfreak.com/Torrentfreak/');
$icount = 1;
$doc = new DOMDocument();
@$doc->loadXML($xml);
$items = $doc->getElementsByTagName('item');
foreach ($items as $item) {
    $html .= "
        <div class='bordered has-text-left bottom20'>
            <h2>" . $item->getElementsByTagName('title')->item(0)->nodeValue . '</h2>
            <hr>' . preg_replace("/<p>Source\:(.*?)width=\"1\"\/>/is", '', $item->getElementsByTagName('encoded')->item(0)->nodeValue) . '<hr>
        </div>';
    if ($use_limit && $icount == $limit) {
        break;
    }

    ++$icount;
}
$html = str_replace(['“', '”'], '"', $html);
$html = str_replace(['’', '‘', '‘'], "'", $html);
$html = str_replace('–', '-', $html);
$html = str_replace('="/images/', '="http://torrentfreak.com/images/', $html);
$html = main_div($html);
echo stdhead('Torrent freak news') . wrapper($html) . stdfoot();
