<?php
global $CURUSER, $cache, $site_config, $lang;
$adminbutton = '';
if ($CURUSER['class'] >= UC_STAFF) {
    $adminbutton = "
        <a class='pull-right size_3' href='{$site_config['baseurl']}staffpanel.php?tool=news&amp;mode=news'>{$lang['index_news_title']}</a>";
}
$HTMLOUT .= "
    <a id='news-hash'></a>
    <fieldset id='news' class='header'>
        <legend class='flipper has-text-primary'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['news_title']}
            <span class='news'>{$adminbutton}</span>
        </legend>
        <div>";

if (($news = $cache->get('latest_news_')) === false) {
    $news = [];
    $res = sql_query('SELECT n.id AS nid, n.userid, n.added, n.title, n.body, n.sticky, n.anonymous
        FROM news AS n
        WHERE n.added + ( 3600 *24 *45 ) > ' . TIME_NOW . '
        ORDER BY sticky, added DESC
        LIMIT 10') or sqlerr(__FILE__, __LINE__);
    while ($array = mysqli_fetch_assoc($res)) {
        $news[] = $array;
    }
    $cache->set('latest_news_', $news, $site_config['expires']['latest_news']);
}
$i = 0;
if ($news) {
    foreach ($news as $array) {
        $button = '';
        if ($CURUSER['class'] >= UC_STAFF) {
            $hash = md5('the@@saltto66??' . $array['nid'] . 'add' . '@##mu55y==');
            $button = "
                <div class='pull-right'>
                    <a href='{$site_config['baseurl']}staffpanel.php?tool=news&amp;mode=edit&amp;newsid=" . (int)$array['nid'] . "'>
                        <i class='fa fa-edit fa-2x tooltipper' aria-hidden='true' title='{$lang['index_news_ed']}'></i>
                    </a>
                    <a href='{$site_config['baseurl']}staffpanel.php?tool=news&amp;mode=delete&amp;newsid=" . (int)$array['nid'] . "&amp;h={$hash}'>
                        <i class='fa fa-remove fa-2x tooltipper' aria-hidden='true' title='{$lang['index_news_del']}'></i>
                    </a>
                </div>";
        }
        $HTMLOUT .= "
            <div class='bordered'>
                <div id='{$array['nid']}' class='header alt_bordered bg-00 has-text-left'>
                    <legend class='flipper has-text-primary'>
                        <i class='fa fa-angle-up right10' aria-hidden='true'></i><small>" . htmlsafechars($array['title']) . "</small>
                    </legend>
                    <div class='bg-02 round5 padding10'>
                        <div class='bottom20 size_2'>" . get_date($array['added'], 'DATE') . "{$lang['index_news_added']}" . (($array['anonymous'] === 'yes' && $CURUSER['class'] < UC_STAFF && $array['userid'] != $CURUSER['id']) ? "<i>{$lang['index_news_anon']}</i>" : format_username($array['userid'])) . "{$button}</div>
                        <div class='has-text-white'>
                            " . format_comment($array['body'], 0) . "
                        </div>
                    </div>
                </div>
            </div>";
    }
}
if (empty($news)) {
    $HTMLOUT .= "
            <div class='bordered'>
                <div class='header alt_bordered bg-00 has-text-left'>
                    <div class='bg-02 round5 padding10'>
                        <div class='has-text-white'>
                            " . format_comment($lang['index_news_not'], 0) . "
                        </div>
                    </div>
                </div>
            </div>";
}

$HTMLOUT .= "
        </div>
    </fieldset>";

