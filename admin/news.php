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
global $container, $site_config, $CURUSER;

$possible_modes = [
    'add',
    'delete',
    'edit',
    'news',
];
$mode = (isset($_GET['mode']) ? htmlsafechars($_GET['mode']) : '');
if (!in_array($mode, $possible_modes)) {
    stderr(_('Error'), _('Invalid Data.'));
}

$cache = $container->get(Cache::class);
$fluent = $container->get(Database::class);
$session = $container->get(Session::class);
if ($mode === 'delete') {
    $newsid = (int) $_GET['newsid'];
    if (!is_valid_id($newsid)) {
        stderr(_('Error'), _('Invalid ID.'));
    }
    $hash = hash('sha256', $site_config['salt']['one'] . $newsid . 'add');
    $sure = '';
    $sure = isset($_GET['sure']) ? (int) $_GET['sure'] : '';
    if (!$sure) {
        stderr(_('Confirm Delete'), _fe('Do you really want to delete this news entry? Click') . "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=delete&amp;sure=1&amp;h=$hash&amp;newsid=$newsid'> " . _('here') . '</a> ' . _('if you are sure.') . '', null);
    }
    if ($_GET['h'] != $hash) {
        stderr(_('Error'), _('what are you doing?'));
    }

    $fluent->deleteFrom('news')
           ->where('id = ?', $newsid)
           ->execute();
    $cache->delete('latest_news_');
    $session->set('is-success', _('News entry deleted'));
    header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=news&mode=news");
    die();
} elseif ($mode === 'add') {
    $body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
    $sticky = isset($_POST['sticky']) ? htmlsafechars($_POST['sticky']) : 'yes';
    $anonymous = isset($_POST['anonymous']) ? htmlsafechars($_POST['anonymous']) : '0';
    if (!$body) {
        stderr(_('Error'), _('The news item cannot be empty!'));
    }
    $title = htmlsafechars($_POST['title']);
    if (!$title) {
        stderr(_('Error'), _('The news title cannot be empty!'));
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
        $session->set('is-success', _('News entry was added successfully.'));
    } else {
        $session->set('is-warning', _("Something's wrong!"));
    }
    header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=news&mode=news");
    die();
} elseif ($mode === 'edit') {
    $newsid = (int) $_GET['newsid'];
    if (!is_valid_id($newsid)) {
        stderr(_('Error'), _('Invalid news item ID.'));
    }
    $arr = $fluent->from('news')
                  ->where('id = ?', $newsid)
                  ->fetch();
    if (empty($arr)) {
        stderr(_('Error'), _('No news item with that ID.'));
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $body = isset($_POST['body']) ? htmlsafechars($_POST['body']) : '';
        $sticky = isset($_POST['sticky']) ? htmlsafechars($_POST['sticky']) : 'yes';
        $anonymous = isset($_POST['anonymous']) ? htmlsafechars($_POST['anonymous']) : '1';
        if ($body == '') {
            stderr(_('Error'), _('Body cannot be empty!'));
        }
        $title = htmlsafechars($_POST['title']);
        if ($title == '') {
            stderr(_('Error'), _('Title cannot be empty!'));
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
        $session->set('is-success', _('News item was edited successfully'));
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=news&mode=news");
        die();
    } else {
        $HTMLOUT .= "
            <h1 class='has-text-centered'>" . _('Edit News Item') . "</h1>
            <form method='post' name='compose' action='./staffpanel.php?tool=news&amp;mode=edit&amp;newsid=$newsid' enctype='multipart/form-data' accept-charset='utf-8'>
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
                            " . BBcode($arr['body']) . '
                        </td>
                    </tr>
                    <tr>
                        <td>
                            ' . _('Sticky') . "
                        </td>
                        <td>
                            <input type='radio' " . ($arr['sticky'] === 'yes' ? 'checked' : '') . " name='sticky' value='yes'>
                            " . _('Yes') . "
                            <input type='radio' " . ($arr['sticky'] === 'no' ? 'checked' : '') . " name='sticky' value='no'>
                            " . _('No') . '
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Anonymous?
                        </td>
                        <td>
                            ' . _('Anonymous') . "
                            <input type='radio' " . ($arr['anonymous'] === '1' ? 'checked' : '') . " name='anonymous' value='1'>
                            " . _('Yes') . "
                            <input type='radio' " . ($arr['anonymous'] === '0' ? 'checked' : '') . " name='anonymous' value='0'>
                            " . _('No') . "
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' value='" . _('Okay') . "' class='button is-small'>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>";
        $title = _('New Manager');
        $breadcrumbs = [
            "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
            "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
        ];
        echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
    }
} elseif ($mode === 'news') {
    $results = $fluent->from('news')
                      ->orderBy('sticky')
                      ->orderBy('added DESC')
                      ->fetchAll();
    $HTMLOUT .= "
    <div class='portlet'>
        <h1 class='has-text-centered'>" . _('Submit News Item') . "</h1>
        <form method='post' name='compose' action='./staffpanel.php?tool=news&amp;mode=add' enctype='multipart/form-data' accept-charset='utf-8'>
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
                        <td class='is-paddingless'>" . BBcode() . '
                        </td>
                    </tr>
                    <tr>
                        <td>
                            ' . _('Sticky') . "
                        </td>
                        <td>
                            <input type='radio' checked name='sticky' value='yes'>
                            " . _('Yes') . "
                            <input name='sticky' type='radio' value='no'>
                            " . _('No') . '
                        </td>
                    </tr>
                    <tr>
                        <td>
                            ' . _('Anonymous') . "
                        </td>
                        <td>
                            <input type='radio' name='anonymous' value='1' checked>
                            " . _('Yes') . "
                            <input type='radio' name='anonymous' value='0'>
                            " . _('No') . "
                        </td>
                    </tr>
                    <tr class='no_hover'>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' value='" . _('Okay') . "' class='button is-small'>
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
        $added = get_date((int) $arr['added'], 'LONG', 0, 1);
        $by = '<b>' . format_username((int) $arr['userid']) . '</b>';
        $hash = hash('sha256', $site_config['salt']['one'] . $newsid . 'add');
        $user = $arr['anonymous'] === '1' ? get_anonymous_name() : format_username((int) $arr['userid']);
        $class = $i++ != 0 ? 'top20' : '';
        $HTMLOUT .= main_div("
            <div class='level bg-01 padding20 round5'>
                <div class='has-text-left'>
                    " . _('News entry created by') . " $user $added
                </div>
                <div class='has-text-right'>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=edit&amp;newsid=$newsid' title='" . _('Edit') . "' class='tooltipper'>
                        <i class='icon-edit icon has-text-info' aria-hidden='true'></i>
                    </a>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=news&amp;mode=delete&amp;newsid=$newsid&amp;sure=1&amp;h=$hash' title='" . _('Delete') . "' class='has-text-danger tooltipper'>
                        <i class='icon-cancel icon has-text-danger' aria-hidden='true'></i>
                    </a>
                </div>
            </div>
            <div class='padding20'>
                <h2>" . htmlsafechars($title) . '</h2>
                <div>' . format_comment($body) . '</div>
            </div>', $class);
    }
}

$title = _('News Manager');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
