<?php

function foxnews_shout()
{
    global $INSTALLER09, $mc1;
    if ($INSTALLER09['autoshout_on'] == 1) {
        require_once INCL_DIR . 'user_functions.php';
        if (($xml = $mc1->get_value('foxnewsrss_')) === false) {
            $xml = file_get_contents('http://feeds.foxnews.com/foxnews/latest');
            $mc1->cache_value('foxnewsrss_', $xml, 300);
        }
        $doc = new DOMDocument();
        @$doc->loadXML($xml);
        $items = $doc->getElementsByTagName('item');
        $pubs = [];
        foreach ($items as $item) {
            $title       = empty($item->getElementsByTagName('title')      ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('title')      ->item(0)->nodeValue;
            $link        = empty($item->getElementsByTagName('link')       ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('link')       ->item(0)->nodeValue;
            $pubs[] = [
                        'title' => $title,
                        'link' => $link
            ];
        }
        $pubs = array_reverse($pubs);
        foreach ($pubs as $pub) {
            $title = sqlesc(htmlsafechars($pub['title']));
            $link = sqlesc(htmlsafechars($pub['link']));
            sql_query("INSERT INTO newsrss (title, link)
                        SELECT $title, $link
                        FROM DUAL
                        WHERE NOT EXISTS(
                            SELECT 1
                            FROM newsrss
                            WHERE title = $title AND link = $link
                        )
                        LIMIT 1") or sqlerr(__FILE__, __LINE__);
            $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
            if ($newid) {
                $msg = "[color=yellow]In The News:[/color] [url={$pub['link']}]" . htmlsafechars($pub['title']) . "[/url]";
                autoshout($msg);
            }
        }
    }
}

function tfreak_shout()
{
    global $INSTALLER09, $mc1;
    if ($INSTALLER09['autoshout_on'] == 1) {
        require_once INCL_DIR . 'user_functions.php';
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
            $link  = empty($item->getElementsByTagName('link') ->item(0)->nodeValue) ? '' : $item->getElementsByTagName('link') ->item(0)->nodeValue;
            $pubs[] = [
                        'title' => $title,
                        'link' => $link
            ];
        }
        $pubs = array_reverse($pubs);
        foreach ($pubs as $pub) {
            $title = sqlesc(htmlsafechars($pub['title']));
            $link = sqlesc(htmlsafechars($pub['link']));
            sql_query("INSERT INTO newsrss (title, link)
                        SELECT $title, $link
                        FROM DUAL
                        WHERE NOT EXISTS(
                            SELECT 1
                            FROM newsrss
                            WHERE title = $title AND link = $link
                        )
                        LIMIT 1") or sqlerr(__FILE__, __LINE__);
            $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
            if ($newid) {
                $msg = "[color=yellow]In The News:[/color] [url={$pub['link']}]" . htmlsafechars($pub['title']) . "[/url]";
                autoshout($msg);
            }
        }
    }
}

function github_shout()
{
    global $INSTALLER09, $mc1;
    if ($INSTALLER09['autoshout_on'] == 1) {
        require_once INCL_DIR . 'user_functions.php';
        if (($rss = $mc1->get_value('githubcommitrss_')) === false) {
            $rss = file_get_contents('https://github.com/darkalchemy/P-239-V1/commits/master.atom');
            $mc1->cache_value('githubcommitrss_', $rss, 300);
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
                        'title' => $title,
                        'link' => $link,
                        'author' => $author,
                        'commit' => $commit
            ];
        }
        $pubs = array_reverse($pubs);
        foreach ($pubs as $pub) {
            $title = sqlesc(htmlsafechars($pub['title']));
            $link = sqlesc(htmlsafechars($pub['link']));
            sql_query("INSERT INTO newsrss (title, link)
                        SELECT $title, $link
                        FROM DUAL
                        WHERE NOT EXISTS(
                            SELECT 1
                            FROM newsrss
                            WHERE title = $title AND link = $link
                        )
                        LIMIT 1") or sqlerr(__FILE__, __LINE__);
            $newid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS['___mysqli_ston']))) ? false : $___mysqli_res);
            if ($newid) {
                $msg = "[color=yellow]Git Commit:[/color] [url={$pub['link']}]" . htmlsafechars($pub['title']) . "[/url]";
                autoshout($msg);
            }
        }
    }
}
