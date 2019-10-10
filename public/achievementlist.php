<?php

declare(strict_types = 1);

use Pu239\Achievementlist;

require_once __DIR__ . '/../include/bittorrent.php';
require_once INCL_DIR . 'function_users.php';
require_once INCL_DIR . 'function_html.php';
$user = check_user_status();
global $container, $site_config;

$achievementlist = $container->get(Achievementlist::class);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user['class'] >= UC_MAX) {
    $values = [
        'achievename' => htmlsafechars($_POST['achievename']),
        'notes' => htmlsafechars($_POST['notes']),
        'clienticon' => htmlsafechars($_POST['clienticon']),
    ];
    $achievementlist->add($values);
    $message = _fe('A New achievment has been added. Achievement: [{0}]', htmlsafechars($_POST['achievename']));
}
$res = sql_query('SELECT a1.*, (SELECT COUNT(a2.id) FROM achievements AS a2 WHERE a2.achievement = a1.achievename) AS count FROM achievementlist AS a1 ORDER BY a1.id') or sqlerr(__FILE__, __LINE__);
$HTMLOUT .= '<h1>' . _('Achievements List') . '</h1>';
if (mysqli_num_rows($res) === 0) {
    $HTMLOUT .= main_div('<div class="has-text-centered padding20">' . _('There are currently no achievements added to the list!<br>The staff has been slacking') . '!</div>', 'bottom20');
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
            $clienticon = "<img src='{$site_config['paths']['images_baseurl']}achievements/" . htmlsafechars($arr['clienticon']) . "' class='tooltipper' title='" . htmlsafechars($arr['achievename']) . "' alt='" . htmlsafechars($arr['achievename']) . "'>";
        }
        $body .= "
            <tr>
                <td>$clienticon</td>
                <td>$notes</td>
                <td>" . _pfe('{0} time', '{0} times', $count) . '</td>
            </tr>';
    }
    $HTMLOUT .= main_table($body, $heading);
}

if ($user['class'] >= UC_MAX) {
    $HTMLOUT .= '
    <h2>' . _('Add an achievement to list.') . "</h2>
    <form method='post' action='achievementlist.php' enctype='multipart/form-data' accept-charset='utf-8'>" . main_table("
            <tr>
                <td class='w-15'>" . _('Achievement Name') . "</td>
                <td><input class='w-100' type='text' name='achievename'></td>
            </tr>
            <tr>
                <td>" . _('Achievement Icon') . "</td>
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
$title = _('Achievements List');
$breadcrumbs = [
    "<a href='{$_SERVER['PHP_SELF']}'>$title</a>",
];
echo stdhead($title, [], 'page-wrapper', $breadcrumbs) . wrapper($HTMLOUT, 'has-text-centered') . stdfoot();
