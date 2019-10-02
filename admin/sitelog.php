<?php

declare(strict_types = 1);

require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_pager.php';
require_once INCL_DIR . 'function_bbcode.php';
require_once CLASS_DIR . 'class_check.php';
$class = get_access(basename($_SERVER['REQUEST_URI']));
class_check($class);
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
$resx = sql_query("SELECT COUNT(id) FROM sitelog $where");
$rowx = mysqli_fetch_array($resx, MYSQLI_NUM);
$count = (int) $rowx[0];
$perpage = 30;
$pager = pager($perpage, $count, 'staffpanel.php?tool=sitelog&amp;action=sitelog&amp;' . (!empty($search) ? "search=$search&amp;" : '') . '');
$HTMLOUT = '';
$res = sql_query("SELECT added, txt FROM sitelog $where ORDER BY added DESC {$pager['limit']} ") or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= "
        <h1 class='has-text-centered'>" . _('Site log') . "</h1>
        <div class='has-text-centered margin20'>
            <h2>" . _('Search Log') . "</h2>
            <form method='post' action='./staffpanel.php?tool=sitelog&amp;action=sitelog' enctype='multipart/form-data' accept-charset='utf-8'>
                <input type='text' name='search' class='w-50' value=''>
                <input type='submit' class='button is-small' value='" . _('Search') . "'>
            </form>
        </div>";
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagertop'];
}
if (mysqli_num_rows($res) == 0) {
    $HTMLOUT .= main_div(_('Log is empty'), '', 'has-text-centered padding20');
} else {
    $heading = '
                <tr>
                    <th>' . _('Date') . '</th>
                    <th>' . _('Event') . '</th>
                </tr>';
    $log_events = [];
    $colors = [];
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $txt = substr($arr['txt'], 0, 50);
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

        $date = get_date((int) $arr['added'], 'LONG', 0, 1);

        $body .= "
                <tr class='table'>
                    <td style='background-color: {$color};'>
                        <span class='has-text-black'>{$date}</span>
                    </td>
                    <td style='background-color: {$color};'>
                        <span class='has-text-black'>" . format_comment($arr['txt']) . '</span>
                    </td>
                </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);
}
if ($count > $perpage) {
    $HTMLOUT .= $pager['pagerbottom'];
}
$title = _('Site Log');
$breadcrumbs = [
    "<a href='{$site_config['paths']['baseurl']}/staffpanel.php'>" . _('Staff Panel') . '</a>',
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT) . stdfoot();
