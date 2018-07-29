<?php

require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'html_functions.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $CURUSER, $site_config, $lang, $cache, $session;

$lang = array_merge($lang, load_language('ad_class_config'));

if (!in_array($CURUSER['id'], $site_config['is_staff']['allowed'])) {
    stderr($lang['classcfg_error'], $lang['classcfg_denied']);
}
$pconf = sql_query('SELECT * FROM class_config ORDER BY value ASC') or sqlerr(__FILE__, __LINE__);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    $cache->delete('is_staffs_');
    if ($mode === 'edit') {
        foreach ($class_config as $c_name => $value) {
            $c_value = $value['value']; // $key is like UC_USER etc....
            $c_classname = strtoupper($value['classname']);
            $c_classcolor = strtoupper($value['classcolor']);
            $c_classcolor = str_replace('#', '', "$c_classcolor");
            $c_classpic = $value['classpic'];
            $post_data = $_POST[$c_name];
            $value = $post_data[0];
            $classname = !empty($post_data[1]) ? strtoupper($post_data[1]) : '';
            $classcolor = !empty($post_data[2]) ? $post_data[2] : '';
            $data[] = [
                'className'  => $classname,
                'classColor' => $classcolor,
            ];
            $classcolor = str_replace('#', '', "$classcolor");
            $classpic = !empty($post_data[3]) ? $post_data[3] : '';
            if (isset($_POST[$c_name][0]) && (($value != $c_value) || ($classname != $c_classname) || ($classcolor != $c_classcolor) || ($classpic != $c_classpic))) {
                $update[$c_name] = '(' . sqlesc($c_name) . ', ' . sqlesc(is_array($value) ? implode('|', $value) : $value) . ', ' . sqlesc(is_array($classname) ? implode('|', $classname) : $classname) . ', ' . sqlesc(is_array($classcolor) ? implode('|', $classcolor) : $classcolor) . ', ' . sqlesc(is_array($classpic) ? implode('|', $classpic) : $classpic) . ')';
            }
        }
        write_css($data);
        if (sql_query('INSERT INTO class_config(name, value, classname, classcolor, classpic) VALUES ' . implode(', ', $update) . ' ON DUPLICATE KEY UPDATE value = VALUES(value), classname = VALUES(classname), classcolor = VALUES(classcolor), classpic = VALUES(classpic)')) { // need to change strut
            $t = 'define(';
            $configfile = '<' . $lang['classcfg_file_created'] . date('M d Y H:i:s') . $lang['classcfg_user_cfg'];
            $res = sql_query('SELECT * FROM class_config ORDER BY value  ASC');
            $the_names = $the_colors = $the_images = '';
            while ($arr = mysqli_fetch_assoc($res)) {
                $configfile .= '' . $t . "'{$arr['name']}', {$arr['value']});\n";
            }
            unset($arr);
            $res = sql_query("SELECT * FROM class_config WHERE name NOT IN ('UC_MIN', 'UC_MAX') ORDER BY value ASC");
            $the_names = $the_colors = $the_images = '';
            $classes[] = 'var UC_MIN = 0;';
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($arr['name'] !== 'UC_STAFF') {
                    $the_names .= "{$arr['name']} => '{$arr['classname']}',";
                    $the_colors .= "{$arr['name']} => '{$arr['classcolor']}',";
                    $the_images .= "{$arr['name']} => " . '$site_config[' . "'pic_baseurl'" . ']' . " . 'class/{$arr['classpic']}',";
                    $js_classes[] = $arr['name'];
                }
                $val = $arr['value'];
                $classes[] = "var {$arr['name']} = {$arr['value']};";
            }
            $classes[] = "var UC_MAX = {$val};";
            file_put_contents(ROOT_DIR . 'chat/js/classes.js', implode("\n", $classes));
            write_classes($js_classes);
            $configfile .= get_cache_config_data($the_names, $the_colors, $the_images);
            file_put_contents(CACHE_DIR . 'class_config.php', $configfile);
            $session->set('is-success', "{$lang['classcfg_success_save']}\n\n{$lang['classcfg_success_uglify']}");
        } else {
            $session->set('is-warning', $lang['classcfg_error_query1']);
        }
        unset($_POST);
    }

    if ($mode === 'add') {
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
                        $cache->update_row('user' . $row2['id'], [
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
                        $cache->update_row('user' . $row2['id'], [
                            'class' => $row2['class'],
                        ], $site_config['expires']['user_cache']);
                    }
                }
            }
            if (sql_query('INSERT INTO class_config (name, value, classname, classcolor, classpic) VALUES (' . sqlesc($name) . ', ' . sqlesc($value) . ', ' . sqlesc($r_name) . ', ' . sqlesc($color) . ', ' . sqlesc($pic) . ')')) {
                $t = 'define(';
                $configfile = '<' . $lang['classcfg_file_created'] . date('M d Y H:i:s') . $lang['classcfg_user_cfg'];
                $res = sql_query('SELECT * FROM class_config ORDER BY value  ASC');
                $the_names = $the_colors = $the_images = '';
                while ($arr = mysqli_fetch_assoc($res)) {
                    $configfile .= '' . $t . "'{$arr['name']}', {$arr['value']});\n";
                }
                unset($arr);
                $res = sql_query("SELECT * FROM class_config WHERE name NOT IN ('UC_MIN', 'UC_MAX') ORDER BY value  ASC");
                $the_names = $the_colors = $the_images = '';
                while ($arr = mysqli_fetch_assoc($res)) {
                    if ($arr['name'] !== 'UC_STAFF') {
                        $the_names .= "{$arr['name']} => '{$arr['classname']}',";
                        $the_colors .= "{$arr['name']} => '{$arr['classcolor']}',";
                        $the_images .= "{$arr['name']} => " . '$site_config[' . "'pic_baseurl'" . ']' . ".'class/{$arr['classpic']}',";
                        $js_classes[] = $arr['name'];
                        $data[] = [
                            'className'  => $arr['classname'],
                            'classColor' => '#' . strtolower($arr['classcolor']),
                        ];
                    }
                    $classes[] = "var {$arr['name']} = {$arr['value']};";
                }
                write_css($data);
                file_put_contents(ROOT_DIR . 'chat/js/classes.js', implode("\n", $classes));
                write_classes($js_classes);
                $configfile .= get_cache_config_data($the_names, $the_colors, $the_images);
                file_put_contents(CACHE_DIR . 'class_config.php', $configfile);
                $session->set('is-success', "{$lang['classcfg_success_save']}\n\n{$lang['classcfg_success_uglify']}");
            } else {
                $session->set('is-warning', $lang['classcfg_error_query2']);
            }
            unset($_POST);
        }
    }

    if ($mode === 'remove') {
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
                $cache->update_row('user' . $row2['id'], [
                    'class' => $row2['class'],
                ], $site_config['expires']['user_cache']);
            }
        }
        if (sql_query('DELETE FROM class_config WHERE name = ' . sqlesc($name))) {
            $t = 'define(';
            $configfile = '<' . $lang['classcfg_file_created'] . date('M d Y H:i:s') . $lang['classcfg_user_cfg'];
            $res = sql_query('SELECT * FROM class_config ORDER BY value  ASC');
            $the_names = $the_colors = $the_images = '';
            while ($arr = mysqli_fetch_assoc($res)) {
                $configfile .= '' . $t . "'{$arr['name']}', {$arr['value']});\n";
            }
            unset($arr);
            $res = sql_query("SELECT * FROM class_config WHERE name NOT IN ('UC_MIN', 'UC_MAX') ORDER BY value  ASC");
            $the_names = $the_colors = $the_images = '';
            while ($arr = mysqli_fetch_assoc($res)) {
                if ($arr['name'] !== 'UC_STAFF') {
                    $the_names .= "{$arr['name']} => '{$arr['classname']}',";
                    $the_colors .= "{$arr['name']} => '{$arr['classcolor']}',";
                    $the_images .= "{$arr['name']} => " . '$site_config[' . "'pic_baseurl'" . ']' . ".'class/{$arr['classpic']}',";
                    $js_classes[] = $arr['name'];
                    $data[] = [
                        'className'  => $arr['classname'],
                        'classColor' => '#' . strtolower($arr['classcolor']),
                    ];
                }
                $classes[] = "var {$arr['name']} = {$arr['value']};";
            }
            write_css($data);
            file_put_contents(ROOT_DIR . 'chat/js/classes.js', implode("\n", $classes));
            write_classes($js_classes);
            $configfile .= get_cache_config_data($the_names, $the_colors, $the_images);
            file_put_contents(CACHE_DIR . 'class_config.php', $configfile);
            $session->set('is-success', "{$lang['classcfg_success_reset']}\n\n{$lang['classcfg_success_uglify']}");
        } else {
            $session->set('is-warning', $lang['classcfg_error_query2']);
        }
        unset($_POST);
    }
}
$HTMLOUT .= "
    <div class='container is-fluid portlet bordered'>
        <h3 class='has-text-centered top20'>{$lang['classcfg_class_settings']}</h3>
        <form name='edit' action='staffpanel.php?tool=class_config&amp;mode=edit' method='post'>
            <table class='table table-bordered table-stiped bottom20'>
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
$res = sql_query("SELECT * FROM class_config WHERE name NOT IN ('UC_MIN', 'UC_MAX', 'UC_STAFF') ORDER BY value  ASC");
while ($arr = mysqli_fetch_assoc($res)) {
    $cname = str_replace(' ', '_', strtolower($arr['classname'])) . '_bk';
    $HTMLOUT .= "
                    <tr class='{$cname}'>
                        <td class='has-text-white'>" . htmlsafechars($arr['name']) . "</td>
                        <td class='has-text-centered'>
                            <input type='text' name='" . htmlsafechars($arr['name']) . "[]' size='2' value='" . (int) $arr['value'] . " 'readonly='readonly' />
                        </td>
                        <td class='has-text-centered'>
                            <input class='w-100' type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . htmlsafechars($arr['classname']) . "' />
                        </td>
                        <td class='has-text-centered'>
                            <input class='w-100' type='text' name='" . htmlsafechars($arr['name']) . "[]' value='#" . htmlsafechars($arr['classcolor']) . "' />
                        </td>
                        <td class='has-text-centered'>
                            <input class='w-100' type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . htmlsafechars($arr['classpic']) . "' />
                        </td>
                        <td class='has-text-centered'>
                            <form name='remove' action='staffpanel.php?tool=class_config&amp;mode=remove' method='post'>
                                <input type='hidden' name='remove' value='" . htmlsafechars($arr['name']) . "' />
                                <input type='submit' class='button is-small' value='{$lang['classcfg_class_remove']}' />
                            </form>
                        </td>
                    </tr>";
}
$HTMLOUT .= '
                </tbody>
            </table>';

