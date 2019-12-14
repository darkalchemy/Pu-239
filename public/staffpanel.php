<?php

declare(strict_types = 1);

use Pu239\Cache;
use Pu239\Database;
use Pu239\Radiance;
use Pu239\Session;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once INCL_DIR . 'function_staff.php';
require_once BIN_DIR . 'uglify.php';
require_once BIN_DIR . 'functions.php';
require_once CLASS_DIR . 'class_check.php';
$user = check_user_status();
global $container, $site_config;

if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] === 'reset=1') {
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=op&reset=1');
    die();
} elseif (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/staffpanel.php?') {
    header('Location: ' . $_SERVER['PHP_SELF'] . '?tool=op');
    die();
}
$session = $container->get(Session::class);
$radiance = $container->get(Radiance::class);
class_check(UC_STAFF);
if (!$site_config['site']['staffpanel_online']) {
    stderr(_('Information'), _('The staffpanel is currently offline for maintenance work'));
}
if ($site_config['tracker']['radiance'] && !file_exists($site_config['tracker']['config_path'])) {
    $session->set('is-danger', "{$site_config['tracker']['config_path']} does not exist. Please set the path correctly in the site settings -> tracker.");
}
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

$HTMLOUT = $page_name = $file_name = $navbar = '';
$fluent = $container->get(Database::class);
$cache = $container->get(Cache::class);
$cache->delete('staff_classes_');
$staff_classes = $cache->get('staff_classes_');
if ($staff_classes === false || is_null($staff_classes)) {
    $available_classes = $fluent->from('class_config')
                                ->select(null)
                                ->select('value')
                                ->where("name != 'UC_MIN'")
                                ->where("name != 'UC_MAX'")
                                ->where("name != 'UC_STAFF'")
                                ->where('value >= ?', UC_STAFF)
                                ->groupBy('value')
                                ->orderBy('value')
                                ->fetchAll();
    foreach ($available_classes as $class) {
        $staff_classes[] = $class['value'];
    }
    $cache->set('staff_classes_', $staff_classes, 0);
}
$data = array_merge($_POST, $_GET);
$action = isset($data['action']) ? htmlsafechars($data['action']) : null;
$id = isset($data['id']) ? (int) $data['id'] : 0;
$tool = !empty($data['tool']) ? $data['tool'] : null;
write_info(_fe('{0} has accessed the {1}', $user['username'], empty($tool) ? 'staffpanel' : _fe('{0} staff page', $tool)));
$staff_tools = [
    'modtask' => 'modtask',
    'iphistory' => 'iphistory',
    'ipsearch' => 'ipsearch',
    'shit_list' => 'shit_list',
    'invite_tree' => 'invite_tree',
    'user_hits' => 'user_hits',
];
$file_names = $fluent->from('staffpanel')
                     ->select(null)
                     ->select('file_name')
                     ->fetchPairs('id', 'file_name');
