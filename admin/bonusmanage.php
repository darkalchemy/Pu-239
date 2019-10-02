<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
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
            stderr(_('Oops'), _('Something went wrong with the sql query'));
        }
    }
}

$heading = '
        <tr>
            <th>' . _('Id') . '</th>
            <th>' . _('Order Id') . "</th>
            <th class='tooltipper' title='" . _('Enabled') . "'>E</th>
            <th>" . _('Bonus') . '</th>
            <th>' . _('Points') . '</th>
            <th>' . _('Points Pool') . '</th>
            <th>' . _('Min Points') . '</th>
            <th>' . _('Min Class') . "</th>
            <th class='w-20'>" . _('Description') . '</th>
            <th>' . _('Type') . '</th>
            <th>' . _('Quantity') . '</th>
            <th>' . _('Action') . '</th>
        </tr>';

$HTMLOUT = "
    <h1 class='has-text-centered'>" . _('Bonus Management') . '</h1>';

$body = '';
while ($arr = mysqli_fetch_assoc($res)) {
    $body .= "
        <tr>
            <form name='bonusmanage' method='post' action='{$_SERVER['PHP_SELF']}?tool=bonusmanage&amp;action=bonusmanage' enctype='multipart/form-data' accept-charset='utf-8'>
                <td><input name='id' type='hidden' value='" . (int) $arr['id'] . "'>" . (int) $arr['id'] . "</td>
                <td><input type='number' name='orderid' value='" . (int) $arr['orderid'] . "' class='w-100'></td>
                <td><input name='enabled' type='checkbox' " . ($arr['enabled'] === 'yes' ? 'checked' : '') . '></td>
                <td>' . format_comment($arr['bonusname']) . "</td>
                <td><input type='number' name='bonuspoints' value='" . (int) $arr['points'] . "' class='w-100'></td>
                <td><input type='number' name='pointspool' value='" . (int) $arr['pointspool'] . "' class='w-100'></td>
                <td><input type='number' name='minpoints' value='" . (int) $arr['minpoints'] . "' class='w-100'></td>
                <td><input type='number' name='minclass' value='" . (int) $arr['minclass'] . "' class='w-100'></td>
                <td><textarea name='description' rows='4' class='w-100'>" . format_comment($arr['description']) . '</textarea></td>
                <td>' . format_comment($arr['art']) . '</td>
                <td>' . (($arr['art'] === 'traffic' || $arr['art'] === 'traffic2' || $arr['art'] === 'gift_1' || $arr['art'] === 'gift_2') ? (htmlsafechars($arr['menge']) / 1024 / 1024 / 1024) . ' GB' : htmlsafechars($arr['menge'])) . "</td>
                <td><input class='button is-small' type='submit' value='" . _('Submit') . "'></td>
            </form>
        </tr>";
}

$HTMLOUT .= main_table($body, $heading);
$title = _('Bonus Manager');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
