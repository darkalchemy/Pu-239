<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;

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
$fluent = $container->get(Database::class);
$session = $container->get(Session::class);
if ($mode === 'delete') {
    $newsid = (int) $_GET['newsid'];
    if (!is_valid_id($newsid)) {
        stderr($lang['news_error'], $lang['news_del_invalid']);
    }
    $hash = hash('sha256', $site_config['salt']['one'] . $newsid . 'add');
    $sure = '';
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : '';
    if (!$sure) {
        stderr($lang['news_del_confirm'], $lang['news_del_click'] . "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=delete&amp;sure=1&amp;h=$hash&amp;newsid=$newsid'> {$lang['news_del_here']}</a> {$lang['news_del_if']}", null);
    }
    if ($_GET['h'] != $hash) {
        stderr($lang['news_error'], $lang['news_del_what']);
    }

    $fluent->deleteFrom('news')
           ->where('id = ?', $newsid)
           ->execute();
    $cache->delete('latest_news_');
    $session->set('is-success', $lang['news_del_redir']);
    header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=news&mode=news");
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
    $values = [
        'userid' => $CURUSER['id'],
        'added' => TIME_NOW,
        'body' => $body,
        'title' => $title,
        'sticky' => $sticky,
        'anonymous' => $anonymous,
    ];
    $results = $fluent->insertInto('news')
                      ->values($values)
                      ->execute();
    if (!empty($results)) {
        $cache->delete('latest_news_');
        $session->set('is-success', $lang['news_add_success']);
    } else {
        $session->set('is-warning', $lang['news_add_something']);
    }
    header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=news&mode=news");
    die();
} elseif ($mode === 'edit') {
    $newsid = (int) $_GET['newsid'];
    if (!is_valid_id($newsid)) {
        stderr($lang['news_error'], $lang['news_edit_invalid']);
    }
    $arr = $fluent->from('news')
                  ->where('id = ?', $newsid)
                  ->fetch();
    if (empty($arr)) {
        stderr($lang['news_error'], $lang['news_edit_nonews']);
    }
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
        $update = [
            'body' => $body,
            'sticky' => $sticky,
            'anonymous' => $anonymous,
            'title' => $title,
        ];
        $fluent->update('news')
               ->set($update)
               ->where('id = ?', $newsid)
               ->execute();
        $cache->delete('latest_news_');
        $session->set('is-success', $lang['news_edit_success']);
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=news&mode=news");
        die();
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
                            <input type='text' name='title' class='w-100' value='" . format_comment($arr['title']) . "'>
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
                            <input type='radio' " . ($arr['sticky'] === 'yes' ? 'checked' : '') . " name='sticky' value='yes'>
                            {$lang['news_yes']}
                            <input type='radio' " . ($arr['sticky'] === 'no' ? 'checked' : '') . " name='sticky' value='no'>
                            {$lang['news_no']}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Anonymous?
                        </td>
                        <td>
                            {$lang['news_anonymous']}
                            <input type='radio' " . ($arr['anonymous'] === 'yes' ? 'checked' : '') . " name='anonymous' value='yes'>
                            {$lang['news_yes']}
                            <input type='radio' " . ($arr['anonymous'] === 'no' ? 'checked' : '') . " name='anonymous' value='no'>
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
    $results = $fluent->from('news')
                      ->orderBy('sticky')
                      ->orderBy('added DESC')
                      ->fetchAll();
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
    $i = 0;
    foreach ($results as $arr) {
        $newsid = $arr['id'];
        $body = $arr['body'];
        $title = $arr['title'];
        $added = get_date($arr['added'], 'LONG', 0, 1);
        $by = '<b>' . format_username($arr['userid']) . '</b>';
        $hash = hash('sha256', $site_config['salt']['one'] . $newsid . 'add');
        $user = $arr['anonymous'] === 'yes' ? get_anonymous_name() : format_username($arr['userid']);
        $class = $i++ != 0 ? 'top20' : '';
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
            </div>', $class);
    }
}

echo stdhead($lang['news_stdhead'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
die();
