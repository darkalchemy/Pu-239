<?php

declare(strict_types = 1);

use Pu239\Database;
use Pu239\Session;

require_once CLASS_DIR . 'class_check.php';
require_once INCL_DIR . 'function_html.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $container, $site_config, $CURUSER;

$fluent = $container->get(Database::class);
$promos = $fluent->from('class_promo')
    ->orderBy('id');
foreach ($promos as $ac) {
    $class_config[$ac['name']]['id'] = $ac['id'];
    $class_config[$ac['name']]['name'] = $ac['name'];
    $class_config[$ac['name']]['min_ratio'] = $ac['min_ratio'];
    $class_config[$ac['name']]['uploaded'] = $ac['uploaded'];
    $class_config[$ac['name']]['time'] = $ac['time'];
    $class_config[$ac['name']]['low_ratio'] = $ac['low_ratio'];
}
$possible_modes = [
    'add',
    'edit',
    'remove',
    '',
];
$mode = (isset($_GET['mode']) ? htmlsafechars($_GET['mode']) : '');
if (!in_array($mode, $possible_modes)) {
    $session->set('is-error', _('A ruffian that will swear, drink, dance, revel the night, rob, murder and commit the oldest of ins the newest kind of ways.'));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session = $container->get(Session::class);
    if ($mode === 'edit') {
        if (!empty($class_config)) {
            foreach ($class_config as $c_name => $value) {
                $c_value = $value['id']; // $key is like 0, 1, 2 etc....
                $c_name = strtoupper($value['name']);
                $c_min_ratio = $value['min_ratio'];
                $c_uploaded = $value['uploaded'];
                $c_time = $value['time'];
                $c_low_ratio = $value['low_ratio'];
                $post_data = $_POST[$c_name]; //    0=> name,1=>min_ratio,2=>uploaded,3=>time,4=>low_ratio
                $value = $post_data[0];
                $name = $post_data[1];
                $min_ratio = strtoupper($post_data[2]);
                $uploaded = $post_data[3];
                $time = $post_data[4];
                $low_ratio = $post_data[5];
                if (isset($_POST[$c_name][0]) && (($value != $c_value) || ($name != $c_name) || ($min_ratio != $c_min_ratio) || ($uploaded != $c_uploaded) || ($time != $c_time) || ($low_ratio != $c_low_ratio))) {
                    $update[$c_name] = '(' . sqlesc($c_name) . ', ' . sqlesc(is_array($min_ratio) ? implode('|', $min_ratio) : $min_ratio) . ', ' . sqlesc(is_array($uploaded) ? implode('|', $uploaded) : $uploaded) . ', ' . sqlesc(is_array($time) ? implode('|', $time) : $time) . ', ' . sqlesc(is_array($low_ratio) ? implode('|', $low_ratio) : $low_ratio) . ')';
                }
            }
        }
        if (sql_query('INSERT INTO class_promo(name,min_ratio,uploaded,time,low_ratio) VALUES ' . implode(', ', $update) . ' ON DUPLICATE KEY UPDATE name = VALUES(name),min_ratio = VALUES(min_ratio),uploaded = VALUES(uploaded),time = VALUES(time),low_ratio = VALUES(low_ratio)')) { // need to change strut
            $session->set('is-success', _('user configuration was saved!'));
        } else {
            $session->set('is-error', _('There was an error while executing the update query or nothing was updated 1.'));
        }
    } elseif ($mode === 'add') {
        if (isset($_POST['name'])) {
            $class_id = (int) $_POST['name'];
            $name = $fluent->from('class_config')
                ->select(null)
                ->select('name')
                ->where('value = ?', $class_id)
                ->where('name != ?', 'UC_STAFF')
                ->where('name != ?', 'UC_MIN')
                ->where('name != ?', 'UC_MAX')
                ->fetch('name');
        } else {
            $session->set('is-error', _('We cannot have empty class name!'));
        }
        if (isset($_POST['min_ratio'])) {
            $min_ratio = (float) $_POST['min_ratio'];
        } else {
            $session->set('is-error', _('We cannot have empty min ratio!'));
        }
        if (isset($_POST['uploaded'])) {
            $uploaded = (int) $_POST['uploaded'];
        } else {
            $session->set('is-error', _('We cannot have empty uploaded!'));
        }
        if (isset($_POST['uploaded'])) {
            $time = (int) $_POST['time'];
        } else {
            $session->set('is-error', _('We cannot have empty time'));
        }
        if (isset($_POST['uploaded'])) {
            $low_ratio = (float) $_POST['low_ratio'];
        } else {
            $session->set('is-error', _('We cannot have empty low ratio!'));
        }
        if (sql_query('INSERT INTO class_promo (name, min_ratio,uploaded,time,low_ratio) VALUES(' . sqlesc($name) . ', ' . sqlesc($min_ratio) . ', ' . sqlesc($uploaded) . ', ' . sqlesc($time) . ', ' . sqlesc($low_ratio) . ')')) {
            $session->set('is-success', _('user configuration was saved!'));
        } else {
            $session->set('is-error', _('There was an error while executing the update query or nothing was updated 2.'));
        }
    } elseif ($mode === 'remove') {
        if (isset($_POST['remove'])) {
            $name = htmlsafechars($_POST['remove']);
        } else {
            $session->set('is-error', _('Required Data Not Given!'));
        }
        if (sql_query('DELETE FROM class_promo WHERE name = ' . sqlesc($name))) {
            $session->set('is-success', _('Class Promo was deleted!'));
        } else {
            $session->set('is-error', 'There was an error while deleting the Class Promo.');
        }
    }
}

