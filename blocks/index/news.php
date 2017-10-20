<?php
global $CURUSER, $mc1, $site_config, $lang;
$adminbutton = '';
if ($CURUSER['class'] >= UC_STAFF) {
    $adminbutton = "
        <a class='pull-right size_3' href='./staffpanel.php?tool=news&amp;mode=news'>{$lang['index_news_title']}</a>";
}
$HTMLOUT .= "
    <a id='news-hash'></a>
    <fieldset id='news' class='header'>
        <legend class='flipper'><i class='fa fa-angle-up right10' aria-hidden='true'></i>{$lang['news_title']}
            <span class='news'>{$adminbutton}</span>
        </legend>
        <div>";

if (($news = $mc1->get_value('latest_news_')) === false) {
    $news = [];
    $res = sql_query('SELECT n.id AS nid, n.userid, n.added, n.title, n.body, n.sticky, n.anonymous
        FROM news AS n
        WHERE n.added + ( 3600 *24 *45 ) > ' . TIME_NOW . '
        ORDER BY sticky, added DESC
        LIMIT 10') or sqlerr(__FILE__, __LINE__);
    while ($array = mysqli_fetch_assoc($res)) {
        $news[] = $array;
    }
    $mc1->cache_value('latest_news_', $news, $site_config['expires']['latest_news']);
}
$i = 0;
if ($news) {
    foreach ($news as $array) {
        $button = '';
        if ($CURUSER['class'] >= UC_STAFF) {
            $hash = md5('the@@saltto66??' . $array['nid'] . 'add' . '@##mu55y==');
            $button = "
                <div class='pull-right'>
                    <a href='./staffpanel.php?tool=news&amp;mode=edit&amp;newsid=" . (int)$array['nid'] . "'>
                        <i class='fa fa-edit fa-2x tooltipper' aria-hidden='true' title='{$lang['index_news_ed']}'></i>
                    </a>
                    <a href='./staffpanel.php?tool=news&amp;mode=delete&amp;newsid=" . (int)$array['nid'] . "&amp;h={$hash}'>
                        <i class='fa fa-remove fa-2x tooltipper' aria-hidden='true' title='{$lang['index_news_del']}'></i>
                    </a>
                </div>";
        }
        $top = $i++ >= 1 ? ' top20' : '';
        $HTMLOUT .= "
            <div class='bordered padleft10 padright10{$top}'>
                <div id='{$array['nid']}' class='header alt_bordered transparent text-left'>
                    <legend class='flipper'>
                        <i class='fa fa-angle-up right10' aria-hidden='true'></i><small>" . htmlsafechars($array['title']) . "</small>
                    </legend>
                    <div class='bg-window round5 padding10'>
                        <div class='bottom20 size_2'>" . get_date($array['added'], 'DATE') . "{$lang['index_news_added']}" . (($array['anonymous'] === 'yes' && $CURUSER['class'] < UC_STAFF && $array['userid'] != $CURUSER['id']) ? "<i>{$lang['index_news_anon']}</i>" : format_username($array['userid'])) . "{$button}</div>
                        <div class='text-white'>
                            " . format_comment($array['body'], 0) . "
                        </div>
                    </div>
                </div>
            </div>";
    }
}
if (empty($news)) {
    $HTMLOUT .= format_comment($lang['index_news_not']);
}

$HTMLOUT .= "
        </div>
    </fieldset>";

