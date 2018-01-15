<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
check_user_status();

$lang = array_merge(load_language('global'), load_language('wiki'));
$HTMLOUT = '';
global $CURUSER;
/**
 * @param string $heading
 * @param string $text
 * @param string $div
 * @param bool   $htmlstrip
 *
 * @return string
 */
function newmsg($heading = '', $text = '', $div = 'is-success', $htmlstrip = false)
{
    if ($htmlstrip) {
        $heading = htmlsafechars(trim($heading));
        $text = htmlsafechars(trim($text));
    }
    return main_div("<div class='$div'>" . ($heading ? "<b>$heading</b><br>" : '') . "$text</div>");
}

/**
 * @param string $heading
 * @param string $text
 * @param bool   $die
 * @param string $div
 * @param bool   $htmlstrip
 */
function newerr($heading = '', $text = '', $die = true, $div = 'error', $htmlstrip = false)
{
    $htmlout = newmsg($heading, $text, $div, $htmlstrip);
    echo stdhead() . wrapper($htmlout) . stdfoot();
    if ($die) {
        die;
    }
}

/**
 * @param $input
 *
 * @return string
 */
function datetimetransform($input)
{
    $todayh = getdate($input);
    if ($todayh['seconds'] < 10) {
        $todayh['seconds'] = '0' . $todayh['seconds'] . '';
    }
    if ($todayh['minutes'] < 10) {
        $todayh['minutes'] = '0' . $todayh['minutes'] . '';
    }
    if ($todayh['hours'] < 10) {
        $todayh['hours'] = '0' . $todayh['hours'] . '';
    }
    if ($todayh['mday'] < 10) {
        $todayh['mday'] = '0' . $todayh['mday'] . '';
    }
    if ($todayh['mon'] < 10) {
        $todayh['mon'] = '0' . $todayh['mon'] . '';
    }
    $sec = $todayh['seconds'];
    $min = $todayh['minutes'];
    $hours = $todayh['hours'];
    $d = $todayh['mday'];
    $m = $todayh['mon'];
    $y = $todayh['year'];
    $input = "$d-$m-$y $hours:$min:$sec";

    return $input;
}

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
        $div .= '
                    <li><a href="' . $site_config['baseurl'] . '/wiki.php?action=sort&amp;letter=' . chr($i + 98) . '">' . chr($i + 66) . '</a></li>';
    }
    $div .= '
                </ul>
            </div>
            <div class="margin20 has-text-centered">
                <input type="text" name="article" />
                <input type="submit" class="button is-small" value="' . $lang['wiki_search'] . '" name="wiki" />
            </div>
        </form>';
    $ret .= main_div($div, 'bottom20') . ' 
    </div>';

    return $ret;
}

/**
 * @param $input
 *
 * @return mixed
 */
function articlereplace($input)
{
    $input = str_replace(' ', '+', $input);

    return $input;
}

/**
 * @param $input
 *
 * @return mixed
 */
function wikisearch($input)
{
    global $lang;

    return str_replace([
                           '%',
                           '_',
                       ], [
                           '\\%',
                           '\\_',
                       ], ((isset($GLOBALS['___mysqli_ston']) && is_object($GLOBALS['___mysqli_ston'])) ? mysqli_real_escape_string($GLOBALS['___mysqli_ston'], $input) : ((trigger_error($lang['wiki_error'], E_USER_ERROR)) ? '' : '')));
}

/**
 * @param $input
 *
 * @return mixed
 */
function wikireplace($input)
{
    global $site_config;
    return preg_replace([
                            '/\[\[(.+?)\]\]/i',
                            '/\=\=\ (.+?)\ \=\=/i',
                        ], [
                            '<a href="' . $site_config['baseurl'] . '/wiki.php?action=article&name=$1">$1</a>',
                            '<div id="$1">$1</div>',
                        ], $input);
}

/**
 * @return string
 */
