<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;

require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_sitesettings'));
$home = 'site';
$stdfoot = [
    'js' => [
        get_file_name('site_config_js'),
    ],
];

global $container;

$fluent = $container->get(Database::class);
$session = $container->get(Session::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = $keys = [];
    $_post = $_POST;
    unset($_POST);
    $post = array_keys($_post);
    foreach ($post as $key) {
        preg_match('/([\d|Add]+)_id/', $key, $match);
        if (!empty($match[1])) {
            $keys[] = $match[1];
        }
    }

    foreach ($keys as $key) {
        $id = $key;
        $parent = $_post["{$id}_parent"];
        $home = $parent;
        $name = $_post["{$id}_name"];
        $type = $_post["{$id}_type"];
        $description = $_post["{$id}_description"];
        $values = [];
        if ($type === 'array') {
            $values[] = isset($_post["{$id}_value"]) ? $_post["{$id}_value"] : '';
            for ($i = 1; $i <= 1000; ++$i) {
                if (!empty($_post["{$id}_value_{$i}"])) {
                    $values[] = $_post["{$id}_value_{$i}"];
                }
            }
            $value = trim(implode('|', $values), '|');
        } else {
            $value = $_post["{$id}_value"];
        }

        $set = [
            'parent' => $parent,
            'name' => $name,
            'type' => $type,
            'value' => $value,
            'description' => $description,
        ];
        if (empty($description)) {
            unset($set['description']);
        }
        $item = !empty($site_config[$parent][$name]) ? $site_config[$parent][$name] : '';
        $parentname = (!empty($parent) ? $parent : '') . '::' . $name;
        if (empty($set['name'])) {
            if ($id != 0) {
                $fluent->deleteFrom('site_config')
                       ->where('id = ?', $id)
                       ->execute();
                $session->set('is-success', "$parentname {$lang['sitesettings_deleted']}");
            }
        } elseif ($id === 'Add') {
            if (!empty($item)) {
                $set['value'] = implode('|', $item) . '|' . $value;
                $fluent->update('site_config')
                       ->set($set)
                       ->where('parent = ?', $parent)
                       ->where('name = ?', $name)
                       ->execute();
                $session->set('is-success', "$parentname {$lang['sitesettings_updated']}");
            } else {
                if (empty($item)) {
                    $fluent->insertInto('site_config')
                           ->values($set)
                           ->execute();
                    $session->set('is-success', "$parentname {$lang['sitesettings_added']}");
                }
            }
        } else {
            $results = $fluent->update('site_config')
                              ->set($set)
                              ->where('id = ?', $id)
                              ->execute();
            if ($results) {
                $session->set('is-success', "$parentname {$lang['sitesettings_updated']}");
            }
        }
        $cache->delete('site_settings_');
    }

    $cache->delete('site_settings_');
}

$HTMLOUT .= "
    <h1 class='has-text-centered top20'>{$lang['sitesettings_sitehead']}</h1>
    <div class='padding20 margin20 bg-01 round10'>
        <p class='has-text-centered'>{$lang['sitesettings_add']}</p>
        <p class='has-text-centered'>{$lang['sitesettings_update']}</p>
        <p class='has-text-centered'>{$lang['sitesettings_delete']}</p>
    </div>";

$heading = "
            <tr>
                <th class='w-1'>ID</th>
                <th class='w-10 min-150'>Key</th>
                <th class='w-10 min-150'>Name</th>
                <th class='w-10 min-150'>Type</th>
                <th class='w-20 min-250'>Value</th>
                <th class='w-25 min-250'>Description</th>
            </tr>";

$body = '';

$sql = $fluent->from('site_config')
              ->orderBy('parent')
              ->orderBy('name');

$keys = $settings = [];
foreach ($sql as $row) {
    switch ($row['type']) {
        case 'int':
            $row['value'] = (int) $row['value'];
            break;
        case 'float':
            $row['value'] = (float) $row['value'];
            break;
        case 'bool':
            $row['value'] = (bool) $row['value'];
            break;
        case 'array':
            if (empty($row['value'])) {
                $row['value'] = [];
            } else {
                $value = explode('|', $row['value']);
                foreach ($value as $key => $item) {
                    if (is_numeric($item)) {
                        $row['value'][$key] = (int) $item;
                    }
                }
                $row['value'] = $value;
            }
            break;
    }
    $settings[] = $row;
    if (!in_array($row['parent'], $keys)) {
        $keys[] = $row['parent'];
    }
}

$settings[] = [
    'id' => 'Add',
    'parent' => 'New',
    'name' => '',
    'type' => '',
    'value' => '',
    'description' => '',
];
$keys[] = 'New';

$select = "
        <div class='has-text-centered bottom20 w-25'>
            <select id='select_key' name='select_key' class='w-100' onchange='show_key()'>";

