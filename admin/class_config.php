<?php

declare(strict_types = 1);

use DI\DependencyException;
use DI\NotFoundException;
use Envms\FluentPDO\Literal;
use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;

require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
require_once BIN_DIR . 'uglify.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_class_config'));
global $container, $CURUSER, $site_config;

$style = get_stylesheet();
$session = $container->get(Session::class);
$all_classes = $fluent->from('class_config')
    ->where('template = ', $style)
    ->orderBy('value');
foreach ($all_classes as $ac) {
    $class_config[$ac['name']]['value'] = $ac['value'];
    $class_config[$ac['name']]['classname'] = $ac['classname'];
    $class_config[$ac['name']]['classcolor'] = $ac['classcolor'];
    $class_config[$ac['name']]['classpic'] = $ac['classpic'];
}
$possible_modes = [
    'add',
    'edit',
    'remove',
    '',
];
$mode = isset($_POST['mode']) ? htmlsafechars($_POST['mode']) : '';
if (!in_array($mode, $possible_modes)) {
    stderr($lang['classcfg_error'], $lang['classcfg_error1']);
}

/**
 * @param int    $value
 * @param string $direction
 *
 * @throws DependencyException
 * @throws NotFoundException
 * @throws \Envms\FluentPDO\Exception
 */
function update_forum_classes(int $value, string $direction)
{
    global $container;

    $fluent = $container->get(Database::class);
    if ($direction === 'increment') {
        $fluent->update('forums')
            ->set(['min_class_read' => new Literal('min_class_read + 1')])
            ->where('min_class_read >= ?', $value)
            ->execute();

        $fluent->update('forums')
            ->set(['min_class_write' => new Literal('min_class_write + 1')])
            ->where('min_class_write >= ?', $value)
            ->execute();

        $fluent->update('forums')
            ->set(['min_class_create' => new Literal('min_class_create + 1')])
            ->where('min_class_create >= ?', $value)
            ->execute();

        $fluent->update('forum_config')
            ->set(['min_delete_view_class' => new Literal('min_delete_view_class + 1')])
            ->where('min_delete_view_class >= ?', $value)
            ->execute();
    } else {
        $fluent->update('forums')
            ->set(['min_class_read' => new Literal('min_class_read - 1')])
            ->where('min_class_read >= ?', $value)
            ->where('min_class_read > 0')
            ->execute();

        $fluent->update('forums')
            ->set(['min_class_write' => new Literal('min_class_write - 1')])
            ->where('min_class_write >= ?', $value)
            ->where('min_class_write>0')
            ->execute();

        $fluent->update('forums')
            ->set(['min_class_create' => new Literal('min_class_create - 1')])
            ->where('min_class_create >= ?', $value)
            ->where('min_class_create>0')
            ->execute();

        $fluent->update('forum_config')
            ->set(['min_delete_view_class' => new Literal('min_delete_view_class - 1')])
            ->where('min_delete_view_class >= ?', $value)
            ->where('min_delete_view_class>0')
            ->execute();
    }
}

