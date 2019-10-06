<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Session;
use Pu239\Wiki;
use Rakit\Validation\Validator;
use Spatie\Image\Exceptions\InvalidManipulation;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_bbcode.php';
$user = check_user_status();
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
    global $site_config;

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
                <li><a href="' . $site_config['paths']['baseurl'] . '/wiki.php" class="' . ($action === 'index' ? 'active ' : '') . 'is-link margin10">' . _('Index') . '</a></li>
                <li><a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=add" class="' . ($action === 'add' ? 'active ' : '') . 'is-link margin10">' . _('Add') . '</a></li>
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
                <input type='submit' class='button is-small' value='" . _('Search') . "' name='wiki'>
            </div>
        </form>";
    $ret .= main_div($div, 'bottom20') . '
    </div>';

    return $ret;
}

/**
 * @throws \Envms\FluentPDO\Exception
 * @throws InvalidManipulation
 * @throws DependencyException
 * @throws NotFoundException
 *
 * @return string|void
 */
function wikimenu()
{
    global $container, $site_config;

    $wiki = $container->get(Wiki::class);
    $name = $wiki->get_last();

    return main_div("
        <div class='padding20'>
            <ul>
            <span class='size_6'>" . _('Permissions') . ':</span>
            <li>' . _('Read: User') . '</li>
            <li>' . _('Write: User') . '</li>
            <li>' . _('Edit: Staff') . "/Author</li><br>
            <span class='size_6'>" . _('Latest Article:') . "</span>
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
    $validator = $container->get(Validator::class);
    if (isset($_POST['article-add'])) {
        $validation = $validator->validate($_POST, [
            'article-name' => 'required|regex:/[A-Za-z][A-Za-z0-9 _-]*/',
            'body' => 'required',
        ]);
        if (!$validation->fails()) {
            $values = [
                'name' => htmlsafechars($_POST['article-name']),
                'body' => htmlsafechars($_POST['body']),
                'userid' => $user['id'],
                'time' => TIME_NOW,
            ];
            $wiki->add($values);
            $session->set('is-success', 'Wiki article added');
        }
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
                <img src='{$site_config['paths']['images_baseurl']}wiki.png' alt='' title='" . _('Wiki') . "' class='tooltipper' width='25'>
                <span class='left10'>" . _('Wiki') . "</span>
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
                $edit = '<div class="left10 top20">Last Updated by: ' . format_username((int) $result['lastedituser']) . ' - ' . get_date((int) $result['lastedit'], 'LONG') . '</div>';
            }
            $div = '
                    <h1 class="has-text-centered">
                        <a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=article&amp;name=' . urlencode($result['name']) . '">' . format_comment($result['name']) . '</a>
                    </h1>
                    <div class="bg-02 padding10 round10">' . ($result['userid'] > 0 ? " <div class='left10 bottom20'>" . _('Article added by ') . ': ' . format_username((int) $result['userid']) . '</div>' : '') . '
                        <div class="w-100 padding20 round10 bg-02">' . format_comment($result['body']) . '</div>
                    </div>' . $edit;
            $div .= (has_access($user['class'], UC_STAFF, 'coder') || $user['id'] === $result['userid'] ? '
                    <div class="has-text-centered">
                        <a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=edit&amp;id=' . $result['id'] . '" class="button is-small margin20">' . _('Edit') . '</a>
                        <a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=delete&amp;id=' . $result['id'] . '" class="button is-small margin20">' . _('Delete') . '</a>
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
            $HTMLOUT .= navmenu() . "<h2 class='has-text-centered'>Article search results for: <b>" . format_comment($name) . '</b></h2>';
            foreach ($results as $result) {
                $HTMLOUT .= main_div('
                    <div class="padding20">
                        <h2><a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=article&amp;name=' . urlencode($result['name']) . '">' . format_comment($result['name']) . ' </a></h2>
                        <div>' . _('Added by') . ': ' . format_username((int) $result['userid']) . '</div>
                        <div>Added on: ' . get_date((int) $result['time'], 'LONG') . '</div>' . (!empty($result['lastedit']) ? '
                        <div>Last Edited on: ' . get_date((int) $result['lastedit'], 'LONG') . '</div>
                    </div>' : '</div>'), 'top20');
            }
        } else {
            $HTMLOUT .= navmenu() . stdmsg(_('Error'), _('No article found.'));
        }
    }
}

if ($action === 'add') {
    $HTMLOUT .= navmenu() . "
            <form method='post' action='wiki.php' enctype='multipart/form-data' accept-charset='utf-8'>
                <input type='text' name='article-name' id='name' class='w-100 top10 bottom10 has-text-centered' placeholder='Article Title' minlength='3' maxlength='100' pattern='[A-Za-z][A-Za-z0-9 _-]*'> " . BBcode() . "
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' name='article-add' value='" . _('OK') . "'>
                </div>
            </form>";
} elseif ($action === 'delete') {
    $result = $wiki->get_by_id($id);
    if (!empty($result) && (has_access($user['class'], UC_STAFF, 'coder') || $user['id'] === $result['userid'])) {
        if ($wiki->delete($id)) {
            $session->set('is-success', _('Wiki Item Has Been Deleted'));
        } else {
            $session->set('is-warning', _('Wiki Item Has [b]NOT[/b] Been Deleted'));
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } else {
        $HTMLOUT .= navmenu() . stdmsg(_('Error'), _('Access Denied'));
    }
} elseif ($action === 'edit') {
    $result = $wiki->get_by_id($id);
    if (!empty($result) && (has_access($user['class'], UC_STAFF, 'coder') || $user['id'] === $result['userid'])) {
        $HTMLOUT .= navmenu() . "
            <form method='post' action='wiki.php' enctype='multipart/form-data' accept-charset='utf-8'>
                <input type='text' name='article-name' id='name' class='w-100 top10 bottom10 has-text-centered' value='" . format_comment($result['name']) . "'>
                <input type='hidden' name='article-id' value='$id'> " . BBcode($result['body']) . "
                <div class='has-text-centered margin20'>
                    <input type='submit' class='button is-small' name='article-edit' value='" . _('OK') . "'>
                </div>
            </form> ";
    } else {
        $HTMLOUT .= navmenu() . stdmsg(_('Error'), _('Access Denied'));
    }
} elseif ($action === 'sort') {
    $results = $wiki->get_by_name($letter);
    if (!empty($results)) {
        $HTMLOUT .= navmenu();
        $div = " <h2 class='has-text-centered'>" . _('Articles starting with the letter') . ': <b> ' . format_comment($letter) . "</b></h2>
        <div class='w-100 padding20 round10 bg-02'> ";
        foreach ($results as $result) {
            $div .= '
            <div class="padding20 bottom10 round10 bg-02">
                <h2><a href="' . $site_config['paths']['baseurl'] . '/wiki.php?action=article&amp;name=' . urlencode($result['name']) . '">' . format_comment($result['name']) . '</a></h2>
                <div>' . _('Added by') . ': ' . format_username((int) $result['userid']) . '</div>
                <div>Added on: ' . get_date((int) $result['time'], 'LONG') . '</div>' . (!empty($result['lastedit']) ? '
                <div>Last Edited on: ' . get_date((int) $result['lastedit'], 'LONG') . '</div>' : '') . '
            </div>';
        }
        $div .= '
        </div>';
        $HTMLOUT .= main_div($div);
    } else {
        $HTMLOUT .= navmenu() . stdmsg(_('Error'), _('No articles starting with letter ') . '<b> ' . format_comment($letter) . ' </b> found.');
    }
}
$HTMLOUT .= '</div>';

$title = _('Wiki');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
