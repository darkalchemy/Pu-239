<?php
global $CURUSER, $cache, $site_config, $lang, $fluent;

$news = $cache->get('latest_news_');
if ($news === false || is_null($news)) {
    $dt = TIME_NOW - (86400 * 45);
    $news = $fluent->from('news')
        ->where('added > ?', $dt)
        ->orderBy('sticky')
        ->orderBy('added DESC')
        ->limit(10)
        ->fetchAll();

    $cache->set('latest_news_', $news, $site_config['expires']['latest_news']);
}

$adminbutton = '';
if ($CURUSER['class'] >= UC_STAFF) {
    $adminbutton = "
        <a class='is-pulled-right size_3' href='{$site_config['baseurl']}/staffpanel.php?tool=news&amp;mode=news'>{$lang['index_news_title']}</a>";
}
$HTMLOUT .= "
    <a id='news-hash'></a>
    <fieldset id='news' class='header'>
        <legend class='flipper has-text-primary'><i class='fa icon-up-open size_3' aria-hidden='true'></i>{$lang['news_title']}
            <span class='news'>{$adminbutton}</span>
        </legend>
        <div>";

$i = 0;
if ($news) {
    foreach ($news as $array) {
        $padding = ++$i >= count($news) ? '' : ' bottom20';
        $button = '';
        if ($CURUSER['class'] >= UC_STAFF) {
            $hash = md5('the@@saltto66??' . $array['id'] . 'add' . '@##mu55y==');
            $button = "
                <div class='is-pulled-right'>
                    <a href='{$site_config['baseurl']}/staffpanel.php?tool=news&amp;mode=edit&amp;newsid=" . (int)$array['id'] . "'>
                        <i class='icon-edit size_6 tooltipper' aria-hidden='true' title='{$lang['index_news_ed']}'></i>
                    </a>
                    <a href='{$site_config['baseurl']}/staffpanel.php?tool=news&amp;mode=delete&amp;newsid=" . (int)$array['id'] . "&amp;h={$hash}'>
                        <i class='icon-cancel size_6 tooltipper' aria-hidden='true' title='{$lang['index_news_del']}'></i>
                    </a>
                </div>";
        }
        $HTMLOUT .= "
            <div class='bordered{$padding}'>
                <div id='{$array['id']}' class='header alt_bordered bg-00 has-text-left'>
                    <legend class='flipper has-text-primary'>
                        <i class='fa icon-up-open size_3' aria-hidden='true'></i><small>" . htmlsafechars($array['title']) . "</small>
                    </legend>
                    <div class='bg-02 round5 padding10'>
                        <div class='bottom20 size_3'>" . get_date($array['added'], 'DATE') . "{$lang['index_news_added']}" . (($array['anonymous'] === 'yes' && $CURUSER['class'] < UC_STAFF && $array['users_id'] != $CURUSER['id']) ? "<i>{$lang['index_news_anon']}</i>" : format_username($array['users_id'])) . "{$button}</div>
                        <div class='has-text-white'>
                            " . format_comment($array['body'], 0) . "
                        </div>
                    </div>
                </div>
            </div>";
    }
} else {
    $HTMLOUT .= main_div("
                    <div class='bg-02 round5 padding10'>
                        <div class='has-text-white'>
                            " . format_comment($lang['index_news_not'], 0) . "
                        </div>
                    </div>");
}

$HTMLOUT .= "
        </div>
    </fieldset>";

