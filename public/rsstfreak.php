<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_users.php';
check_user_status();
$html = '';
$lang = load_language('global');
$use_limit = true;
$limit = 15;
$icount = 1;

$xml = $cache->get('tfreaknewsrss_');
if ($xml === false || is_null($xml)) {
    $xml = file_get_contents('http://feed.torrentfreak.com/Torrentfreak/');
    $cache->set('tfreaknewsrss_', $xml, 300);
}

$doc = new DOMDocument();
@$doc->loadXML($xml);
$items = $doc->getElementsByTagName('item');
foreach ($items as $item) {
    $div = "
        <div class='has-text-left padding20'>
            <h2>" . $item->getElementsByTagName('title')
            ->item(0)->nodeValue . '</h2>
            <hr>' . preg_replace("/<p>Source\:(.*?)width=\"1\"\/>/is", '', $item->getElementsByTagName('encoded')
            ->item(0)->nodeValue) . '
        </div>';
    $html .= main_div($div, $icount < $limit ? 'bottom20' : '');
    if ($use_limit && $icount++ >= $limit) {
        break;
    }
}

$html = str_replace([
    '“',
    '”',
], '"', $html);
$html = str_replace([
    '’',
    '‘',
    '‘',
], "'", $html);
$html = str_replace('–', '-', $html);
$html = str_replace('href="', 'href="' . $site_config['site']['anonymizer_url'], $html);
$html = str_replace('="/images/', '="https://torrentfreak.com/images/', $html);
preg_match_all('/<img.*?src=["|\'](.*?)["|\'](.*?)>/s', $html, $matches);
$i = 0;
foreach ($matches[1] as $match) {
    $html = str_replace($match, url_proxy($match, true), $html);
}
echo stdhead('Torrent freak news') . wrapper($html) . stdfoot();
