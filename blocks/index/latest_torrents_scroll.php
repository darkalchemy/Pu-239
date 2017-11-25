<?php
global $site_config, $cache, $lang;

if (($scroll_torrents = $cache->get('scroll_tor_')) === false) {
    $scroll = sql_query("SELECT id, seeders, leechers, name, poster
                            FROM torrents
                            WHERE seeders >= 0
                            ORDER BY added
                            DESC LIMIT {$site_config['latest_torrents_limit_scroll']}") or sqlerr(__FILE__, __LINE__);
    while ($scroll_torrent = mysqli_fetch_assoc($scroll)) {
        $scroll_torrents[] = $scroll_torrent;
    }
    $cache->set('scroll_tor_', $scroll_torrents, $site_config['expires']['scroll_torrents']);
}

if ($scroll_torrents) {
    $HTMLOUT .= "
    <a id='scroller-hash'></a>
    <fieldset id='scroller' class='header'>
        <legend class='flipper has-text-primary'>
            <i class='fa fa-angle-up right10' aria-hidden='true'></i>
            {$lang['index_latest']} Scroller
        </legend>
        <div class='bordered'>
            <div id='carousel-container' class='alt_bordered bg-00 carousel-container'>
                <div id='icarousel' class='icarousel'>";

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
                        <a href='./details.php?id={$id}'>
                            <img src='" . htmlsafechars($poster) . "' class='tooltipper' alt='{$name}' title='{$name}<br>{$lang['latesttorrents_seeders']} : {$seeders}<br>{$lang['latesttorrents_leechers']} : {$leechers}' width='200' height='300' border='0' />
                        </a>
                    </div>";
    }


    $HTMLOUT .= '
                </div>
            </div>
        </div>
    </fieldset>';
} else {
    $HTMLOUT .= "
    <a id='scroller-hash'></a>
    <fieldset id='scroller' class='header'>
        <legend class='flipper has-text-primary'>
            <i class='fa fa-angle-up right10' aria-hidden='true'></i>
            {$lang['index_latest']} Scroller
        </legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>
                No torrents found.
            </div>
        </div>
    </fieldset>";
}

