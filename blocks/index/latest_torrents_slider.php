<?php

global $site_config, $lang, $fluent, $CURUSER, $cache;

$slider_torrents = $cache->get('sliderl_torrents_');
if ($slider_torrents === false || is_null($slider_torrents)) {
    $slider_torrents = $fluent->from('torrents')
        ->select(null)
        ->select('torrents.id')
        ->select('torrents.added')
        ->select('torrents.seeders')
        ->select('torrents.leechers')
        ->select('torrents.name')
        ->select('torrents.size')
        ->select('torrents.poster')
        ->select('torrents.banner')
        ->select('torrents.anonymous')
        ->select('torrents.owner')
        ->select('users.username')
        ->select('users.class')
        ->leftJoin('users ON torrents.owner = users.id')
        ->where('banner != ""')
        ->where('poster != ""')
        ->orderBy('torrents.added DESC')
        ->limit($site_config['latest_torrents_limit_slider'])
        ->fetchAll();

    $cache->set('slider_torrents_', $slider_torrents, $site_config['expires']['slider_torrents']);
}

if ($slider_torrents) {
    $HTMLOUT .= "
    <a id='slider-hash'></a>
    <fieldset id='slider' class='header'>
        <legend class='flipper has-text-primary'>
            <i class='icon-down-open size_3' aria-hidden='true'></i>
            {$lang['index_latest']} Slider
        </legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00 automatic-slider flexslider'>
                <ul class='slides'>";
    $i = 0;
    foreach ($slider_torrents as $slider_torrent) {
        $owner = $anonymous = $name = $poster = $seeders = $leechers = $size = $added = $class = $username = $id = $cat = $image = '';
        extract($slider_torrent);
        $i = $site_config['latest_torrents_limit_slider'];

        if ($anonymous === 'yes' && ($CURUSER['class'] < UC_STAFF || $owner === $CURUSER['id'])) {
            $uploader = '<span>' . get_anonymous_name() . '</span>';
        } else {
            $uploader = "<span class='" . get_user_class_name($class, true) . "'>" . htmlsafechars($username) . '</span>';
        }

        $src = $i++ <= 1 ? "src='" . url_proxy($banner, true, 1000, 185) . "' class='noshow round10'" : "data-src='" . url_proxy($banner, true, 1000, 185) . "' class='noshow lazy round10'";
        $poster = empty($poster) ? "<img src='{$site_config['pic_baseurl']}noposter.png' class='tooltip-poster' />" : "<img src='" . url_proxy($poster, true, 150, null) . "' class='tooltip-poster' />";

        $HTMLOUT .= "
                    <li>
                        <a href='{$site_config['baseurl']}/details.php?id={$id}&amp;hit=1'>
                            <div class='dt-tooltipper-large' data-tooltip-content='#slider_id_{$id}_tooltip'>
                                <img $src />
                                <div class='tooltip_templates'>
                                    <span id='slider_id_{$id}_tooltip'>
                                        <div class='is-flex tooltip-torrent'>
                                            <span class='margin10'>
                                                $poster
                                            </span>
                                            <span class='margin10'>
                                                <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_name']}</b>" . htmlsafechars($name) . "<br>
                                                <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_uploader']}</b>$uploader<br>
                                                <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_added']}</b>" . get_date($added, 'DATE', 0, 1) . "<br>
                                                <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_size']}</b>" . mksize(htmlsafechars($size)) . "<br>
                                                <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_seeder']}</b>{$seeders}<br>
                                                <b class='size_4 right10 has-text-primary'>{$lang['index_ltst_leecher']}</b>{$leechers}<br>
                                            </span>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </li>";
    }

    $HTMLOUT .= '
                </ul>
            </div>
        </div>
    </fieldset>';
} else {
    $HTMLOUT .= "
    <a id='slider-hash'></a>
    <fieldset id='slider' class='header'>
        <legend class='flipper has-text-primary'>
            <i class='icon-down-open size_3' aria-hidden='true'></i>
            {$lang['index_latest']} Slider
        </legend>
        <div class='bordered'>
            <div class='alt_bordered bg-00'>
                {$lang['last5torrents_no_torrents']}
            </div>
        </div>
    </fieldset>";
}
