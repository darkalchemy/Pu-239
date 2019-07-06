<?php

declare(strict_types = 1);

use Pu239\Session;
use Pu239\Wiki;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
$user = check_user_status();

$lang = array_merge(load_language('global'), load_language('wiki'));
$HTMLOUT = '';
$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('sceditor_js'),
    ],
];

/**
 * @return string
 */
function navmenu()
{
    global $site_config, $lang;

    $url = $_SERVER['REQUEST_URI'];
    $parsed_url = parse_url($url);
    $action = 'index';
    if (!empty($parsed_url['query'])) {
        $queries = explode('&', $parsed_url['query']);
        $values = explode('=', $queries[0]);
        $action = $values[1] === 'sort' ? 'index' : 'add';
    }
    $ret = '
    <div id="wiki-navigation">
        <div class="tabs is-centered">
            <ul>
                <li><a href="' . $site_config['paths']['baseurl'] . '/wiki.php" class="' . ($action === 'index' ? 'active ' : '') . 'is-link margin10">' . $lang['wiki_index'] . '</a></li>
                <li><a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=add" class="' . ($action === 'add' ? 'active ' : '') . 'is-link margin10">' . $lang['wiki_add'] . '</a></li>
            </ul>
        </div>';
    $div = '
        <form action="wiki.php" method="post" accept-charset="utf-8">
            <div class="tabs is-centered is-small padtop10">
                <ul>
                    <li><a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=sort&amp;letter=a">A</a></li>';
    for ($i = 0; $i < 25; ++$i) {
        $active = !empty($_GET['letter']) && $_GET['letter'] === chr($i + 98) ? "class='active'" : '';
        $div .= " <li><a href='{$site_config['paths']['baseurl']}/wiki.php?action=sort&amp;letter=" . chr($i + 98) . "' $active> " . chr($i + 66) . '</a></li>';
    }
    $value = !empty($_POST['article']) ? $_POST['article'] : '';
    $div .= " </ul>
            </div>
            <div class='has-text-centered padding20'>
                <input type='text' name='article' value='$value'>
                <input type='submit' class='button is-small' value='{$lang['wiki_search']}' name='wiki'>
            </div>
        </form>";
    $ret .= main_div($div, 'bottom20') . '
    </div>';

    return $ret;
}

function wikimenu()
{
    global $container, $site_config, $lang;

    $wiki = $container->get(Wiki::class);
    $name = $wiki->get_last();

    return main_div("
        <div class='padding20'>
            <ul>
            <span class='size_6'>{$lang['wiki_permissions']}:</span>
            <li>{$lang['wiki_read_user']}</li>
            <li>{$lang['wiki_write_user']}</li>
            <li>{$lang['wiki_edit_staff']}/Author</li><br>
            <span class='size_6'>{$lang['wiki_latest_article']}</span>
            <li><a href='{$site_config['paths']['baseurl']}/wiki.php?action=article&amp;name=" . urlencode($name) . "'> " . format_comment($name) . '</a></li>
            </ul>
        </div>');
}

global $site_config, $container;

$wiki = $container->get(Wiki::class);
$session = $container->get(Session::class);
$action = 'article';
$mode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['article-add'])) {
        $values = [
            'name' => htmlsafechars($_POST['article-name']),
            'body' => htmlsafechars($_POST['body']),
            'userid' => $user['id'],
            'time' => TIME_NOW,
        ];
        $wiki->add($values);
        $session->set('is-success', 'Wiki article added');
    } elseif (isset($_POST['article-edit'])) {
        $id = (int) $_POST['article-id'];
        $update = [
            'name' => htmlsafechars($_POST['article-name']),
            'body' => htmlsafechars($_POST['body']),
            'lastedit' => TIME_NOW,
            'lastedituser' => $user['id'],
        ];
        $wiki->update($update, $id);
        $session->set('is-success', 'Wiki article edited');
    } elseif (isset($_POST['wiki'])) {
        $name = htmlsafechars(urldecode($_POST['article']));
        $mode = 'name';
    }
}
$HTMLOUT .= "
        <div class='level-center'>
            <h1>
            <span class='level-left'>
                <img src='{$site_config['paths']['images_baseurl']}wiki.png' alt='' title='{$lang['wiki_title']}' class='tooltipper' width='25'>
                <span class='left10'>{$lang['wiki_title']}</span>
            </span>
            </h1>
        </div>
        <div class='global_text'>";

if (isset($_GET['action'])) {
    $action = htmlsafechars($_GET['action']);
    if (isset($_GET['name'])) {
        $mode = 'name';
        $name = htmlsafechars($_GET['name']);
    }
    if (isset($_GET['id'])) {
        $mode = 'id';
        $id = (int) $_GET['id'];
        if (!is_valid_id($id)) {
            die();
        }
    }
    if (isset($_GET['letter'])) {
        $letter = htmlsafechars($_GET['letter']);
    }
}

