<?php
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'pager_functions.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
$lang = array_merge($lang, load_language('ad_log'));
$txt = $where = '';
$search = isset($_POST['search']) ? strip_tags($_POST['search']) : '';
if (isset($_GET['search'])) {
    $search = strip_tags($_GET['search']);
}
if (!empty($search)) {
    $where = 'WHERE txt LIKE ' . sqlesc("%$search%") . '';
}
// delete items older than 1 month
$secs = TIME_NOW - (30 * 86400);
sql_query("DELETE FROM sitelog WHERE added < $secs") or sqlerr(__FILE__, __LINE__);
$resx = sql_query("SELECT COUNT(*) FROM sitelog $where");
$rowx = mysqli_fetch_array($resx, MYSQLI_NUM);
$count = $rowx[0];
$perpage = 30;
$pager = pager($perpage, $count, 'staffpanel.php?tool=sitelog&amp;action=sitelog&amp;' . (!empty($search) ? "search=$search&amp;" : '') . '');
$HTMLOUT = '';
$res = sql_query("SELECT added, txt FROM sitelog $where ORDER BY added DESC {$pager['limit']} ") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= "
    <h1 class='text-center'>{$lang['text_sitelog']}</h1>
    <div class='container-fluid portlet'>
        <div class='text-center bordered top20'>
            <h2>{$lang['log_search']}</h2>
            <form method='post' action='./staffpanel.php?tool=sitelog&amp;action=sitelog'>
                <input type='text' name='search' class='w-50' value='' />
                <input type='submit' class='btn' value='{$lang['log_search_btn']}' />
            </form>
        </div>";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= "<b>{$lang['text_logempty']}</b>";
} else {
    $HTMLOUT .= "
        <table class='table table-bordered bottom20'>
            <thead>
                <tr>
                    <th>{$lang['header_date']}</th>
                    <th>{$lang['header_event']}</th>
                </tr>
            </thead>
            <tbody>";
    $log_events = [];
    $colors = [];
    while ($arr = mysqli_fetch_assoc($res)) {
        $txt = substr($arr['txt'], 0, 20);
        if (!in_array($txt, $log_events)) {
            $color = random_color(100, 200);
            while (in_array($color, $colors)) {
                $color = random_color(100, 200);

            }
            $log_events[] = $txt;
            $colors[] = $color;
        }
        $key = array_search($txt, $log_events);
        $color = $colors[$key];

        $date = explode(',', get_date($arr['added'], 'LONG'));
        $HTMLOUT .= "
                <tr class='table'>
                    <td style='background-color:$color'>
                        <span class='tex-black'>{$date[0]}{$date[1]}</span>
                    </td>
                    <td style='background-color:$color'>
                        <span class='text-black'>" . $arr['txt'] . "</span>
                    </td>
                </tr>";
    }
    $HTMLOUT .= "
            </tbody>
        </table>
    </div>";
}
$HTMLOUT .= "<p>{$lang['text_times']}</p>";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
echo stdhead("{$lang['stdhead_log']}") . $HTMLOUT . stdfoot();
