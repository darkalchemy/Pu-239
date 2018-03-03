<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$scroll_torrents = $cache->get('scroll_tor_');
if ($scroll_torrents === false || is_null($scroll_torrents)) {
    $scroll_torrents = $fluent->from('torrents')
        ->select(null)
        ->select('torrents.id')
        ->select('torrents.added')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.name')
        ->select('torrents.size')
        ->select('torrents.poster')
        ->select('torrents.anonymous')
        ->select('torrents.owner')
        ->select('users.username')
        ->select('users.class')
        ->leftJoin('users ON torrents.owner = users.id')
        ->where('poster != ""')
        ->orderBy('torrents.added DESC')
        ->limit($site_config['latest_torrents_limit_scroll'])
        ->fetchAll();

    $cache->set('scroll_tor_', $scroll_torrents, $site_config['expires']['scroll_torrents']);
}

if ($scroll_torrents) {
    $HTMLOUT .= "
    <a id='scroller-hash'></a>
    <fieldset id='scroller' class='header'>
        <legend class='flipper has-text-primary'>
            <i class='fa icon-up-open size_3' aria-hidden='true'></i>
            {$lang['index_latest']} Scroller
        </legend>
        <div class='bordered'>
            <div id='carousel-container' class='alt_bordered bg-00 carousel-container'>
                <div id='icarousel' class='icarousel'>";

    foreach ($scroll_torrents as $scroll_torrent) {
        $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = '';
        extract($scroll_torrent);
        $i = $site_config['latest_torrents_limit_scroll'];

        if ('yes' == $anonymous && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
            $uploader = '<span>' . get_anonymous_name() . '</span>';
        } else {
            $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
        }

        $HTMLOUT .= "
                    <div class='slide'>
                        <a href='{$site_config['baseurl']}/details.php?id={$id}&amp;hit=1'>
                            <div class='dt-tooltipper-small' data-tooltip-content='#scroll_id_{$id}_tooltip'>
                            <img src='" . image_proxy($poster) . "' alt='{$name}' style='width: auto; height: 300px; max-height: 300px;'  />
                                <div class='tooltip_templates'>
                                    <span id='scroll_id_{$id}_tooltip'>
                                        <span>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($name) . "<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b>$username<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($added, 'DATE', 0, 1) . "<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($size)) . "<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>{$seeders}<br>
                                            <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>{$leechers}<br>
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
            <i class='fa icon-up-open size_3' aria-hidden='true'></i>
            {$lang['index_latest']} Scroller
        </legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>
                {$lang['last5torrents_no_torrents']}
            </div>
        </div>
    </fieldset>";
}
