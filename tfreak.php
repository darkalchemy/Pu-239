<?php

/**
 * @return mixed|string
 */
function rsstfreakinfo()
{
    require_once INCL_DIR . 'html_functions.php';
    global $site_config, $cache;

    $html = '';
    $use_limit = true;
    $limit = 5;
    $i = 0;

    $xml = $cache->get('tfreaknewsrss_');
    if ($xml === false || is_null($xml)) {
        $xml = fetch('http://feed.torrentfreak.com/Torrentfreak/');
        $cache->set('tfreaknewsrss_', $xml, 300);
    }

    $doc = new DOMDocument();
    @$doc->loadXML($xml);
    $items = $doc->getElementsByTagName('item');
    foreach ($items as $item) {
        $top = $i >= 1 ? 'top20' : '';
        $html .= "
            <div class='bordered $top'>
                <div id='" . md5($item->getElementsByTagName('title')
                ->item(0)->nodeValue) . "' class='header alt_bordered bg-00 has-text-left'>
                    <legend class='flipper has-text-primary flex flex-left'>
                        <i class='icon-down-open size_2' aria-hidden='true'></i>
                        " . htmlsafechars($item->getElementsByTagName('title')
                ->item(0)->nodeValue) . "
                    </legend>
                    <div class='bg-02 round5 padding10'>
                        <div class='bottom20 size_2 has-text-primary'>
                            by " . str_replace([
                '<![CDATA[',
                ']]>',
            ], '', htmlsafechars($item->getElementsByTagName('creator')
                ->item(0)->nodeValue)) . ' on ' . htmlsafechars($item->getElementsByTagName('pubDate')
                ->item(0)->nodeValue) . '
                        </div>
                        <div>
                            ' . str_replace([
                '<![CDATA[',
                ']]>',
                'href="',
            ], [
                '',
                '',
                'href="' . $site_config['anonymizer_url'],
            ], preg_replace('/<p>/', "<p class='size_4 has-text-white'>", $item->getElementsByTagName('description')
                ->item(0)->nodeValue, 1)) . "
                            <a href='{$site_config['anonymizer_url']}" . $item->getElementsByTagName('link')
                ->item(0)->nodeValue . "' target='_blank'>
                                <span class='size_2 has-text-primary'>
                                    Read more
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>";
        if ($use_limit && ++$i >= $limit) {
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

    return $html;
}