function wikimenu()
{
    global $lang, $site_config;
    $res2 = sql_query('SELECT name FROM wiki ORDER BY id DESC LIMIT 1');
    $latest = mysqli_fetch_assoc($res2);
    $latestarticle = articlereplace(htmlsafechars($latest['name']));
    return main_div("
        <span class='size_6'>{$lang['wiki_permissions']}:</span>
        <li>{$lang['wiki_read_user']}</li>
        <li>{$lang['wiki_write_user']}</li>
        <li>{$lang['wiki_edit_staff']} / Author</li><br>
        <span class='size_6'>{$lang['wiki_latest_article']}</span>
        <li><a href='{$site_config['baseurl']}/wiki.php?action=article&amp;name=$latestarticle'>" . htmlsafechars($latest['name']) . '</a></li>');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['article-add'])) {
        $name = htmlsafechars($_POST['article-name']);
        $body = htmlsafechars($_POST['body']);
        sql_query('INSERT INTO `wiki` ( `name` , `body` , `userid`, `time` )
          VALUES (' . sqlesc($name) . ', ' . sqlesc($body) . ', ' . sqlesc($CURUSER['id']) . ", '" . TIME_NOW . "')") or sqlerr(__FILE__, __LINE__);
        $HTMLOUT .= '<meta http-equiv="refresh" content="0; url=wiki.php?action=article&name=' . htmlsafechars($_POST['article-name']) . '">';
    }
    if (isset($_POST['article-edit'])) {
        $id = (int)$_POST['article-id'];
        $name = htmlsafechars($_POST['article-name']);
        $body = htmlsafechars($_POST['body']);
        sql_query('UPDATE wiki SET name = ' . sqlesc($name) . ', body =' . sqlesc($body) . ", lastedit = '" . TIME_NOW . "', lastedituser =" . sqlesc($CURUSER['id']) . ' WHERE id = ' . sqlesc($id));
        $HTMLOUT .= '<meta http-equiv="refresh" content="0; url=wiki.php?action=article&name=' . htmlsafechars($_POST['article-name']) . '">';
    }
    if (isset($_POST['wiki'])) {
        $wikisearch = articlereplace(htmlsafechars($_POST['article']));
        $HTMLOUT .= "<meta http-equiv='refresh' content='0; url=wiki.php?action=article&name=$wikisearch'>";
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
        $id = (int)$_GET['id'];
        if (!is_valid_id($id)) {
            die();
        }
    }
    if (isset($_GET['letter'])) {
        $letter = htmlsafechars($_GET['letter']);
    }
} else {
    $action = 'article';
    $mode = 'name';
    $name = 'index';
}

if ($action == 'article') {
    $res = sql_query("SELECT * FROM wiki WHERE $mode = '" . ($mode == 'name' ? "$name" : "$id") . "'");
    if (mysqli_num_rows($res) == 1) {
        $HTMLOUT .= navmenu();
        $edit = '';
        $HTMLOUT .= '
        <div id="wiki-container">
            <div id="wiki-row">';
        while ($wiki = mysqli_fetch_array($res)) {
            if ($wiki['lastedit']) {
                $edit = '<div>Last Updated by: ' . format_username($wiki['lastedituser']) . ' - ' . datetimetransform($wiki['lastedit']) . '</div>';
            }
            $HTMLOUT .= main_div('
                    <h2>
                        <a href="' . $site_config['baseurl'] . '/wiki.php?action=article&amp;name=' . htmlsafechars($wiki['name']) . '">' . htmlsafechars($wiki['name']) . '</a></b>
                    </h2>
                    <div id="bg-02 padding10 round10">' . ($wiki['userid'] > 0 ? "<span class='right10'>{$lang['wiki_added_by_art']}:</span>" . format_username($wiki['userid']) : '') . wikireplace(format_comment($wiki['body'])) . '</div>' .
                                 $edit);
            $HTMLOUT .= ($CURUSER['class'] >= UC_STAFF || $CURUSER['id'] == $wiki['userid'] ? '
                    <div class="has-text-centered">
                        <a href="' . $site_config['baseurl'] . '/wiki.php?action=edit&amp;id=' . (int)$wiki['id'] . '" class="button is-small margin20">' . $lang['wiki_edit'] . '</a>
                    </div>' : '');
        }
        $HTMLOUT .= wikimenu() . '
            </div>
        </div>';
    } else {
        $search = sql_query("SELECT * FROM wiki WHERE name LIKE '%" . wikisearch($name) . "%'");
        if (mysqli_num_rows($search) > 0) {
            $HTMLOUT .= 'Search results for: <b>' . htmlsafechars($name) . '</b>';
            while ($wiki = mysqli_fetch_array($search)) {
                if ($wiki['userid'] !== 0) {
                    $wikiname = mysqli_fetch_assoc(sql_query('SELECT username FROM users WHERE id = ' . sqlesc($wiki['userid'])));
                }
                $HTMLOUT .= '
                <div class="wiki-search">
                    <b><a href="' . $site_config['baseurl'] . '/wiki.php?action=article&amp;name=' . articlereplace(htmlsafechars($wiki['name'])) . '">' . htmlsafechars($wiki['name']) . "</a></b>{$lang['wiki_added_by']} " . format_username($wiki['userid']) . '</div>';
            }
        } else {
            $HTMLOUT .= newerr($lang['wiki_error'], $lang['wiki_no_art_found']);
        }
    }
}
$wiki = 0;
if ($action == 'add') {
    $HTMLOUT .= navmenu() . "
            <form method='post' action='wiki.php'>
                <input type='text' name='article-name' id='name' class='w-100 top10 bottom10 has-text-centered' placeholder='Article Title' />" .
        BBcode() . "
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' name='article-add' value='{$lang['wiki_ok']}' />
                </div>
            </form>";
}
if ($action == 'edit') {
    $sql = sql_query('SELECT * FROM wiki WHERE id = ' . sqlesc($id));
    $result = mysqli_fetch_assoc($sql);
    if (($CURUSER['class'] >= UC_STAFF) || ($CURUSER['id'] == $result['userid'])) {
        $HTMLOUT .= navmenu() . "
            <form method='post' action='wiki.php'>
                <input type='text' name='article-name' id='name' class='w-100 top10 bottom10 has-text-centered' value='" . htmlsafechars($result['name']) . "' />" .
            BBcode(htmlsafechars($result['body'])) . "
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' name='article-edit' value='{$lang['wiki_ok']}' />
                </div>
            </form>";
    } else {
        $HTMLOUT .= newerr($lang['wiki_error'], $lang['wiki_access_denied']);
    }
}
if ($action == 'sort') {
    $sortres = sql_query("SELECT * FROM wiki WHERE name LIKE '$letter%' ORDER BY name");
    if (mysqli_num_rows($sortres) > 0) {
        $HTMLOUT .= navmenu() . "
        {$lang['wiki_art_found_starting']}<b>" . htmlsafechars($letter) . '</b>';
        while ($wiki = mysqli_fetch_array($sortres)) {
            if ($wiki['userid'] !== 0) {
                $wikiname = mysqli_fetch_assoc(sql_query('SELECT username FROM users WHERE id = ' . sqlesc($wiki['userid'])));
            }
            $HTMLOUT .= '
                <div class="wiki-search">
                    <b><a href="' . $site_config['baseurl'] . '/wiki.php?action=article&amp;name=' . articlereplace(htmlsafechars($wiki['name'])) . '">' . htmlsafechars($wiki['name']) . "</a></b>{$lang['wiki_added_by1']} " . format_username($wiki['userid']) . '
                </div>';
        }
    } else {
        $HTMLOUT .= navmenu();
        $HTMLOUT .= newerr($lang['wiki_error'], "{$lang['wiki_no_art_found_starting']}<b>$letter</b> found.");
    }
}
$HTMLOUT .= '</div>';

echo stdhead($lang['wiki_title']) . wrapper(main_div($HTMLOUT)) . stdfoot();