if ($action === 'article') {
    if (!empty($mode) && !empty($name)) {
        if ($mode === 'name') {
            $results = $wiki->get_by_name($name);
        } else {
            $results = $wiki->get_by_id($id);
        }
    } else {
        $results = $wiki->get_latest();
    }
    if (!empty($results)) {
        $HTMLOUT .= navmenu();
        $edit = '';
        $HTMLOUT .= '
        <div id="wiki-container">
            <div id="wiki-row">';
        foreach ($results as $result) {
            if ($result['lastedit']) {
                $edit = '<div class="left10 top20">Last Updated by: ' . format_username($result['lastedituser']) . ' - ' . get_date($result['lastedit'], 'LONG') . '</div>';
            }
            $div = '
                    <h1 class="has-text-centered">
                        <a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=article&amp;name=' . htmlsafechars($result['name']) . '">' . htmlsafechars($result['name']) . '</a>
                    </h1>
                    <div class="bg-02 padding10 round10">' . ($result['userid'] > 0 ? " <div class='left10 bottom20'>{$lang['wiki_added_by_art']}: " . format_username($result['userid']) . '</div>' : '') . '
                        <div class="w-100 padding20 round10 bg-02">' . format_comment($result['body']) . '</div>
                    </div>' . $edit;
            $div .= ($user['class'] >= UC_STAFF || $user['id'] === $result['userid'] ? '
                    <div class="has-text-centered">
                        <a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=edit&amp;id=' . $result['id'] . '" class="button is-small margin20">' . $lang['wiki_edit'] . '</a>
                    </div>' : '');
            $HTMLOUT .= main_div($div, 'bottom20');
        }
        $HTMLOUT .= wikimenu() . '
            </div>
        </div>';
    } else {
        if (!empty($name)) {
            $results = $wiki->get_by_name($name);
        }
        if (!empty($results)) {
            $HTMLOUT .= navmenu() . "<h2 class='has-text-centered'>Article search results for: <b>" . htmlsafechars($name) . '</b></h2>';
            foreach ($results as $result) {
                $HTMLOUT .= main_div('
                    <div class="padding20">
                        <h2><a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=article&amp;name=' . urlencode($result['name']) . '">' . format_comment($result['name']) . " </a></h2>
                        <div>{$lang['wiki_added_by']}: " . format_username($result['userid']) . '</div>
                        <div>Added on: ' . get_date($result['time'], 'LONG') . '</div>' . (!empty($result['lastedit']) ? '
                        <div>Last Edited on: ' . get_date($result['lastedit'], 'LONG') . '</div>
                    </div>' : '</div>'), 'top20');
            }
        } else {
            $HTMLOUT .= navmenu() . stdmsg($lang['wiki_error'], $lang['wiki_no_art_found']);
        }
    }
}

if ($action === 'add') {
    $HTMLOUT .= navmenu() . "
            <form method='post' action='wiki.php' accept-charset='utf-8'>
                <input type='text' name='article-name' id='name' class='w-100 top10 bottom10 has-text-centered' placeholder='Article Title'> " . BBcode() . "
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' name='article-add' value='{$lang['wiki_ok']}'>
                </div>
            </form>";
}
if ($action === 'edit') {
    $result = $wiki->get_by_id($id);
    if (($user['class'] >= UC_STAFF) || ($user['id'] === $result['userid'])) {
        $HTMLOUT .= navmenu() . "
            <form method='post' action='wiki.php' accept-charset='utf-8'>
                <input type='text' name='article-name' id='name' class='w-100 top10 bottom10 has-text-centered' value='" . htmlsafechars($result['name']) . "'>
                <input type='hidden' name='article-id' value='$id'> " . BBcode(htmlsafechars($result['body'])) . "
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' name='article-edit' value='{$lang['wiki_ok']}'>
                </div>
            </form> ";
    } else {
        $HTMLOUT .= navmenu() . stdmsg($lang['wiki_error'], $lang['wiki_access_denied']);
    }
}
if ($action === 'sort') {
    $results = $wiki->get_by_name($letter);
    if (!empty($results)) {
        $HTMLOUT .= navmenu();
        $div = " <h2 class='has-text-centered'>{$lang['wiki_art_found_starting']}: <b> " . htmlsafechars($letter) . "</b></h2>
        <div class='w-100 padding20 round10 bg-02'> ";
        foreach ($results as $result) {
            $div .= '
            <div class="padding20 bottom10 round10 bg-02">
                <h2><a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=article&amp;name=' . urlencode($result['name']) . '">' . htmlsafechars($result['name']) . "</a></h2>
                <div>{$lang['wiki_added_by']}: " . format_username($result['userid']) . '</div>
                <div>Added on: ' . get_date($result['time'], 'LONG') . '</div>' . (!empty($result['lastedit']) ? '
                <div>Last Edited on: ' . get_date($result['lastedit'], 'LONG') . '</div>' : '') . '
            </div>';
        }
        $div .= '
        </div>';
        $HTMLOUT .= main_div($div);
    } else {
        $HTMLOUT .= navmenu() . stdmsg($lang['wiki_error'], "{$lang['wiki_no_art_found_starting']}<b> $letter</b> found.");
    }
}
$HTMLOUT .= '</div>';

echo stdhead($lang['wiki_title'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
