<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
dbconn();
global $site_config;

$torrent_pass = (isset($_GET['torrent_pass']) ? htmlsafechars($_GET['torrent_pass']) : '');
$feed = (isset($_GET['type']) && $_GET['type'] === 'dl' ? 'dl' : 'web');
$cats = (isset($_GET['cats']) ? $_GET['cats'] : '');
if ($cats) {
    $validate_cats = explode(',', $cats);
    $cats = implode(', ', array_map('intval', $validate_cats));
    $cats = implode(', ', array_map('sqlesc', $validate_cats));
}
if (!empty($torrent_pass)) {
    if (strlen($torrent_pass) != 64) {
        die('Your passkey is not long enough! Go to ' . $site_config['site_name'] . ' and reset your passkey');
    } else {
        $q0 = sql_query('SELECT id, class FROM users WHERE torrent_pass = ' . sqlesc($torrent_pass)) or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($q0) == 0) {
            die('Your passkey is invalid! Go to ' . $site_config['site_name'] . ' and reset your passkey');
        } else {
            $CURUSER = mysqli_fetch_assoc($q0);
        }
    }
} else {
    die("Your link doesn't have a passkey");
}
$site_config['rssdescr'] = $site_config['site_name'] . ' RSS Feed - Please Donate';
$where = [];
$join = $limit = '';
$where[] = "t.visible != 'yes'";
if (!empty($cats)) {
    $where[] = "t.category IN ($cats)";
}
if ($CURUSER['class'] < UC_VIP) {
    $where[] = "t.vip = '0'";
}
if (isset($_POST['bm']) && is_int($_POST['bm']) && $_POST['bm'] == 1) {
    $join = 'LEFT JOIN bookmarks AS b ON b.torrentid = t.id';
}
$counts = [15, 30, 50, 100];
if (!empty($_GET['count']) && in_array((int) $_GET['count'], $counts)) {
    $limit = 'LIMIT ' . (int) $_GET['count'];
} else {
    $limit = 'LIMIT 15';
}

$url = htmlsafechars($site_config['baseurl'] . $_SERVER['REQUEST_URI']);

$HTMLOUT = "<?xml version='1.0'?>
<?xml-stylesheet type='text/css' href='{$site_config['baseurl']}/css/1/rss.css\" ?>
<rss version='2.0' xmlns:atom='http://www.w3.org/2005/Atom'>
    <channel>
        <title>{$site_config['site_name']}</title>
        <link>{$site_config['baseurl']}</link>
        <description>{$site_config['rssdescr']}</description>
        <language>en-us</language>
        <copyright>Copyright © " . date('Y') . " {$site_config['site_name']}</copyright>
        <webMaster>{$site_config['site_email']}({$site_config['site_name']})</webMaster>
        <atom:link href='{$url}' rel='self' type='application/rss+xml' />
        <image>
            <title>{$site_config['site_name']}</title>
            <url>{$site_config['baseurl']}/favicon-16x16.png</url>
            <link>{$site_config['baseurl']}</link>
            <width>16</width>
            <height>16</height>
            <description>{$site_config['rssdescr']}</description>
        </image>";

$sql = "SELECT t.id, t.name, t.descr, t.size, t.category, t.seeders, t.leechers, t.added, c.name as catname
        FROM torrents as t
        $join
        LEFT JOIN categories as c ON t.category = c.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY t.added
        DESC $limit";

$res = sql_query($sql) or sqlerr(__FILE__, __LINE__);

while ($a = mysqli_fetch_assoc($res)) {
    $link = $site_config['baseurl'] . ($feed === 'dl' ? '/download.php?torrent=' . (int) $a['id'] . '&amp;torrent_pass=' . $torrent_pass : '/details.php?id=' . (int) $a['id'] . '&amp;hit=1');
    $br = '&lt;br/&gt;';
    $guidlink = $site_config['baseurl'] . '/details.php?id=' . (int) $a['id'];
    $HTMLOUT .= '
        <item>
            <title>' . htmlsafechars($a['name']) . "</title>
            <link>{$link}</link>
            <description>{$br}Category: " . htmlsafechars($a['catname']) . " {$br} Size: " . mksize((int) $a['size']) . " {$br} Leechers: " . (int) $a['leechers'] . " {$br} Seeders: " . (int) $a['seeders'] . " {$br} Added: " . get_date($a['added'], 'DATE') . " {$br} Description: " . htmlsafechars(substr($a['descr'], 0, 450)) . " {$br}</description>
            <guid>{$guidlink}</guid>
            <pubDate>" . date(DATE_RSS, $a['added']) . '</pubDate>
        </item>';
}
$HTMLOUT .= '
    </channel>
</rss>';

header('Content-Type: application/xml');
echo $HTMLOUT;
