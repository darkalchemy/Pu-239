<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once INCL_DIR . 'function_pager.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_sysoplog'));
global $site_config;

$HTMLOUT = $where = '';
$search = isset($_POST['search']) ? strip_tags($_POST['search']) : '';
if (isset($_GET['search'])) {
    $search = strip_tags($_GET['search']);
}
if (!empty($search)) {
    $where = 'WHERE txt LIKE ' . sqlesc("%$search%") . '';
}
//== Delete items older than 1 month
$secs = 30 * 86400;
sql_query('DELETE FROM infolog WHERE ' . TIME_NOW . " - added>$secs") or sqlerr(__FILE__, __LINE__);
$res = sql_query("SELECT COUNT(id) FROM infolog $where");
$row = mysqli_fetch_array($res);
$count = (int) $row[0];
$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=sysoplog&amp;action=sysoplog&amp;' . (!empty($search) ? "search=$search&amp;" : '') . '');
$HTMLOUT = '';
$res = sql_query("SELECT added, txt FROM infolog $where ORDER BY added DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['sysoplog_staff']}</h1>
    <div class='has-text-centered bottom20'>
        <form method='post' action='{$_SERVER['PHP_SELF']}?tool=sysoplog&amp;action=sysoplog' enctype='multipart/form-data' accept-charset='utf-8'>
            <input type='text' name='search' size='40' value='' placeholder='{$lang['sysoplog_search']}'>
            <input type='submit' value='{$lang['sysoplog_search']}' class='button is-small'>
        </form>
    </div>";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= main_div("<div class='padding20'>{$lang['sysoplog_norecord']}</div>");
} else {
    $heading = "
      <tr>
          <th>{$lang['sysoplog_date']}</th>
          <th>{$lang['sysoplog_time']}</th>
          <th>{$lang['sysoplog_event']}</th>
      </tr>";
    $body = '';
    $log_events = [];
    $colors = [];
    while ($arr = mysqli_fetch_assoc($res)) {
        $txt = substr($arr['txt'], 0, 50);
        if (!in_array($txt, $log_events)) {
            $color = random_color();
            while (in_array($color, $colors)) {
                $color = random_color();
            }
            $log_events[] = $txt;
            $colors[] = $color;
        }
        $key = array_search($txt, $log_events);
        $color = $colors[$key];
        $date = get_date((int) $arr['added'], 'DATE');
        $time = get_date((int) $arr['added'], 'LONG', 0, 1);
        $body .= "
        <tr>
            <td style='background-color: $color;'>
                <span class='has-text-black'>{$date}</span>
            </td>
            <td style='background-color: $color;'>
                <span class='has-text-black'>{$time}</span>
            </td>
            <td style='background-color: $color;'>
                <span class='has-text-black'>{$arr['txt']}</span>
            </td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading);
}

if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead($lang['sysoplog_sys']) . wrapper($HTMLOUT) . stdfoot();
