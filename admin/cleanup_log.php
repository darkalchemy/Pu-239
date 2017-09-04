<?php
if (!defined('IN_INSTALLER09_ADMIN')) {
    setSessionVar('error', 'Access Not Allowed');
    header("Location: {$INSTALLER09['baseurl']}/index.php");
    exit();
}
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_cleanlog'));
$txt = $where = '';
$search = isset($_POST['search']) ? strip_tags($_POST['search']) : '';
if (isset($_GET['search'])) {
    $search = strip_tags($_GET['search']);
}
if (!empty($search)) {
    $where = 'WHERE clog_event LIKE ' . sqlesc("%$search%") . '';
}
// delete items older than 1 month
$secs = TIME_NOW - (30 * 86400);
sql_query("DELETE FROM cleanup_log WHERE clog_time < $secs") or sqlerr(__FILE__, __LINE__);
$resx = sql_query("SELECT COUNT(*) FROM cleanup_log $where");
$rowx = mysqli_fetch_array($resx, MYSQLI_NUM);
$count = $rowx[0];
$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=cleanup_log&amp;action=cleanup_log&amp;' . (!empty($search) ? "search=$search&amp;" : '') . '');
$HTMLOUT = '';
$res = sql_query("SELECT clog_time, clog_event, clog_desc FROM cleanup_log $where ORDER BY clog_time DESC {$pager['limit']} ") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= "
    <h1>{$lang['text_cleanup_log']}</h1>
    <table border='1' cellspacing='0' width='115' cellpadding='5'>
        <tr>
		    <td class='tabletitle' align='left'>{$lang['log_search']}</td>
		</tr>
        <tr>
            <td class='table' align='left'>
                <form method='post' action='staffpanel.php?tool=cleanup_log&amp;action=cleanup_log'>
                    <input type='text' name='search' size='40' value='' />
                    <input type='submit' value='{$lang['log_search_btn']}' style='height: 20px' />
                </form>
            </td>
        </tr>
    </table>";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= "
    <b>{$lang['text_logempty']}</b>";
} else {
    $HTMLOUT .= "
    <table class='table table-bordered' border='1' cellspacing='0' cellpadding='5'>
        <tr>
            <td class='colhead' align='left'>{$lang['header_date']}</td>
            <td class='colhead' align='left'>{$lang['header_event']}</td>
            <td class='colhead' align='left'>{$lang['header_text']}</td>
        </tr>";
    $clog_events = [];
    $colors = [];
    while ($arr = mysqli_fetch_assoc($res)) {
        if (!in_array($arr['clog_event'], $clog_events)) {
            $color = random_color(100, 200);
            while (in_array($color, $colors)) {
                $color = random_color(100, 200);

            }
            $clog_events[] = $arr['clog_event'];
            $colors[] = $color;
        }
        $key = array_search($arr['clog_event'], $clog_events);
        $color = $colors[$key];
        $date = explode(',', get_date($arr['clog_time'], 'LONG'));
        $HTMLOUT .= "
        <tr class='table'>
            <td style='background-color: $color'>
                <font color='black'>{$date[0]}{$date[1]}</font>
            </td>
            <td style='background-color: $color' align='left'>
                <font color='black'>" . $arr['clog_event'] . "</font>
            </td>
            <td style='background-color: $color' align='left'>
                <font color='black'>" . $arr['clog_desc'] . "</font>
            </td>
        </tr>";
    }
    $HTMLOUT .= "
    </table>";
}
$HTMLOUT .= "
    <p>{$lang['text_times']}</p>";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead("{$lang['stdhead_log']}") . $HTMLOUT . stdfoot();
