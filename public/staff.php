<?php

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
check_user_status();
global $site_config, $fluent;

$image = placeholder_image();
$lang = array_merge(load_language('global'), load_language('staff'));
$support = $mods = $admin = $sysop = [];
$htmlout = $firstline = '';
$query = $fluent->from('users')
    ->select(null)
    ->select('users.id')
    ->select('users.class')
    ->select('users.perms')
    ->select('users.last_access')
    ->select('users.support')
    ->select('users.supportfor')
    ->select('users.country')
    ->select('countries.flagpic')
    ->select('countries.name as flagname')
    ->leftJoin('countries ON countries.id=users.country')
    ->whereOr([
        'users.class>= ?' => UC_STAFF,
        'users.support = ?' => 'yes',
    ])
    ->where('users.status = ?', 'confirmed')
    ->orderBy('class DESC')
    ->orderBy('username');

foreach ($query as $arr2) {
    if ($arr2['support'] === 'yes') {
        $support[] = $arr2;
    } else {
        $staffs[strtolower($site_config['class_names'][$arr2['class']])][] = $arr2;
    }
}

/**
 * @param $staff_array
 * @param $staffclass
 *
 * @return string|null
 */
function DoStaff($staff_array, $staffclass)
{
    global $site_config;

    $image = placeholder_image();
    if (empty($staff_array)) {
        return null;
    }
    $htmlout = $body = '';
    $dt = TIME_NOW - 180;

    $htmlout .= "
                <h2 class='left10 top20'>{$staffclass}</h2>";
    foreach ($staff_array as $staff) {
        $body .= '
                    <tr>';
        $flagpic = !empty($staff['flagpic']) ? "{$site_config['paths']['images_baseurl']}flag/{$staff['flagpic']}" : '';
        $flagname = !empty($staff['flagname']) ? $staff['flagname'] : '';
        $body .= '
                        <td>' . format_username($staff['id']) . "</td>
                        <td><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}" . ($staff['last_access'] > $dt && $staff['perms'] < bt_options::PERMS_STEALTH ? 'online.png' : 'offline.png') . "' alt='' class='emoticon lazy'></td>" . "
                        <td><a href='{$site_config['paths']['baseurl']}/messages.php?action=send_message&amp;receiver=" . (int) $staff['id'] . '&amp;returnto=' . urlencode($_SERVER['REQUEST_URI']) . "'><i class='icon-mail icon tooltipper' aria-hidden='true' title='Personal Message'></i></a></td>" . "
                        <td><img src='{$image}' data-src='$flagpic' alt='" . htmlsafechars($flagname) . "' class='emoticon lazy'></td>
                    </tr>";
    }

    return $htmlout . main_table($body);
}

foreach ($staffs as $key => $value) {
    if (!empty($key)) {
        $htmlout .= DoStaff($value, ucfirst($key) . 's');
    }
}

$dt = TIME_NOW - 180;
if (!empty($support)) {
    $body = '';
    foreach ($support as $a) {
        $flagpic = !empty($staff['flagpic']) ? "{$site_config['paths']['images_baseurl']}flag/{$staff['flagpic']}" : '';
        $flagname = !empty($staff['flagname']) ? $staff['flagname'] : '';
        $body .= '
                <tr>
                    <td>' . format_username($a['id']) . "</td>
                    <td><img src='{$image}' data-src='{$site_config['paths']['images_baseurl']}" . ($a['last_access'] > $dt ? 'online.png' : 'offline.png') . "' alt='' class='emoticon lazy'></td>
                    <td><a href='{$site_config['paths']['baseurl']}messages.php?action=send_message&amp;receiver=" . (int) $a['id'] . "'><i class='icon-mail icon tooltipper' aria-hidden='true' title='{$lang['alt_pm']}'></i></a></td>
                    <td><img src='{$image}' data-src='$flagpic' alt='" . htmlsafechars($flagname) . "' class='emoticon lazy'></td>
                    <td>" . htmlsafechars($a['supportfor']) . '</td>
                </tr>';
    }
    $htmlout .= "
            <h2 class='left10 top20'>{$lang['header_fls']}</h2>";
    $heading = "
                    <tr>
                        <th class='staff_username' colspan='5'>{$lang['text_first']}<br><br></th>
                    </tr>
                    <tr>
                        <th class='staff_username'>{$lang['first_name']}</th>
                        <th>{$lang['first_active']}</th>
                        <th>{$lang['first_contact']}</th>
                        <th>{$lang['first_lang']}</th>
                        <th>{$lang['first_supportfor']}</th>
                    </tr>";
    $htmlout .= main_table($body, $heading);
}
echo stdhead('Staff') . wrapper($htmlout) . stdfoot();