$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    $old_max = 0;
    $cache->delete('staff_classes_');
    if ($mode === 'edit') {
        $edited = false;
        if ($_POST['UC_MAX'] > UC_MAX || $_POST['UC_STAFF'] > UC_MAX || $_POST['UC_MIN'] > UC_MAX || $_POST['UC_MAX'] < UC_MIN || $_POST['UC_STAFF'] < UC_MIN || $_POST['UC_MIN'] < UC_MIN || $_POST['UC_MAX'] < $_POST['UC_MIN'] || $_POST['UC_MAX'] < $_POST['UC_STAFF'] || $_POST['UC_STAFF'] < $_POST['UC_MIN']) {
            stderr('Error', 'Invalid Class Configuration UC_MAX|UC_STAFF|UC_MIN');
        }
        if (!empty($class_config)) {
            foreach ($class_config as $current_name => $value) {
                $current_value = $value['value'];
                $current_classname = strtoupper($value['classname']);
                $current_classcolor = strtoupper($value['classcolor']);
                $current_classcolor = str_replace('#', '', "$current_classcolor");
                $current_classpic = $value['classpic'];
                $post_data = $_POST[$current_name];
                $value = trim($post_data[0]);
                $classname = !empty($post_data[1]) ? strtoupper($post_data[1]) : '';
                $classcolor = !empty($post_data[2]) ? $post_data[2] : '';
                $classcolor = str_replace('#', '', "$classcolor");
                $classpic = !empty($post_data[3]) ? $post_data[3] : '';
                if (isset($_POST[$current_name][0]) && (($value != $current_value) || ($classname != $current_classname) || ($classcolor != $current_classcolor) || ($classpic != $current_classpic))) {
                    $set = [
                        'value' => is_array($value) ? implode('|', $value) : $value,
                        'classname' => is_array($classname) ? implode('|', $classname) : $classname,
                        'classcolor' => is_array($classcolor) ? implode('|', $classcolor) : $classcolor,
                        'classpic' => is_array($classpic) ? implode('|', $classpic) : $classpic,
                    ];
                    $fluent->update('class_config')
                        ->set($set)
                        ->where('template = ?', $style)
                        ->where('name = ?', $current_name)
                        ->execute();

                    write_class_files($style);
                    $edited = true;
                }
            }
        }
        if ($edited) {
            $session->set('is-success', "{$lang['classcfg_success_save']}");
        } else {
            $session->set('is-warning', $lang['classcfg_error_query1']);
        }
        unset($_POST);
    } elseif ($mode === 'add') {
        if (!empty($_POST['name']) && isset($_POST['value']) && !empty($_POST['cname']) && !empty($_POST['color'])) {
            if (isset($_POST['name'])) {
                $name = htmlsafechars($_POST['name']);
            } else {
                stderr($lang['classcfg_error'], $lang['classcfg_error_class_name']);
            }
            if (isset($_POST['value'])) {
                $value = (int) $_POST['value'];
            } else {
                stderr($lang['classcfg_error'], $lang['classcfg_error_class_value']);
            }
            if (isset($_POST['cname'])) {
                $r_name = htmlsafechars($_POST['cname']);
            } else {
                stderr($lang['classcfg_error'], $lang['classcfg_error_class_value']);
            }
            $color = isset($_POST['color']) ? htmlsafechars($_POST['color']) : '';
            $color = str_replace('#', '', "$color");
            $pic = isset($_POST['pic']) ? htmlsafechars($_POST['pic']) : '';
            $res = $fluent->from('class_config')
                ->where("name = 'UC_MAX'");
            foreach ($res as $arr) {
                $update = [
                    'value' => $arr['value'] + 1,
                ];
                $fluent->update('class_config')
                    ->set($update)
                    ->where("name = 'UC_MAX")
                    ->execute();
            }

            $res = $fluent->from('class_config')
                ->where("name = 'UC_STAFF'");
            foreach ($res as $arr) {
                foreach ($res as $arr) {
                    if ($value <= $arr['value']) {
                        $update = [
                            'value' => $arr['value'] + 1,
                        ];
                        $fluent->update('class_config')
                            ->set($update)
                            ->where("name = 'UC_STAFF")
                            ->execute();
                    }
                }
            }
            $i = $old_max;
            while ($i >= $value) {
                sql_query("UPDATE class_config SET value = value +1 where value = $i AND name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX')") or sqlerr(__FILE__, __LINE__);
                --$i;
            }

            if ($value > UC_MAX) {
                sql_query("UPDATE users SET class = class +1 where class = $old_max") or sqlerr(__FILE__, __LINE__);
            } else {
                $i = $old_max;
                while ($i >= $value) {
                    sql_query("UPDATE users SET class = class + 1 where class = $i") or sqlerr(__FILE__, __LINE__);
                    sql_query("UPDATE staffpanel SET av_class = av_class + 1 where av_class = $i") or sqlerr(__FILE__, __LINE__);
                    --$i;
                }
            }
            $stylesheets = $fluent->from('stylesheets')
                ->select(null)
                ->select('id');

            $class_id = false;
            foreach ($stylesheets as $stylesheet) {
                $values = [
                    'name' => $name,
                    'value' => $value,
                    'classname' => $r_name,
                    'classcolor' => $color,
                    'classpic' => $pic,
                    'template' => $stylesheet['id'],
                ];
                $class_id = $fluent->insertInto('class_config')
                    ->values($values)
                    ->execute();

                write_class_files($stylesheet['id']);
            }
            if ($class_id) {
                $session->set('is-success', "{$lang['classcfg_success_save']}");
            } else {
                $session->set('is-warning', $lang['classcfg_error_query2']);
            }
            update_forum_classes($value, 'increment');
            unset($_POST);
        }
    } elseif ($mode === 'remove') {
        $value = (int) $_POST['class'];
        $deleted = $fluent->deleteFrom('class_config')
            ->where('value = ?', $value)
            ->where('name != ?', 'UC_MIN')
            ->where('name != ?', 'UC_MAX')
            ->where('name != ?', 'UC_STAFF')
            ->execute();
        if ($deleted) {
            $max = $fluent->from('class_config')
                ->select(null)
                ->select('value')
                ->where('name = ?', 'UC_MAX')
                ->fetch('value');
            $set = [
                'value' => new Literal('value - 1'),
            ];
            $result = $fluent->update('class_config')
                ->set($set)
                ->where('name = ?', 'UC_MAX')
                ->execute();
            $fluent->update('class_config')
                ->set($set)
                ->where('name = ?', 'UC_STAFF')
                ->execute();
            $fluent->update('class_config')
                ->set($set)
                ->where('value > ?', $value)
                ->where('value <= ?', $max)
                ->where('name != ?', 'UC_MIN')
                ->where('name != ?', 'UC_MAX')
                ->where('name != ?', 'UC_STAFF')
                ->execute();
            $set = [
                'class' => new Literal('class - 1'),
            ];
            $fluent->update('users')
                ->set($set)
                ->where('class > ?', $value)
                ->where('class <= ?', $max)
                ->execute();
            $set = [
                'av_class' => new Literal('av_class - 1'),
            ];
            $fluent->update('staffpanel')
                ->set($set)
                ->where('av_class > ?', $value)
                ->where('av_class <= ?', $max)
                ->execute();

            $stylesheets = $fluent->from('stylesheets')
                ->select(null)
                ->select('id');
            foreach ($stylesheets as $stylesheet) {
                write_class_files($stylesheet['id']);
            }
            update_forum_classes($value, 'decrement');
            $session->set('is-success', "{$lang['classcfg_success_reset']}");
        } else {
            $session->set('is-warning', $lang['classcfg_error_query2']);
        }
        unset($_POST);
    }
    run_uglify();
    $cache->flushDB();
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=class_config');
    die();
}
$HTMLOUT .= "
        <h1 class='has-text-centered top20'>{$lang['classcfg_class_settings']} for Template $style</h1>
        <br>
        <h2 class='has-text-centered top20'>{$lang['classcfg_class_config']}</h2>
        <form name='edit' action='staffpanel.php?tool=class_config' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <table class='table table-bordered table-stiped'>
                <thead>
                    <tr>
                        <th>{$lang['classcfg_class_name']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_value']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_refname']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_color']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_pic']}</th>
                    </tr>
                </thead>
                <tbody>";

