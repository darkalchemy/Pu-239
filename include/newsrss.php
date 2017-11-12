<?php

/**
 * @param array $links
 *
 * @return bool
 */
function foxnews_shout($links = [])
{
    global $site_config, $mc1;
    $feeds = [
        'Tech'          => 'http://feeds.foxnews.com/foxnews/tech',
        'World'         => 'http://feeds.foxnews.com/foxnews/world',
        'Entertainment' => 'http://feeds.foxnews.com/foxnews/entertainment',
    ];

    if ($site_config['autoshout_on'] == 1) {
        include_once INCL_DIR . 'user_functions.php';
        foreach ($feeds as $key => $feed) {
            $hash = md5($feed);
            if (($xml = $mc1->get_value('foxnewsrss_' . $hash)) === false) {
                $xml = file_get_contents($feed);
                $mc1->cache_value('foxnewsrss_' . $hash, $xml, 300);
            }
            $doc = new DOMDocument();
            @$doc->loadXML($xml);
            $items = $doc->getElementsByTagName('item');
            $pubs = [];
            foreach ($items as $item) {
                $title = empty($item->getElementsByTagName('title')->item(0)->nodeValue) ? '' : $item->getElementsByTagName('title')->item(0)->nodeValue;
                $link = empty($item->getElementsByTagName('link')->item(0)->nodeValue) ? '' : $item->getElementsByTagName('link')->item(0)->nodeValue;
                $pubs[] = [
                    'title' => replace_unicode_strings($title),
                    'link'  => replace_unicode_strings($link),
                ];
            }
            $pubs = array_reverse($pubs);
            foreach ($pubs as $pub) {
                $link = hash('sha256', $pub['link']);
                if (in_array($link, $links)) {
                    continue;
                }
                $links[] = $link;
                $link = sqlesc($link);
                $mc1->cache_value('tfreak_news_links_', $links, 86400);
                sql_query(
                    "INSERT INTO newsrss (link)
                        SELECT $link
                        FROM DUAL
                        WHERE NOT EXISTS(
                            SELECT 1
                            FROM newsrss
                            WHERE link = $link
                        )
                        LIMIT 1"
                ) or sqlerr(__FILE__, __LINE__);
                $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
                if ($newid) {
                    $msg = "[color=yellow]In $key News:[/color] [url={$pub['link']}]{$pub['title']}[/url]";
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
function tfreak_shout($links = [])
{
    global $site_config, $mc1;
    if ($site_config['autoshout_on'] == 1) {
        include_once INCL_DIR . 'user_functions.php';
        if (($xml = $mc1->get_value('tfreaknewsrss_')) === false) {
            $xml = file_get_contents('http://feed.torrentfreak.com/Torrentfreak/');
            $mc1->cache_value('tfreaknewsrss_', $xml, 300);
        }
        $doc = new DOMDocument();
        @$doc->loadXML($xml);
        $items = $doc->getElementsByTagName('item');
        $pubs = [];
        foreach ($items as $item) {
            $title = empty($item->getElementsByTagName('title')->item(0)->nodeValue) ? '' : $item->getElementsByTagName('title')->item(0)->nodeValue;
            $link = empty($item->getElementsByTagName('link')->item(0)->nodeValue) ? '' : $item->getElementsByTagName('link')->item(0)->nodeValue;
            $pubs[] = [
                'title' => replace_unicode_strings($title),
                'link'  => replace_unicode_strings($link),
            ];
        }
        $pubs = array_reverse($pubs);
        foreach ($pubs as $pub) {
            $link = hash('sha256', $pub['link']);
            if (in_array($link, $links)) {
                continue;
            }
            $links[] = $link;
            $link = sqlesc($link);
            $mc1->cache_value('tfreak_news_links_', $links, 86400);
            sql_query(
                "INSERT INTO newsrss (link)
                        SELECT $link
                        FROM DUAL
                        WHERE NOT EXISTS(
                            SELECT 1
                            FROM newsrss
                            WHERE link = $link
                        )
                        LIMIT 1"
            ) or sqlerr(__FILE__, __LINE__);
            $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
            if ($newid) {
                $msg = "[color=yellow]TFreak News:[/color] [url={$pub['link']}]{$pub['title']}[/url]";
                autoshout($msg, 0, 1800);
                autoshout($msg, 3, 0);
                break;
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
    global $site_config, $mc1;
    $feeds = [
        'dev'    => 'https://github.com/darkalchemy/Pu-239/commits/dev.atom',
        'master' => 'https://github.com/darkalchemy/Pu-239/commits/master.atom',
    ];
    if ($site_config['autoshout_on'] == 1) {
        include_once INCL_DIR . 'user_functions.php';
        foreach ($feeds as $key => $feed) {
            $hash = md5($feed);
            if (($rss = $mc1->get_value('githubcommitrss_' . $hash)) === false) {
                $rss = file_get_contents($feed);
                $mc1->cache_value('githubcommitrss_' . $hash, $rss, 300);
            }
            $xml = simplexml_load_string($rss);
            $items = $xml->entry;
            $pubs = [];
            foreach ($items as $item) {
                $devices = json_decode(json_encode($item), true);
                preg_match('/Commit\/(.*)/', $devices['id'], $match);
                $commit = trim($match[1]);
                $title = trim($devices['title']);
                $link = trim($devices['link']["@attributes"]['href']);
                $author = trim($devices['author']['name']);

                $pubs[] = [
                    'title'  => replace_unicode_strings($title),
                    'link'   => replace_unicode_strings($link),
                    'author' => replace_unicode_strings($author),
                    'commit' => replace_unicode_strings($commit),
                ];
            }
            $pubs = array_reverse($pubs);
            foreach ($pubs as $pub) {
                $link = hash('sha256', $pub['link']);
                if (in_array($link, $links)) {
                    continue;
                }
                $links[] = $link;
                $link = sqlesc($link);
                $mc1->cache_value('tfreak_news_links_', $links, 86400);
                sql_query(
                    "INSERT INTO newsrss (link)
                        SELECT $link
                        FROM DUAL
                        WHERE NOT EXISTS(
                            SELECT 1
                            FROM newsrss
                            WHERE link = $link
                        )
                        LIMIT 1"
                ) or sqlerr(__FILE__, __LINE__);
                $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
                if ($newid) {
                    $msg = "[color=yellow]Git Commit [$key branch]:[/color] [url={$pub['link']}]{$pub['title']}[/url] => {$pub['commit']}";
                    autoshout($msg, 0, 1800);
                    autoshout($msg, 4, 0);
                    break;
                }
            }
        }
    }
}
