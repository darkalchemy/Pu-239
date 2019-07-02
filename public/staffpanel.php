<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_staff.php';
require_once BIN_DIR . 'uglify.php';
require_once BIN_DIR . 'functions.php';
$user = check_user_status();
$stdhead = [
    'css' => [
        get_file_name('sceditor_css'),
    ],
];
$stdfoot = [
    'js' => [
        get_file_name('sceditor_js'),
        get_file_name('navbar_show_js'),
    ],
];

$HTMLOUT = '';
$lang = array_merge(load_language('global'), load_language('index'), load_language('staff_panel'));
$staff_classes1['name'] = $page_name = $file_name = $navbar = '';
$staff = sqlesc(UC_STAFF);
global $container, $site_config;

$cache = $container->get(Cache::class);
$staff_classes = $cache->get('staff_classes_');
if ($staff_classes === false || is_null($staff_classes)) {
    $res = sql_query("SELECT value FROM class_config WHERE name NOT IN ('UC_MIN', 'UC_STAFF', 'UC_MAX') AND value>= '$staff' GROUP BY value ORDER BY value");
    $staff_classes = [];
    while (($row = mysqli_fetch_assoc($res))) {
        $staff_classes[] = $row['value'];
    }
    $cache->set('staff_classes_', $staff_classes, 0);
}

if (!$site_config['site']['staffpanel_online']) {
    stderr($lang['spanel_information'], $lang['spanel_panel_cur_offline']);
}
require_once CLASS_DIR . 'class_check.php';
class_check(UC_STAFF);
$action = isset($_GET['action']) ? htmlsafechars($_GET['action']) : (isset($_POST['action']) ? htmlsafechars($_POST['action']) : null);
$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);
$tool = !empty($_GET['tool']) ? $_GET['tool'] : (!empty($_POST['tool']) ? $_POST['tool'] : null);
write_info("{$user['username']} has accessed the " . (empty($tool) ? 'staffpanel' : "$tool staff page"));
$staff_tools = [
    'modtask' => 'modtask',
    'iphistory' => 'iphistory',
    'ipsearch' => 'ipsearch',
    'shit_list' => 'shit_list',
    'invite_tree' => 'invite_tree',
    'user_hits' => 'user_hits',
];

