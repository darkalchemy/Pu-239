<?php

require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache, $session;

$lang = array_merge($lang, load_language('ad_class_config'));

$style = get_stylesheet();
if (!in_array($CURUSER['id'], $site_config['is_staff'])) {
    stderr($lang['classcfg_error'], $lang['classcfg_denied']);
}
$pconf = sql_query('SELECT * FROM class_config WHERE template = ' . sqlesc($style) . ' ORDER BY value') or sqlerr(__FILE__, __LINE__);
while ($ac = mysqli_fetch_assoc($pconf)) {
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
$mode = (isset($_GET['mode']) ? htmlsafechars($_GET['mode']) : '');
if (!in_array($mode, $possible_modes)) {
    stderr($lang['classcfg_error'], $lang['classcfg_error1']);
}

/**
 * @param int    $value
 * @param string $direction
 *
 * @throws \Envms\FluentPDO\Exception
 */
function update_forum_classes(int $value, string $direction)
{
    global $fluent, $cache;

    if ($direction === 'increment') {
        $fluent->update('forums')
               ->set(['min_class_read' => new Envms\FluentPDO\Literal('min_class_read + 1')])
               ->where('min_class_read>= ?', $value)
               ->execute();

        $fluent->update('forums')
               ->set(['min_class_write' => new Envms\FluentPDO\Literal('min_class_write + 1')])
               ->where('min_class_write>= ?', $value)
               ->execute();

        $fluent->update('forums')
               ->set(['min_class_create' => new Envms\FluentPDO\Literal('min_class_create + 1')])
               ->where('min_class_create>= ?', $value)
               ->execute();

        $fluent->update('forum_config')
               ->set(['min_delete_view_class' => new Envms\FluentPDO\Literal('min_delete_view_class + 1')])
               ->where('min_delete_view_class>= ?', $value)
               ->execute();
    } else {
        $fluent->update('forums')
               ->set(['min_class_read' => new Envms\FluentPDO\Literal('min_class_read - 1')])
               ->where('min_class_read>= ?', $value)
               ->where('min_class_read>0')
               ->execute();

        $fluent->update('forums')
               ->set(['min_class_write' => new Envms\FluentPDO\Literal('min_class_write - 1')])
               ->where('min_class_write>= ?', $value)
               ->where('min_class_write>0')
               ->execute();

        $fluent->update('forums')
               ->set(['min_class_create' => new Envms\FluentPDO\Literal('min_class_create - 1')])
               ->where('min_class_create>= ?', $value)
               ->where('min_class_create>0')
               ->execute();

        $fluent->update('forum_config')
               ->set(['min_delete_view_class' => new Envms\FluentPDO\Literal('min_delete_view_class - 1')])
               ->where('min_delete_view_class>= ?', $value)
               ->where('min_delete_view_class>0')
               ->execute();
    }

    $cache->delete('staff_forums_');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    $old_max = 0;
    $cache->delete('staff_classes_');
    if ($mode === 'edit') {
        $edited = false;
        if (!empty($class_config)) {
            foreach ($class_config as $current_name => $value) {
                $current_value = $value['value']; // $key is like UC_USER etc....
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
            $session->set('is-success', "{$lang['classcfg_success_save']}\n\n{$lang['classcfg_success_uglify']}");
        } else {
            $session->set('is-warning', $lang['classcfg_error_query1']);
        }
        $cache->deleteMulti([
            'class_config_' . $style,
            'badwords_',
        ]);
        unset($_POST);
    } elseif ($mode === 'add') {
        if (!empty($_POST['name']) && !empty($_POST['value']) && !empty($_POST['cname']) && !empty($_POST['color'])) {
            $name = isset($_POST['name']) ? htmlsafechars($_POST['name']) : stderr($lang['classcfg_error'], $lang['classcfg_error_class_name']);
            $value = isset($_POST['value']) ? (int) $_POST['value'] : stderr($lang['classcfg_error'], $lang['classcfg_error_class_value']);
            $r_name = isset($_POST['cname']) ? htmlsafechars($_POST['cname']) : stderr($lang['classcfg_error'], $lang['classcfg_error_class_value']);
            $color = isset($_POST['color']) ? htmlsafechars($_POST['color']) : '';
            $color = str_replace('#', '', "$color");
            $pic = isset($_POST['pic']) ? htmlsafechars($_POST['pic']) : '';

            $res = sql_query("SELECT * FROM class_config WHERE name IN ('UC_MAX') ");
            while ($arr = mysqli_fetch_array($res)) {
                $old_max = $arr['value'];
                $new_max = $arr['value'] + 1;
                sql_query("UPDATE class_config SET value = '$new_max' WHERE name = 'UC_MAX'");
            }
            $res = sql_query("SELECT * FROM class_config WHERE name = 'UC_STAFF'");
            while ($arr = mysqli_fetch_array($res)) {
                if ($value <= $arr['value']) {
                    $new_staff = $arr['value'] + 1;
                    sql_query("UPDATE class_config SET value = '$new_staff' WHERE name = 'UC_STAFF'");
                }
            }
            $i = $old_max;
            while ($i >= $value) {
                sql_query("UPDATE class_config SET value = value +1 where value = $i AND name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX')");
                --$i;
            }

            if ($value > UC_MAX) {
                sql_query("UPDATE users SET class = class +1 where class = $old_max");
                $result = sql_query('SELECT id, class FROM users');
                $result = sql_query('SELECT id, class FROM users');
                while ($row = mysqli_fetch_assoc($result)) {
                    $row1 = [];
                    $row1[] = $row;
                    foreach ($row1 as $row2) {
                        $cache->update_row('user_' . $row2['id'], [
                            'class' => $row2['class'],
                        ], $site_config['expires']['user_cache']);
                    }
                }
            } else {
                $i = $old_max;
                while ($i >= $value) {
                    sql_query("UPDATE users SET class = class + 1 where class = $i");
                    sql_query("UPDATE staffpanel SET av_class = av_class + 1 where av_class = $i");
                    --$i;
                }

                $result = sql_query('SELECT id, class FROM users');
                while ($row = mysqli_fetch_assoc($result)) {
                    $row1 = [];
                    $row1[] = $row;
                    foreach ($row1 as $row2) {
                        $cache->update_row('user_' . $row2['id'], [
                            'class' => $row2['class'],
                        ], $site_config['expires']['user_cache']);
                    }
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
                $session->set('is-success', "{$lang['classcfg_success_save']}\n\n{$lang['classcfg_success_uglify']}");
            } else {
                $session->set('is-warning', $lang['classcfg_error_query2']);
            }
            unset($_POST);
            $cache->deleteMulti([
                'class_config_' . $style,
                'badwords_',
            ]);
            update_forum_classes($value, 'increment');
            $cache->delete('is_staff_');
        }
    } elseif ($mode === 'remove') {
        $name = isset($_POST['remove']) ? htmlsafechars($_POST['remove']) : stderr($lang['classcfg_error'], $lang['classcfg_error_required']);
        $res = sql_query("SELECT value from class_config WHERE name = '$name' ");
        while ($arr = mysqli_fetch_array($res)) {
            $value = $arr['value'];
        }
        $res = sql_query("SELECT * FROM class_config WHERE name IN ('UC_MAX') ");
        while ($arr = mysqli_fetch_array($res)) {
            $old_max = $arr['value'];
            $new_max = $arr['value'] - 1;
            sql_query("UPDATE class_config SET value = '$new_max' WHERE name = 'UC_MAX'");
        }
        $res = sql_query("SELECT * FROM class_config WHERE name = 'UC_STAFF'");
        while ($arr = mysqli_fetch_array($res)) {
            if ($value <= $arr['value']) {
                $new_staff = $arr['value'] - 1;
                sql_query("UPDATE class_config SET value = '$new_staff' WHERE name = 'UC_STAFF'");
            }
        }
        $i = $value;
        while ($i <= $old_max) {
            sql_query("UPDATE class_config SET value = value -1 where value = $i AND name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX')");
            ++$i;
        }
        $i = $value;
        while ($i <= $old_max) {
            sql_query("UPDATE users SET class = class -1 where class = $i");
            sql_query("UPDATE staffpanel SET av_class = av_class -1 where av_class = $i");
            ++$i;
        }
        $result = sql_query('SELECT id, class FROM users');
        while ($row = mysqli_fetch_assoc($result)) {
            $row1 = [];
            $row1[] = $row;
            foreach ($row1 as $row2) {
                $cache->update_row('user_' . $row2['id'], [
                    'class' => $row2['class'],
                ], $site_config['expires']['user_cache']);
            }
        }
        $deleted = $fluent->deleteFrom('class_config')
                          ->where('name = ?', $name)
                          ->execute();

        $stylesheets = $fluent->from('stylesheets')
                              ->select(null)
                              ->select('id');

        foreach ($stylesheets as $stylesheet) {
            write_class_files($stylesheet['id']);
        }

        if ($deleted) {
            $session->set('is-success', "{$lang['classcfg_success_reset']}\n\n{$lang['classcfg_success_uglify']}");
        } else {
            $session->set('is-warning', $lang['classcfg_error_query2']);
        }
        $cache->deleteMulti([
            'class_config_' . $style,
            'badwords_',
        ]);
        update_forum_classes($value, 'decrement');
        unset($_POST);
    }
}
$HTMLOUT .= "
        <h1 class='has-text-centered top20'>{$lang['classcfg_class_settings']} for Template $style</h1>
        <form name='edit' action='staffpanel.php?tool=class_config&amp;mode=edit' method='post' accept-charset='utf-8'>
            <table class='table table-bordered table-stiped'>
                <thead>
                    <tr>
                        <th>{$lang['classcfg_class_name']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_value']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_refname']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_color']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_pic']}</th>
                        <th class='has-text-centered'>{$lang['classcfg_class_del']}</th>
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
foreach ($classes as $class) {
    if (!in_array($class['name'], $base)) {
        $primary_classes[] = $class;
    } else {
        $base_classes[] = $class;
    }
}

if (!empty($primary_classes)) {
    foreach ($primary_classes as $arr) {
        $cname = str_replace(' ', '_', strtolower($arr['classname'])) . '_bk';
        $HTMLOUT .= "
                        <tr class='{$cname}'>
                            <td class='has-text-black has-text-weight-bold'>" . htmlsafechars($arr['name']) . "</td>
                            <td class='has-text-centered'>
                                <input type='text' name='" . htmlsafechars($arr['name']) . "[]' size='2' value='" . (int) $arr['value'] . " 'readonly>
                            </td>
                            <td class='has-text-centered'>
                                <input class='w-100' type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . htmlsafechars($arr['classname']) . "'>
                            </td>
                            <td class='has-text-centered'>
                                <input class='w-100' type='text' name='" . htmlsafechars($arr['name']) . "[]' value='#" . htmlsafechars($arr['classcolor']) . "'>
                            </td>
                            <td class='has-text-centered'>
                                <input class='w-100' type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . htmlsafechars($arr['classpic']) . "'>
                            </td>
                            <td class='has-text-centered'>
                                <form name='remove' action='staffpanel.php?tool=class_config&amp;mode=remove' method='post' accept-charset='utf-8'>
                                    <input type='hidden' name='remove' value='" . htmlsafechars($arr['name']) . "'>
                                    <input type='submit' class='button is-small' value='{$lang['classcfg_class_remove']}'>
                                </form>
                            </td>
                        </tr>";
    }
}
$HTMLOUT .= '
                </tbody>
            </table>';

$HTMLOUT .= "
            <h2 class='has-text-centered top20'>{$lang['classcfg_class_security']}</h2>
            <table class='table table-bordered table-stiped'>
                <thead>
                    <tr>
                        <th>{$lang['classcfg_class_name']}</th>
                        <th>{$lang['classcfg_class_value']}</th>
                    </tr>
                </thead>
                <tbody>";

if (!empty($base_classes)) {
    foreach ($base_classes as $arr1) {
        $HTMLOUT .= '
                        <tr>
                            <td>' . htmlsafechars($arr1['name']) . "</td>
                            <td>
                                <input class='w-100' type='text' name='" . htmlsafechars($arr1['name']) . "[]' value='" . (int) $arr1['value'] . "'>
                            </td>
                        </tr>";
    }
}
$HTMLOUT .= "
                    <tr>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' class='button is-small' value='{$lang['classcfg_class_apply']}'>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>";

$HTMLOUT .= "
        <h2 class='has-text-centered top20'>{$lang['classcfg_class_add']}</h2>
        <form name='add' action='staffpanel.php?tool=class_config&amp;mode=add' method='post' accept-charset='utf-8'>
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
                        <td><input class='w-100' type='text' name='color' value='#ff0000'></td>
                        <td><input class='w-100' type='text' name='pic' value=''></td>
                    </tr>
                    <tr>
                        <td colspan='5'>
                            <div class='has-text-centered'>
                                <input type='submit' class='button is-small' value='{$lang['classcfg_add_new']}'>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>";
echo stdhead($lang['classcfg_stdhead']) . wrapper($HTMLOUT) . stdfoot();
