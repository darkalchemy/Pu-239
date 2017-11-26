<?php
global $site_config, $cache, $lang, $db;

$scroll_torrents = $cache->get('scroll_tor_');
if ($scroll_torrents === false || is_null($scroll_torrents)) {
    $query = $db->from('torrents')
        ->leftJoin('users ON torrents.owner = users.id')
        ->select('users.username')
        ->select('users.class')
        ->orderBy('added DESC')
        ->limit($site_config['latest_torrents_limit_scroll']);
    foreach ($query as $row) {
        $scroll_torrents[] = $row;
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

    foreach ($scroll_torrents as $scroll_torrent) {
        $i = $site_config['latest_torrents_limit_scroll'];
        $poster = ($scroll_torrent['poster'] == '' ? '' . $site_config['pic_base_url'] . 'noposter.png' : htmlsafechars($scroll_torrent['poster']));
        $HTMLOUT .= "
                    <div class='slide'>
                        <a href='{$site_config['baseurl']}/details.php?id=" . (int)$scroll_torrent['id'] . "&amp;hit=1'>
                            <div class='dt-tooltipper-small' data-tooltip-content='#scroll_id_{$scroll_torrent['id']}_tooltip'>
                            <img src='" . htmlsafechars($poster) . "' alt='{$scroll_torrent['name']}' width='200' height='300' border='0' />
                                <div class='tooltip_templates'>
                                    <span id='scroll_id_{$scroll_torrent['id']}_tooltip'>
                                        <span>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($scroll_torrent['name']) . "<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b><span class='" . get_user_class_name($scroll_torrent['class'], true) . "'>" . htmlsafechars($scroll_torrent['username']) . "</span><br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($scroll_torrent['added'], 'DATE', 0, 1) . "<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($scroll_torrent['size'])) . "<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>" . (int)$scroll_torrent['seeders'] . "<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>" . (int)$scroll_torrent['leechers'] . "<br>
                                        </span>
                                    </span>
                                </div>
                            </div>
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