$classes = $fluent->from('class_config')
    ->where('template = ?', $style)
    ->orderBy('value');

$base = [
    'UC_MIN',
    'UC_MAX',
    'UC_STAFF',
];
$primary_classes = [];
foreach ($classes as $class) {
    if (!in_array($class['name'], $base)) {
        $primary_classes[] = $class;
    } else {
        $base_classes[] = $class;
    }
}

if (!empty($primary_classes)) {
    foreach ($primary_classes as $arr) {
        $cname = str_replace('uc_', '', strtolower($arr['name'])) . '_bk';
        $HTMLOUT .= "
                        <tr class='{$cname}'>
                            <td class='has-text-black has-text-weight-bold w-20'>" . htmlsafechars($arr['name']) . "</td>
                            <td class='has-text-centered w-10'>
                                <input type='number' name='" . htmlsafechars($arr['name']) . "[]' value='" . (int) $arr['value'] . "' class='has-text-centered w-100' readonly>
                            </td>
                            <td class='has-text-centered w-20'>
                                <input type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . htmlsafechars($arr['classname']) . "' class='w-100'>
                            </td>
                            <td class='has-text-centered w-20'>
                                <input type='color' name='" . htmlsafechars($arr['name']) . "[]' value='#" . htmlsafechars($arr['classcolor']) . "' class='w-100 is-paddingless'>
                            </td>
                            <td class='has-text-centered w-20'>
                                <input type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . htmlsafechars($arr['classpic']) . "' class='w-100'>
                            </td>
                        </tr>";
    }
}

