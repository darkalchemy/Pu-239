<?php

declare(strict_types = 1);

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $site_config;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user['class'] >= UC_MAX) {
    $clienticon = htmlsafechars(trim($_POST['clienticon']));
    $achievname = htmlsafechars(trim($_POST['achievname']));
    $notes = htmlsafechars($_POST['notes']);
    $clienticon = htmlsafechars($clienticon);
    $achievname = htmlsafechars($achievname);
    sql_query('INSERT INTO achievementist (achievname, notes, clienticon) VALUES(' . sqlesc($achievname) . ', ' . sqlesc($notes) . ', ' . sqlesc($clienticon) . ')') or sqlerr(__FILE__, __LINE__);
    $message = '' . _('A New achievment has been added') . '. ' . _('Achievement') . ": [{$achievname}]";
    //autoshout($message);
    //$doUpdate = true;
}
$res = sql_query('SELECT a1.*, (SELECT COUNT(a2.id) FROM achievements AS a2 WHERE a2.achievement = a1.achievname) AS count FROM achievementist AS a1 ORDER BY a1.id') or sqlerr(__FILE__, __LINE__);
$HTMLOUT = '';
$HTMLOUT .= '<h1>' . _('Achievements List') . "</h1>\n";
if (mysqli_num_rows($res) === 0) {
    $HTMLOUT .= '<p><b>' . _('There are currently no achievements added to the list') . '!<br>' . _('staff has been slacking') . "!</b></p>\n";
} else {
    $heading = '
            <tr>
                <th>' . _('Achievement Name') . '</th>
                <th>' . _('Description') . '</th>
                <th>' . _('Earned') . '</th>
            </tr>';
    $body = '';
    while ($arr = mysqli_fetch_assoc($res)) {
        $notes = htmlsafechars($arr['notes']);
        $count = (int) $arr['count'];
        $clienticon = '';
        if ($arr['clienticon'] != '') {
            $clienticon = "<img src='" . $site_config['paths']['images_baseurl'] . 'achievements/' . htmlsafechars($arr['clienticon']) . "' title='" . htmlsafechars($arr['achievname']) . "' alt='" . htmlsafechars($arr['achievname']) . "'>";
        }
        $body .= "
            <tr>
                <td>$clienticon</td>
                <td>$notes</td>
                <td>" . $count . ' time' . plural($count) . '</td>
            </tr>';
    }
}
$HTMLOUT .= main_table($body, $heading);

if ($user['class'] >= UC_MAX) {
    $HTMLOUT .= '
    <h2>' . _('Add an achievement to list.') . "</h2>
    <form method='post' action='achievementlist.php' enctype='multipart/form-data' accept-charset='utf-8'>" . main_table("
            <tr>
                <td class='w-15'>" . _('Achievement Name') . "</td>
                <td><input class='w-100' type='text' name='achievname'></td>
            </tr>
            <tr>
                <td>" . _('AchievIcon') . "</td>
                <td><textarea class='w-100' rows='3' name='clienticon'></textarea></td>
            </tr>
            <tr>
                <td>" . _('Description') . "</td>
                <td><textarea class='w-100' rows='6' name='notes'></textarea></td>
            </tr>
            <tr>
                <td colspan='2' class='has-text-centered'>
                    <input type='submit' name='okay' value='" . _('Add Me') . "!' class='button is-small'>
                </td>
            </tr>") . '
    </form>';
}
$title = _('Achievement List');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT, 'has-text-centered') . stdfoot();
