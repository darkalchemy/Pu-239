<?php

require_once INCL_DIR . 'user_functions.php';
require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'html_functions.php';
class_check(UC_MAX);
global $site_config, $lang, $fluent, $session, $cache;

$lang = array_merge($lang, load_language('ad_themes'));
$HTML = '';

function clear_template_cache()
{
    global $cache;
    for ($i = 0; $i <= UC_MAX; ++$i) {
        $cache->delete('templates_' . $i);
    }
}

if (isset($_GET['act'])) {
    if (!isset($_GET['act'])) {
        stderr("{$lang['themes_error']}", "{$lang['themes_inv_id']}");
    }
    if (!is_valid_id($_GET['act'])) {
        stderr("{$lang['themes_error']}", "{$lang['themes_inv_act']}");
    }
    $act = (int) $_GET['act'];

    if ($act === 1) {
        $template = $fluent->from('stylesheets')
            ->where('id = ?', $id)
            ->fetch();

        $HTML .= "
        <form action='{$site_config['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=4' method='post'>
            <input type='hidden' value='{$template['id']}' name='tid'>
            <input type='hidden' value='default.css' name='uri'>
            <h1 class='has-text-centered'>{$lang['themes_edit_tem']}: " . htmlsafechars($template['name']) . '</h1>';
        $body = "
            <tr>
                <td>{$lang['themes_id']}<br>{$lang['themes_explain_id']}</td>
                <td><input type='text' value='{$template['id']}' name='id' class='w-100' required></td>
            </tr>
            <tr>
                <td>{$lang['themes_name']}</td>
                <td><input type='text' value='" . htmlsafechars($template['name']) . "' name='title' class='w-100' required></td>
            </tr>
            <tr>
                <td>{$lang['themes_min_class']}</td>
                <td>
                    <select name='class' class='w-100'>";
        for ($i = 0; $i <= UC_MAX; ++$i) {
            $body .= "
                        <option value='$i'" . ($template['min_class_to_view'] == $i ? ' selected' : '') . '>' . get_user_class_name($i) . '</option>';
        }
        $body .= "
                    </select>
                </td>
            </tr>
            <tr>
                <td>{$lang['themes_is_folder']}</td>
                <td>
                    <b>" . (file_exists(TEMPLATE_DIR . $template['id'] . '/template.php') ? "{$lang['themes_file_exists']}" : "{$lang['themes_not_exists']}") . '</b>
                </td>
            </tr>
            <tr>';
        $HTML .= main_table($body) . "
            <div class='has-text-centered margin20'>
                <input type='submit' value='{$lang['themes_save']}' class='button is-small'>
            </div>
        </form>";
    }
    if ($act === 2) {
        stderr("{$lang['themes_delete_q']}", "
            {$lang['themes_delete_sure_q']}
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=5&amp;id=$id&amp;sure=1'>
                {$lang['themes_delete_sure_q2']}
            </a> {$lang['themes_delete_sure_q3']}");
    }
    if ($act === 3) {
        $ids = $fluent->from('stylesheets')
            ->select(null)
            ->select('id')
            ->orderBy('id');
        foreach ($ids as $id) {
            if (file_exists(TEMPLATE_DIR . (int) $id['id'] . '/template.php')) {
                $taken[] = "<span class='has-text-success'>{$id['id']}</span>";
            } else {
                $taken[] = "<span class='has-text-danger'>{$id['id']}</span>";
            }
        }
        $HTML .= "
        <form action='staffpanel.php?tool=themes&amp;action=themes&amp;act=6' method='post'>
            <input type='hidden' value='default.css' name='uri'>
            <h1 class='has-text-centered'>{$lang['themes_addnew']}</h1>";
        $body = "
                <tr>
                    <td>{$lang['themes_id']}</td>
                    <td>
                        <input type='text' value='' name='id' placeholder='Must be a positive integer'> {$lang['themes_takenids']}<b>" . implode(', ', $taken) . "</b>
                    </td>
                </tr>
                <tr>
                    <td>{$lang['themes_name']}</td>
                    <td><input type='text' value='' name='name' placeholder='Template Name'></td>
                </tr>
                <tr>
                    <td>{$lang['themes_min_class']}</td>
                    <td>
                        <select name='class'>";
        for ($i = 0; $i <= UC_MAX; ++$i) {
            $body .= "
                            <option value='$i'>" . get_user_class_name($i) . '</option>';
        }
        $body .= "
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>{$lang['themes_guide']}</td>
                </tr>";
        $HTML .= main_table($body) . "
                <div class='has-text-centered margin20'>
                    <input type='submit' value='{$lang['themes_add']}' class='button is-small'>
                </div>
        </form>";
    }
    if ($act === 4) {
        if (!isset($_POST['id'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_id']}");
        }
        if (!isset($_POST['uri'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_uri']}");
        }
        if (!isset($_POST['title'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_name']}");
        }
        $tid = (int) $_POST['tid'];
        $id = (int) $_POST['id'];
        $uri = $_POST['uri'];
        $min_class = $_POST['class'];
        $name = htmlsafechars($_POST['title']);
        if (!is_valid_id($id)) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_id']}");
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
            $session->set('is-danger', $lang['themes_some_wrong']);
        } else {
            clear_template_cache();
            $session->set('is-success', $lang['themes_msg']);
        }
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=themes&action=themes");
        die();
    }
    if ($act === 5) {
        if (!isset($_GET['id'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_id']}");
        }
        $id = (int) $_GET['id'];
        if (!is_valid_id($id)) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_id']}");
        }
        if (!isset($_GET['sure'])) {
            header('Location: staffpanel.php?tool=themes');
            die();
        }
        if (isset($_GET['sure']) && $_GET['sure'] != 1) {
            header('Location: staffpanel.php?tool=themes');
            die();
        }

        $fluent->deleteFrom('stylesheets')
            ->where('id = ?', $id)
            ->execute();

        $set = [
            'stylesheet' => $site_config['stylesheet'],
        ];
        $fluent->update('users')
            ->set($set)
            ->where('stylesheet = ?', $id)
            ->execute();

        clear_template_cache();
        $session->set('is-success', $lang['themes_msg2']);
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=themes&action=themes");
        die();
    }
    if ($act === 6) {
        if (!isset($_POST['id'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_id']}");
        }
        if (!isset($_POST['uri'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_uri']}");
        }
        if (!isset($_POST['name'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_name']}");
        }
        if (!file_exists(TEMPLATE_DIR . $_POST['id'] . '/template.php')) {
            stderr("{$lang['themes_nofile']}", "{$lang['themes_inv_file']}<a href='{$site_config['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=7&amp;id=" . (int) $_POST['id'] . '&amp;uri=' . $_POST['uri'] . '&amp;name=' . htmlsafechars($_POST['name']) . "'>{$lang['themes_file_exists']}</a>/
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=themes'>{$lang['themes_not_exists']}</a>");
        }
        if (!isset($_POST['class'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_class']}");
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
        $session->set('is-success', $lang['themes_msg']);
        header("Location: {$site_config['baseurl']}/staffpanel.php?tool=themes&action=themes");
        die();
    }
    if ($act === 7) {
        if (!isset($_GET['id'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_id']}");
        }
        if (!isset($_GET['uri'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_uri']}");
        }
        if (!isset($_GET['name'])) {
            stderr("{$lang['themes_error']}", "{$lang['themes_inv_name']}");
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
        $session->set('is-success', $lang['themes_msg3']);
        header('Location: staffpanel.php?tool=themes&action=themes');
        die();
    }
}

if (!isset($_GET['act'])) {
    $heading = "
            <tr>
                <th>{$lang['themes_id']}</th>
                <th>{$lang['themes_uri']}</th>
                <th>{$lang['themes_name']}</th>
                <th>{$lang['themes_is_folder']}</th>
                <th>{$lang['themes_min_class']}</th>
                <th>{$lang['themes_e_d']}</th>
            </tr>";

    $templates = $fluent->from('stylesheets')
        ->orderBy('id');

    $body = '';
    foreach ($templates as $template) {
        $body .= "
        <tr>
            <td>$template[id]</td>
            <td>" . htmlsafechars($template['uri'], ENT_QUOTES) . '</td>
            <td>' . htmlsafechars($template['name'], ENT_QUOTES) . '</td>
            <td><b>' . (file_exists(TEMPLATE_DIR . (int) $template['id'] . '/template.php') ? "{$lang['themes_file_exists']}" : "{$lang['themes_not_exists']}") . '</b></td>
            <td>' . get_user_class_name($template['min_class_to_view']) . "</td>
            <td>
                <span>
                    <a href='{$site_config['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=1&amp;id=" . (int) $template['id'] . "' class='tooltipper' title='{$lang['themes_edit']}'>
                        <i class='icon-edit icon'></i>
                    </a>
                </span>
                <span>
                    <a href='{$site_config['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=2&amp;id=" . (int) $template['id'] . "' class='tooltipper' title='{$lang['themes_delete']}'>
                        <i class='icon-trash-empty icon has-text-danger'></i>
                    </a>
                </span>
            </td>
        </tr>";
    }
    $HTML .= main_table($body, $heading) . "
        <div class='has-text-centered margin20'>
            <a href='{$site_config['baseurl']}/staffpanel.php?tool=themes&amp;action=themes&amp;act=3' class='tooltipper' title='{$lang['themes_addnew']}'>
                <span class='button is-small'>{$lang['themes_addnew']}</span>
            </a>
        </div>";
}
echo stdhead("{$lang['stdhead_templates']}") . wrapper($HTML) . stdfoot();