$HTMLOUT .= "
                        <tr>
                            <td colspan='5'
                        </tr>";
if (!empty($base_classes)) {
    foreach ($base_classes as $arr) {
        $cname = str_replace('uc_', '', strtolower($primary_classes[$arr['value']]['name'])) . '_bk';
        $HTMLOUT .= "
                        <tr class='{$cname}'>
                            <td colspan='2' class='has-text-black has-text-weight-bold'>" . htmlsafechars($arr['name']) . "</td>
                            <td colspan='3'>
                                <input class='w-100' type='number' min='0' max='" . UC_MAX . "' name='" . htmlsafechars($arr['name']) . "' value='" . (int) $arr['value'] . "'>
                            </td>
                        </tr>";
    }
}
$HTMLOUT .= "
                    <tr>
                        <td colspan='5'>
                            <div class='has-text-centered'>
                                <input type='hidden' name='mode' value='edit'>
                                <input type='submit' class='button is-small' value='{$lang['classcfg_class_apply']}'>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>";

$HTMLOUT .= "
        <h2 class='has-text-centered top20'>{$lang['classcfg_class_del']}</h2>
        <form name='add' action='staffpanel.php?tool=class_config' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <table class='table table-bordered table-stiped'>
                <tr>
                    <td colspan='5'>
                        <div class='level-center-center'>
                            <select name='class'>";
foreach ($classes as $class) {
    if (!in_array($class['name'], [
        'UC_MIN',
        'UC_STAFF',
        'UC_MAX',
    ])) {
        $HTMLOUT .= "
                                <option value='{$class['value']}'>{$class['name']}</option>";
    }
}

$HTMLOUT .= "
                            </select>
                            <input type='hidden' name='mode' value='remove'>
                            <input type='submit' class='button is-small left10' value='{$lang['classcfg_class_remove']}'>
                        </div>
                    </td>
                </tr>
            </table>
        </form>";

$HTMLOUT .= "
        <h2 class='has-text-centered top20'>{$lang['classcfg_class_add']}</h2>
        <form name='add' action='staffpanel.php?tool=class_config' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
            <table class='table table-bordered table-stiped'>
                <thead>
                    <tr>
                        <th>{$lang['classcfg_class_name']}</th>
                        <th>{$lang['classcfg_class_level']}</th>
                        <th>{$lang['classcfg_class_refname']}</th>
                        <th>{$lang['classcfg_class_color']}</th>
                        <th>{$lang['classcfg_class_pic']}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input class='w-100 tooltipper' type='text' name='name' value='' placeholder='UC_OWNER' title='All class names must begin with UC_'></td>
                        <td><input class='w-100' type='text' name='value' value=''></td>
                        <td><input class='w-100 tooltipper' type='text' name='cname' value='' placeholder='OWNER' title='All class reference names must be same as class name without UC_'></td>
                        <td><input class='w-100 is-paddingless' type='color' name='color' value='#e6fb04'></td>
                        <td><input class='w-100' type='text' name='pic' value=''></td>
                    </tr>
                    <tr>
                        <td colspan='5'>
                            <div class='has-text-centered'>
                                <input type='hidden' name='mode' value='add'>
                                <input type='submit' class='button is-small' value='{$lang['classcfg_add_new']}'>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>";

echo stdhead($lang['classcfg_stdhead']) . wrapper($HTMLOUT) . stdfoot();
