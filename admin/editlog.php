<?php

declare(strict_types = 1);

use Pu239\Session;

require_once INCL_DIR . 'function_users.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('editlog'));
global $site_config, $CURUSER;

$HTMLOUT = '';
$file_data = ROOT_DIR . 'dir_list' . DIRECTORY_SEPARATOR . 'data_' . $CURUSER['username'] . '.txt';
if (file_exists($file_data)) {
    $data = json_decode(file_get_contents($file_data), true);
    $exist = true;
} else {
    $exist = false;
}
$fetch_set = [];
$i = 0;
$directories = [ROOT_DIR];
$included_extentions = $site_config['coders']['log_allowed_ext'];
foreach ($directories as $path) {
    $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($objects as $name => $object) {
        preg_match('/(\.idea|\.git|vendor|node_modules)/', $name, $match);
        if (empty($match)) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            if (in_array($ext, $included_extentions)) {
                $fetch_set[$i]['modify'] = filemtime($name);
                $fetch_set[$i]['size'] = filesize($name);
                $fetch_set[$i]['hash'] = hash_file('sha256', $name);
                $fetch_set[$i]['name'] = $name;
                $fetch_set[$i]['key'] = $i;
                ++$i;
            }
        }
    }
}
if (!$exist || (isset($_POST['update']) && ($_POST['update'] === 'Update'))) {
    $data = json_encode($fetch_set);
    $session = $container->get(Session::class);
    if (file_put_contents($file_data, $data)) {
        $session->set('is-success', "Coder's Log was updated for {$CURUSER['username']}");
    } else {
        $session->set('is-warning', "[h3]Could not save data to:[/h3][p]{$file_data}[/p]");
    }
    $data = $fetch_set;
    unset($_POST);
}
reset($fetch_set);
reset($data);
$current = $fetch_set;
$last = $data;
foreach ($current as $x) {
    foreach ($last as $y) {
        if ($x['name'] == $y['name']) {
            if (($x['hash'] === $y['hash'])) {
                unset($current[$x['key']], $last[$y['key']]);
            } else {
                $current[$x['key']]['status'] = 'modified';
            }
        }
        if (isset($last[$y['key']])) {
            $last[$y['key']]['status'] = 'deleted';
        }
    }
    if (isset($current[$x['key']]['name']) && !isset($current[$x['key']]['status'])) {
        $current[$x['key']]['status'] = 'new';
    }
}
$current += $last;
unset($last, $data, $fetch_set);

$HTMLOUT .= "
        <h1 class='has-text-centered top20'>Coder's Log</h1>
        <div class='bordered bottom20'>
            <div class='alt_bordered bg-00 padding20'>
                <div class='has-text-centered'>Tracking " . implode(', ', $site_config['coders']['log_allowed_ext']) . " files only!</div>
                <div class='has-text-centered'>" . number_format(count($current)) . ' files have been added, modifed or deleted since your last update of the ' . number_format($i) . " files being tracked.</div>
            </div>
        </div>
        <div class='table-wrapper'>
        <table class='table table-bordered table-striped'>
            <thead>
                <tr>
                    <th>{$lang['editlog_new']}</th>
                    <th class='w-15'>{$lang['editlog_added']}</th>
                </tr>
            </thead>";
reset($current);
$count = 0;
$current = array_msort($current, ['name' => SORT_ASC]);
foreach ($current as $x) {
    if ($x['status'] === 'new') {
        $HTMLOUT .= '
                <tr>
                    <td>' . htmlsafechars(str_replace(ROOT_DIR, '', $x['name'])) . '
                    </td>
                    <td>' . get_date((int) $x['modify'], 'DATE', 0, 1) . '
                    </td>
                </tr>';
        ++$count;
    }
}
if (!$count) {
    $HTMLOUT .= "
                <tr>
                    <td colspan='2' class='is-primary'>{$lang['editlog_no_new']}</td>
                </tr>";
}
$HTMLOUT .= "
        </table>
        </div>
        <div class='table-wrapper'>
        <table class='table table-bordered table-striped top20'>
            <thead>
                <tr>
                    <th>{$lang['editlog_modified']}</th>
                    <th class='w-15'>{$lang['editlog_modified1']}</th>
                </tr>
            </thead>";
reset($current);
$count = 0;
foreach ($current as $x) {
    if ($x['status'] === 'modified') {
        $HTMLOUT .= '
                <tr>
                    <td>' . htmlsafechars(str_replace(ROOT_DIR, '', $x['name'])) . '
                    </td>
                    <td>' . get_date((int) $x['modify'], 'DATE', 0, 1) . '
                    </td>
                </tr>';
        ++$count;
    }
}
if (!$count) {
    $HTMLOUT .= "
                <tr>
                    <td colspan='2' class='is-primary'>{$lang['editlog_no_modified']}</td>
                </tr>";
}
$HTMLOUT .= "
        </table>
        </div>
        <div class='table-wrapper'>
        <table class='table table-bordered table-striped top20'>
            <thead>
                <tr>
                    <th>{$lang['editlog_deleted']}</th>
                    <th class='w-15'>{$lang['editlog_deleted1']}</th>
                </tr>
            </thead>";
reset($current);
$count = 0;
foreach ($current as $x) {
    if ($x['status'] === 'deleted') {
        $HTMLOUT .= '
                <tr>
                    <td>' . htmlsafechars(str_replace(ROOT_DIR, '', $x['name'])) . '
                    </td>
                    <td>' . get_date((int) $x['modify'], 'DATE', 0, 1) . '
                    </td>
                </tr>';
        ++$count;
    }
}
if (!$count) {
    $HTMLOUT .= "
                <tr>
                    <td colspan='2' class='is-primary'>{$lang['editlog_no_deleted']}</td>
                </tr>";
}
$HTMLOUT .= "
        </table>
        </div>
        <form method='post' action='staffpanel.php?tool=editlog&amp;action=editlog' enctype='multipart/form-data' accept-charset='utf-8'>
            <div class='has-text-centered top20 bottom20'>
                <input name='update' type='submit' value='{$lang['editlog_update']}' class='button is-small'>
            </div>
        </form>";
echo stdhead($lang['editlog_stdhead']) . wrapper($HTMLOUT) . stdfoot();
