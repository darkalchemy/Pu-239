<?php
if (!defined('IN_site_config_ADMIN')) {
    setSessionVar('error', 'Access Not Allowed');
    header("Location: {$site_config['baseurl']}/index.php");
    exit();
}
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('bonusmanager'));
$HTMLOUT = $count = '';
$res = sql_query('SELECT * FROM bonus ORDER BY orderid, bonusname') or sqlerr(__FILE__, __LINE__);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id']) || isset($_POST['orderid']) || isset($_POST['points']) || isset($_POST['pointspool']) || isset($_POST['minpoints']) || isset($_POST['description']) || isset($_POST['enabled']) || isset($_POST['minclass'])) {
        $id = (int)$_POST['id'];
        $points = (int)$_POST['bonuspoints'];
        $pointspool = (int)$_POST['pointspool'];
        $minpoints = (int)$_POST['minpoints'];
        $minclass = (int)$_POST['minclass'];
        $descr = htmlsafechars($_POST['description']);
        $enabled = 'yes';
        if (isset($_POST['enabled']) == '') {
            $enabled = 'no';
        }
        $orderid = (int) $_POST['orderid'];
        $sql = sql_query('UPDATE bonus SET orderid = '.sqlesc($orderid).', points = '.sqlesc($points).', pointspool='.sqlesc($pointspool).', minpoints='.sqlesc($minpoints).', minclass='.sqlesc($minclass).', enabled = '.sqlesc($enabled).', description = '.sqlesc($descr).' WHERE id = '.sqlesc($id)) or sqlerr(__FILE__, __LINE__);
        sql_query("UPDATE bonus SET orderid = orderid + 1 WHERE orderid >= $orderid AND id != $id") or sqlerr(__FILE__, __LINE__);

        $query = sql_query('SELECT id FROM bonus ORDER BY orderid, id');
        $iter = 0;
        while ($arr = mysqli_fetch_assoc($query)) {
            sql_query('UPDATE bonus SET orderid = '.++$iter.' WHERE id = '.$arr['id']) or sqlerr(__FILE__, __LINE__);
        }

        if ($sql) {
            header("Location: {$site_config['baseurl']}/staffpanel.php?tool=bonusmanage");
        } else {
            stderr($lang['bonusmanager_oops'], "{$lang['bonusmanager_sql']}");
        }
    }
}
while ($arr = mysqli_fetch_assoc($res)) {
    $count = (++$count) % 2;
    $class = ($count == 0 ? 'one' : 'two');
    $HTMLOUT .= "<form name='bonusmanage' method='post' action='staffpanel.php?tool=bonusmanage&amp;action=bonusmanage'>
	  <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div class='colhead'><span style='font-weight:bold;font-size:12pt;'>{$lang['bonusmanager_bm']}</span></div>
	  <table width='100%' border='2' cellpadding='8'>
	  <tr>
		<td class='colhead'>{$lang['bonusmanager_id']}</td>
		<td class='colhead'>{$lang['bonusmanager_order_id']}</td>
		<td class='colhead'>{$lang['bonusmanager_enabled']}</td>
		<td class='colhead'>{$lang['bonusmanager_bonus']}</td>
		<td class='colhead'>{$lang['bonusmanager_points']}</td>
		<td class='colhead'>{$lang['bonusmanager_pointspool']}</td>
		<td class='colhead'>{$lang['bonusmanager_minpoints']}</td>
		<td class='colhead'>{$lang['bonusmanager_minclass']}</td>
		<td class='colhead'>{$lang['bonusmanager_description']}</td>
		<td class='colhead'>{$lang['bonusmanager_type']}</td>
		<td class='colhead'>{$lang['bonusmanager_quantity']}</td>
		<td class='colhead'>{$lang['bonusmanager_action']}</td></tr> 
	  <tr><td class='$class'>
		<input name='id' type='hidden' value='" . (int)$arr['id'] . "' />" . (int)$arr['id'] . "</td>
		<td class='$class'><input type='text' name='orderid' value='".(int) $arr['orderid']."' size='4' /></td>
		<td class='$class'><input name='enabled' type='checkbox'" . ($arr['enabled'] == 'yes' ? " checked='checked'" : '') . " /></td>
		<td class='$class'>" . htmlsafechars($arr['bonusname']) . "</td>
		<td class='$class'><input type='text' name='bonuspoints' value='" . (int)$arr['points'] . "' size='4' /></td>
		<td class='$class'><input type='text' name='pointspool' value='" . (int)$arr['pointspool'] . "' size='4' /></td>
		<td class='$class'><input type='text' name='minpoints' value='" . (int)$arr['minpoints'] . "' size='4' /></td>
		<td class='$class'><input type='text' name='minclass' value='".(int) $arr['minclass']."' size='4' /></td>
		<td class='$class'><textarea name='description' rows='4' cols='10'>" . htmlsafechars($arr['description']) . "</textarea></td>
		<td class='$class'>" . htmlsafechars($arr['art']) . "</td>
		<td class='$class'>" . (($arr['art'] == 'traffic' || $arr['art'] == 'traffic2' || $arr['art'] == 'gift_1' || $arr['art'] == 'gift_2') ? (htmlsafechars($arr['menge']) / 1024 / 1024 / 1024) . ' GB' : htmlsafechars($arr['menge'])) . "</td>
		<td align='center'><input type='submit' value='{$lang['bonusmanager_submit']}' /></td>
		</tr></table></div></form>";
}
echo stdhead($lang['bonusmanager_stdhead']) . $HTMLOUT . stdfoot();
