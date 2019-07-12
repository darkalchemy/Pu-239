<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;

/**
 * @return mixed|string
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 *
 * @throws DependencyException
 */
function rsstfreakinfo()
{
    global $container, $site_config;

    $cache = $container->get(Cache::class);
    require_once INCL_DIR . 'function_html.php';
    $use_limit = true;
    $limit = 5;
    $i = 0;

    $html = $cache->get('tfreaknewsrss_block_');
    if ($html === false || is_null($html)) {
        $xml = fetch('http://feed.torrentfreak.com/Torrentfreak/');

        if (empty($xml)) {
            return null;
        }
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $items = $doc->getElementsByTagName('item');
        foreach ($items as $item) {
            $id = uniqid();
            $top = $i >= 1 ? ' top20' : '';
            $title = htmlsafechars($item->getElementsByTagName('title')
                                        ->item(0)->nodeValue);
            $creator = str_replace([
                '<![CDATA[',
                ']]>',
            ], '', htmlsafechars($item->getElementsByTagName('creator')
                                      ->item(0)->nodeValue));
            $date = htmlsafechars($item->getElementsByTagName('pubDate')
                                       ->item(0)->nodeValue);
            $content = str_replace([
                '<![CDATA[',
                ']]>',
                'href="',
            ], [
                '',
                '',
                'href="' . $site_config['site']['anonymizer_url'],
            ], preg_replace('/<p>/', "<p class='is-primary'>", $item->getElementsByTagName('description')
                                                                    ->item(0)->nodeValue, 1));
            $link = "
                            <a href='{$site_config['site']['anonymizer_url']}" . $item->getElementsByTagName('link')
                                                                                      ->item(0)->nodeValue . "' target='_blank'>
                                <span class='size_2 has-text-primary'>Read more</span>
                            </a>";

            $html .= "
            <div class='portlet{$top}'>
            <div class='bordered'>
                <div id='$id' class='header alt_bordered bg-00 has-text-left'>
                    <div class='has-text-primary size_5 padding10'>$title</div>
                    <div class='bg-02 round5 padding10'>
                        <div class='bottom20 size_2 has-text-primary'>
                            by $creator on $date
                        </div>
                        <div>{$content}{$link}</div>
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
        $cache->set('tfreaknewsrss_block_', $html, 300);
    }

    return $html;
}
