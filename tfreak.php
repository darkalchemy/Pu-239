<?php

/**
 |--------------------------------------------------------------------------|
 |   https://github.com/Bigjoos/                                |
 |--------------------------------------------------------------------------|
 |   Licence Info: GPL                                              |
 |--------------------------------------------------------------------------|
 |   Copyright (C) 2010 U-232 V4                        |
 |--------------------------------------------------------------------------|
 |   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.   |
 |--------------------------------------------------------------------------|
 |   Project Leaders: Mindless,putyn.                       |
 |--------------------------------------------------------------------------|
 _   _   _   _   _     _   _   _   _   _   _     _   _   _   _
 / \ / \ / \ / \ / \   / \ / \ / \ / \ / \ / \   / \ / \ / \ / \
 ( U | - | 2 | 3 | 2 )-( S | o | u | r | c | e )-( C | o | d | e )
 \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/ \_/ \_/   \_/ \_/ \_/ \_/
 */
function rsstfreakinfo() {
    require_once INCL_DIR . 'html_functions.php';
    global $INSTALLER09;
    $html = '';
    $use_limit = true;
    $limit = 5;
    $xml = file_get_contents('http://feed.torrentfreak.com/Torrentfreak/');
    $html = begin_main_frame() . begin_frame('Torrent Freak news');
    $icount = 1;
    $doc = new DOMDocument();
    @$doc->loadXML($xml);
    $items = $doc->getElementsByTagName('item');
    foreach ($items as $item) {
        $html.= '<h3><u>' . $item->getElementsByTagName('title')->item(0)->nodeValue . '</u></h3><font class="small">by ' . str_replace(array('<![CDATA[', ']]>'), '', $item->getElementsByTagName('creator')->item(0)->nodeValue) . ' on ' . $item->getElementsByTagName('pubDate')->item(0)->nodeValue . '</font><br />' . str_replace(array('<![CDATA[', ']]>'), '', $item->getElementsByTagName('description')->item(0)->nodeValue) . '<br /><a href="' . $item->getElementsByTagName('link')->item(0)->nodeValue . '" target="_blank"><font class="small">Read more</font></a>';
        if ($use_limit && $icount == $limit) break;
        $icount++;
    }
    $html = str_replace(array('“','”'), '"', $html);
    $html = str_replace(array("’","‘", "‘"), "'", $html);
    $html = str_replace("–", "-", $html);
    $html.= end_frame() . end_main_frame();
    return $html;
}
?>