$res = sql_query('SELECT * FROM class_promo ORDER BY id');
if (mysqli_num_rows($res) >= 1) {
    $head_top = "
    <h3 class='has-text-centered top20'>" . _('User Promotion Settings') . "</h3>
    <form name='edit' action='{$site_config['paths']['baseurl']}/staffpanel.php?tool=class_promo&amp;mode=edit' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";

    $heading = "
        <tr>
            <th class='has-text-centered'>" . _('Class Name') . "</th>
            <th class='has-text-centered'>" . _('Min Ratio') . "</th>
            <th class='has-text-centered'>" . _('Min Uploaded (GB)') . "</th>
            <th class='has-text-centered'>" . _('Min Time On Site (Days)') . "</th>
            <th class='has-text-centered'>" . _('Low Ratio') . "</th>
            <th class='has-text-centered'>" . _('Remove') . '</th>
        </tr>';
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $body .= '
        <tr>
            <td>
                ' . get_user_class_name(constant($arr['name']), false) . "
                <input type='hidden' name='" . htmlsafechars($arr['name']) . "[]' value='" . htmlsafechars($arr['name']) . "'>
                <input type='hidden' name='" . htmlsafechars($arr['name']) . "[]' value='" . htmlsafechars($arr['id']) . "'>
            </td>
            <td class='has-text-centered'><input type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . format_comment($arr['min_ratio']) . "' class='has-text-centered'></td>
            <td class='has-text-centered'><input type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . format_comment($arr['uploaded']) . "' class='has-text-centered'></td>
            <td class='has-text-centered'><input type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . format_comment($arr['time']) . "' class='has-text-centered'></td>
            <td class='has-text-centered'><input type='text' name='" . htmlsafechars($arr['name']) . "[]' value='" . format_comment($arr['low_ratio']) . "' class='has-text-centered'></td>
            <td class='has-text-centered'>
                <form name='remove' action='staffpanel.php?tool=class_promo&amp;mode=remove' method='post' enctype='multipart/form-data' accept-charset='utf-8'>
                    <input type='hidden' name='remove' value='" . htmlsafechars($arr['name']) . "'>
                    <input type='submit' value='" . _('Remove') . "' class='button is-small'>
                </form>
            </td>
        </tr>";
    }

    $body .= "
        <tr>
            <td colspan='7' class='has-text-centered'>
                <input type='submit' value='" . _('Apply changes') . "' class='button is-small'>
            </td>
        </tr>";

    $HTMLOUT .= $head_top . main_table($body, $heading) . "
    </form>
    <div class='margin20 has-text-centered'>
        " . _('Min ratio = The minimum ratio a user needs to achieve to reach this class.') . '<br>
        ' . _('Min Uploaded = The minimum uploaded amount a user needs to achieve to reach this class.') . '<br>
        ' . _('Min Time On Site = The minimum time a user needs to of been registered with the site to reach this class.') . '<br>
        ' . _('Low Ratio = If a user in this class falls below this ratio, they will be demoted back to the previous class.') . '<br>
    </div>';
}

$HTMLOUT .= "
    <h3 class='has-text-centered top20'>" . _('ADD NEW PROMOTION RULE') . "</h3>
    <form name='add' action='staffpanel.php?tool=class_promo&amp;mode=add' method='post' enctype='multipart/form-data' accept-charset='utf-8'>";
$heading = "
        <tr>
            <th class='w-15'>" . _('Class Name') . '</th>
            <th>' . _('Min Ratio') . '</th>
            <th>' . _('Min Uploaded (GB)') . '</th>
            <th>' . _('Min Time On Site (Days)') . '</th>
            <th>' . _('Low Ratio') . '</th>
        </tr>';

$body = "
        <tr>
            <td>
                <select name='name'>";
$maxclass = UC_STAFF;
for ($i = 1; $i < $maxclass; ++$i) {
    $body .= "
                    <option value='$i'>" . get_user_class_name((int) $i) . '</option>';
}
$body .= "
                </select>
            </td>
            <td><input type='text' name='min_ratio' value='' class='w-100'></td>
            <td><input type='text' name='uploaded' value='' class='w-100'></td>
            <td><input type='text' name='time' value='' class='w-100'></td>
            <td><input type='text' name='low_ratio' value='' class='w-100'></td>
        </tr>
        <tr><td colspan='5' class='has-text-centered'>
                <input type='submit' value='" . _('Add new class') . "' class='button is-small'>
            </td>
        </tr>";
$HTMLOUT .= main_table($body, $heading) . '
    </form>';
$title = _('Promotion Settings');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