foreach ($keys as $key) {
    $key = empty($key) ? 'null' : $key;
    $select .= "
                <option value='{$key}' " . ($key === $home ? 'selected' : '') . ">{$key}</option>";
}
$select .= '
            </select>
        </div>';
$HTMLOUT .= $select;

foreach ($keys as $key) {
    if ($key != 'New') {
        $settings[] = [
            'id' => 'Add',
            'parent' => $key,
            'name' => '',
            'type' => '',
            'value' => '',
            'description' => '',
        ];
    }
    $i = 0;
    $key = empty($key) ? 'null' : $key;
    $body = "
                <form action='{$_SERVER['PHP_SELF']}?tool=site_settings' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";

    foreach ($settings as $row) {
        if ($row['parent'] === $key) {
            if ($key === 'New') {
                $row['parent'] = '';
            }
            $body .= "
            <tr>
                <input type='hidden' name='{$row['id']}_id' value='{$row['id']}'>
                <td>{$row['id']}</td>
                <td>
                    <div class='top5 bottom5'>
                        <input type='text' name='{$row['id']}_parent' value='{$row['parent']}' placeholder='Key Value' class='w-100 margin5'>
                    </div>
                </td>
                <td>
                    <div class='top5 bottom5'>
                        <input type='text' name='{$row['id']}_name' value='{$row['name']}' placeholder='Name' class='w-100 margin5'>
                    </div>
                </td>
                <td>
                    <div class='top5 bottom5'>
                        <select name='{$row['id']}_type' class='w-100'>
                            <option value='bool' " . ($row['type'] === 'bool' ? 'selected' : '') . ">Boolean</option>
                            <option value='int' " . ($row['type'] === 'int' ? 'selected' : '') . ">Integer</option>
                            <option value='float' " . ($row['type'] === 'float' ? 'selected' : '') . ">Float</option>
                            <option value='string' " . ($row['type'] === 'string' ? 'selected' : '') . ">String</option>
                            <option value='array' " . ($row['type'] === 'array' ? 'selected' : '') . '>Array</option>
                        </select>
                    </div>
                </td>
                <td>';
            if ($row['type'] === 'bool') {
                $body .= "
                    <div class='top5 bottom5'>
                        <select name='{$row['id']}_value' class='w-100'>
                            <option value='0' " . (!$row['value'] ? 'selected' : '') . ">False</option>
                            <option value='1' " . ($row['value'] ? 'selected' : '') . '>True</option>
                        </select>
                    </div>';
            } elseif ($row['type'] === 'int') {
                $body .= "
                    <div class='top5 bottom5'>
                        <input type='number' name='{$row['id']}_value' value='{$row['value']}' placeholder='value' class='w-100 margin5'>
                    </div>";
            } elseif ($row['type'] === 'array' && is_array($row['value']) && !empty($row['value'])) {
                foreach ($row['value'] as $value) {
                    ++$i;
                    $body .= "
                    <div class='top5 bottom5'>
                        <input type='text' name='{$row['id']}_value_{$i}' value='{$value}' placeholder='value' class='w-100 margin5'>
                    </div>";
                }
            } elseif ($row['type'] === 'array' && is_array($row['value']) && empty($row['value'])) {
                $body .= "
                    <div class='top5 bottom5'>
                        <input type='text' name='{$row['id']}_value' value='' placeholder='value' class='w-100 margin5'>
                    </div>";
            } else {
                $body .= "
                    <div class='top5 bottom5'>
                        <input type='text' name='{$row['id']}_value' value='{$row['value']}' placeholder='value' class='w-100'>
                    </div>";
            }
            $body .= "
                </td>
                <td>
                    <div class='top5 bottom5'>
                        <textarea name='{$row['id']}_description' rows='6' class='w-100' placeholder='{$lang['sitesettings_info']}'>{$row['description']}</textarea>
                    </div>
                </td>
            </tr>" . (!empty($row['parent']) ? "
            <tr><td colspan='6' class='has-text-warning has-text-weight-bold has-text-centered'>Usage: \$site_config['{$row['parent']}']['{$row['name']}']</td></tr>
            <tr><td colspan='6'></td></tr>" : '');
        }
    }

    $body .= "
            <tr>
                <td colspan='6'>
                    <div class='margin20 has-text-centered'>
                        <input type='submit' class='button is-small' value='{$lang['sitesettings_apply']}'>
                    </div>
                </td>
            </tr>
        </form>";

    $HTMLOUT .= "
    <div id='$key'" . ($key != $home ? " class='is_hidden'" : '') . ">
        <h2 class='has-text-centered top20'> Key: " . strtoupper($key) . '</h1>' . main_table($body, $heading, 'top20') . '
    </div>';
}

echo stdhead($lang['sitesettings_stdhead']) . wrapper($HTMLOUT) . stdfoot($stdfoot);
