<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;

global $container, $lang, $site_config, $CURUSER;

$cache = $container->get(Cache::class);
$news = $cache->get('latest_news_');
if ($news === false || is_null($news)) {
    $dt = TIME_NOW - (86400 * 45);
    $fluent = $container->get(Database::class);
    $news = $fluent->from('news')
                   ->where('(added > ? AND sticky = "no") OR sticky = "yes"', $dt)
                   ->orderBy('sticky')
                   ->orderBy('added DESC')
                   ->limit(10)
                   ->fetchAll();

    $cache->set('latest_news_', $news, $site_config['expires']['latest_news']);
}

$adminbutton = '';
if ($CURUSER['class'] >= UC_STAFF) {
    $adminbutton = "
        <a class='is-pulled-right size_2' href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=news'>{$lang['index_news_title']}</a>";
}
$site_news .= "
    <a id='news-hash'></a>
    <div id='news' class='box'>
        <div>";

$i = 0;
if ($news) {
    foreach ($news as $array) {
        $padding = ++$i >= count($news) ? '' : ' bottom20';
        $button = '';
        if ($CURUSER['class'] >= UC_STAFF) {
            $hash = hash('sha256', $site_config['salt']['one'] . $array['id'] . 'add');
            $button = "
                <div class='is-pulled-right'>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=edit&amp;newsid=" . (int) $array['id'] . "'>
                        <i class='icon-edit icon size_4 tooltipper' aria-hidden='true' title='{$lang['index_news_ed']}'></i>
                    </a>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=delete&amp;newsid=" . (int) $array['id'] . "&amp;h={$hash}'>
                        <i class='icon-trash-empty icon has-text-danger size_4 tooltipper' aria-hidden='true' title='{$lang['index_news_del']}'></i>
                    </a>
                </div>";
        }
        $username = format_username((int) $array['userid']);
        if ($array['anonymous'] === 'yes') {
            if ($CURUSER['class'] < UC_STAFF || $array['userid'] === $CURUSER['id']) {
                $username = get_anonymous_name();
            } else {
                $username = get_anonymous_name() . ' - ' . format_username((int) $array['userid']);
            }
        }
        $site_news .= "
            <div class='bordered{$padding}'>
                <div id='{$array['id']}' class='header alt_bordered bg-00'>
                    <div class='has-text-primary size_5 padding10 has-text-centered'>" . htmlsafechars($array['title']) . "</div>
                    <div class='bottom20 size_2 left20 right20 padding10 bg-00 round5'>" . get_date((int) $array['added'], 'DATE') . "{$lang['index_news_added']} {$username}{$button}</div>
                    <div class='is-primary padding20'>
                        " . format_comment($array['body']) . '
                    </div>
                </div>
            </div>';
    }
} else {
    $site_news .= main_div("
                    <div class='bg-02 round5 padding20'>
                        <div class='is-primary level-center-center'>
                            " . format_comment($lang['index_news_not']) . '
                        </div>
                    </div>');
}

$site_news .= '
        </div>
    </div>';
