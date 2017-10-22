<?php
require_once realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('staff_panel'));

$staff_classes1['name'] = '';
$staff = sqlesc(UC_STAFF);
if (($staff_classes = $mc1->get_value('is_staffs_')) === false) {
    $res = sql_query("SELECT value FROM class_config WHERE name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX') AND value >= '$staff' ORDER BY value ASC");
    $staff_classes = [];
    while (($row = mysqli_fetch_assoc($res))) {
        $staff_classes[] = $row['value'];
    }
    $mc1->cache_value('is_staffs_', $staff_classes, 0);
}

if (!$CURUSER) {
    stderr($lang['spanel_error'], $lang['spanel_access_denied']);
}

if ($site_config['staffpanel_online'] == 0) {
    stderr($lang['spanel_information'], $lang['spanel_panel_cur_offline']);
}
require_once CLASS_DIR . 'class_check.php';
class_check(UC_STAFF);
$action = (isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : null));
$id = (isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : null));
$class_color = (function_exists('get_user_class_color') ? true : false);
$tool = (isset($_GET['tool']) ? $_GET['tool'] : (isset($_POST['tool']) ? $_POST['tool'] : null));
$tool = isset($_GET['tool']) ? $_GET['tool'] : '';

$staff_tools['modtask'] = 'modtask';
$staff_tools['iphistory'] = 'iphistory';
$staff_tools['ipsearch'] = 'ipsearch';
$staff_tools['shit_list'] = 'shit_list';

$sql = sql_query('SELECT file_name FROM staffpanel') or sqlerr(__FILE__, __LINE__);
while ($list = mysqli_fetch_assoc($sql)) {
    $item = str_replace(array('staffpanel.php?tool=', '.php', '&mode=news', '&action=app'), '', $list['file_name']);
    $staff_tools[$item] = $item;
}