foreach ($file_names as $key => $file_name) {
    $item = str_replace([
        'staffpanel.php?tool=',
        '.php',
        '&mode=news',
        '&action=app',
    ], '', $file_name);
    $staff_tools[$item] = $item;
}
ksort($staff_tools);
if (in_array($tool, $staff_tools) && file_exists(ADMIN_DIR . $staff_tools[$tool] . '.php')) {
    require_once ADMIN_DIR . $staff_tools[$tool] . '.php';
} else {
    if ($action === 'delete' && is_valid_id($id) && has_access($user['class'], UC_MAX, 'coder')) {
        $sure = (isset($_GET['sure']) ? $_GET['sure'] : '') === 'yes';
        $arr = $fluent->from('staffpanel')
                      ->select(null)
                      ->select('navbar')
                      ->select('added_by')
                      ->select('av_class')
                      ->select('page_name')
                      ->where('id = ?', $id)
                      ->fetch();
        if ($user['class'] < $arr['av_class']) {
            stderr(_('Error'), _('You are not allowed to delete this page.'));
        }
        if (!$sure) {
            stderr(_('Sanity check'), _('Are you sure you want to delete this page') . ': "' . htmlsafechars($arr['page_name']) . '"? ' . _('Click') . ' <a href="' . $_SERVER['PHP_SELF'] . '?action=' . $action . '&amp;id=' . $id . '&amp;sure=yes">' . _('here') . '</a> ' . _('to delete it or') . ' <a href="' . $_SERVER['PHP_SELF'] . '">' . _('here') . '</a> ' . _('to go back') . '.');
        }
        $cache->delete('staff_classes_');
        $result = $fluent->deleteFrom('staffpanel')
                         ->where('id = ?', $id)
                         ->execute();
        $cache->delete('av_class_');
        $cache->delete('staff_panels_6');
        $cache->delete('staff_panels_5');
        $cache->delete('staff_panels_4');
        if ($result >= 1) {
            if ($user['class'] <= UC_MAX) {
                $page = _('Page') . " '[color=#" . get_user_class_color((int) $arr['av_class']) . "]{$arr['page_name']}[/color]'";
                $user_bbcode = "[url={$site_config['paths']['baseurl']}/userdetails.php?id={$user['id']}][color=#" . get_user_class_color($user['class']) . "]{$user['username']}[/color][/url]";
                write_log("$page " . _('in the staff panel was') . " $action by $user_bbcode");
            }
            header('Location: ' . $_SERVER['PHP_SELF']);
            die();
        } else {
            stderr(_('Error'), _('There was a database error, please retry.'));
        }
    } elseif ($action === 'flush' && has_access($user['class'], UC_SYSOP, 'coder')) {
        $cache->flushDB();
        $session->set('is-success', _fe('You flushed the {0} cache', ucfirst($site_config['cache']['driver'])));
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif ($action === 'uglify' && has_access($user['class'], UC_SYSOP, 'coder')) {
        toggle_site_status(true);
        $result = run_uglify();
        toggle_site_status(false);
        if ($result) {
            $session->set('is-success', _('All CSS and Javascript files processed'));
            $cache->flushDB();
            $session->set('is-success', _fe('You flushed the {0} cache', ucfirst($site_config['cache']['driver'])));
        } else {
            $session->set('is-warning', _('uglify.php failed'));
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif ($action === 'clear_ajaxchat' && has_access($user['class'], UC_SYSOP, 'coder')) {
        $fluent->deleteFrom('ajax_chat_messages')
               ->where('id>0')
               ->execute();
        $session->set('is-success', 'You deleted [i]all[/i] messages in AJAX Chat.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif ($action === 'radiance_start' && has_access($user['class'], UC_SYSOP, 'coder')) {
        if (empty($radiance->start_radiance())) {
            $session->set('is-success', 'Radiance has started.');
        } else {
            $session->set('is-danger', 'Radiance has failed to start, please check it manually.');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif ($action === 'radiance_reload' && has_access($user['class'], UC_SYSOP, 'coder')) {
        if (empty($radiance->reload_radiance('SIGUSR1'))) {
            $session->set('is-success', 'You have reloaded radiance.');
        } else {
            $session->set('is-danger', 'Radiance has failed to reload, please check it manually.');
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif ($action === 'toggle_status' && has_access($user['class'], UC_SYSOP, 'coder')) {
        if (toggle_site_status($site_config['site']['online'])) {
            $session->set('is-success', _('Site is Online.'));
        } else {
            $session->set('is-success', _('Site is Offline.'));
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        die();
    } elseif (($action === 'add' && has_access($user['class'], UC_MAX, 'coder')) || ($action === 'edit' && is_valid_id($id) && $user['class'] >= UC_MAX)) {
        $names = [
            'page_name',
            'file_name',
            'description',
            'type',
            'av_class',
            'navbar',
        ];
        if ($action === 'edit') {
            $arr = $fluent->from('staffpanel')
                          ->select(null)
                          ->select('page_name')
                          ->select('file_name')
                          ->select('description')
                          ->select('type')
                          ->select('av_class')
                          ->select('navbar')
                          ->where('id = ?', $id)
                          ->fetch();
        }
        foreach ($names as $name) {
            ${$name} = (isset($_POST[$name]) ? $_POST[$name] : ($action === 'edit' ? $arr[$name] : ''));
        }
        if ($action === 'edit' && $user['class'] < $arr['av_class']) {
            stderr(_('Error'), _('You are not allowed to edit this page.'));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            if (empty($page_name)) {
                $errors[] = _('The page name cannot be empty.');
            }
            if (empty($file_name)) {
                $errors[] = _('The filename cannot be empty.');
            }
            if (empty($description)) {
                $errors[] = _('The description cannot be empty.');
            }
            if (!isset($navbar)) {
                $errors[] = _('Show in Navbar cannot be empty.');
            }
            if (!is_array($staff_classes)) {
                $errors[] = _('There are no valid staff classes.');
            }
            if (!in_array((int) $_POST['av_class'], $staff_classes)) {
                $errors[] = _('The selected class is not a valid staff class.');
            }
            if (!empty($file_name) && !is_file($file_name . '.php') && !preg_match('/.php/', $file_name)) {
                $errors[] = _('Non-existent php file.');
            }
            if (!empty($page_name) && strlen($page_name) < 4) {
                $errors[] = _('The page name is too short (min 4 chars).');
            }
            if (!empty($page_name) && strlen($page_name) > 80) {
                $errors[] = _('The page name is too long max 80 chars.');
            }
            if (!empty($file_name) && strlen($file_name) > 80) {
                $errors[] = _('The filename is too long max 80 chars.');
            }
            if (strlen($description) > 100) {
                $errors[] = _('The description is too long max 100 chars.');
            }
            if (empty($errors)) {
                if ($action === 'add') {
                    $values = [
                        'page_name' => $page_name,
                        'file_name' => $file_name,
                        'description' => $description,
                        'type' => $type,
                        'av_class' => (int) $_POST['av_class'],
                        'added_by' => $user['id'],
                        'added' => TIME_NOW,
                        'navbar' => $navbar,
                    ];
                    try {
                        $new_id = $fluent->insertInto('staffpanel')
                                         ->values($values)
                                         ->execute();
                    } catch (Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                    $cache->delete('staff_classes_');
                    $cache->delete('av_class_');
                    $classes = $fluent->from('class_config')
                                      ->select(null)
                                      ->select('DISTINCT value AS value')
                                      ->where('value >= ?', UC_STAFF);
                    foreach ($classes as $class) {
                        $cache->delete('staff_panels_' . $class['value']);
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
                    $res = $fluent->update('staffpanel')
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
                    if (empty($res)) {
                        $errors[] = _('There was a database error, please retry.');
                    }
                }
                if (empty($errors)) {
                    if ($user['class'] <= UC_MAX) {
                        $page = _('Page') . " '[color=#" . get_user_class_color((int) $_POST['av_class']) . "]{$page_name}[/color]'";
                        $what = $action === 'add' ? 'added' : 'edited';
                        $user_bbcode = "[url={$site_config['paths']['baseurl']}/userdetails.php?id={$user['id']}][color=#" . get_user_class_color($user['class']) . "]{$user['username']}[/color][/url]";
                        write_log("$page " . _('in the staff panel was') . " $what by $user_bbcode");
                    }
                    $session->set('is-success', "'{$page_name}' " . ucwords($action) . 'ed Successfully');
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    die();
                }
            }
        }
        if (!empty($errors)) {
            $HTMLOUT .= stdmsg(_pfe('There is {0} error in the form.', 'There are {0} errors in the form.', count($errors)), '<b>' . implode('<br>', $errors) . '</b>');
        }
        $HTMLOUT .= "<form method='post' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>
    <input type='hidden' name='action' value='{$action}'>";
        if ($action === 'edit') {
            $HTMLOUT .= "<input type='hidden' name='id' value='{$id}'>";
        }
        $header = "
                <tr>
                    <th colspan='2'>
                        <h2 class='has-text-centered'>" . ($action === 'edit' ? _fe('Editing: {0}', $page_name) : _('Add A New Staff Page')) . '</h2>
                    </th>
                </tr>';
        $body = "
                <tr>
                    <td class='rowhead'>
                        " . _('Page name') . "
                    </td>
                    <td>
                        <input type='text' class='w-100' name='page_name' value='{$page_name}' required>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        " . _('Filename') . "
                    </td>
                    <td>
                        <input type='text' class='w-100' name='file_name' value='{$file_name}' required>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        " . _('Description') . "
                    </td>
                    <td>
                        <input type='text' class='w-100' name='description' value='{$description}' required>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        " . _('Show in Navbar') . "
                    </td>
                    <td>
                        <input name='navbar' value='1' type='radio' " . ($navbar == 1 ? 'checked' : '') . "><span class='left5'>" . _('Yes') . "</span><br>
                        <input name='navbar' value='0' type='radio' " . ($navbar == 0 ? 'checked' : '') . "><span class='left5'>" . _('No') . '</span>
                    </td>
                </tr>';

        $types = [
            'user',
            'settings',
            'stats',
            'other',
        ];

        $body .= "
                <tr>
                    <td class='rowhead'>" . _('Type Of Tool') . "</td>
                    <td>
                        <select name='type' required>
                            <option value=''>" . _('Choose Type') . '</option>';
        foreach ($types as $this_type) {
            $body .= '
                            <option value="' . $this_type . '" ' . _($type === $this_type ? 'selected' : '') . '>' . ucfirst($this_type) . '</option>';
        }
        $body .= "
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='rowhead'>
                        <span>" . _('Available for') . "</span>
                        </td>
                    <td>
                        <select name='av_class' required>
                            <option value=''>" . _('Choose Class') . '</option>';
        $maxclass = UC_MAX;
        for ($class = UC_STAFF; $class <= $maxclass; ++$class) {
            $body .= '
                           <option value="' . $class . '" ' . (isset($arr['av_class']) && $arr['av_class'] == $class ? 'selected' : '') . '>' . get_user_class_name((int) $class) . '</option>';
        }
        $body .= '
                        </select>
                    </td>';

        $body .= '
                </tr>';

        $HTMLOUT .= main_table($body, $header);
        $HTMLOUT .= "
    <div class='level-center margin20'>
            <input type='submit' class='button is-small' value='" . _('Submit') . "'>
        </form>
        <form method='post' action='{$_SERVER['PHP_SELF']}' enctype='multipart/form-data' accept-charset='utf-8'>
            <input type='submit' class='button is-small' value='" . _('Cancel') . "'>
        </form>
    </div>";
        $title = $action === 'edit' ? _('Edit Staff Page') : _('Add Staff Page');
        $breadcrumbs = [
            "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
        ];
        echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
    } else {
        $add_button = '';
        if (has_access($user['class'], UC_SYSOP, 'coder')) {
            $add_button = "
                <ul class='level-center bg-06'>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=add' class='tooltipper' title='" . _('Add A New Page') . "'>" . _('Add A New Page') . "</a>
                    </li>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=clear_ajaxchat' class='tooltipper' title='" . _('CAUTION: This <b>DELETES</b> all messages in AJAX Chat!') . "'>" . _('Clear Chat') . "</a>
                    </li>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=uglify' class='tooltipper' title='" . _('Uglify') . "'>" . _('Uglify') . "</a>
                    </li>
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=flush' class='tooltipper' title='" . _('Flush Cache') . "'>" . _('Flush Cache') . "</a>
                    </li>" . ($site_config['tracker']['radiance'] ? empty($radiance->check_status()) ? "
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=radiance_start' class='tooltipper' title='" . _('Start Radiance Server') . "'>" . _('Start Radiance') . "</a>
                    </li>" : "
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=radiance_reload' class='tooltipper' title='" . _('Reload torrent list, user list and client blacklist') . "'>" . _('Reload Radiance') . "</a>
                    </li>" : '') . "
                    <li class='margin10'>
                        <a href='{$_SERVER['PHP_SELF']}?action=toggle_status' class='tooltipper' title='" . _('Toggle Site Online/Offline') . "'>" . _('Toggle Site') . '</a>
                    </li>
                </ul>';
        }
        $user_class = $user['class'] >= UC_STAFF ? $user['class'] : UC_MAX;
        $data = $fluent->from('staffpanel AS s')
                       ->select('u.username')
                       ->leftJoin('users AS u ON s.added_by = u.id')
                       ->where('s.av_class <= ?', $user_class)
                       ->orderBy('s.av_class DESC')
                       ->orderBy('s.page_name')
                       ->fetchAll();
        if (!empty($data)) {
            $db_classes = $unique_classes = [];
            foreach ($data as $key => $value) {
                $db_classes[$value['av_class']][] = $value['av_class'];
            }
            $i = 1;
            $HTMLOUT .= "{$add_button}
            <h1 class='has-text-centered'>" . _('Welcome') . " {$user['username']} " . _('to the') . ' ' . _('Staff Panel') . '!</h1>';

            $header = "
                    <tr>
                        <th class='w-50'>" . _('Page name') . "</th>
                        <th><div class='has-text-centered'>" . _('Show in Navbar') . "</div></th>
                        <th><div class='has-text-centered'>" . _('Added by') . "</div></th>
                        <th><div class='has-text-centered'>" . _('Date added') . '</div></th>';
            if ($user['class'] >= UC_MAX) {
                $header .= "
                        <th><div class='has-text-centered'>" . _('Links') . '</div></th>';
            }
            $header .= '
                    </tr>';
            $body = '';
            foreach ($data as $key => $arr) {
                $end_table = count($db_classes[$arr['av_class']]) == $i ? true : false;

                if (!in_array($arr['av_class'], $unique_classes)) {
                    $unique_classes[] = $arr['av_class'];
                    $table = "
            <h1 class='has-text-centered text-shadow " . get_user_class_name((int) $arr['av_class'], true) . "'>" . _fe("{0}'s Panel", get_user_class_name((int) $arr['av_class'])) . '</h1>';
                }
                $show_in_nav = $arr['navbar'] == 1 ? '
                <span class="has-text-success show_in_navbar tooltipper" title="' . _('Hide from Navbar') . '" data-show="' . $arr['navbar'] . '" data-id="' . $arr['id'] . '">' . _('true') . '</span>' : '
                <span class="has-text-info show_in_navbar tooltipper" title="' . _('Show in Navbar') . '" data-show="' . $arr['navbar'] . '" data-id="' . $arr['id'] . '">' . _('false') . '</span>';
                $body .= "
                    <tr>
                        <td>
                            <div class='size_4'>
                                <a href='{$site_config['paths']['baseurl']}/" . htmlsafechars($arr['file_name']) . "' class='tooltipper' title='" . htmlsafechars($arr['description'] . '<br>' . $arr['file_name']) . "'>" . ucwords(htmlsafechars($arr['page_name'])) . "</a>
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
                if (has_access($user['class'], UC_MAX, 'coder')) {
                    $body .= "
                        <td>
                            <div class='level-center'>
                                <a href='{$_SERVER['PHP_SELF']}?action=edit&amp;id=" . (int) $arr['id'] . "' class='tooltipper' title='" . _('Edit') . "'>
                                    <i class='icon-edit icon has-text-info' aria-hidden='true'></i>
                                </a>
                                <a href='{$_SERVER['PHP_SELF']}?action=delete&amp;id=" . (int) $arr['id'] . "' class='tooltipper' title='" . _('Delete') . "'>
                                    <i class='icon-trash-empty icon has-text-danger' aria-hidden='true'></i>
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
            $HTMLOUT .= stdmsg(_('Sorry'), _('Nothing found.'));
        }
        $title = _('Staff Panel');
        $breadcrumbs = [
            "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
        ];
        echo stdhead($title, $stdhead, 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot($stdfoot);
    }
}
