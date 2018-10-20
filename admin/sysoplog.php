<?php

require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'bbcode_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
global $lang;

$lang = array_merge($lang, load_language('ad_sysoplog'));
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
sql_query('DELETE FROM infolog WHERE ' . TIME_NOW . " - added > $secs") or sqlerr(__FILE__, __LINE__);
$res = sql_query("SELECT COUNT(id) FROM infolog $where");
$row = mysqli_fetch_array($res);
$count = $row[0];
$perpage = 15;
$pager = pager($perpage, $count, 'staffpanel.php?tool=sysoplog&amp;action=sysoplog&amp;' . (!empty($search) ? "search=$search&amp;" : '') . '');
$HTMLOUT = '';
$res = sql_query("SELECT added, txt FROM infolog $where ORDER BY added DESC {$pager['limit']}") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= "
    <h1 class='has-text-centered'>{$lang['sysoplog_staff']}</h1>
    <div class='has-text-centered'>
        <form method='post' action='{$site_config['baseurl']}/staffpanel.php?tool=sysoplog&amp;action=sysoplog'>
            <input type='text' name='search' size='40' value='' placeholder='{$lang['sysoplog_search']}'>
            <input type='submit' value='{$lang['sysoplog_search']}' class='button is-small'>
        </form>
    </div>";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= main_div($lang['sysoplog_norecord']);
} else {
    $heading = "
      <tr>
          <th>{$lang['sysoplog_date']}</th>
          <th>{$lang['sysoplog_time']}</th>
          <th>{$lang['sysoplog_event']}</th>
      </tr>";
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $color = random_color();
        $date = get_date($arr['added'], 'DATE');
        $time = get_date($arr['added'], 'LONG', 0, 1);
        $body .= "
        <tr>
            <td style='background-color: $color;'>
                {$date}
            </td>
            <td style='background-color: $color;'>
                {$time}
            </td>
            <td style='background-color: $color;'>
                {$arr['txt']}
            </td>
        </tr>";
    }
    $HTMLOUT .= main_table($body, $heading);
}

if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$HTMLOUT .= "<p>{$lang['sysoplog_times']}</p>\n";
echo stdhead($lang['sysoplog_sys']) . wrapper($HTMLOUT) . stdfoot();
