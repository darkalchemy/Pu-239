<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\User;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_bbcode.php';
$torrent_pass = isset($_GET['torrent_pass']) ? htmlsafechars($_GET['torrent_pass']) : '';
global $container, $site_config;

if (!empty($torrent_pass)) {
    if (strlen($torrent_pass) != 64) {
        format_rss('Your torrent pass is not long enough! Go to ' . $site_config['site']['name'] . ' and reset your passkey', null);
    } else {
        $users_class = $container->get(User::class);
        $user = $users_class->get_user_from_torrent_pass($torrent_pass);
        if (!$user) {
            format_rss('Your torrent pass is invalid! Go to ' . $site_config['site']['name'] . ' and reset your passkey', null);
        } elseif ($user['enabled'] === 'no') {
            format_rss("Permission denied, you're account is disabled", null);
        } elseif ($user['parked'] === 'yes') {
            format_rss("Permission denied, you're account is parked", null);
        } elseif ($user['downloadpos'] != 1) {
            format_rss('Your download privileges have been removed.', null);
        }
    }
} else {
    format_rss("Your link doesn't have a torrent pass", null);
}
$cats = isset($_GET['cats']) ? $_GET['cats'] : '';
if ($cats) {
    $cats = explode(',', $cats);
} else {
    $cats = [];
}

$counts = [
    15,
    30,
    50,
    100,
];
if (!empty($_GET['count']) && in_array((int) $_GET['count'], $counts)) {
    $limit = (int) $_GET['count'];
} else {
    $limit = 15;
}

$cache = $container->get(Cache::class);
$hash = hash('sha256', json_encode($_POST));
$cache->delete('rss_query_' . $hash);
$data = $cache->get('rss_query_' . $hash);
if ($data === false || is_null($data)) {
    $fluent = $container->get(Database::class);
    $data = $fluent->from('torrents AS t')
                   ->select(null)
                   ->select('t.id')
                   ->select('t.name')
                   ->select('t.descr')
                   ->select('t.size')
                   ->select('t.category')
                   ->select('t.seeders')
                   ->select('t.leechers')
                   ->select('t.added')
                   ->select('c.name AS catname')
                   ->where('t.visible = "yes"');

    if (!empty($cats)) {
        $data = $data->where('t.category', $cats);
    }
    if ($user['class'] != UC_VIP) {
        $data = $data->where('t.vip = "0"');
    }
    if (isset($_GET['bm']) && (int) $_GET['bm'] === 1) {
        $data = $data->where('b.userid = ?', $user['id'])
                     ->innerJoin('bookmarks AS b ON t.id=b.torrentid');
    }

    $data = $data->leftJoin('categories AS c ON t.category = c.id')
                 ->orderBy('t.added DESC')
                 ->limit($limit)
                 ->fetchAll();

    if (!empty($data)) {
        $cache->set('rss_query_' . $hash, $data, 300);
    } else {
        $data = 'No results in your request';
    }
}

format_rss($data, $torrent_pass);

/**
 * @param             $data
 * @param string|null $torrent_pass
 *
 * @throws DependencyException
 * @throws InvalidManipulation
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function format_rss($data, ?string $torrent_pass)
{
    global $site_config;

    $rssdescr = $site_config['site']['name'] . ' RSS Feed - Please Donate';
    $feed = isset($_GET['type']) && $_GET['type'] === 'dl' ? 'dl' : 'web';
    $url = urlencode($site_config['paths']['baseurl'] . $_SERVER['REQUEST_URI']);
    $br = ''; // '<br />'; TODO
    $date = date(DATE_RSS, TIME_NOW);

    $rss = '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/css" href="' . $site_config['paths']['baseurl'] . '/css/rss.css"?>
<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:slash="http://purl.org/rss/1.0/modules/slash/">
    <channel>
        <title>' . $site_config['site']['name'] . '</title>
        <atom:link href="' . $url . '" rel="self" type="application/rss+xml" />
        <link>' . $site_config['paths']['baseurl'] . '</link>
        <description>' . $rssdescr . '</description>
        <language>en-us</language>
        <copyright>Copyright © ' . date('Y') . ' ' . $site_config['site']['name'] . '</copyright>
        <webMaster>' . $site_config['site']['email'] . '(' . $site_config['site']['name'] . ')</webMaster>
        <lastBuildDate>' . $date . '</lastBuildDate>
        <ttl>5</ttl>
        <image>
            <title>' . $site_config['site']['name'] . '</title>
            <url>' . $site_config['paths']['baseurl'] . '/favicon-16x16.png</url>
            <link>' . $site_config['paths']['baseurl'] . '</link>
            <width>16</width>
            <height>16</height>
            <description>' . $rssdescr . '</description>
        </image>';

    if (is_array($data)) {
        foreach ($data as $a) {
            $id = (int) $a['id'];
            $size = mksize((int) $a['size']);
            $seeders = (int) $a['seeders'];
            $leechers = (int) $a['leechers'];
            $name = htmlsafechars($a['name']);
            $cat = htmlsafechars($a['catname']);
            $added = get_date((int) $a['added'], 'DATE');
            $descr = htmlsafechars(substr(format_comment_no_bbcode(strip_tags($a['descr'])), 0, 450));
            $date = date(DATE_RSS, $a['added']);
            $link = $site_config['paths']['baseurl'] . ($feed === 'dl' ? '/download.php?torrent=' . $id . '&amp;torrent_pass=' . $torrent_pass : '/details.php?id=' . $id . '&amp;hit=1');
            $guidlink = $site_config['paths']['baseurl'] . '/details.php?id=' . $id;
            $rss .= '
        <item>
            <title>' . $name . '</title>
            <link>' . $link . '</link>
            <description>' . $br . 'Category: ' . $cat . $br . 'Size: ' . $size . $br . 'Leechers: ' . $leechers . $br . 'Seeders: ' . $seeders . $br . 'Added: ' . $added . $br . 'Description: ' . $descr . $br . '</description>
            <guid>' . $guidlink . '</guid>
            <pubDate>' . $date . '</pubDate>
        </item>';
        }
    } else {
        $rss .= '
        <item>
            <title>Empty Results</title>
            <link>' . $site_config['paths']['baseurl'] . '/getrss.php</link>
            <description>' . $data . '</description>
            <guid>' . $site_config['paths']['baseurl'] . '/getrss.php</guid>
            <pubDate>' . $date . '</pubDate>
        </item>';
    }

    $rss .= '
    </channel>
</rss>';

    header('Content-Type: application/xml');
    echo $rss;
    die();
}
