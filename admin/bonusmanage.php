<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('bonusmanager'));
global $site_config;

$HTMLOUT = $count = '';
$res = sql_query('SELECT * FROM bonus ORDER BY orderid, bonusname') or sqlerr(__FILE__, __LINE__);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) || isset($_POST['orderid']) || isset($_POST['points']) || isset($_POST['pointspool']) || isset($_POST['minpoints']) || isset($_POST['description']) || isset($_POST['enabled']) || isset($_POST['minclass'])) {
        $id = (int) $_POST['id'];
        $points = (int) $_POST['bonuspoints'];
        $pointspool = (int) $_POST['pointspool'];
        $minpoints = (int) $_POST['minpoints'];
        $minclass = (int) $_POST['minclass'];
        $descr = htmlsafechars($_POST['description']);
        $enabled = 'yes';
        if (empty($_POST['enabled'])) {
            $enabled = 'no';
        }
        $orderid = (int) $_POST['orderid'];
        $cache->delete('bonus_points_' . $id);
        $cache->delete('freeleech_alerts_');
        $cache->delete('doubleupload_alerts_');
        $cache->delete('halfdownload_alerts_');
        $sql = sql_query('UPDATE bonus SET orderid=' . sqlesc($orderid) . ', points = ' . sqlesc($points) . ', pointspool = ' . sqlesc($pointspool) . ', minpoints = ' . sqlesc($minpoints) . ', minclass = ' . sqlesc($minclass) . ', enabled = ' . sqlesc($enabled) . ', description = ' . sqlesc($descr) . ' WHERE id=' . sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        sql_query("UPDATE bonus SET orderid=orderid + 1 WHERE orderid>= $orderid AND id != $id") or sqlerr(__FILE__, __LINE__);

        $query = sql_query('SELECT id FROM bonus ORDER BY orderid, id');
        $iter = 0;
        while ($arr = mysqli_fetch_assoc($query)) {
            sql_query('UPDATE bonus SET orderid=' . ++$iter . ' WHERE id=' . $arr['id']) or sqlerr(__FILE__, __LINE__);
        }

        if ($sql) {
            header("Location: {$_SERVER['PHP_SELF']}?tool=bonusmanage");
            die();
        } else {
            stderr($lang['bonusmanager_oops'], $lang['bonusmanager_sql']);
        }
    }
}

$heading = "
        <tr>
            <th>{$lang['bonusmanager_id']}</th>
            <th>{$lang['bonusmanager_order_id']}</th>
            <th class='tooltipper' title='{$lang['bonusmanager_enabled']}'>E</th>
            <th>{$lang['bonusmanager_bonus']}</th>
            <th>{$lang['bonusmanager_points']}</th>
            <th>{$lang['bonusmanager_pointspool']}</th>
            <th>{$lang['bonusmanager_minpoints']}</th>
            <th>{$lang['bonusmanager_minclass']}</th>
            <th class='w-20'>{$lang['bonusmanager_description']}</th>
            <th>{$lang['bonusmanager_type']}</th>
            <th>{$lang['bonusmanager_quantity']}</th>
            <th>{$lang['bonusmanager_action']}</th>
        </tr>";

$HTMLOUT = "
    <h1 class='has-text-centered'>{$lang['bonusmanager_bm']}</h1>";

$body = '';
while ($arr = mysqli_fetch_assoc($res)) {
    $body .= "
        <tr>
            <form name='bonusmanage' method='post' action='{$_SERVER['PHP_SELF']}?tool=bonusmanage&amp;action=bonusmanage' accept-charset='utf-8'>
                <td><input name='id' type='hidden' value='" . (int) $arr['id'] . "'>" . (int) $arr['id'] . "</td>
                <td><input type='number' name='orderid' value='" . (int) $arr['orderid'] . "' class='w-100'></td>
                <td><input name='enabled' type='checkbox'" . ($arr['enabled'] === 'yes' ? ' checked' : '') . '></td>
                <td>' . htmlsafechars($arr['bonusname']) . "</td>
                <td><input type='number' name='bonuspoints' value='" . (int) $arr['points'] . "' class='w-100'></td>
                <td><input type='number' name='pointspool' value='" . (int) $arr['pointspool'] . "' class='w-100'></td>
                <td><input type='number' name='minpoints' value='" . (int) $arr['minpoints'] . "' class='w-100'></td>
                <td><input type='number' name='minclass' value='" . (int) $arr['minclass'] . "' class='w-100'></td>
                <td><textarea name='description' rows='4' class='w-100'>" . htmlsafechars($arr['description']) . '</textarea></td>
                <td>' . htmlsafechars($arr['art']) . '</td>
                <td>' . (($arr['art'] === 'traffic' || $arr['art'] === 'traffic2' || $arr['art'] === 'gift_1' || $arr['art'] === 'gift_2') ? (htmlsafechars($arr['menge']) / 1024 / 1024 / 1024) . ' GB' : htmlsafechars($arr['menge'])) . "</td>
                <td><input class='button is-small' type='submit' value='{$lang['bonusmanager_submit']}'></td>
            </form>
        </tr>";
}

$HTMLOUT .= main_table($body, $heading);
echo stdhead($lang['bonusmanager_stdhead']) . wrapper($HTMLOUT) . stdfoot();