$HTMLOUT .= "
            <h3 class='has-text-centered top20'>{$lang['classcfg_class_security']}</h3>
            <table class='table table-bordered table-stiped bottom20'>
                <thead>
                    <tr>
                        <th>{$lang['classcfg_class_name']}</th>
                        <th>{$lang['classcfg_class_value']}</th>
                    </tr>
                </thead>
                <tbody>";
$res1 = sql_query("SELECT * FROM class_config WHERE name IN ('UC_MIN', 'UC_MAX', 'UC_STAFF') ORDER BY value  ASC");
while ($arr1 = mysqli_fetch_assoc($res1)) {
    $HTMLOUT .= '
                    <tr>
                        <td>' . htmlsafechars($arr1['name']) . "</td>
                        <td>
                            <input class='w-100' type='text' name='" . htmlsafechars($arr1['name']) . "[]' value='" . (int) $arr1['value'] . "' />
                        </td>
                    </tr>";
}
$HTMLOUT .= "
                    <tr>
                        <td colspan='2'>
                            <div class='has-text-centered'>
                                <input type='submit' class='button is-small' value='{$lang['classcfg_class_apply']}' />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>";

$HTMLOUT .= "
        <h3 class='has-text-centered top20'>{$lang['classcfg_class_add']}</h3>
        <form name='add' action='staffpanel.php?tool=class_config&amp;mode=add' method='post'>
            <table class='table table-bordered table-stiped bottom20'>
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
                        <td><input class='w-100 tooltipper' type='text' name='name' value='' placeholder='UC_OWNER' title='All class names must begin with UC_' /></td>
                        <td><input class='w-100' type='text' name='value' value='' /></td>
                        <td><input class='w-100 tooltipper' type='text' name='cname' value='' placeholder='OWNER' title='All class reference names must be same as class name without UC_' /></td>
                        <td><input class='w-100' type='text' name='color' value='#ff0000' /></td>
                        <td><input class='w-100' type='text' name='pic' value='' /></td>
                    </tr>
                    <tr>
                        <td colspan='5'>
                            <div class='has-text-centered'>
                                <input type='submit' class='button is-small' value='{$lang['classcfg_add_new']}' />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>";
echo stdhead($lang['classcfg_stdhead']) . $HTMLOUT . stdfoot();
