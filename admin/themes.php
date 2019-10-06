<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
class_check(UC_MAX);
global $container, $site_config;

$fluent = $container->get(Database::class);
$HTML = '';

/**
 * @throws DependencyException
 * @throws NotFoundException
 */
function clear_template_cache()
{
    global $container;

    $cache = $container->get(Cache::class);
    for ($i = 0; $i <= UC_MAX; ++$i) {
        $cache->delete('templates_' . $i);
    }
}

if (isset($_GET['act'])) {
    $session = $container->get(Session::class);
    if (!isset($_GET['act'])) {
        stderr(_('Error'), _('Invalid ID'));
    }
    if (!is_valid_id((int) $_GET['act'])) {
        stderr(_('Error'), _('Invalid action'));
    }
    $act = (int) $_GET['act'];

    if ($act === 1) {
        $template = $fluent->from('stylesheets')
                           ->where('id = ?', $id)
                           ->fetch();

        $HTML .= "
        <form action='{$_SERVER['PHP_SELF']}?tool=themes&amp;action=themes&amp;act=4' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <input type='hidden' value='{$template['id']}' name='tid'>
            <input type='hidden' value='default.css' name='uri'>
            <h1 class='has-text-centered'>" . _('Edit Template') . ': ' . htmlsafechars($template['name']) . '</h1>';
        $body = '
            <tr>
                <td>' . _('ID') . '<br>' . _('This shall be the same as the folder name') . "</td>
                <td><input type='text' value='{$template['id']}' name='id' class='w-100' required></td>
            </tr>
            <tr>
                <td>" . _('Name') . "</td>
                <td><input type='text' value='" . htmlsafechars($template['name']) . "' name='title' class='w-100' required></td>
            </tr>
            <tr>
                <td>" . _('Min Class To View') . "</td>
                <td>
                    <select name='class' class='w-100'>";
        for ($i = 0; $i <= UC_MAX; ++$i) {
            $body .= "
                        <option value='$i' " . ($template['min_class_to_view'] == $i ? 'selected' : '') . '>' . get_user_class_name((int) $i) . '</option>';
        }
        $body .= '
                    </select>
                </td>
            </tr>
            <tr>
                <td>' . _('Folder Exists?') . '</td>
                <td>
                    <b>' . (file_exists(TEMPLATE_DIR . $template['id'] . '/template.php') ? "<span class='has-text-success'>" . _('Yes') . '</span>' : "<span class='has-text-danger'>" . _('No') . '</span>') . '</b>
                </td>
            </tr>
            <tr>';
        $HTML .= main_table($body) . "
            <div class='has-text-centered margin20'>
                <input type='submit' value='" . _('Save') . "' class='button is-small'>
            </div>
        </form>";
    }
    if ($act === 2) {
        stderr(_('Delete Template'), _fe('Are you sure you want to delete this template? CLick {0}here{1} if you are sure', "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=5&amp;id=$id&amp;sure=1'>", '</a>'));
    }
    if ($act === 3) {
        $ids = $fluent->from('stylesheets')
                      ->select(null)
                      ->select('id')
                      ->orderBy('id');
        $taken = [];
        foreach ($ids as $id) {
            $taken[] = "<span class='has-text-danger'>{$id['id']}</span>";
            if (file_exists(TEMPLATE_DIR . (int) $id['id'] . '/template.php')) {
                $taken[] = "<span class='has-text-success'>{$id['id']}</span>";
            }
        }
        $HTML .= "
        <form action='staffpanel.php?tool=themes&amp;action=themes&amp;act=6' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <input type='hidden' value='default.css' name='uri'>
            <h1 class='has-text-centered'>" . _('Add a template') . '</h1>';
        $body = '
                <tr>
                    <td>' . _('ID') . "</td>
                    <td>
                        <input type='text' value='' name='id' placeholder='" . _('Must be a positive integer') . "'> " . _("Taken ID's") . ': <b>' . implode(', ', $taken) . '</b>
                    </td>
                </tr>
                <tr>
                    <td>' . _('Name') . "</td>
                    <td><input type='text' value='' name='name' placeholder='" . _('Template Name') . "'></td>
                </tr>
                <tr>
                    <td>" . _('Min Class To View') . "</td>
                    <td>
                        <select name='class'>";
        for ($i = 0; $i <= UC_MAX; ++$i) {
            $body .= "
                            <option value='$i'>" . get_user_class_name((int) $i) . '</option>';
        }
        $body .= "
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                    <ul class='left20'>
                        <li class='bullet'>" . _fe('Make a folder in the Templates dir: {0} and create files', TEMPLATE_DIR) . "
                            <ul>
                                <li class='bullet'>default.css</li>
                                <li class='bullet'>custom.css</li>
                                <li class='bullet'>template.php</li>
                            </ul>
                        </li><br>
                        <li class='bullet'>" . _('In template.php there shall be minimum 4 functions') . "
                            <ul>
                                <li class='bullet'>stdhead</li>
                                <li class='bullet'>stdfoot</li>
                                <li class='bullet'>stdmsg</li>
                                <li class='bullet'>StatusBar</li>
                            </ul>
                        </li><br>
                        <li class='bullet'>" . _fe('Make a folder in the AJAX Chat dir: {0} and copy these files from {1}', AJAX_CHAT_PATH . 'css/', AJAX_CHAT_PATH . 'css/1/') . "
                            <ul>
                                <li class='bullet'>global.css</li>
                                <li class='bullet'>fonts.css</li>
                                <li class='bullet'>custom.css</li>
                                <li class='bullet'>default.css</li>
                            </ul>
                        </li><br>
                    </ul>
                    </td>
                </tr>";
        $HTML .= main_table($body) . "
                <div class='has-text-centered margin20'>
                    <input type='submit' value='" . _('Add') . "' class='button is-small'>
                </div>
        </form>";
    }
    if ($act === 4) {
        if (!isset($_POST['id'])) {
            stderr(_('Error'), _('Invalid ID'));
        }
        if (!isset($_POST['uri'])) {
            stderr(_('Error'), _('Invalid Uri'));
        }
        if (!isset($_POST['title'])) {
            stderr(_('Error'), _('Invalid Name'));
        }
        $tid = (int) $_POST['tid'];
        $id = (int) $_POST['id'];
        $uri = $_POST['uri'];
        $min_class = $_POST['class'];
        $name = htmlsafechars($_POST['title']);
        if (!is_valid_id($id)) {
            stderr(_('Error'), _('Invalid ID'));
        }

        $cur = $fluent->from('stylesheets')
                      ->where('id = ?', $tid)
                      ->fetch();

        if ($id != $cur['id']) {
            $set['id'] = $id;
        }
        if ($uri != $cur['uri']) {
            $set['uri'] = $uri;
        }
        if ($name != $cur['name']) {
            $set['name'] = $name;
        }
        if ($min_class != $cur['min_class_to_view']) {
            $set['min_class_to_view'] = $min_class;
        }
        $update = $fluent->update('stylesheets')
                         ->set($set)
                         ->where('id = ?', $tid)
                         ->execute();
        if (!$update) {
            $session->set('is-danger', _('Something Went Wrong'));
        } else {
            clear_template_cache();
            $session->set('is-success', _('Succesfully Edited'));
        }
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=themes&action=themes");
        die();
    }
    if ($act === 5) {
        if (!isset($_GET['id'])) {
            stderr(_('Error'), _('Invalid ID'));
        }
        $id = (int) $_GET['id'];
        if (!is_valid_id($id)) {
            stderr(_('Error'), _('Invalid ID'));
        }
        if (!isset($_GET['sure'])) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=themes');
            die();
        }
        if (isset($_GET['sure']) && $_GET['sure'] != 1) {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=themes');
            die();
        }

        $fluent->deleteFrom('stylesheets')
               ->where('id = ?', $id)
               ->execute();

        $set = [
            'stylesheet' => $site_config['site']['stylesheet'],
        ];
        $fluent->update('users')
               ->set($set)
               ->where('stylesheet = ?', $id)
               ->execute();

        clear_template_cache();
        $session->set('is-success', _('Succesfully Deleted'));
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=themes&action=themes");
        die();
    }
    if ($act === 6) {
        if (!isset($_POST['id'])) {
            stderr(_('Error'), _('Invalid ID'));
        }
        if (!isset($_POST['uri'])) {
            stderr(_('Error'), _('Invalid Uri'));
        }
        if (!isset($_POST['name'])) {
            stderr(_('Error'), _('Invalid Name'));
        }
        if (!file_exists(TEMPLATE_DIR . $_POST['id'] . '/template.php')) {
            stderr(_('Error'), _fe('Template file does not exist. Continue? {0}Yes{1} {2}No{3}', "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=7&amp;id=" . (int) $_POST['id'] . '&amp;uri=' . $_POST['uri'] . '&amp;name=' . htmlsafechars($_POST['name']) . "'><span class='has-text-success left5'>", '</span></a>', "<a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=themes'><span class='has-text-danger left5'>", '</span></a>'));
        }
        if (!isset($_POST['class'])) {
            stderr(_('Error'), _('Invalid Class'));
        }

        $values = [
            'id' => $_POST['id'],
            'uri' => $_POST['uri'],
            'name' => htmlsafechars($_POST['name']),
            'min_class_to_view' => $_POST['class'],
        ];
        $fluent->insertInto('stylesheets')
               ->values($values)
               ->execute();

        clear_template_cache();
        $session->set('is-success', _('Succesfully Edited'));
        header("Location: {$site_config['paths']['baseurl']}/staffpanel.php?tool=themes&action=themes");
        die();
    }
    if ($act === 7) {
        if (!isset($_GET['id'])) {
            stderr(_('Error'), _('Invalid ID'));
        }
        if (!isset($_GET['uri'])) {
            stderr(_('Error'), _('Invalid Uri'));
        }
        if (!isset($_GET['name'])) {
            stderr(_('Error'), _('Invalid Name'));
        }

        $values = [
            'id' => $_GET['id'],
            'uri' => $_GET['uri'],
            'name' => htmlsafechars($_GET['name']),
        ];
        $fluent->insertInto('stylesheets')
               ->values($values)
               ->execute();

        clear_template_cache();
        $session->set('is-success', _('Succesfully Added'));
        header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=themes&action=themes');
        die();
    }
}

