<?php

$HTMLOUT .= "
    <a id='scroller-hash'></a>
    <fieldset id='scroller' class='header'>
        <legend class='flipper'>
            <i class='fa fa-angle-up' aria-hidden='true'></i>
            {$lang['index_latest']}
        </legend>
        <div id='carousel-container' class='carousel-container'>
            <div id='icarousel' class='icarousel'>";

if (($scroll_torrents = $mc1->get_value('scroll_tor_')) === false) {
    $scroll = sql_query("SELECT id, seeders, leechers, name, poster
                            FROM torrents
                            WHERE seeders >= 0
                            ORDER BY added
                            DESC LIMIT {$site_config['latest_torrents_limit_scroll']}") or sqlerr(__FILE__, __LINE__);
    while ($scroll_torrent = mysqli_fetch_assoc($scroll)) {
        $scroll_torrents[] = $scroll_torrent;
    }
    $mc1->cache_value('scroll_tor_', $scroll_torrents, $site_config['expires']['scroll_torrents']);
}

if ($scroll_torrents) {
    foreach ($scroll_torrents as $s_t) {
        $i = $site_config['latest_torrents_limit_scroll'];
        $id = (int)$s_t['id'];
        $name = htmlsafechars($s_t['name']);
        $poster = ($s_t['poster'] == '' ? '' . $site_config['pic_base_url'] . 'noposter.png' : htmlsafechars($s_t['poster']));
        $seeders = number_format((int)$s_t['seeders']);
        $leechers = number_format((int)$s_t['leechers']);
        $name = str_replace('_', ' ', $name);
        $name = str_replace('.', ' ', $name);
        $name = substr($name, 0, 50);

        $HTMLOUT .= "
                <div class='slide'>
                    <a href='./details?id={$id}'>
                        <img src='" . htmlsafechars($poster) . "' alt='{$name}' title='{$name}\n{$lang['latesttorrents_seeders']} : {$seeders}\n{$lang['latesttorrents_leechers']} : {$leechers}' width='200' height='350' border='0' />
                    </a>
                </div>";
    }
}

$HTMLOUT .= '
            </div>
        </div>
    </fieldset>';
