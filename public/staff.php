<?php
require_once dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'bittorrent.php';
require_once INCL_DIR . 'user_functions.php';
require_once INCL_DIR . 'html_functions.php';
check_user_status();
global $site_config;

$lang = array_merge(load_language('global'), load_language('staff'));
$stdhead = [
    'css' => [
        get_file_name('staff_css'),
    ],
];
$support = $mods = $admin = $sysop = [];
$htmlout = $firstline = $support = '';
$query = sql_query('SELECT u.id, u.perms, u.username, u.support, u.supportfor, u.email, u.last_access, u.class, u.title, u.country, u.status, countries.flagpic, countries.name FROM users AS u LEFT  JOIN countries ON countries.id = u.country WHERE u.class >= ' . UC_STAFF . " OR u.support='yes' AND u.status='confirmed' ORDER BY username") or sqlerr(__FILE__, __LINE__);
unset($support);
while ($arr2 = mysqli_fetch_assoc($query)) {
    if ($arr2['support'] == 'yes') {
        $support[] = $arr2;
    }
    if ($arr2['class'] == UC_MODERATOR) {
        $mods[] = $arr2;
    }
    if ($arr2['class'] == UC_ADMINISTRATOR) {
        $admin[] = $arr2;
    }
    if ($arr2['class'] == UC_SYSOP) {
        $sysop[] = $arr2;
    }
}
/**
 * @param     $staff
 * @param     $staffclass
 * @param int $cols
 *
 * @return string
 */
function DoStaff($staff, $staffclass, $cols = 2)
{
    global $site_config;
    $htmlout = '';
    $dt = TIME_NOW - 180;
    $counter = count($staff);
    $rows = ceil($counter / $cols);
    $cols = ($counter < $cols) ? $counter : $cols;
    $r = 0;
    $htmlout .= "
            <div class='global_text'>
                <h2 class='left10 top20'>{$staffclass}</h2>
                <table class='table table-bordered table-striped'>";
    for ($ia = 0; $ia < $rows; ++$ia) {
        $htmlout .= '
                    <tr>';
        for ($i = 0; $i < $cols; ++$i) {
            if (isset($staff[$r])) {
                $htmlout .= "
                        <td class='staff_username'>" . format_username($staff[$r]['id']) . "</td>
                        <td class='staff_online'><img src='{$site_config['pic_baseurl']}staff" . ($staff[$r]['last_access'] > $dt && $staff[$r]['perms'] < bt_options::PERMS_STEALTH ? '/online.png' : '/offline.png') . "' border='0' height='16' alt='' /></td>" . "
                        <td class='staff_online'><a href='{$site_config['baseurl']}/pm_system.php?action=send_message&amp;receiver=" . (int)$staff[$r]['id'] . '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) . "'><img src='{$site_config['pic_baseurl']}mailicon.png' border='0' class='tooltipper' title='Personal Message' alt='' /></a></td>" . "
                        <td class='staff_online'><img height='16' src='{$site_config['pic_baseurl']}flag/" . htmlsafechars($staff[$r]['flagpic']) . "' border='0' alt='" . htmlsafechars($staff[$r]['name']) . "' /></td>";
                ++$r;
            } else {
                $htmlout .= '<td></td>';
            }
        }
        $htmlout .= '</tr>';
    }
    $htmlout .= '</table></div>';

    return $htmlout;
}

$htmlout .= DoStaff($sysop, 'Sysops');
$htmlout .= isset($admin) ? DoStaff($admin, 'Administrator') : DoStaff($admin = false, 'Administrator');
$htmlout .= isset($mods) ? DoStaff($mods, 'Moderators') : DoStaff($mods = false, 'Moderators');
$dt = TIME_NOW - 180;
if (!empty($support)) {
    foreach ($support as $a) {
        $firstline .= "
                <tr>
                    <td class='staff_username'>" . format_username($a['id']) . "</td>
                    <td class='staff_online'><img src='{$site_config['pic_baseurl']}" . ($a['last_access'] > $dt ? 'online.png' : 'offline.png') . "' alt='' /></td>
                    <td class='staff_online'><a href='{$site_config['baseurl']}pm_system.php?action=send_message&amp;receiver=" . (int)$a['id'] . "'><img src='{$site_config['pic_baseurl']}mailicon.png' class='tooltipper' title='{$lang['alt_pm']}' alt='' /></a></td>
                    <td class='staff_online'><img src='{$site_config['pic_baseurl']}flag/" . htmlsafechars($a['flagpic']) . "' alt='" . htmlsafechars($a['name']) . "' /></td>
                    <td class='staff_online'>" . htmlsafechars($a['supportfor']) . '</td>
                </tr>';
    }
    $htmlout .= "
        <div class='global_text'>
            <h2 class='left10 top20'>{$lang['header_fls']}</h2>
            <table class='table table-bordered table-striped'>
                <thead>
                    <tr>
                        <th class='staff_username' colspan='5'>{$lang['text_first']}<br><br></th>
                    </tr>
                    <tr>
                        <th class='staff_username'>{$lang['first_name']}</th>
                        <th class='staff_online'>{$lang['first_active']}</th>
                        <th class='staff_online'>{$lang['first_contact']}</th>
                        <th class='staff_online'>{$lang['first_lang']}</th>
                        <th class='staff_online'>{$lang['first_supportfor']}</th>
                    </tr>
                </thead>
                <tbody>
                    $firstline
                </tbody>
            </table>
        </div>";
}
echo stdhead('Staff', true, $stdhead) . wrapper($htmlout) . stdfoot();
