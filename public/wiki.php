<?php

require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();

$lang = array_merge(load_language('global'), load_language('wiki'));
$HTMLOUT = '';
global $CURUSER, $fluent, $user_stuffs, $session;

/**
 * @return string
 */
function navmenu()
{
    global $lang, $site_config;

    $ret = '
    <div id="wiki-navigation">
        <div class="tabs is-centered">
            <ul>
                <li><a href="' . $site_config['baseurl'] . '/wiki.php" class="' . (empty($_SERVER['QUERY_STRING']) ? 'active ' : '') . 'altlink margin10">' . $lang['wiki_index'] . '</a></li>
                <li><a href="' . $site_config['baseurl'] . '/wiki.php?action=add" class="' . (!empty($_SERVER['QUERY_STRING']) ? 'active ' : '') . 'altlink margin10">' . $lang['wiki_add'] . '</a></li>
            </ul>
        </div>';
    $div = '
        <form action="wiki.php" method="post">
            <div class="tabs is-centered is-small">
                <ul>
                    <li><a href="' . $site_config['baseurl'] . '/wiki.php?action=sort&amp;letter=a">A</a></li>';
    for ($i = 0; $i < 25; ++$i) {
        $active = !empty($_GET['letter']) && $_GET['letter'] == chr($i + 98) ? "class='active'" : '';
        $div .= "
                    <li><a href='{$site_config['baseurl']}/wiki.php?action=sort&amp;letter=" . chr($i + 98) . "' $active>" . chr($i + 66) . '</a></li>';
    }
    $value = !empty($_POST['article']) ? $_POST['article'] : '';
    $div .= "
                </ul>
            </div>
            <div class='margin20 has-text-centered'>
                <input type='text' name='article' value='$value'>
                <input type='submit' class='button is-small' value='{$lang['wiki_search']}' name='wiki'>
            </div>
        </form>";
    $ret .= main_div($div, 'bottom20') . '
    </div>';

    return $ret;
}

/**
 * @return string
 */
