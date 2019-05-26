<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$HTMLOUT = '';
$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('upload_js'),
        get_file_name('sceditor_js'),
    ],
];
$lang = array_merge($lang, load_language('ad_news'));
global $container, $site_config, $CURUSER;

$possible_modes = [
    'add',
    'delete',
    'edit',
    'news',
];
$mode = (isset($_GET['mode']) ? htmlsafechars($_GET['mode']) : '');
if (!in_array($mode, $possible_modes)) {
    stderr($lang['news_error'], $lang['news_error_ruffian']);
}

$cache = $container->get(Cache::class);
if ($mode === 'delete') {
    $newsid = (int) $_GET['newsid'];
    if (!is_valid_id($newsid)) {
        stderr($lang['news_error'], $lang['news_del_invalid']);
    }
    $hash = hash('sha256', $site_config['site']['use_12_hour'] . $newsid . 'add');
    $sure = '';
    $sure = (isset($_GET['sure']) ? intval($_GET['sure']) : '');
    if (!$sure) {
        stderr($lang['news_del_confirm'], $lang['news_del_click'] . "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=delete&amp;sure=1&amp;h=$hash&amp;newsid=$newsid'> {$lang['news_del_here']}</a> {$lang['news_del_if']}", null);
    }
    if ($_GET['h'] != $hash) {
        stderr($lang['news_error'], $lang['news_del_what']);
    }

    $HTMLOUT .= deletenewsid($newsid);
    header('Refresh: 3; url=staffpanel.php?tool=news&mode=news');
    stderr($lang['news_success'], "<h2>{$lang['news_del_redir']}</h2>");
    echo stdhead($lang['news_del_stdhead'], $stdhead) . wrapper($HTMLOUT) . stdfoot();
    die();
} elseif ($mode === 'add') {
    $body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
    $sticky = isset($_POST['sticky']) ? htmlsafechars($_POST['sticky']) : 'yes';
    $anonymous = isset($_POST['anonymous']) ? htmlsafechars($_POST['anonymous']) : 'no';
    if (!$body) {
        stderr($lang['news_error'], $lang['news_add_item']);
    }
    $title = htmlsafechars($_POST['title']);
    if (!$title) {
        stderr($lang['news_error'], $lang['news_add_title']);
    }
    $added = isset($_POST['added']) ? $_POST['added'] : '';
    if (!$added) {
        $added = TIME_NOW;
    }
    sql_query('INSERT INTO news (userid, added, body, title, sticky, anonymous) VALUES (' . sqlesc($CURUSER['id']) . ',' . sqlesc($added) . ', ' . sqlesc($body) . ', ' . sqlesc($title) . ', ' . sqlesc($sticky) . ', ' . sqlesc($anonymous) . ')') or sqlerr(__FILE__, __LINE__);
    $cache->delete('latest_news_');
    header('Refresh: 3; url=staffpanel.php?tool=news&mode=news');
    mysqli_affected_rows($mysqli) == 1 ? stderr($lang['news_success'], $lang['news_add_success']) : stderr($lang['news_add_oopss'], $lang['news_add_something']);
} elseif ($mode === 'edit') {
    $newsid = (int) $_GET['newsid'];
    if (!is_valid_id($newsid)) {
        stderr($lang['news_error'], $lang['news_edit_invalid']);
    }
    $res = sql_query('SELECT id, body, title, userid, added, anonymous, sticky FROM news WHERE id=' . sqlesc($newsid)) or sqlerr(__FILE__, __LINE__);
    if (mysqli_num_rows($res) != 1) {
        stderr($lang['news_error'], $lang['news_edit_nonews']);
    }
    $arr = mysqli_fetch_assoc($res);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
        $sticky = isset($_POST['sticky']) ? htmlsafechars($_POST['sticky']) : 'yes';
        $anonymous = isset($_POST['anonymous']) ? htmlsafechars($_POST['anonymous']) : 'no';
        if ($body == '') {
            stderr($lang['news_error'], $lang['news_edit_body']);
        }
        $title = htmlsafechars($_POST['title']);
        if ($title == '') {
            stderr($lang['news_error'], $lang['news_edit_title']);
        }
        sql_query('UPDATE news SET body=' . sqlesc($body) . ', sticky=' . sqlesc($sticky) . ', anonymous=' . sqlesc($anonymous) . ', title=' . sqlesc($title) . ' WHERE id=' . sqlesc($newsid)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('latest_news_');
        header('Refresh: 3; url=staffpanel.php?tool=news&mode=news');
        stderr($lang['news_success'], $lang['news_edit_success']);
    } else {
        $HTMLOUT .= "
            <h1 class='has-text-centered'>{$lang['news_edit_item']}</h1>
            <form method='post' name='compose' action='./staffpanel.php?tool=news&amp;mode=edit&amp;newsid=$newsid' accept-charset='utf-8'>
                <table class='table table-bordered table-striped'>
                    <tr>
                        <td>
                            Title
                        </td>
                        <td>
                            <input type='text' name='title' class='w-100' value='" . htmlsafechars($arr['title']) . "'>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            BBcode Editor
                        </td>
                        <td class='is-paddingless'>
                            " . BBcode($arr['body']) . "
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {$lang['news_sticky']}
                        </td>
                        <td>
                            <input type='radio' " . ($arr['sticky'] === 'yes' ? ' checked' : '') . " name='sticky' value='yes'>
                            {$lang['news_yes']}
                            <input type='radio' " . ($arr['sticky'] === 'no' ? ' checked' : '') . " name='sticky' value='no'>
                            {$lang['news_no']}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Anonymous?
                        </td>
                        <td>
                            {$lang['news_anonymous']}
                            <input type='radio' " . ($arr['anonymous'] === 'yes' ? ' checked' : '') . " name='anonymous' value='yes'>
                            {$lang['news_yes']}
                            <input type='radio' " . ($arr['anonymous'] === 'no' ? ' checked' : '') . " name='anonymous' value='no'>
                            {$lang['news_no']}
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' value='{$lang['news_okay']}' class='button is-small'>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>";
        echo stdhead($lang['news_stdhead'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
        die();
    }
} elseif ($mode === 'news') {
    $res = sql_query('SELECT n.id AS newsid, n.body, n.title, n.userid, n.added, n.anonymous, u.id, u.username, u.class, u.warned, u.chatpost, u.pirate, u.king, u.leechwarn, u.enabled, u.donor FROM news AS n LEFT JOIN users AS u ON u.id=n.userid ORDER BY sticky, added DESC') or sqlerr(__FILE__, __LINE__);
    $HTMLOUT .= "
    <div class='portlet'>
        <h1 class='has-text-centered'>{$lang['news_submit_new']}</h1>
        <form method='post' name='compose' action='./staffpanel.php?tool=news&amp;mode=add' accept-charset='utf-8'>
                <table class='table table-bordered table-striped'>
                    <tr>
                        <td>
                            Title
                        </td>
                        <td>
                            <input type='text' name='title' class='w-100' value=''>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            BBcode Editor
                        </td>
                        <td class='is-paddingless'>" . BBcode() . "
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {$lang['news_sticky']}
                        </td>
                        <td>
                            <input type='radio' checked name='sticky' value='yes'>
                            {$lang['news_yes']}
                            <input name='sticky' type='radio' value='no'>
                            {$lang['news_no']}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {$lang['news_anonymous']}
                        </td>
                        <td>
                            <input type='radio' checked name='anonymous' value='yes'>
                            {$lang['news_yes']}
                            <input name='anonymous' type='radio' value='no'>
                            {$lang['news_no']}
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' value='{$lang['news_okay']}' class='button is-small'>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>";
    while ($arr = mysqli_fetch_assoc($res)) {
        $newsid = (int) $arr['newsid'];
        $body = $arr['body'];
        $title = $arr['title'];
        $added = get_date((int) $arr['added'], 'LONG', 0, 1);
        $by = '<b>' . format_username((int) $arr['userid']) . '</b>';
        $hash = hash('sha256', $site_config['salt']['one'] . $newsid . 'add');
        $user = $arr['anonymous'] === 'yes' ? get_anonymous_name() : format_username((int) $arr['userid']);
        $HTMLOUT .= main_div("
            <div class='level bg-01 padding20 round5'>
                <div class='has-text-left'>
                    {$lang['news_created_by']} $user $added
                </div>
                <div class='has-text-right'>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=edit&amp;newsid=$newsid' title='{$lang['news_edit']}' class='tooltipper'>
                        <i class='icon-edit icon'></i>
                    </a>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=delete&amp;newsid=$newsid&amp;sure=1&amp;h=$hash' title='{$lang['news_delete']}' class='has-text-danger tooltipper'>
                        <i class='icon-cancel icon has-text-danger'></i>
                    </a>
                </div>
            </div>
            <div class='padding20'>
                <h2>" . htmlsafechars($title) . '</h2>
                <div>' . format_comment($body) . '</div>
            </div>');
    }
}

echo stdhead($lang['news_stdhead'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
die();

/**
 * @param $newsid
 *
 * @throws DependencyException
 * @throws NotFoundException
 */
function deletenewsid($newsid)
{
    global $container, $CURUSER;

    $cache = $container->get(Cache::class);
    sql_query('DELETE FROM news WHERE id=' . sqlesc($newsid) . ' AND userid=' . sqlesc($CURUSER['id'])) or sqlerr(__FILE__, __LINE__);
    $cache->delete('latest_news_');
}