if (in_array($tool, $staff_tools) and file_exists(ADMIN_DIR . $staff_tools[$tool] . '.php')) {
    require_once ADMIN_DIR . $staff_tools[$tool] . '.php';
} else {
    if ($action == 'delete' && is_valid_id($id) && $CURUSER['class'] == UC_MAX) {
        $sure = ((isset($_GET['sure']) ? $_GET['sure'] : '') == 'yes');
        $res = sql_query('SELECT navbar, added_by, av_class' . (!$sure || $CURUSER['class'] <= UC_MAX ? ', page_name' : '') . ' FROM staffpanel WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if ($CURUSER['class'] < $arr['av_class']) {
            stderr($lang['spanel_error'], $lang['spanel_you_not_allow_del_page']);
        }
        if (!$sure) {
            stderr($lang['spanel_sanity_check'], $lang['spanel_are_you_sure_del'] . ': "' . htmlsafechars($arr['page_name']) . '"? ' . $lang['spanel_click'] . ' <a href="' . $_SERVER['PHP_SELF'] . '?action=' . $action . '&amp;id=' . $id . '&amp;sure=yes">' . $lang['spanel_here'] . '</a> ' . $lang['spanel_to_del_it_or'] . ' <a href="' . $_SERVER['PHP_SELF'] . '">' . $lang['spanel_here'] . '</a> ' . $lang['spanel_to_go_back'] . '.');
        }
        $mc1->delete_value('is_staffs_');
        sql_query('DELETE FROM staffpanel WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $mc1->delete_value('av_class_');
        $mc1->delete_value('staff_panels_6');
        $mc1->delete_value('staff_panels_5');
        $mc1->delete_value('staff_panels_4');
        if (mysqli_affected_rows($GLOBALS['___mysqli_ston'])) {
            if ($CURUSER['class'] <= UC_MAX) {
                write_log($lang['spanel_page'] . ' "' . htmlsafechars($arr['page_name']) . '"(' . ($class_color ? '[color="#' . get_user_class_color($arr['av_class']) . '"]' : '') . get_user_class_name($arr['av_class']) . ($class_color ? '[/color]' : '') . ') ' . $lang['spanel_was_del_sp_by'] . ' [url="' . $site_config['baseurl'] . '/userdetails.php?id=' . (int)$CURUSER['id'] . '"]' . $CURUSER['username'] . '[/url](' . ($class_color ? '[color="#' . get_user_class_color($CURUSER['class']) . '"]' : '') . get_user_class_name($CURUSER['class']) . ($class_color ? '[/color]' : '') . ')');
            }
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            stderr($lang['spanel_error'], $lang['spanel_db_error_msg']);
        }
    } elseif (($action == 'add' && $CURUSER['class'] == UC_MAX) || ($action == 'edit' && is_valid_id($id) && $CURUSER['class'] == UC_MAX)) {
        $names = [
            'page_name',
            'file_name',
            'description',
            'type',
            'av_class',
            'navbar',
        ];
        if ($action == 'edit') {
            $res = sql_query('SELECT ' . implode(', ', $names) . ' FROM staffpanel WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);
        }
        foreach ($names as $name) {
            $$name = (isset($_POST[$name]) ? $_POST[$name] : ($action == 'edit' ? $arr[$name] : ''));
        }
        if ($action == 'edit' && $CURUSER['class'] < $av_class) {
            stderr($lang['spanel_error'], $lang['spanel_cant_edit_this_pg']);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $errors = [];
            if (empty($page_name)) {
                $errors[] = $lang['spanel_the_pg_name'] . ' ' . $lang['spanel_cannot_be_empty'] . '.';
            }
            if (empty($file_name)) {
                $errors[] = $lang['spanel_the_filename'] . ' ' . $lang['spanel_cannot_be_empty'] . '.';
            }
            if (empty($description)) {
                $errors[] = $lang['spanel_the_descr'] . ' ' . $lang['spanel_cannot_be_empty'] . '.';
            }
            if (!isset($navbar)) {
                $errors[] = 'Show in Navbar ' . $lang['spanel_cannot_be_empty'] . '.';
            }
            if (!in_array((int)$av_class, $staff_classes)) {
                $errors[] = $lang['spanel_selected_class_not_valid'];
            }
            if (!is_file($file_name . '.php') && !empty($file_name) && !preg_match('/.php/', $file_name)) {
                $errors[] = $lang['spanel_inexistent_php_file'];
            }
            if (strlen($page_name) < 4 && !empty($page_name)) {
                $errors[] = $lang['spanel_the_pg_name'] . ' ' . $lang['spanel_is_too_short_min_4'] . '.';
            }
            if (strlen($page_name) > 80) {
                $errors[] = $lang['spanel_the_pg_name'] . ' ' . $lang['spanel_is_too_long'] . ' (' . $lang['spanel_max_80'] . ').';
            }
            if (strlen($file_name) > 80) {
                $errors[] = $lang['spanel_the_filename'] . ' ' . $lang['spanel_is_too_long'] . ' (' . $lang['spanel_max_80'] . ').';
            }
            if (strlen($description) > 100) {
                $errors[] = $lang['spanel_the_descr'] . ' ' . $lang['spanel_is_too_long'] . ' (' . $lang['spanel_max_100'] . ').';
            }
            if (empty($errors)) {
                if ($action == 'add') {
                    $res = sql_query('INSERT INTO staffpanel (page_name, file_name, description, type, av_class, added_by, added, navbar)
                                      VALUES (' . implode(', ', array_map('sqlesc', [
                            $page_name,
                            $file_name,
                            $description,
                            $type,
                            (int)$av_class,
                            (int)$CURUSER['id'],
                            TIME_NOW,
                            $navbar,
                        ])) . ')');
                    $mc1->delete_value('is_staffs_');
                    $mc1->delete_value('av_class_');
                    $mc1->delete_value('staff_panels_6');
                    $mc1->delete_value('staff_panels_5');
                    $mc1->delete_value('staff_panels_4');
                    if (!$res) {
                        if (((is_object($GLOBALS['___mysqli_ston'])) ? mysqli_errno($GLOBALS['___mysqli_ston']) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
                            $errors[] = $lang['spanel_this_fname_sub'];
                        } else {
                            $errors[] = $lang['spanel_db_error_msg'];
                        }
                    }
                } else {
                    $res = sql_query('UPDATE staffpanel SET navbar = ' . sqlesc($navbar) . ', page_name = ' . sqlesc($page_name) . ', file_name = ' . sqlesc($file_name) . ', description = ' . sqlesc($description) . ', type = ' . sqlesc($type) . ', av_class = ' . sqlesc((int)$av_class) . ' WHERE id = ' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
                    $mc1->delete_value('av_class_');
                    $mc1->delete_value('staff_panels_6');
                    $mc1->delete_value('staff_panels_5');
                    $mc1->delete_value('staff_panels_4');
                    if (!$res) {
                        $errors[] = $lang['spanel_db_error_msg'];
                    }
                }
                if (empty($errors)) {
                    if ($CURUSER['class'] <= UC_MAX) {
                        write_log($lang['spanel_page'] . ' "' . $page_name . '"(' . ($class_color ? '[color="#' . get_user_class_color($av_class) . '"]' : '') . get_user_class_name($av_class) . ($class_color ? '[/color]' : '') . ') ' . $lang['spanel_in_the_sp_was'] . ' ' . ($action == 'add' ? 'added' : 'edited') . ' by [url="' . $site_config['baseurl'] . '/userdetails.php?id=' . $CURUSER['id'] . '">' . $CURUSER['username'] . '[/url](' . ($class_color ? '[color="#' . get_user_class_color($CURUSER['class']) . '"]' : '') . get_user_class_name($CURUSER['class']) . ($class_color ? '[/color]' : '') . ')');
                    }
                    setSessionVar('success', "'{$page_name}' " . ucwords($action) . "ed Successfully");
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                }
            }
        }
        if (!empty($errors)) {
            $HTMLOUT .= stdmsg($lang['spanel_there'] . ' ' . (count($errors) > 1 ? 'are' : 'is') . ' ' . count($errors) . ' error' . (count($errors) > 1 ? 's' : '') . ' ' . $lang['spanel_in_the_form'] . '.', '<b>' . implode('<br>', $errors) . '</b>');
            $HTMLOUT .= '<br>';
        }
        $HTMLOUT .= "<form method='post' action='{$_SERVER['PHP_SELF']}'>
    <input type='hidden' name='action' value='{$action}' />";
        if ($action == 'edit') {
            $HTMLOUT .= "<input type='hidden' name='id' value='{$id}' />";
        }
        $HTMLOUT .= "
    <div class='container-fluid portlet'>
        <table class='table table-bordered table-striped top20'>
            <thead>
                <tr>
                    <th colspan='2'>
                        " . ($action == 'edit' ? $lang['spanel_edit'] . ' "' . $page_name . '"' : $lang['spanel_add_a_new']) . ' Staffpage' . "
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class='rowhead'>
                        {$lang['spanel_pg_name']}
                    </td>
                    <td>
                        <input type='text' class='w-100' name='page_name' value='{$page_name}' />
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['spanel_filename']}
                    </td>
                    <td>
                        <input type='text' class='w-100' name='file_name' value='{$file_name}' />
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['spanel_description']}
                    </td>
                    <td>
                        <input type='text' class='w-100' name='description' value='{$description}' />
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        Show in Navbar
                    </td>
                    <td>
                        <input name='navbar' value='1' type='radio'" . ($navbar == '1s' ? " checked" : '') . " /><span class='left5'>Yes</span><br>
                        <input name='navbar' value='0' type='radio'" . ($navbar == '0' ? " checked" : '') . " /><span class='left5'>No</span>
                    </td>
                </tr>";

        $types = [
            'user',
            'settings',
            'stats',
            'other',
        ];

        $HTMLOUT .= "
            <tr>
                <td class='rowhead'>{$lang['spanel_type_of_tool']}</td>
                <td>
                    <select name='type'>";
        foreach ($types as $types) {
            $HTMLOUT .= '
                        <option value="' . $types . '"' . ($types == $type ? ' selected' : '') . '>' . ucfirst($types) . '</option>';
        }
        $HTMLOUT .= "
                    </select>
                </td>
            </tr>
            <tr>
                <td class='rowhead'>
                    <span>{$lang['spanel_available_for']}</span>
                    </td>
                <td>
                    <select name='av_class'>";
        $maxclass = UC_MAX;
        for ($class = UC_STAFF; $class <= $maxclass; ++$class) {
            $HTMLOUT .= '
                        <option value="' . $class . '"' . ($class == $av_class ? ' selected' : '') . '>' . get_user_class_name($class) . '</option>';
        }
        $HTMLOUT .= '
                    </select>
                </td>';

        $HTMLOUT .= "
            </tr>
        </tbody>
    </table>

    <div class='flex flex-justify-center top20'>
            <input type='submit' class='btn' value='{$lang['spanel_submit']}' />
        </form>
        <form method='post' action='{$_SERVER['PHP_SELF']}'>
            <input type='submit' class='btn' value='{$lang['spanel_cancel']}' />
        </form>
    </div>
    </div>";
        echo stdhead($lang['spanel_header'] . ' :: ' . ($action == 'edit' ? '' . $lang['spanel_edit'] . ' "' . $page_name . '"' : $lang['spanel_add_a_new']) . ' page') . $HTMLOUT . stdfoot();
    } else {
        if ($CURUSER['class'] == UC_MAX) {
            $add_button = "
                <div class='text-center bottom20'>
                    <a href='{$site_config['baseurl']}/staffpanel.php?action=add' class='tooltipper btn' title='{$lang['spanel_add_a_new_pg']}'>{$lang['spanel_add_a_new_pg']}</a>
                </div>";
        }
        $res = sql_query('SELECT s.*, u.username 
                                FROM staffpanel AS s
                                LEFT JOIN users AS u ON u.id = s.added_by
                                WHERE s.av_class <= ' . sqlesc($CURUSER['class']) . '
                                ORDER BY s.av_class DESC, s.page_name ASC') or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            $db_classes = $unique_classes = $mysql_data = [];
            while ($arr = mysqli_fetch_assoc($res)) {
                $mysql_data[] = $arr;
            }
            foreach ($mysql_data as $key => $value) {
                $db_classes[$value['av_class']][] = $value['av_class'];
            }
            $i = 1;
            $HTMLOUT .= "
        <div class='container-fluid portlet'>
            <h1 class='text-center'>{$lang['spanel_welcome']} {$CURUSER['username']} {$lang['spanel_to_the']} {$lang['spanel_header']}!</h1>";

            foreach ($mysql_data as $key => $arr) {
                $end_table = (count($db_classes[$arr['av_class']]) == $i ? true : false);
                if (!in_array($arr['av_class'], $unique_classes)) {
                    $unique_classes[] = $arr['av_class'];
                    $HTMLOUT .= "
        <div class='bg-window round5'>
            <h2 class='text-center top20 text-shadow'>" . ($class_color ? '<font color="#' . get_user_class_color($arr['av_class']) . '">' : '') . get_user_class_name($arr['av_class']) . '\'s Panel' . ($class_color ? '</font>' : '') . "</h2>
            {$add_button}
            <table class='table table-bordered table-striped bottom20'>
                <thead>
                    <tr>
                        <th>{$lang['spanel_pg_name']}</th>
                        <th><div class='text-center'>Show in Navbar</div></th>
                        <th><div class='text-center'>{$lang['spanel_added_by']}</div></th>
                        <th><div class='text-center'>{$lang['spanel_date_added']}</div></th>";
                        if ($CURUSER['class'] == UC_MAX) {
                            $HTMLOUT .= "
                        <th><div class='text-center'>{$lang['spanel_links']}</div></th>";
                        }
                        $HTMLOUT .= '
                    </tr>
                </thead>
                <tbody>';
                }
                $HTMLOUT .= "
                    <tr>
                        <td>
                            <div class='size_4'>
                                <a href='" . htmlsafechars($arr['file_name']) . "' class='tooltipper' title='" . htmlsafechars($arr['description'] . '<br>' . $arr['file_name']) . "'>" . htmlsafechars($arr['page_name']) . "</a>
                            </div>
                        </td>
                        <td>
                            <div class='text-center'>
                                {$arr['navbar']}
                            </div>
                        </td>
                        <td>
                            <div class='text-center'>
                                " . format_username($arr['added_by']) . "
                            </div>
                        </td>
                        <td>
                            <div class='text-center'>
                                <span>" . get_date($arr['added'], 'DATE', 0, 1) . "</span>
                            </div>
                        </td>";
                if ($CURUSER['class'] == UC_MAX) {
                    $HTMLOUT .= "
                        <td>
                            <div class='flex flex-justify-center'>
                                <a href='{$site_config['baseurl']}/staffpanel.php?action=edit&amp;id=" . (int)$arr['id'] . "' class='tooltipper' title='" . $lang['spanel_edit'] . "'>
                                    <img src='{$site_config['pic_base_url']}button_edit2.gif' width='15px' height='auto' alt='" . $lang['spanel_edit'] . "' />
                                </a>
                                <a href='{$site_config['baseurl']}/staffpanel.php?action=delete&amp;id=" . (int)$arr['id'] . "' class='tooltipper' title='" . $lang['spanel_delete'] . "'>
                                    <img src='{$site_config['pic_base_url']}button_delete2.gif' width='14px' height='auto' alt='" . $lang['spanel_delete'] . "' />
                                </a>
                            </div>
                        </td>";
                }
                $HTMLOUT .= '
                    </tr>';
                ++$i;
                if ($end_table) {
                    $i = 1;
                    $HTMLOUT .= '
                </tbody>
            </table>
        </div>';
                }
            }
        $HTMLOUT .= '
        </div>';
        } else {
            $HTMLOUT .= stdmsg($lang['spanel_sorry'], $lang['spanel_nothing_found']);
        }
        echo stdhead($lang['spanel_header']) . $HTMLOUT . stdfoot();
    }
}