if (!isset($_GET['act'])) {
    $heading = '
            <tr>
                <th>' . _('ID') . '</th>
                <th>' . _('Uri') . '</th>
                <th>' . _('Name') . '</th>
                <th>' . _('Folder Exists?') . '</th>
                <th>' . _('Min Class To View') . '</th>
                <th>' . _('Edit/Delete') . '</th>
            </tr>';

    $templates = $fluent->from('stylesheets')
                        ->orderBy('id');

    $body = '';
    foreach ($templates as $template) {
        $body .= "
        <tr>
            <td>$template[id]</td>
            <td>" . htmlsafechars($template['uri']) . '</td>
            <td>' . htmlsafechars($template['name']) . '</td>
            <td><b>' . (file_exists(TEMPLATE_DIR . (int) $template['id'] . '/template.php') ? "<span class='has-text-success'>" . _('Yes') . '</span>' : "<span class='has-text-danger'>" . _('No') . '</span>') . '</b></td>
            <td>' . get_user_class_name((int) $template['min_class_to_view']) . "</td>
            <td>
                <span>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=1&amp;id=" . (int) $template['id'] . "' class='tooltipper' title='" . _('Edit') . "'>
                        <i class='icon-edit icon has-text-info' aria-hidden='true'></i>
                    </a>
                </span>
                <span>
                    <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=2&amp;id=" . (int) $template['id'] . "' class='tooltipper' title='" . _('Delete') . "'>
                        <i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i>
                    </a>
                </span>
            </td>
        </tr>";
    }
    $HTML .= main_table($body, $heading) . "
        <div class='has-text-centered margin20'>
            <a href='{$site_config['paths']['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=3' class='tooltipper' title='" . _('Add a template') . "'>
                <span class='button is-small'>" . _('Add a template') . '</span>
            </a>
        </div>';
}
$title = _('Templates');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