$sql = sql_query('SELECT file_name FROM staffpanel') or sqlerr(__FILE__, __LINE__);
while ($list = mysqli_fetch_assoc($sql)) {
    $item = str_replace([
        'staffpanel.php?tool=',
        '.php',
        '&mode=news',
        '&action=app',
    ], '', $list['file_name']);
    $staff_tools[$item] = $item;
}
ksort($staff_tools);
$fluent = $container->get(Database::class);
if (in_array($tool, $staff_tools) && file_exists(ADMIN_DIR . $staff_tools[$tool] . '.php')) {
    require_once ADMIN_DIR . $staff_tools[$tool] . '.php';
} else {
    $session = $container->get(Session::class);
    if ($action === 'delete' && is_valid_id($id) && $user['class'] >= UC_MAX) {
        $sure = ((isset($_GET['sure']) ? $_GET['sure'] : '') === 'yes');
        $res = sql_query('SELECT navbar, added_by, av_class' . (!$sure || $user['class'] <= UC_MAX ? ', page_name' : '') . ' FROM staffpanel WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $arr = mysqli_fetch_assoc($res);
        if ($user['class'] < $arr['av_class']) {
            stderr($lang['spanel_error'], $lang['spanel_you_not_allow_del_page']);
        }
        if (!$sure) {
            stderr($lang['spanel_sanity_check'], $lang['spanel_are_you_sure_del'] . ': "' . htmlsafechars($arr['page_name']) . '"? ' . $lang['spanel_click'] . ' <a href="' . $_SERVER['PHP_SELF'] . '?action=' . $action . '&amp;id=' . $id . '&amp;sure=yes">' . $lang['spanel_here'] . '</a> ' . $lang['spanel_to_del_it_or'] . ' <a href="' . $_SERVER['PHP_SELF'] . '">' . $lang['spanel_here'] . '</a> ' . $lang['spanel_to_go_back'] . '.');
        }
        $cache->delete('staff_classes_');
        sql_query('DELETE FROM staffpanel WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        $cache->delete('av_class_');
        $cache->delete('staff_panels_6');
        $cache->delete('staff_panels_5');
        $cache->delete('staff_panels_4');
        if (mysqli_affected_rows($mysqli)) {
            if ($user['class'] <= UC_MAX) {
                $page = "{$lang['spanel_page']} '[color=#" . get_user_class_color((int) $arr['av_class']) . "]{$arr['page_name']}[/color]'";
                $user_bbcode = "[url={$site_config['paths']['baseurl']}/userdetails.php?id={$user['id']}][color=#" . get_user_class_color($user['class']) . "]{$user['username']}[/color][/url]";
                write_log("$page {$lang['spanel_in_the_sp_was']} $action by $user_bbcode");
            }
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        } else {
            stderr($lang['spanel_error'], $lang['spanel_db_error_msg']);
        }
    } elseif (($action === 'flush' && $user['class'] >= UC_SYSOP)) {
        $cache->flushDB();
        $session->set('is-success', 'You flushed the ' . ucfirst($site_config['cache']['driver']) . ' cache');
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif (($action === 'uglify' && $user['class'] >= UC_SYSOP)) {
        toggle_site_status(true);
        $result = run_uglify();
        toggle_site_status(false);
        if ($result) {
            $session->set('is-success', 'All CSS and Javascript files processed');
            $cache->flushDB();
            $session->set('is-success', 'You flushed the ' . ucfirst($site_config['cache']['driver']) . ' cache');
        } else {
            $session->set('is-warning', 'uglify.php failed');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif (($action === 'clear_ajaxchat' && $user['class'] >= UC_SYSOP)) {
        $fluent->deleteFrom('ajax_chat_messages')
               ->where('id>0')
               ->execute();
        $session->set('is-success', 'You deleted [i]all[/i] messages in AJAX Chat.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif (($action === 'toggle_status' && $user['class'] >= UC_SYSOP)) {
        if (toggle_site_status($site_config['site']['online'])) {
            $session->set('is-success', 'Site is Online.');
        } else {
            $session->set('is-success', 'Site is Offline.');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif (($action === 'add' && $user['class'] >= UC_MAX) || ($action === 'edit' && is_valid_id($id) && $user['class'] >= UC_MAX)) {
        $names = [
            'page_name',
            'file_name',
            'description',
            'type',
            'av_class',
            'navbar',
        ];
        if ($action === 'edit') {
            $res = sql_query('SELECT ' . implode(', ', $names) . ' FROM staffpanel WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
            $arr = mysqli_fetch_assoc($res);
        }
        foreach ($names as $name) {
            ${$name} = (isset($_POST[$name]) ? $_POST[$name] : ($action === 'edit' ? $arr[$name] : ''));
        }
        if ($action === 'edit' && $user['class'] < $arr['av_class']) {
            stderr($lang['spanel_error'], $lang['spanel_cant_edit_this_pg']);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            if (!in_array((int) $_POST['av_class'], $staff_classes)) {
                $errors[] = $lang['spanel_selected_class_not_valid'];
            }
            if (!empty($file_name) && !is_file($file_name . '.php') && !preg_match('/.php/', $file_name)) {
                $errors[] = $lang['spanel_inexistent_php_file'];
            }
            if (!empty($page_name) && strlen($page_name) < 4) {
                $errors[] = $lang['spanel_the_pg_name'] . ' ' . $lang['spanel_is_too_short_min_4'] . '.';
            }
            if (!empty($page_name) && strlen($page_name) > 80) {
                $errors[] = $lang['spanel_the_pg_name'] . ' ' . $lang['spanel_is_too_long'] . ' (' . $lang['spanel_max_80'] . ').';
            }
            if (!empty($file_name) && strlen($file_name) > 80) {
                $errors[] = $lang['spanel_the_filename'] . ' ' . $lang['spanel_is_too_long'] . ' (' . $lang['spanel_max_80'] . ').';
            }
            if (strlen($description) > 100) {
                $errors[] = $lang['spanel_the_descr'] . ' ' . $lang['spanel_is_too_long'] . ' (' . $lang['spanel_max_100'] . ').';
            }
            if (empty($errors)) {
                if ($action === 'add') {
                    $res = sql_query('INSERT INTO staffpanel (page_name, file_name, description, type, av_class, added_by, added, navbar)
                                      VALUES (' . implode(', ', array_map('sqlesc', [
                        $page_name,
                        $file_name,
                        $description,
                        $type,
                        (int) $_POST['av_class'],
                        $user['id'],
                        TIME_NOW,
                        $navbar,
                    ])) . ')');
                    $cache->delete('staff_classes_');
                    $cache->delete('av_class_');
                    $classes = $fluent->from('class_config')
                                      ->select(null)
                                      ->select('DISTINCT value AS value')
                                      ->where('value>= ?', UC_STAFF);
                    foreach ($classes as $class) {
                        $cache->delete('staff_panels_' . $class);
                    }
                    if (!$res) {
                        if (((is_object($mysqli)) ? mysqli_errno($mysqli) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)) == 1062) {
                            $errors[] = $lang['spanel_this_fname_sub'];
                        } else {
                            $errors[] = $lang['spanel_db_error_msg'];
                        }
                    }
                } else {
                    $set = [
                        'navbar' => $navbar,
                        'page_name' => $page_name,
                        'file_name' => $file_name,
                        'description' => $description,
                        'type' => $type,
                        'av_class' => (int) $_POST['av_class'],
                    ];
                    $fluent->update('staffpanel')
                           ->set($set)
                           ->where('id=?', $id)
                           ->execute();
                    $cache->delete('av_class_');
                    $classes = $fluent->from('class_config')
                                      ->select(null)
                                      ->select('DISTINCT value AS value')
                                      ->where('value>= ?', UC_STAFF);
                    foreach ($classes as $class) {
                        $cache->delete('staff_panels_' . $class['value']);
                    }
                    if (!$res) {
                        $errors[] = $lang['spanel_db_error_msg'];
                    }
                }
                if (empty($errors)) {
                    if ($user['class'] <= UC_MAX) {
                        $page = "{$lang['spanel_page']} '[color=#" . get_user_class_color((int) $_POST['av_class']) . "]{$page_name}[/color]'";
                        $what = $action === 'add' ? 'added' : 'edited';
                        $user_bbcode = "[url={$site_config['paths']['baseurl']}/userdetails.php?id={$user['id']}][color=#" . get_user_class_color($user['class']) . "]{$user['username']}[/color][/url]";
                        write_log("$page {$lang['spanel_in_the_sp_was']} $what by $user_bbcode");
                    }
                    $session->set('is-success', "'{$page_name}' " . ucwords($action) . 'ed Successfully');
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    die();
                }
            }
        }
        if (!empty($errors)) {
            $HTMLOUT .= stdmsg($lang['spanel_there'] . ' ' . (count($errors) > 1 ? 'are' : 'is') . ' ' . count($errors) . ' error' . (count($errors) > 1 ? 's' : '') . ' ' . $lang['spanel_in_the_form'] . '.', '<b>' . implode('<br>', $errors) . '</b>');
            $HTMLOUT .= '<br>';
        }
        $HTMLOUT .= "<form method='post' action='{$_SERVER['PHP_SELF']}' accept-charset='utf-8'>
    <input type='hidden' name='action' value='{$action}'>";
        if ($action === 'edit') {
            $HTMLOUT .= "<input type='hidden' name='id' value='{$id}'>";
        }
        $header = "
                <tr>
                    <th colspan='2'>
                        " . ($action === 'edit' ? $lang['spanel_edit'] . ' "' . $page_name . '"' : $lang['spanel_add_a_new']) . ' Staffpage' . '
                    </th>
                </tr>';
        $body = "
                <tr>
                    <td class='rowhead'>
                        {$lang['spanel_pg_name']}
                    </td>
                    <td>
                        <input type='text' class='w-100' name='page_name' value='{$page_name}'>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['spanel_filename']}
                    </td>
                    <td>
                        <input type='text' class='w-100' name='file_name' value='{$file_name}'>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        {$lang['spanel_description']}
                    </td>
                    <td>
                        <input type='text' class='w-100' name='description' value='{$description}'>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        Show in Navbar
                    </td>
                    <td>
                        <input name='navbar' value='1' type='radio'" . ($navbar == 1 ? ' checked' : '') . "><span class='left5'>Yes</span><br>
                        <input name='navbar' value='0' type='radio'" . ($navbar == 0 ? ' checked' : '') . "><span class='left5'>No</span>
                    </td>
                </tr>";

        $types = [
            'user',
            'settings',
            'stats',
            'other',
        ];

        $body .= "
                <tr>
                    <td class='rowhead'>{$lang['spanel_type_of_tool']}</td>
                    <td>
                        <select name='type'>";
        foreach ($types as $type) {
            $body .= '
                            <option value="' . $type . '"' . ($arr['type'] === $type ? ' selected' : '') . '>' . ucfirst($type) . '</option>';
        }
        $body .= "
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
            $body .= '
                           <option value="' . $class . '"' . ($arr['av_class'] == $class ? ' selected' : '') . '>' . get_user_class_name((int) $class) . '</option>';
        }
        $body .= '
                        </select>
                    </td>';

        $body .= '
                </tr>';

        $HTMLOUT .= main_table($body, $header);
        $HTMLOUT .= "
    <div class='level-center margin20'>
            <input type='submit' class='button is-small' value='{$lang['spanel_submit']}'>
        </form>
        <form method='post' action='{$_SERVER['PHP_SELF']}' accept-charset='utf-8'>
            <input type='submit' class='button is-small' value='{$lang['spanel_cancel']}'>
        </form>
    </div>";
        echo stdhead($lang['spanel_header'] . ' :: ' . ($action == 'edit' ? '' . $lang['spanel_edit'] . ' "' . $page_name . '"' : $lang['spanel_add_a_new']) . ' page', $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
    } else {
        $add_button = '';
        if ($user['class'] >= UC_SYSOP) {
            $add_button = "
                <ul class='level-center bg-06'>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=add' class='tooltipper' title='{$lang['spanel_add_a_new_pg']}'>{$lang['spanel_add_a_new_pg']}</a>
                    </li>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=clear_ajaxchat' class='tooltipper' title='{$lang['spanel_clear_chat_caution']}'>{$lang['spanel_clear_chat']}</a>
                    </li>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=uglify' class='tooltipper' title='{$lang['spanel_uglify']}'>{$lang['spanel_uglify']}</a>
                    </li>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=flush' class='tooltipper' title='{$lang['spanel_flush_cache']}'>{$lang['spanel_flush_cache']}</a>
                    </li>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=toggle_status' class='tooltipper' title='{$lang['spanel_toggle_status_title']}'>{$lang['spanel_toggle_status']}</a>
                    </li>
                </ul>";
        }
        $res = sql_query('SELECT s.*, u.username
                                FROM staffpanel AS s
                                LEFT JOIN users AS u ON u.id=s.added_by
                                WHERE s.av_class <= ' . sqlesc($user['class']) . '
                                ORDER BY s.av_class DESC, s.page_name') or sqlerr(__FILE__, __LINE__);
        if (mysqli_num_rows($res) > 0) {
            $db_classes = $unique_classes = $mysql_data = [];
            while ($arr = mysqli_fetch_assoc($res)) {
                $mysql_data[] = $arr;
            }
            foreach ($mysql_data as $key => $value) {
                $db_classes[$value['av_class']][] = $value['av_class'];
            }
            $i = 1;
            $HTMLOUT .= "{$add_button}
            <h1 class='has-text-centered'>{$lang['spanel_welcome']} {$user['username']} {$lang['spanel_to_the']} {$lang['spanel_header']}!</h1>";

            $header = "
                    <tr>
                        <th class='w-50'>{$lang['spanel_pg_name']}</th>
                        <th><div class='has-text-centered'>Show in Navbar</div></th>
                        <th><div class='has-text-centered'>{$lang['spanel_added_by']}</div></th>
                        <th><div class='has-text-centered'>{$lang['spanel_date_added']}</div></th>";
            if ($user['class'] >= UC_MAX) {
                $header .= "
                        <th><div class='has-text-centered'>{$lang['spanel_links']}</div></th>";
            }
            $header .= '
                    </tr>';
            $body = '';
            foreach ($mysql_data as $key => $arr) {
                $end_table = (count($db_classes[$arr['av_class']]) == $i ? true : false);

                if (!in_array($arr['av_class'], $unique_classes)) {
                    $unique_classes[] = $arr['av_class'];
                    $table = "
            <h1 class='has-text-centered text-shadow " . get_user_class_name((int) $arr['av_class'], true) . "'>" . get_user_class_name((int) $arr['av_class']) . "'s Panel</h1>";
                }
                $show_in_nav = $arr['navbar'] == 1 ? '
                <span class="has-text-success show_in_navbar tooltipper" title="Hide from Navbar" data-show="' . $arr['navbar'] . '" data-id="' . $arr['id'] . '">true</span>' : '
                <span class="has-text-info show_in_navbar tooltipper" title="Show in Navbar" data-show="' . $arr['navbar'] . '" data-id="' . $arr['id'] . '">false</span>';

                $body .= "
                    <tr>
                        <td>
                            <div class='size_4'>
                                <a href='" . htmlsafechars($arr['file_name']) . "' class='tooltipper' title='" . htmlsafechars($arr['description'] . '<br>' . $arr['file_name']) . "'>" . ucwords(htmlsafechars($arr['page_name'])) . "</a>
                            </div>
                        </td>
                        <td>
                            <div class='has-text-centered'>
                                {$show_in_nav}
                            </div>
                        </td>
                        <td>
                            <div class='has-text-centered'>
                                " . format_username((int) $arr['added_by']) . "
                            </div>
                        </td>
                        <td>
                            <div class='has-text-centered'>
                                <span>" . get_date((int) $arr['added'], 'DATE', 0, 1) . '</span>
                            </div>
                        </td>';
                if ($user['class'] >= UC_MAX) {
                    $body .= "
                        <td>
                            <div class='level-center'>
                                <a href='{$_SERVER['PHP_SELF']}?action=edit&amp;id=" . (int) $arr['id'] . "' class='tooltipper' title='{$lang['spanel_edit']}'>
                                    <i class='icon-edit icon'></i>
                                </a>
                                <a href='{$_SERVER['PHP_SELF']}?action=delete&amp;id=" . (int) $arr['id'] . "' class='tooltipper' title='{$lang['spanel_delete']}'>
                                    <i class='icon-trash-empty icon has-text-danger'></i>
                                </a>
                            </div>
                        </td>";
                }
                $body .= '
                    </tr>';
                ++$i;
                if ($end_table) {
                    $i = 1;
                    $HTMLOUT .= "<div class='bg-00 top20 round10'>$table" . main_table($body, $header) . '</div>';
                    $body = '';
                }
            }
        } else {
            $HTMLOUT .= stdmsg($lang['spanel_sorry'], $lang['spanel_nothing_found']);
        }
        echo stdhead($lang['spanel_header'], $stdhead) . wrapper($HTMLOUT) . stdfoot($stdfoot);
    }
}