function wikimenu()
{
    global $lang, $site_config;
    $res2 = sql_query('SELECT name FROM wiki ORDER BY id DESC LIMIT 1');
    $latest = mysqli_fetch_assoc($res2);
    $latestarticle = htmlsafechars($latest['name']);

    return main_div("
        <span class='size_6'>{$lang['wiki_permissions']}:</span>
        <li>{$lang['wiki_read_user']}</li>
        <li>{$lang['wiki_write_user']}</li>
        <li>{$lang['wiki_edit_staff']} / Author</li><br>
        <span class='size_6'>{$lang['wiki_latest_article']}</span>
        <li><a href='{$site_config['baseurl']}/wiki.php?action=article&amp;name=$latestarticle'>" . htmlsafechars($latest['name']) . '</a></li>');
}

$action = 'article';
$mode = $name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['article-add'])) {
        $name = htmlsafechars(urldecode($_POST['article-name']));
        $body = htmlsafechars($_POST['body']);
        $sql = 'INSERT INTO `wiki` ( `name` , `body` , `userid`, `time` ) VALUES (' . sqlesc($name) . ', ' . sqlesc($body) . ', ' . sqlesc($CURUSER['id']) . ", '" . TIME_NOW . "')";
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
        $session->set('is-success', 'Wiki article added');
    } elseif (isset($_POST['article-edit'])) {
        $id = (int) $_POST['article-id'];
        $name = htmlspecialchars(urldecode($_POST['article-name']));
        $body = htmlspecialchars($_POST['body']);
        $sql = 'UPDATE wiki SET name = ' . sqlesc($name) . ', body =' . sqlesc($body) . ", lastedit = '" . TIME_NOW . "', lastedituser =" . sqlesc($CURUSER['id']) . ' WHERE id = ' . sqlesc($id);
        sql_query($sql) or sqlerr(__FILE__, __LINE__);
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
                <img src='{$site_config['pic_baseurl']}wiki.png' alt='' title='{$lang['wiki_title']}' class='tooltipper' width='25'/>
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
        $res = sql_query("SELECT * FROM wiki WHERE $mode = '" . ($mode === 'name' ? "$name" : "$id") . "'");
    } else {
        $res = sql_query('SELECT * FROM wiki ORDER BY GREATEST(time, lastedit) DESC');
    }
    if (mysqli_num_rows($res) === 1) {
        $HTMLOUT .= navmenu();
        $edit = '';
        $HTMLOUT .= '
        <div id="wiki-container">
            <div id="wiki-row">';
        while ($wiki = mysqli_fetch_array($res)) {
            if ($wiki['lastedit']) {
                $edit = '<div class="left10 top20">Last Updated by: ' . format_username($wiki['lastedituser']) . ' - ' . get_date($wiki['lastedit'], 'LONG') . '</div>';
            }
            $HTMLOUT .= main_div('
                    <h1 class="has-text-centered">
                        <a href="' . $site_config['baseurl'] . '/wiki.php?action=article&amp;name=' . htmlsafechars($wiki['name']) . '">' . htmlsafechars($wiki['name']) . '</a></b>
                    </h1>
                    <div id="bg-02 padding10 round10">' . ($wiki['userid'] > 0 ? "
                        <div class='left10 bottom20'>{$lang['wiki_added_by_art']}: " . format_username($wiki['userid']) . '</div>' : '') . '
                        <div class="w-100 padding20 round10 bg-02">' . format_comment($wiki['body']) . '</div>
                    </div>' .
                $edit);
            $HTMLOUT .= ($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $wiki['userid'] ? '
                    <div class="has-text-centered">
                        <a href="' . $site_config['baseurl'] . '/wiki.php?action=edit&amp;id=' . (int) $wiki['id'] . '" class="button is-small margin20">' . $lang['wiki_edit'] . '</a>
                    </div>' : '');
        }
        $HTMLOUT .= wikimenu() . '
            </div>
        </div>';
    } else {
        if (!empty($name)) {
            $res = sql_query("SELECT * FROM wiki WHERE name LIKE '%" . sqlesc_noquote($name) . "%' ORDER BY GREATEST(time, lastedit) DESC LIMIT 25");
        }
        if (mysqli_num_rows($res) > 0) {
            $HTMLOUT .= navmenu() . "
            <h2 class='has-text-centered'>Article search results for: <b>" . htmlsafechars($name) . '</b></h2>';
            while ($wiki = mysqli_fetch_array($res)) {
                if ($wiki['userid'] !== 0) {
                    $user = $user_stuffs->getUserFromId($wiki['userid']);
                    $wikiname = $user['username'];
                }
                $HTMLOUT .= main_div('
                    <h2><a href="' . $site_config['baseurl'] . '/wiki.php?action=article&amp;name=' . urlencode($wiki['name']) . '">' . htmlsafechars($wiki['name']) . "</a></h2>
                    <div>{$lang['wiki_added_by']}: " . format_username($wiki['userid']) . '</div>
                    <div>Added on: ' . get_date($wiki['time'], 'LONG') . '</div>' . (!empty($wiki['lastedit']) ? '
                    <div>Last Edited on: ' . get_date($wiki['lastedit'], 'LONG') . '</div>' : ''), 'top20');
            }
        } else {
            stderr($lang['wiki_error'], $lang['wiki_no_art_found']);
        }
    }
}
$wiki = 0;
if ($action === 'add') {
    $HTMLOUT .= navmenu() . "
            <form method='post' action='wiki.php'>
                <input type='text' name='article-name' id='name' class='w-100 top10 bottom10 has-text-centered' placeholder='Article Title' />" .
        BBcode() . "
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' name='article-add' value='{$lang['wiki_ok']}' />
                </div>
            </form>";
}
if ($action === 'edit') {
    $sql = sql_query('SELECT * FROM wiki WHERE id = ' . sqlesc($id));
    $result = mysqli_fetch_assoc($sql);
    if (($CURUSER['class'] >= UC_STAFF) || ($CURUSER['id'] == $result['userid'])) {
        $HTMLOUT .= navmenu() . "
            <form method='post' action='wiki.php'>
                <input type='text' name='article-name' id='name' class='w-100 top10 bottom10 has-text-centered' value='" . htmlsafechars($result['name']) . "' />
                <input type='hidden' name='article-id' value='$id' />" .
            BBcode(htmlsafechars($result['body'])) . "
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' name='article-edit' value='{$lang['wiki_ok']}' />
                </div>
            </form>";
    } else {
        stderr($lang['wiki_error'], $lang['wiki_access_denied']);
    }
}
if ($action === 'sort') {
    $sortres = sql_query("SELECT * FROM wiki WHERE name LIKE '$letter%' ORDER BY name");
    if (mysqli_num_rows($sortres) > 0) {
        $HTMLOUT .= navmenu() . "
        <h2 class='has-text-centered'>{$lang['wiki_art_found_starting']}: <b>" . htmlsafechars($letter) . "</b></h2>
        <div class='w-100 padding20 round10 bg-02'>";
        while ($wiki = mysqli_fetch_array($sortres)) {
            if ($wiki['userid'] !== 0) {
                $user = $user_stuffs->getUserFromId($wiki['userid']);
                $wikiname = $user['username'];
            }
            $HTMLOUT .= '
            <div class="padding20 bottom10 round10 bg-02">
                <h2><a href="' . $site_config['baseurl'] . '/wiki.php?action=article&amp;name=' . urlencode($wiki['name']) . '">' . htmlsafechars($wiki['name']) . "</a></h2>
                <div>{$lang['wiki_added_by']}: " . format_username($wiki['userid']) . '</div>
                <div>Added on: ' . get_date($wiki['time'], 'LONG') . '</div>' . (!empty($wiki['lastedit']) ? '
                <div>Last Edited on: ' . get_date($wiki['lastedit'], 'LONG') . '</div>' : '') . '
            </div>';
        }
        $HTMLOUT .= '
        </div>';
    } else {
        stderr($lang['wiki_error'], "{$lang['wiki_no_art_found_starting']}<b>$letter</b> found.");
    }
}
$HTMLOUT .= '</div>';

echo stdhead($lang['wiki_title']) . wrapper(main_div($HTMLOUT)) . stdfoot();
