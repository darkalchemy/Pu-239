<?php

/**
 * @param array $links
 *
 * @return bool
 */
function foxnews_shout($links = [])
{
    global $site_config, $cache, $fluent;

    $empty = empty($links);
    $feeds = [
        'Tech' => 'http://feeds.foxnews.com/foxnews/tech',
        //'World' => 'http://feeds.foxnews.com/foxnews/world',
        //'Entertainment' => 'http://feeds.foxnews.com/foxnews/entertainment',
        //'Sports' => 'http://feeds.foxnews.com/foxnews/sports',
    ];

    if ($site_config['autoshout_on'] == 1) {
        include_once INCL_DIR . 'user_functions.php';
        foreach ($feeds as $key => $feed) {
            $hash = md5($feed);
            $xml = $cache->get('foxnewsrss_' . $hash);
            if ($xml === false || is_null($xml)) {
                $xml = fetch($feed);
                $cache->set('foxnewsrss_' . $hash, $xml, 300);
            }
            $doc = new DOMDocument();
            @$doc->loadXML($xml);
            $items = $doc->getElementsByTagName('item');
            $pubs = [];
            foreach ($items as $item) {
                $title = empty($item->getElementsByTagName('title')
                    ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('title')
                    ->item(0)->nodeValue;
                $link = empty($item->getElementsByTagName('link')
                    ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('link')
                    ->item(0)->nodeValue;
                $pubs[] = [
                    'title' => replace_unicode_strings($title),
                    'link' => replace_unicode_strings($link),
                ];
            }
            $pubs = array_reverse($pubs);
            $count = count($pubs);
            $i = 1;
            foreach ($pubs as $pub) {
                if (empty($pub['link'])) {
                    continue;
                }
                $link = hash('sha256', $pub['link']);
                if (in_array($link, $links)) {
                    continue;
                }
                $links[] = $link;
                $values = [
                    'link' => $link,
                ];
                $query = $fluent->insertInto('newsrss')
                    ->values($values);
                $newid = $query->execute();
                if ($newid) {
                    $cache->set('tfreak_news_links_', $links, 86400);
                    if (!$empty || $count === $i++) {
                        $msg = "[color=yellow]In $key News:[/color] [url={$pub['link']}]{$pub['title']}[/url]";
                        autoshout($msg, 0, 1800);
                        autoshout($msg, 3, 0);
                        break;
                    }
                }
            }
        }

        return true;
    }

    return false;
}

/**
 * @param array $links
 *
 * @return bool
 */
function tfreak_shout($links = [])
{
    global $site_config, $cache, $fluent;

    $empty = empty($links);
    if ($site_config['autoshout_on'] == 1) {
        include_once INCL_DIR . 'user_functions.php';
        $xml = $cache->get('tfreaknewsrss_');
        if ($xml === false || is_null($xml)) {
            $xml = fetch('http://feed.torrentfreak.com/Torrentfreak/');
            $cache->set('tfreaknewsrss_', $xml, 300);
        }
        $doc = new DOMDocument();
        @$doc->loadXML($xml);
        $items = $doc->getElementsByTagName('item');
        $pubs = [];
        foreach ($items as $item) {
            $title = empty($item->getElementsByTagName('title')
                ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('title')
                ->item(0)->nodeValue;
            $link = empty($item->getElementsByTagName('link')
                ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('link')
                ->item(0)->nodeValue;
            $pubs[] = [
                'title' => replace_unicode_strings($title),
                'link' => replace_unicode_strings($link),
            ];
        }
        $pubs = array_reverse($pubs);
        $count = count($pubs);
        $i = 1;
        foreach ($pubs as $pub) {
            if (empty($pub['link'])) {
                continue;
            }
            $link = hash('sha256', $pub['link']);
            if (in_array($link, $links)) {
                continue;
            }
            $links[] = $link;
            $values = [
                'link' => $link,
            ];
            $query = $fluent->insertInto('newsrss')
                ->values($values);
            $newid = $query->execute();
            if ($newid) {
                $cache->set('tfreak_news_links_', $links, 86400);
                if (!$empty || $count === $i++) {
                    $msg = "[color=yellow]TFreak News:[/color] [url={$pub['link']}]{$pub['title']}[/url]";
                    autoshout($msg, 0, 1800);
                    autoshout($msg, 3, 0);
                    break;
                }
            }
        }
    }
}

/**
 * @param array $links
 *
 * @return bool
 */
function github_shout($links = [])
{
    global $site_config, $cache, $fluent;

    $empty = empty($links);
    $feeds = [
        //'dev'    => 'https://github.com/darkalchemy/Pu-239/commits/dev.atom',
        'master' => 'https://github.com/darkalchemy/Pu-239/commits/master.atom',
    ];
    if ($site_config['autoshout_on'] == 1) {
        include_once INCL_DIR . 'user_functions.php';
        foreach ($feeds as $key => $feed) {
            $hash = md5($feed);
            $rss = $cache->get('githubcommitrss_' . $hash);
            if ($rss === false || is_null($rss)) {
                $rss = fetch($feed);
                $cache->set('githubcommitrss_' . $hash, $rss, 300);
            }
            $xml = simplexml_load_string($rss);
            $items = $xml->entry;
            $pubs = [];
            foreach ($items as $item) {
                $devices = json_decode(json_encode($item), true);
                preg_match('/Commit\/(.*)/', $devices['id'], $match);
                $commit = trim($match[1]);
                $title = trim($devices['title']);
                $link = trim($devices['link']['@attributes']['href']);

                $pubs[] = [
                    'title' => replace_unicode_strings($title),
                    'link' => replace_unicode_strings($link),
                    'commit' => replace_unicode_strings($commit),
                ];
            }
            $pubs = array_reverse($pubs);
            $count = count($pubs);
            $i = 1;
            foreach ($pubs as $pub) {
                if (empty($pub['link'])) {
                    continue;
                }
                $link = hash('sha256', $pub['link']);
                if (in_array($link, $links)) {
                    continue;
                }
                $links[] = $link;
                $values = [
                    'link' => $link,
                ];
                $query = $fluent->insertInto('newsrss')
                    ->values($values);
                $newid = $query->execute();
                if ($newid) {
                    $cache->set('tfreak_news_links_', $links, 86400);
                    if (!$empty || $count === $i++) {
                        $msg = "[color=yellow]Git Commit [$key branch]:[/color] [url={$pub['link']}]{$pub['title']}[/url] => {$pub['commit']}";
                        autoshout($msg, 0, 1800);
                        autoshout($msg, 4, 0);
                        break;
                    }
                }
            }
        }
    }
}
